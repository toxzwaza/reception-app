<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LanRelayEvent;
use App\Services\LanRelay\LanRelayEventRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 受付横断連携：中継役（音楽サーバー）向けAPI（要件書 §13.1）
 */
class LanRelayController extends Controller
{
    /**
     * GET /api/lan-relay/events?after=<id>&limit=<1..50>
     * after 省略時は events を空で返し latest_id だけ返す（中継役の初回起動用）
     * settings.chime_volume は管理画面で設定したチャイム音量（中継役が毎回取り込む）
     */
    public function events(Request $request): JsonResponse
    {
        $validated = $this->validateJson($request, [
            'after' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $now = now();
        $latestId = (int) (LanRelayEvent::max('id') ?? 0);

        $events = [];
        if (isset($validated['after'])) {
            $events = LanRelayEvent::where('id', '>', (int) $validated['after'])
                ->orderBy('id')
                ->limit((int) ($validated['limit'] ?? 20))
                ->get()
                ->map(fn (LanRelayEvent $e) => [
                    'id' => $e->id,
                    'type' => $e->type,
                    'terminal' => $e->terminal,
                    'camera' => $e->camera,
                    'message' => $e->message,
                    'occurred_at' => $e->occurred_at->toIso8601String(),
                    'age_sec' => round(max(0, $now->getPreciseTimestamp(3) - $e->occurred_at->getPreciseTimestamp(3)) / 1000, 1),
                    'suppressed' => (bool) $e->suppressed,
                    'suppress_reason' => $e->suppress_reason,
                ])
                ->values()
                ->all();
        }

        return response()->json([
            'latest_id' => $latestId,
            'server_time' => $now->toIso8601String(),
            'events' => $events,
            'settings' => [
                'chime_volume' => LanRelayEventRecorder::chimeVolume(),
            ],
        ]);
    }

    /**
     * POST /api/lan-relay/events/{id}/ack {"display":"ok|fail|skip","chime":"ok|fail|skip","detail":"…"}
     * 同じIDへの2回目以降は上書き
     */
    public function ack(Request $request, int $id): JsonResponse
    {
        $validated = $this->validateJson($request, [
            'display' => 'required|in:ok,fail,skip',
            'chime' => 'required|in:ok,fail,skip',
            'detail' => 'nullable|string',
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $event = LanRelayEvent::find($id);
        if (!$event) {
            return response()->json(['message' => 'Not Found.'], 404);
        }

        $detail = isset($validated['detail']) ? mb_substr($validated['detail'], 0, 200) : null;

        $event->update([
            'ack_display' => $validated['display'],
            'ack_chime' => $validated['chime'],
            'ack_detail' => $detail === '' ? null : $detail,
            'acked_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * 中継役は Accept ヘッダに依らず JSON で受け取れるよう、検証エラーは常に 422 JSON で返す
     */
    private function validateJson(Request $request, array $rules): array|JsonResponse
    {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        return $validator->validated();
    }
}
