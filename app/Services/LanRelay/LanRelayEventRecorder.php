<?php

namespace App\Services\LanRelay;

use App\Models\Appointment;
use App\Models\Group;
use App\Models\LanRelayEvent;
use App\Models\ReceptionTerminal;
use App\Models\ScheduleEvent;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * 来客イベントの記録（受付横断連携 §6.2）。
 *
 * 受付の4経路（QR・受付番号・本日の予定・アポなし・面接）で、受付データ保存の直後・
 * Teams送信の前に1回だけ呼ぶ。通知設定の有無とは無関係に1操作=1件を記録する。
 * 記録に失敗しても受付本体は止めない（例外はログに残して null を返す）。
 */
class LanRelayEventRecorder
{
    /** 端末番号を受け取るリクエストのキー（ボディ or クエリ）とヘッダ */
    public const INPUT_KEY = 'terminal';
    public const HEADER = 'X-Reception-Terminal';

    /** 既定カメラを保存する Setting のキー */
    public const DEFAULT_CAMERA_SETTING = 'lan_relay_default_camera';

    public const CHIME_VOLUME_SETTING = 'lan_relay_chime_volume';

    /** 管理画面からのテスト送信で表示文字の先頭に付ける印 */
    public const TEST_PREFIX = '【テスト】';

    /** 番号なし（未設定・不正値）の端末をまとめて扱う単位 */
    private const NO_TERMINAL_KEY = 'none';

    /**
     * アポイントあり（QR・受付番号）：アポイントの会社名・担当者名で記録
     */
    public function recordAppointment(Request $request, $appointmentId): ?LanRelayEvent
    {
        return $this->record($request, LanRelayEvent::TYPE_APPOINTMENT, function () use ($appointmentId) {
            $appointment = Appointment::with('staffMember')->find($appointmentId);

            return [$appointment->company_name ?? null, $appointment->staffMember->name ?? null];
        });
    }

    /**
     * アポイントあり（本日の予定から到着通知）：紐づくアポイントがあれば会社名・担当者、
     * なければ予定の主催者を担当者として記録
     */
    public function recordScheduleArrival(Request $request, ScheduleEvent $scheduleEvent): ?LanRelayEvent
    {
        return $this->record($request, LanRelayEvent::TYPE_APPOINTMENT, function () use ($scheduleEvent) {
            $appointment = $scheduleEvent->appointment_id
                ? Appointment::with('staffMember')->find($scheduleEvent->appointment_id)
                : null;

            return [
                $appointment->company_name ?? null,
                ($appointment->staffMember->name ?? null) ?: $scheduleEvent->organizer_name,
            ];
        });
    }

    /**
     * アポイントなし：入力された会社名と選択部署名で記録（訪問者名は記録しない）
     */
    public function recordWalkIn(Request $request, ?string $companyName, $groupId): ?LanRelayEvent
    {
        return $this->record($request, LanRelayEvent::TYPE_WALK_IN, function () use ($companyName, $groupId) {
            return [$companyName, Group::find($groupId)->name ?? null];
        });
    }

    /**
     * 面接：会社名・氏名とも記録しない
     */
    public function recordInterview(Request $request): ?LanRelayEvent
    {
        return $this->record($request, LanRelayEvent::TYPE_INTERVIEW, fn () => [null, null]);
    }

    /**
     * 管理画面からのテスト送信：表示文字の先頭に【テスト】を付けて記録する。
     * 頻度制限・面接2分抑止は掛けない（端末が無効なら実際の受付と同じく抑止）。Teams には送らない。
     */
    public function recordTest(string $type, ?int $terminal, ?string $companyName, ?string $staffName): LanRelayEvent
    {
        $registered = $terminal === null
            ? null
            : ReceptionTerminal::where('number', $terminal)->first();
        $isInterview = $type === LanRelayEvent::TYPE_INTERVIEW;

        return $this->create([
            'type' => $type,
            'terminal' => $terminal,
            'camera' => $registered !== null ? $registered->camera_src : self::defaultCamera(),
            'company_name' => $isInterview ? null : self::nullIfEmpty($companyName),
            'staff_name' => $isInterview ? null : self::nullIfEmpty($staffName),
            'message' => self::truncate(
                self::TEST_PREFIX . self::buildMessage($type, $companyName, $staffName),
                (int) config('lan_relay.message_max_length', 60)
            ),
        ], $registered !== null && !$registered->enabled ? LanRelayEvent::REASON_TERMINAL_DISABLED : null);
    }

