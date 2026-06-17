<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\ScheduleEvent;
use App\Models\User;
use App\Models\Group;
use App\Models\ProjectGroup;
use App\Services\OutlookCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class FacilityReservationController extends Controller
{
    /**
     * 施設予約一覧を表示
     */
    public function index(Request $request): Response
    {
        $query = ScheduleEvent::with(['facility', 'participants']);

        // 検索条件
        if ($request->has('facility_id') && $request->facility_id) {
            $query->where('facility_id', $request->facility_id);
        }

        if ($request->has('date') && $request->date) {
            $query->where('date', $request->date);
        }

        $reservations = $query->orderBy('date', 'desc')
            ->orderBy('start_datetime', 'desc')
            ->paginate(20)
            ->withQueryString();

        $facilities = Facility::orderBy('name')
            ->get();

        return Inertia::render('Admin/FacilityReservations/Index', [
            'reservations' => $reservations,
            'facilities' => $facilities,
            'filters' => $request->only(['facility_id', 'date']),
        ]);
    }

    /**
     * 施設予約登録画面を表示
     */
    public function create(): Response
    {
        $facilities = Facility::orderBy('name')
            ->get();

        $staffMembers = User::where('del_flg', 0)
            ->orderBy('name')
            ->get();

        $groups = Group::orderBy('name')->get();

        $projectGroups = ProjectGroup::with('users')->orderBy('name')->get();

        return Inertia::render('Admin/FacilityReservations/Create', [
            'facilities' => $facilities,
            'staffMembers' => $staffMembers,
            'groups' => $groups,
            'projectGroups' => $projectGroups,
        ]);
    }

    /**
     * 施設予約を登録
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'title' => 'required|string|max:500',
            'start_datetime' => 'required|date_format:Y-m-d H:i',
            'end_datetime' => 'required|date_format:Y-m-d H:i|after:start_datetime',
            'badge' => 'nullable|string|max:100',
            'description_url' => 'nullable|url|max:500',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            // 日付と時刻を分離
            $startDatetime = $validated['start_datetime'];
            $endDatetime = $validated['end_datetime'];

            $date = explode(' ', $startDatetime)[0];
            $startTime = explode(' ', $startDatetime)[1];
            $endTime = explode(' ', $endDatetime)[1];

            $facility = Facility::find($validated['facility_id']);
            $outlook = app(OutlookCalendarService::class);

            // 重複チェック（Outlook会議室の予定とリアルタイム照合）
            if ($facility && $facility->outlook_resource_email
                && $outlook->hasConflict($facility, $date, $startTime, $endTime)) {
                DB::rollBack();
                return Redirect::back()->withInput()
                    ->withErrors(['facility_conflict' => '指定した時間は予約を入れることができません。最新の予約状況に更新しましたので、空き時間を確認して再度選択してください。']);
            }

            // ScheduleEventを作成
            $scheduleEvent = ScheduleEvent::create([
                'facility_id' => $validated['facility_id'],
                'date' => $date,
                'title' => $validated['title'],
                'start_datetime' => $startTime,
                'end_datetime' => $endTime,
                'badge' => $validated['badge'] ?? null,
                'description_url' => $validated['description_url'] ?? null,
                'status' => 1,
            ]);

            // タイトルを[id]タイトル名の形式に更新
            $scheduleEvent->update([
                'title' => '[' . $scheduleEvent->id . ']' . $validated['title']
            ]);

            // 参加者を追加
            if (!empty($validated['participants'])) {
                $scheduleEvent->participants()->attach($validated['participants']);
            }

            // Outlook会議室カレンダーへ書き込み（失敗してもDB予約は成立させる）
            if ($facility && $facility->outlook_resource_email) {
                try {
                    $outlookId = $outlook->createEvent($facility, $scheduleEvent->fresh());
                    if ($outlookId) {
                        $scheduleEvent->update(['outlook_event_id' => $outlookId]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Outlook予定作成に失敗', [
                        'schedule_event_id' => $scheduleEvent->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            return Redirect::route('admin.facility-reservations.index')
                ->with('success', '施設予約を登録しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with('error', '施設予約の登録に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * 施設予約詳細を表示
     */
    public function show(ScheduleEvent $facilityReservation): Response
    {
        $facilityReservation->load(['facility', 'participants']);

        return Inertia::render('Admin/FacilityReservations/Show', [
            'reservation' => $facilityReservation,
        ]);
    }

    /**
     * 施設予約編集画面を表示
     */
    public function edit(ScheduleEvent $facilityReservation): Response
    {
        $facilityReservation->load(['facility', 'participants']);

        $facilities = Facility::orderBy('name')
            ->get();

        $staffMembers = User::where('del_flg', 0)
            ->orderBy('name')
            ->get();

        $groups = Group::orderBy('name')->get();

        $projectGroups = ProjectGroup::with('users')->orderBy('name')->get();

        return Inertia::render('Admin/FacilityReservations/Edit', [
            'reservation' => $facilityReservation,
            'facilities' => $facilities,
            'staffMembers' => $staffMembers,
            'groups' => $groups,
            'projectGroups' => $projectGroups,
        ]);
    }

    /**
     * 施設予約を更新
     */
    public function update(Request $request, ScheduleEvent $facilityReservation)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'title' => 'required|string|max:500',
            'start_datetime' => 'required|date_format:Y-m-d H:i',
            'end_datetime' => 'required|date_format:Y-m-d H:i|after:start_datetime',
            'badge' => 'nullable|string|max:100',
            'description_url' => 'nullable|url|max:500',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            // 日付と時刻を分離
            $startDatetime = $validated['start_datetime'];
            $endDatetime = $validated['end_datetime'];

            $date = explode(' ', $startDatetime)[0];
            $startTime = explode(' ', $startDatetime)[1];
            $endTime = explode(' ', $endDatetime)[1];

            // IDを除いたタイトルを取得
            $originalTitle = $validated['title'];
            if (preg_match('/^\[(\d+)\](.+)$/', $originalTitle, $matches)) {
                $originalTitle = $matches[2];
            }

            // 更新前の情報を保持
            $oldFacilityId = $facilityReservation->facility_id;
            $oldOutlookId = $facilityReservation->outlook_event_id;
            $oldFacility = Facility::find($oldFacilityId);

            $newFacility = Facility::find($validated['facility_id']);
            $outlook = app(OutlookCalendarService::class);

            // 重複チェック（自分自身は除外）
            if ($newFacility && $newFacility->outlook_resource_email
                && $outlook->hasConflict($newFacility, $date, $startTime, $endTime, $oldOutlookId)) {
                DB::rollBack();
                return Redirect::back()->withInput()
                    ->withErrors(['facility_conflict' => '指定した時間は予約を入れることができません。最新の予約状況に更新しましたので、空き時間を確認して再度選択してください。']);
            }

            // ScheduleEventを更新
            $facilityReservation->update([
                'facility_id' => $validated['facility_id'],
                'date' => $date,
                'title' => '[' . $facilityReservation->id . ']' . $originalTitle,
                'start_datetime' => $startTime,
                'end_datetime' => $endTime,
                'badge' => $validated['badge'] ?? null,
                'description_url' => $validated['description_url'] ?? null,
            ]);

            // 参加者を更新
            if (isset($validated['participants'])) {
                $facilityReservation->participants()->sync($validated['participants']);
            } else {
                $facilityReservation->participants()->detach();
            }

            // Outlook会議室カレンダーへ反映（失敗してもDB更新は成立させる）
            try {
                $sameFacility = ($oldFacilityId == $validated['facility_id']);
                if ($newFacility && $newFacility->outlook_resource_email) {
                    if ($oldOutlookId && $sameFacility) {
                        // 同じ会議室 → 既存予定を更新
                        $outlook->updateEvent($newFacility, $facilityReservation->fresh());
                    } else {
                        // 会議室変更 or 未連携 → 旧予定を削除して新規作成
                        if ($oldOutlookId && $oldFacility && $oldFacility->outlook_resource_email) {
                            $tmp = clone $facilityReservation;
                            $tmp->outlook_event_id = $oldOutlookId;
                            $outlook->deleteEvent($oldFacility, $tmp);
                        }
                        $newId = $outlook->createEvent($newFacility, $facilityReservation->fresh());
                        $facilityReservation->update(['outlook_event_id' => $newId]);
                    }
                } elseif ($oldOutlookId && $oldFacility && $oldFacility->outlook_resource_email) {
                    // 変更後の会議室がOutlook未連携 → 旧予定を削除
                    $tmp = clone $facilityReservation;
                    $tmp->outlook_event_id = $oldOutlookId;
                    $outlook->deleteEvent($oldFacility, $tmp);
                    $facilityReservation->update(['outlook_event_id' => null]);
                }
            } catch (\Throwable $e) {
                Log::error('Outlook予定更新に失敗', [
                    'schedule_event_id' => $facilityReservation->id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            return Redirect::route('admin.facility-reservations.index')
                ->with('success', '施設予約を更新しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with('error', '施設予約の更新に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * 施設予約を削除
     */
    public function destroy(ScheduleEvent $facilityReservation)
    {
        // Outlook会議室カレンダーからも削除（失敗してもDB削除は実行）
        if ($facilityReservation->outlook_event_id
            && $facilityReservation->facility
            && $facilityReservation->facility->outlook_resource_email) {
            try {
                app(OutlookCalendarService::class)
                    ->deleteEvent($facilityReservation->facility, $facilityReservation);
            } catch (\Throwable $e) {
                Log::error('Outlook予定削除に失敗', [
                    'schedule_event_id' => $facilityReservation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $facilityReservation->delete();

        return Redirect::route('admin.facility-reservations.index')
            ->with('success', '施設予約を削除しました。');
    }
}
