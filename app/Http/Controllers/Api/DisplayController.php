<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\ScheduleEvent;
use App\Services\OutlookCalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DisplayController extends Controller
{
    /**
     * 事務所ディスプレイ表示システム用：施設の当日予定を取得
     *
     * GET /api/display/facility-schedules?date=YYYY-MM-DD&facility_ids=1,2,3
     * date 省略時は当日、facility_ids 省略時は全施設を返す。
     */
    public function facilitySchedules(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
            'facility_ids' => ['nullable', 'string', 'regex:/^\d+(,\d+)*$/'],
        ]);

        $date = $validated['date'] ?? now()->format('Y-m-d');

        $facilities = Facility::orderBy('id')
            ->when(!empty($validated['facility_ids']), function ($query) use ($validated) {
                $query->whereIn('id', explode(',', $validated['facility_ids']));
            })
            ->get();

        // facility_ids 指定時は、指定された並び順のまま返す（ディスプレイの表示順に合わせる）
        if (!empty($validated['facility_ids'])) {
            $order = array_flip(explode(',', $validated['facility_ids']));
            $facilities = $facilities->sortBy(fn ($facility) => $order[$facility->id])->values();
        }

        // Outlook連携施設は、表示直前に最新の予定を取り込む（管理画面と同じ挙動）。
        // 失敗してもDBの内容で表示を続行する。
        foreach ($facilities as $facility) {
            if ($facility->outlook_resource_email) {
                try {
                    app(OutlookCalendarService::class)->syncFacility($facility, $date, $date);
                } catch (\Throwable $e) {
                    Log::warning('ディスプレイ表示用の施設予定同期に失敗（DB内容で表示）', [
                        'facility_id' => $facility->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $events = ScheduleEvent::where('date', $date)
            ->whereIn('facility_id', $facilities->pluck('id'))
            ->orderBy('start_datetime')
            ->get()
            ->groupBy('facility_id');

        $facilityData = $facilities->map(function ($facility) use ($events) {
            return [
                'id' => $facility->id,
                'name' => $facility->name,
                'events' => ($events->get($facility->id) ?? collect())->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => preg_replace('/^\[\d+\]/', '', $event->title),
                        'start' => $event->start_datetime,
                        'end' => $event->end_datetime,
                        'badge' => $event->badge,
                        'organizer_name' => $event->organizer_name,
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'date' => $date,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'facilities' => $facilityData,
        ]);
    }
}