    /**
     * リクエストから端末番号（1〜99）を取り出す。未設定・不正な値は null。
     */
    public static function terminalFromRequest(Request $request): ?int
    {
        $raw = $request->input(self::INPUT_KEY, $request->header(self::HEADER));
        if (is_int($raw)) {
            $raw = (string) $raw;
        }
        if (!is_string($raw) || !preg_match('/^[0-9]{1,2}$/', $raw)) {
            return null;
        }
        $number = (int) $raw;

        return ($number >= 1 && $number <= 99) ? $number : null;
    }

    /**
     * チャイム音量（0〜100）。管理画面で保存した値、無ければ config の値
     */
    public static function chimeVolume(): int
    {
        try {
            $saved = Setting::get(self::CHIME_VOLUME_SETTING);
        } catch (\Throwable $e) {
            $saved = null;
        }
        $volume = is_numeric($saved) ? (int) $saved : (int) config('lan_relay.chime_volume', 60);

        return max(0, min(100, $volume));
    }

    /**
     * 既定カメラ（管理画面の設定 → config の順。許可リスト外なら config 値）
     */
    public static function defaultCamera(): string
    {
        $configured = (string) config('lan_relay.default_camera', 'nvr1');
        try {
            $saved = Setting::get(self::DEFAULT_CAMERA_SETTING);
        } catch (\Throwable $e) {
            $saved = null;
        }

        return (is_string($saved) && array_key_exists($saved, config('lan_relay.cameras', [])))
            ? $saved
            : $configured;
    }

    /**
     * ディスプレイに重ねる文字（§3・§13.1 の書式。60文字以内に切り詰め）
     */
    public static function buildMessage(string $type, ?string $companyName, ?string $staffName): string
    {
        $company = self::clean($companyName);
        $staff = self::clean($staffName);

        switch ($type) {
            case LanRelayEvent::TYPE_INTERVIEW:
                $message = '面接の方がご来社';
                break;
            case LanRelayEvent::TYPE_WALK_IN:
                $message = 'ご来客（アポイントなし）'
                    . ($company !== '' ? $company . ' 様' : '')
                    . ($staff !== '' ? ' → 部署：' . $staff : '');
                break;
            default:
                $message = 'ご来客（アポイントあり）'
                    . ($company !== '' ? $company . ' 様' : '')
                    . ($staff !== '' ? ' → 担当：' . $staff : '');
                break;
        }

        return self::truncate(trim($message), (int) config('lan_relay.message_max_length', 60));
    }

