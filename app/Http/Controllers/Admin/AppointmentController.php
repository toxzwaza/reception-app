<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Mail\AppointmentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
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

        return Inertia::render('Admin/Appointments/Create', [
            'staffMembers' => $staffMembers,
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
        ]);

        // 受付番号を自動生成
        $validated['reception_number'] = Appointment::generateReceptionNumber();

        $appointment = Appointment::create($validated);

        // QRコードデータを生成して保存
        $appointment->qr_code = $appointment->generateQrCode();
        $appointment->save();

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

        $message = '事前アポイントを登録しました。受付番号: ' . $appointment->reception_number;
        if ($emailSent) {
            $message .= ' (確認メールを送信しました)';
        }

        return Redirect::route('admin.appointments.index')
            ->with('success', $message);
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
}
