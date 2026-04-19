<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\ScheduleEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FacilityTimelineController extends Controller
{
    public function index(Request $request): Response
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        $facilities = Facility::orderBy('id')->get();

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
                    ];
                })->values(),
            ];
        });

        return Inertia::render('FacilityTimeline', [
            'date' => $date,
            'facilities' => $facilityData,
        ]);
    }
}