    /**
     * @param  callable():array{0:?string,1:?string}  $resolveNames  表示用の [会社名, 担当者名(部署名)]
     */
    private function record(Request $request, string $type, callable $resolveNames): ?LanRelayEvent
    {
        try {
            [$companyName, $staffName] = $resolveNames();
            $terminal = self::terminalFromRequest($request);
            $terminalKey = $terminal === null ? self::NO_TERMINAL_KEY : (string) $terminal;

            $registered = $terminal === null
                ? null
                : ReceptionTerminal::where('number', $terminal)->first();

            $disabled = $registered !== null && !$registered->enabled;
            $camera = $registered !== null ? $registered->camera_src : self::defaultCamera();

            // 頻度制限：同じ端末単位で 10秒に3回まで（超えた分は記録するが抑止＝鳴らさない）
            $rateLimited = !$disabled && $this->hitRateLimit($terminalKey);

            $attributes = [
                'type' => $type,
                'terminal' => $terminal,
                'camera' => $camera,
                'company_name' => $type === LanRelayEvent::TYPE_INTERVIEW ? null : self::nullIfEmpty($companyName),
                'staff_name' => $type === LanRelayEvent::TYPE_INTERVIEW ? null : self::nullIfEmpty($staffName),
                'message' => self::buildMessage($type, $companyName, $staffName),
            ];

            if ($disabled) {
                return $this->create($attributes, LanRelayEvent::REASON_TERMINAL_DISABLED);
            }

            if ($type === LanRelayEvent::TYPE_INTERVIEW) {
                return $this->createInterview($attributes, $terminalKey, $rateLimited);
            }

            return $this->create($attributes, $rateLimited ? LanRelayEvent::REASON_RATE_LIMITED : null);
        } catch (\Throwable $e) {
            Log::error('来客イベントの記録に失敗しました（受付処理は継続）', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 面接：同じ端末単位で最後に鳴らした面接から2分以内なら抑止。
     * 端末単位のロック行を SELECT ... FOR UPDATE で取り、判定と記録を直列化する
     * （同時押しでも抑止しないイベントは1件だけ）。
     */
    private function createInterview(array $attributes, string $terminalKey, bool $rateLimited): LanRelayEvent
    {
        DB::table('lan_relay_cooldowns')->insertOrIgnore([
            'terminal_key' => $terminalKey,
            'last_fired_at' => null,
        ]);

        return DB::transaction(function () use ($attributes, $terminalKey, $rateLimited) {
            $lock = DB::table('lan_relay_cooldowns')
                ->where('terminal_key', $terminalKey)
                ->lockForUpdate()
                ->first();

            $now = now();
            $cooldown = (int) config('lan_relay.interview_cooldown_seconds', 120);
            $inCooldown = $lock !== null
                && $lock->last_fired_at !== null
                && $now->lt(Carbon::parse($lock->last_fired_at)->addSeconds($cooldown));

            if ($inCooldown) {
                return $this->create($attributes, LanRelayEvent::REASON_INTERVIEW_COOLDOWN, $now);
            }
            if ($rateLimited) {
                return $this->create($attributes, LanRelayEvent::REASON_RATE_LIMITED, $now);
            }

            DB::table('lan_relay_cooldowns')
                ->where('terminal_key', $terminalKey)
                ->update(['last_fired_at' => $now->format('Y-m-d H:i:s.v')]);

            return $this->create($attributes, null, $now);
        });
    }

    private function create(array $attributes, ?string $suppressReason, $occurredAt = null): LanRelayEvent
    {
        return LanRelayEvent::create($attributes + [
            'occurred_at' => $occurredAt ?? now(),
            'suppressed' => $suppressReason !== null,
            'suppress_reason' => $suppressReason,
        ]);
    }

    /**
     * 頻度制限のカウントを1つ進め、上限を超えていれば true
     */
    private function hitRateLimit(string $terminalKey): bool
    {
        $key = 'lan-relay:terminal:' . $terminalKey;
        $max = (int) config('lan_relay.rate_limit.attempts', 3);
        $decay = (int) config('lan_relay.rate_limit.decay_seconds', 10);

        if (RateLimiter::tooManyAttempts($key, $max)) {
            return true;
        }
        RateLimiter::hit($key, $decay);

        return false;
    }

    private static function clean(?string $value): string
    {
        // 改行・制御文字を空白にして前後を詰める（帯は1行表示）
        return trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string) $value) ?? '');
    }

    private static function nullIfEmpty(?string $value): ?string
    {
        $value = self::clean($value);

        return $value === '' ? null : mb_substr($value, 0, 255);
    }

    /**
     * 最大文字数に切り詰める。ディスプレイ側（JS）の length でも超えないよう
     * UTF-16 の長さ（絵文字等は2）でも上限以内にする。
     */
    private static function truncate(string $text, int $max): string
    {
        $text = mb_substr($text, 0, $max);
        while ($text !== '' && strlen(mb_convert_encoding($text, 'UTF-16LE', 'UTF-8')) / 2 > $max) {
            $text = mb_substr($text, 0, mb_strlen($text) - 1);
        }

        return $text;
    }
}
