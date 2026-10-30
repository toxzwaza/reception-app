<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LanRelayEvent;
use App\Models\ReceptionTerminal;
use App\Models\Setting;
use App\Services\LanRelay\LanRelayEventRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 受付端末（端末番号・設置場所・全画面にするカメラ・有効/無効）と既定カメラの管理。
 * あわせて直近の来客イベントと中継役の受け取り結果（ack）を確認できる。
 */
class ReceptionTerminalController extends Controller
{
    /**
     * 受付端末一覧＋既定カメラ＋直近の来客イベント
     */
    public function index(): Response
    {
        $events = LanRelayEvent::orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn (LanRelayEvent $e) => [
                'id' => $e->id,
                'type' => $e->type,
                'terminal' => $e->terminal,
                'camera' => $e->camera,
                'message' => $e->message,
                'occurred_at' => $e->occurred_at?->format('Y-m-d H:i:s'),
                'suppressed' => $e->suppressed,
                'suppress_reason' => $e->suppress_reason,
                'ack_display' => $e->ack_display,
                'ack_chime' => $e->ack_chime,
                'ack_detail' => $e->ack_detail,
                'acked_at' => $e->acked_at?->format('Y-m-d H:i:s'),
            ]);

        return Inertia::render('Admin/ReceptionTerminals/Index', [
            'terminals' => ReceptionTerminal::orderBy('number')->get(),
            'cameras' => $this->cameraOptions(),
            'defaultCamera' => LanRelayEventRecorder::defaultCamera(),
            'chimeVolume' => LanRelayEventRecorder::chimeVolume(),
            'events' => $events,
        ]);
    }

    /**
     * 受付端末登録画面
     */
    public function create(): Response
    {
        return Inertia::render('Admin/ReceptionTerminals/Create', [
            'cameras' => $this->cameraOptions(),
        ]);
    }

    /**
     * 受付端末を登録
     */
    public function store(Request $request)
    {
        ReceptionTerminal::create($this->validated($request));

        return Redirect::route('admin.reception-terminals.index')
            ->with('success', '受付端末を登録しました。');
    }

    /**
     * 受付端末編集画面
     */
    public function edit(ReceptionTerminal $receptionTerminal): Response
    {
        return Inertia::render('Admin/ReceptionTerminals/Edit', [
            'terminal' => $receptionTerminal,
            'cameras' => $this->cameraOptions(),
        ]);
    }

    /**
     * 受付端末を更新
     */
    public function update(Request $request, ReceptionTerminal $receptionTerminal)
    {
        $receptionTerminal->update($this->validated($request, $receptionTerminal));

        return Redirect::route('admin.reception-terminals.index')
            ->with('success', '受付端末を更新しました。');
    }

    /**
     * 既定カメラ（端末が未設定・未登録のときに全画面にするカメラ）を更新
     */
    public function updateDefaultCamera(Request $request)
    {
        $validated = $request->validate([
            'default_camera' => ['required', 'string', Rule::in(array_keys(config('lan_relay.cameras', [])))],
        ], [], [
            'default_camera' => '既定カメラ',
        ]);

        Setting::put(LanRelayEventRecorder::DEFAULT_CAMERA_SETTING, $validated['default_camera']);

        return Redirect::route('admin.reception-terminals.index')
            ->with('success', '既定カメラを更新しました。');
    }

    /**
     * チャイム音量（音楽サーバーで鳴らす来客チャイムの音量）を更新。中継役は次の取得（約2秒後）から反映
     */
    public function updateChimeVolume(Request $request)
    {
        $validated = $request->validate([
            'chime_volume' => 'required|integer|min:0|max:100',
        ], [], [
            'chime_volume' => 'チャイム音量',
        ]);

        Setting::put(LanRelayEventRecorder::CHIME_VOLUME_SETTING, (string) $validated['chime_volume']);

        return Redirect::route('admin.reception-terminals.index')
            ->with('success', 'チャイム音量を更新しました。');
    }

    private function validated(Request $request, ?ReceptionTerminal $terminal = null): array
    {
        $validated = $request->validate([
            'number' => [
                'required', 'integer', 'min:1', 'max:99',
                Rule::unique('reception_terminals', 'number')->ignore($terminal?->id),
            ],
            'name' => 'required|string|max:100',
            'camera_src' => ['required', 'string', Rule::in(array_keys(config('lan_relay.cameras', [])))],
            'enabled' => 'boolean',
        ], [], [
            'number' => '端末番号',
            'name' => '設置場所名',
            'camera_src' => '全画面にするカメラ',
            'enabled' => '有効',
        ]);
        $validated['enabled'] = (bool) ($validated['enabled'] ?? false);

        return $validated;
    }

    /**
     * カメラの選択肢（許可リスト）
     */
    private function cameraOptions(): array
    {
        return collect(config('lan_relay.cameras', []))
            ->map(fn ($label, $src) => ['src' => $src, 'label' => $label])
            ->values()
            ->all();
    }
}
