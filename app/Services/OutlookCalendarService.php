<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\ScheduleEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutlookCalendarService
{
    private const BASE_URL = 'https://graph.microsoft.com/v1.0';
    private const TOKEN_CACHE_KEY = 'outlook_graph_token';
    private const TOKEN_TTL = 3300; // 55分（トークン有効期限60分の手前）

    private string $tenantId;
    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        $this->tenantId = config('services.microsoft_graph.tenant_id') ?? '';
        $this->clientId = config('services.microsoft_graph.client_id') ?? '';
        $this->clientSecret = config('services.microsoft_graph.client_secret') ?? '';
    }

    public function isConfigured(): bool
    {
        return !empty($this->tenantId) && !empty($this->clientId) && !empty($this->clientSecret);
    }

    private function getToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, self::TOKEN_TTL, function () {
            $response = Http::asForm()->post(
                "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token",
                [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'https://graph.microsoft.com/.default',
                ]
            );

            if (!$response->successful()) {
                Log::error('Graph API token acquisition failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('Failed to acquire Graph API token');
            }

            return $response->json('access_token');
        });
    }

    private function graphGet(string $path, array $query = []): array
    {
        $response = Http::withToken($this->getToken())
            ->withHeaders(['Prefer' => 'outlook.timezone="Tokyo Standard Time"'])
            ->get(self::BASE_URL . $path, $query);

        if ($response->status() === 401) {
            Cache::forget(self::TOKEN_CACHE_KEY);
            $response = Http::withToken($this->getToken())
                ->withHeaders(['Prefer' => 'outlook.timezone="Tokyo Standard Time"'])
                ->get(self::BASE_URL . $path, $query);
        }

        if ($response->status() === 429) {
            $retryAfter = (int) ($response->header('Retry-After') ?: 10);
            Log::warning('Graph API rate limited', ['retry_after' => $retryAfter]);
            sleep(min($retryAfter, 30));
            $response = Http::withToken($this->getToken())
                ->withHeaders(['Prefer' => 'outlook.timezone="Tokyo Standard Time"'])
                ->get(self::BASE_URL . $path, $query);
        }

        if (!$response->successful()) {
            Log::error('Graph API GET failed', [
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        }

        return $response->json();
    }

    public function listRooms(): array
    {
        $data = $this->graphGet('/places/microsoft.graph.room');
        return $data['value'] ?? [];
    }

    public function getCalendarView(string $resourceEmail, string $startDateTime, string $endDateTime): array
    {
        $allEvents = [];
        $path = "/users/{$resourceEmail}/calendarView";
        $query = [
            'startDateTime' => $startDateTime,
            'endDateTime' => $endDateTime,
            '$select' => 'id,subject,start,end,organizer,location,bodyPreview,isAllDay',
            '$orderby' => 'start/dateTime',
            '$top' => 100,
        ];

        $data = $this->graphGet($path, $query);
        $allEvents = array_merge($allEvents, $data['value'] ?? []);

        while (!empty($data['@odata.nextLink'])) {
            $nextUrl = str_replace(self::BASE_URL, '', $data['@odata.nextLink']);
            $data = $this->graphGet($nextUrl);
            $allEvents = array_merge($allEvents, $data['value'] ?? []);
        }

        return $allEvents;
    }

    public function syncFacility(Facility $facility, string $startDate, string $endDate): int
    {
        if (empty($facility->outlook_resource_email)) {
            return 0;
        }

        $startDateTime = "{$startDate}T00:00:00";
        $endDateTime = "{$endDate}T23:59:59";

        $events = $this->getCalendarView(
            $facility->outlook_resource_email,
            $startDateTime,
            $endDateTime
        );

        if (empty($events)) {
            return 0;
        }

        $syncedOutlookIds = [];
        $upsertCount = 0;

        foreach ($events as $event) {
            $outlookEventId = $event['id'];
            $syncedOutlookIds[] = $outlookEventId;

            $start = Carbon::parse($event['start']['dateTime']);
            $end = Carbon::parse($event['end']['dateTime']);

            $isAllDay = $event['isAllDay'] ?? false;

            $date = $start->format('Y-m-d');
            $startTime = $isAllDay ? '00:00' : $start->format('H:i');
            $endTime = $isAllDay ? '23:59' : $end->format('H:i');

            $subject = $event['subject'] ?? '(件名なし)';
            $organizer = $event['organizer']['emailAddress']['name'] ?? '';
            $title = $organizer ? "{$subject}（{$organizer}）" : $subject;

            $existing = ScheduleEvent::where('outlook_event_id', $outlookEventId)->first();

            if ($existing) {
                $existing->update([
                    'date' => $date,
                    'title' => mb_substr($title, 0, 500),
                    'start_datetime' => $startTime,
                    'end_datetime' => $endTime,
                ]);
            } else {
                ScheduleEvent::create([
                    'facility_id' => $facility->id,
                    'date' => $date,
                    'title' => mb_substr($title, 0, 500),
                    'start_datetime' => $startTime,
                    'end_datetime' => $endTime,
                    'badge' => null,
                    'description_url' => null,
                    'status' => 1,
                    'outlook_event_id' => $outlookEventId,
                ]);
            }

            $upsertCount++;
        }

        // Outlook側で削除された予定をDBからも削除
        $deleted = ScheduleEvent::where('facility_id', $facility->id)
            ->whereNotNull('outlook_event_id')
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotIn('outlook_event_id', $syncedOutlookIds)
            ->delete();

        if ($deleted > 0) {
            Log::info("Outlook sync: deleted {$deleted} removed events", [
                'facility' => $facility->name,
            ]);
        }

        return $upsertCount;
    }

    public function syncAllFacilities(int $days = 30): array
    {
        $facilities = Facility::whereNotNull('outlook_resource_email')
            ->where('outlook_resource_email', '!=', '')
            ->get();

        if ($facilities->isEmpty()) {
            Log::info('Outlook sync: no facilities with outlook_resource_email configured');
            return ['total' => 0, 'facilities' => 0, 'errors' => []];
        }

        $startDate = now()->format('Y-m-d');
        $endDate = now()->addDays($days)->format('Y-m-d');

        $totalEvents = 0;
        $errors = [];

        foreach ($facilities as $facility) {
            try {
                $count = $this->syncFacility($facility, $startDate, $endDate);
                $totalEvents += $count;
                Log::info("Outlook sync: {$facility->name} => {$count} events");
            } catch (\Throwable $e) {
                $errors[] = [
                    'facility' => $facility->name,
                    'error' => $e->getMessage(),
                ];
                Log::error("Outlook sync failed for {$facility->name}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'total' => $totalEvents,
            'facilities' => $facilities->count(),
            'errors' => $errors,
        ];
    }

    // ============================================================
    // 書き込み系（DB → Outlook）
    // ============================================================

    private function graphSend(string $method, string $path, array $body = []): ?array
    {
        $send = function () use ($method, $path, $body) {
            $req = Http::withToken($this->getToken())
                ->withHeaders(['Prefer' => 'outlook.timezone="Tokyo Standard Time"']);
            return match ($method) {
                'POST' => $req->post(self::BASE_URL . $path, $body),
                'PATCH' => $req->patch(self::BASE_URL . $path, $body),
                'DELETE' => $req->delete(self::BASE_URL . $path),
                default => throw new \InvalidArgumentException("Unsupported method: {$method}"),
            };
        };

        $response = $send();

        if ($response->status() === 401) {
            Cache::forget(self::TOKEN_CACHE_KEY);
            $response = $send();
        }

        if ($response->status() === 429) {
            $retryAfter = (int) ($response->header('Retry-After') ?: 10);
            Log::warning('Graph API rate limited (write)', ['retry_after' => $retryAfter]);
            sleep(min($retryAfter, 30));
            $response = $send();
        }

        if (!$response->successful()) {
            Log::error('Graph API write failed', [
                'method' => $method,
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        // DELETE は 204 No Content（ボディなし）
        return $response->body() ? $response->json() : [];
    }

    /**
     * 予約日時から Graph 用の dateTime 文字列を組み立てる。
     * ScheduleEvent は date="Y-m-d" / start_datetime="HH:MM" 形式。
     */
    private function buildEventBody(Facility $facility, ScheduleEvent $event): array
    {
        $startIso = $event->date instanceof Carbon
            ? $event->date->format('Y-m-d')
            : Carbon::parse($event->date)->format('Y-m-d');

        $body = [
            'subject' => mb_substr((string) $event->title, 0, 255),
            'start' => [
                'dateTime' => "{$startIso}T{$event->start_datetime}:00",
                'timeZone' => 'Tokyo Standard Time',
            ],
            'end' => [
                'dateTime' => "{$startIso}T{$event->end_datetime}:00",
                'timeZone' => 'Tokyo Standard Time',
            ],
            'body' => [
                'contentType' => 'text',
                'content' => '受付システムから登録された予約です。',
            ],
            // 場所を会議室名に設定
            'location' => [
                'displayName' => $facility->name,
            ],
        ];

        // 予約の参加者を attendees に設定（選択したユーザーのメールアドレス）
        $event->loadMissing('participants');
        $attendees = [];
        foreach ($event->participants as $user) {
            if (!empty($user->email)) {
                $attendees[] = [
                    'emailAddress' => [
                        'address' => $user->email,
                        'name' => $user->name,
                    ],
                    'type' => 'required',
                ];
            }
        }
        if (!empty($attendees)) {
            $body['attendees'] = $attendees;
        }

        return $body;
    }

    /**
     * 会議室カレンダーに予定を作成する。成功時は outlook_event_id を返す。
     */
    public function createEvent(Facility $facility, ScheduleEvent $event): ?string
    {
        if (!$this->isConfigured() || empty($facility->outlook_resource_email)) {
            return null;
        }

        $result = $this->graphSend(
            'POST',
            "/users/{$facility->outlook_resource_email}/events",
            $this->buildEventBody($facility, $event)
        );

        return $result['id'] ?? null;
    }

    /**
     * 会議室カレンダーの予定を更新する。
     */
    public function updateEvent(Facility $facility, ScheduleEvent $event): bool
    {
        if (!$this->isConfigured() || empty($facility->outlook_resource_email) || empty($event->outlook_event_id)) {
            return false;
        }

        $result = $this->graphSend(
            'PATCH',
            "/users/{$facility->outlook_resource_email}/events/{$event->outlook_event_id}",
            $this->buildEventBody($facility, $event)
        );

        return $result !== null;
    }

    /**
     * 会議室カレンダーから予定を削除する。
     */
    public function deleteEvent(Facility $facility, ScheduleEvent $event): bool
    {
        if (!$this->isConfigured() || empty($facility->outlook_resource_email) || empty($event->outlook_event_id)) {
            return false;
        }

        $result = $this->graphSend(
            'DELETE',
            "/users/{$facility->outlook_resource_email}/events/{$event->outlook_event_id}"
        );

        return $result !== null;
    }

    /**
     * 指定会議室の指定日時に、既存予約と重複があるか判定する（リアルタイム）。
     * $ignoreOutlookEventId を指定すると、その予定自身は除外する（更新時用）。
     */
    public function hasConflict(Facility $facility, string $date, string $startTime, string $endTime, ?string $ignoreOutlookEventId = null): bool
    {
        if (!$this->isConfigured() || empty($facility->outlook_resource_email)) {
            return false;
        }

        $events = $this->getCalendarView(
            $facility->outlook_resource_email,
            "{$date}T00:00:00",
            "{$date}T23:59:59"
        );

        $reqStart = Carbon::parse("{$date} {$startTime}");
        $reqEnd = Carbon::parse("{$date} {$endTime}");

        foreach ($events as $event) {
            if ($ignoreOutlookEventId && ($event['id'] ?? null) === $ignoreOutlookEventId) {
                continue;
            }
            if ($event['isAllDay'] ?? false) {
                return true;
            }
            $evStart = Carbon::parse($event['start']['dateTime']);
            $evEnd = Carbon::parse($event['end']['dateTime']);
            // 時間帯が重なるか（開始 < 相手終了 かつ 終了 > 相手開始）
            if ($reqStart->lt($evEnd) && $reqEnd->gt($evStart)) {
                return true;
            }
        }

        return false;
    }
}
