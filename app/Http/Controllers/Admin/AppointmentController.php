<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Facility;
use App\Models\ScheduleEvent;
use App\Models\UserSchedule;
use App\Models\Group;
use App\Models\ProjectGroup;
use App\Mail\AppointmentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    /**
     * アポイント一覧を表示
     */
    public function index(Request $request): Response
    {
        $query = Appointment::with('staffMember')
            ->orderBy('visit_date', 'desc')
            ->orderBy('visit_time', 'desc');

        // 検索条件があれば適用
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('visitor_name', 'like', "%{$search}%")
                    ->orWhere('reception_number', 'like', "%{$search}%");
            });
        }

        // 日付フィルター
        if ($date = $request->input('date')) {
            $query->whereDate('visit_date', $date);
        }

        $appointments = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Appointments/Index', [
            'appointments' => $appointments,
            'filters' => $request->only(['search', 'date']),
        ]);
    }

    /**
     * 新規アポイント登録フォームを表示
     */
    public function create(): Response
    {
        $staffMembers = User::select('id', 'name', 'email')
            ->whereNotNull('email')
            ->active()
            ->orderBy('name')
            ->get();

        // 施設一覧を取得
        $facilities = Facility::select('id', 'name')
            ->orderBy('name')
            ->get();

        // 部署一覧を取得
        $groups = Group::select('id', 'name')
            ->orderBy('name')
            ->get();

        // プロジェクトグループ一覧を取得
        $projectGroups = ProjectGroup::with('users')->orderBy('name')->get();

        return Inertia::render('Admin/Appointments/Create', [
            'staffMembers' => $staffMembers,
            'facilities' => $facilities,
            'groups' => $groups,
            'projectGroups' => $projectGroups,
        ]);
    }

    /**
     * 新規アポイントを保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'visitor_name' => 'required|string|max:255',
            'visitor_email' => 'nullable|email|max:255',
            'visitor_phone' => 'nullable|string|max:20',
            'staff_member_id' => 'required',
            'visit_date' => 'required|date',
            'visit_time' => 'required|date_format:H:i',
            'purpose' => 'nullable|string|max:1000',
            'send_email' => 'nullable|boolean',
            // 施設予約のバリデーション
            'facility_reservation' => 'nullable|array',
            'facility_reservation.facility_id' => 'required_with:facility_reservation|exists:akioka_db.facilities,id',
            'facility_reservation.title' => 'required_with:facility_reservation|string|max:255',
            'facility_reservation.start_datetime' => 'required_with:facility_reservation|date_format:Y-m-d H:i',
            'facility_reservation.end_datetime' => 'required_with:facility_reservation|date_format:Y-m-d H:i',
            'facility_reservation.badge' => 'nullable|string|max:100',
            'facility_reservation.description_url' => 'nullable|url|max:500',
            // 参加者のバリデーション
            'facility_reservation.participants' => 'nullable|array',
            'facility_reservation.participants.*' => 'exists:akioka_db.users,id',
        ]);

        // トランザクション処理
        DB::beginTransaction();
        
        try {
            // 受付番号を自動生成
            $validated['reception_number'] = Appointment::generateReceptionNumber();

            $appointment = Appointment::create($validated);

            // QRコードデータを生成して保存
            $appointment->qr_code = $appointment->generateQrCode();
            $appointment->save();

            // 施設予約の処理
            $scheduleEvent = null;
            if ($request->has('facility_reservation') && $request->input('facility_reservation') !== null) {
                $facilityData = $request->input('facility_reservation');
                
                // ScheduleEventを作成
                // start_datetimeとend_datetimeから日付と時刻を分離
                $startDatetime = $facilityData['start_datetime']; // "YYYY-MM-DD HH:MM"
                $endDatetime = $facilityData['end_datetime'];     // "YYYY-MM-DD HH:MM"
                
                $date = explode(' ', $startDatetime)[0];          // "YYYY-MM-DD"
                $startTime = explode(' ', $startDatetime)[1];     // "HH:MM"
                $endTime = explode(' ', $endDatetime)[1];         // "HH:MM"
                
                $scheduleEvent = ScheduleEvent::create([
                    'facility_id' => $facilityData['facility_id'],
                    'date' => $date,
                    'title' => $facilityData['title'], // 一旦タイトルだけで保存
                    'start_datetime' => $startTime,
                    'end_datetime' => $endTime,
                    'badge' => $facilityData['badge'] ?? null,
                    'description_url' => $facilityData['description_url'] ?? null,
                    'status' => 1, // statusを1に設定
                ]);

                // タイトルを[id]タイトル名の形式に更新
                $scheduleEvent->update([
                    'title' => '[' . $scheduleEvent->id . ']' . $facilityData['title']
                ]);

                // 担当スタッフを参加者として追加
                $participants = [$validated['staff_member_id']];
                
                // 追加の参加者がいる場合は追加
                if (!empty($facilityData['participants'])) {
                    foreach ($facilityData['participants'] as $participantId) {
                        if (!in_array($participantId, $participants)) {
                            $participants[] = $participantId;
                        }
                    }
                }
                
                $scheduleEvent->participants()->attach($participants);
            }

            // メール送信処理
            $sendEmail = $request->input('send_email', false);
            $emailSent = false;

            if ($sendEmail && $appointment->visitor_email) {
                try {
                    Mail::to($appointment->visitor_email)->send(new AppointmentConfirmation($appointment));
                    $appointment->update(['send_flg' => true]);
                    $emailSent = true;
                } catch (\Exception $e) {
                    // メール送信エラーはログに記録するが、処理は継続
                    Log::error('Appointment confirmation email failed: ' . $e->getMessage());
                }
            }

            DB::commit();

            $message = '事前アポイントを登録しました。受付番号: ' . $appointment->reception_number;
            if ($scheduleEvent) {
                $message .= '（施設予約も登録しました）';
            }
            if ($emailSent) {
                $message .= ' (確認メールを送信しました)';
            }

            return Redirect::route('admin.appointments.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Appointment creation failed: ' . $e->getMessage());
            
            return Redirect::back()
                ->withInput()
                ->withErrors(['error' => 'アポイントの登録に失敗しました。もう一度お試しください。']);
        }
    }

    /**
     * アポイント詳細を表示
     */
    public function show(Appointment $appointment): Response
    {
        $appointment->load('staffMember');

        return Inertia::render('Admin/Appointments/Show', [
            'appointment' => $appointment,
        ]);
    }

    /**
     * アポイント編集フォームを表示
     */
    public function edit(Appointment $appointment): Response
    {
        $appointment->load('staffMember');

        $staffMembers = User::select('id', 'name', 'email')
            ->whereNotNull('email')
            ->active()
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Appointments/Edit', [
            'appointment' => $appointment,
            'staffMembers' => $staffMembers,
        ]);
    }

    /**
     * アポイントを更新
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'visitor_name' => 'required|string|max:255',
            'visitor_email' => 'nullable|email|max:255',
            'visitor_phone' => 'nullable|string|max:20',
            'staff_member_id' => 'required|exists:users,id',
            'visit_date' => 'required|date',
            'visit_time' => 'required|date_format:H:i',
            'purpose' => 'nullable|string|max:1000',
        ]);

        $appointment->update($validated);

        return Redirect::route('admin.appointments.index')
            ->with('success', 'アポイント情報を更新しました。');
    }

    /**
     * アポイントを削除
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return Redirect::route('admin.appointments.index')
            ->with('success', 'アポイントを削除しました。');
    }

    /**
     * 施設の予定を取得（指定期間）
     */
    public function getFacilitySchedule(Request $request, Facility $facility)
    {
        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->addDays(7)->format('Y-m-d'));

        Log::info('Fetching schedules', [
            'facility_id' => $facility->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $schedules = ScheduleEvent::where('facility_id', $facility->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->with(['participants' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email')
                    ->where('users.del_flg', 0);
            }])
            ->orderBy('date')
            ->orderBy('start_datetime')
            ->get()
            ->map(function ($schedule) {
                // dateカラムを文字列として明示的に返す（タイムゾーン変換を避ける）
                $dateValue = $schedule->date;
                if ($dateValue instanceof \DateTime || $dateValue instanceof \Carbon\Carbon) {
                    $schedule->date = $dateValue->format('Y-m-d');
                } elseif (is_string($dateValue)) {
                    // すでに文字列の場合は、YYYY-MM-DD形式に統一
                    $schedule->date = \Carbon\Carbon::parse($dateValue)->format('Y-m-d');
                }
                return $schedule;
            });

        Log::info('Schedules found', [
            'count' => $schedules->count(),
            'schedules' => $schedules->toArray()
        ]);

        return response()->json([
            'schedules' => $schedules,
            'facility' => [
                'id' => $facility->id,
                'name' => $facility->name,
            ],
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]);
    }

    /**
     * 部署に属するユーザーを取得
     */
    public function getUsersByGroup(Request $request, Group $group)
    {
        $users = User::where('group_id', $group->id)
            ->where('del_flg', 0)
            ->select('id', 'emp_no', 'name', 'email', 'group_id')
            ->orderBy('name')
            ->get();

        return response()->json([
            'users' => $users,
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
            ],
        ]);
    }

    /**
     * 複数ユーザーの予定を取得（指定期間）
     */
    public function getUserSchedules(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:akioka_db.users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $userIds = $validated['user_ids'];
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $schedules = UserSchedule::whereIn('user_id', $userIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 1) // アクティブな予定のみ
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'email')
                    ->where('del_flg', 0);
            }])
            ->get()
            ->map(function ($schedule) {
                // dateカラムを文字列として明示的に返す（タイムゾーン変換を避ける）
                $dateValue = $schedule->date;
                if ($dateValue instanceof \DateTime || $dateValue instanceof \Carbon\Carbon) {
                    $schedule->date = $dateValue->format('Y-m-d');
                } elseif (is_string($dateValue)) {
                    $schedule->date = \Carbon\Carbon::parse($dateValue)->format('Y-m-d');
                }
                return $schedule;
            });

        return response()->json([
            'schedules' => $schedules,
        ]);
    }
}
