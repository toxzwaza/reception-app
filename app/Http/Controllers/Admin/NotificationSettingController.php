<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Models\NotificationRecipient;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationSettingController extends Controller
{
    // 通知設定一覧
    public function index(): Response
    {
        $notificationSettings = NotificationSetting::with(['recipients.user'])
            ->orderBy('trigger_event')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/NotificationSettings/Index', [
            'notificationSettings' => $notificationSettings,
            'triggerEvents' => NotificationSetting::TRIGGER_EVENTS,
            'notificationTypes' => NotificationRecipient::NOTIFICATION_TYPES,
        ]);
    }

    // 通知設定作成画面
    public function create(): Response
    {
        $staffMembers = StaffMember::with('user')->select('id', 'user_id')->get();

        return Inertia::render('Admin/NotificationSettings/Create', [
            'triggerEvents' => NotificationSetting::TRIGGER_EVENTS,
            'notificationTypes' => NotificationRecipient::NOTIFICATION_TYPES,
            'staffMembers' => $staffMembers,
        ]);
    }

    // 通知設定保存
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_event' => 'required|string|in:' . implode(',', array_keys(NotificationSetting::TRIGGER_EVENTS)),
            'is_active' => 'boolean',
            'recipients' => 'required|array|min:1',
            'recipients.*.staff_member_id' => 'required|exists:staff_members,id',
            'recipients.*.notification_type' => 'required|string|in:' . implode(',', array_keys(NotificationRecipient::NOTIFICATION_TYPES)),
            'recipients.*.notification_data' => 'required|string|max:255',
        ]);

        // 通知設定を作成
        $notificationSetting = NotificationSetting::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'trigger_event' => $validated['trigger_event'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // 通知受信者を作成
        foreach ($validated['recipients'] as $recipientData) {
            $staffMember = StaffMember::find($recipientData['staff_member_id']);
            NotificationRecipient::create([
                'notification_setting_id' => $notificationSetting->id,
                'user_id' => $staffMember->user_id,
                'notification_type' => $recipientData['notification_type'],
                'notification_data' => $recipientData['notification_data'],
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.notification-settings.index')
            ->with('success', '通知設定を作成しました。');
    }

    // 通知設定詳細
    public function show(NotificationSetting $notificationSetting): Response
    {
        $notificationSetting->load(['recipients.user']);

        return Inertia::render('Admin/NotificationSettings/Show', [
            'notificationSetting' => $notificationSetting,
            'triggerEvents' => NotificationSetting::TRIGGER_EVENTS,
            'notificationTypes' => NotificationRecipient::NOTIFICATION_TYPES,
        ]);
    }

    // 通知設定編集画面
    public function edit(NotificationSetting $notificationSetting): Response
    {
        $notificationSetting->load(['recipients.user']);
        $staffMembers = StaffMember::with('user')->select('id', 'user_id')->get();

        return Inertia::render('Admin/NotificationSettings/Edit', [
            'notificationSetting' => $notificationSetting,
            'triggerEvents' => NotificationSetting::TRIGGER_EVENTS,
            'notificationTypes' => NotificationRecipient::NOTIFICATION_TYPES,
            'staffMembers' => $staffMembers,
        ]);
    }

    // 通知設定更新
    public function update(Request $request, NotificationSetting $notificationSetting)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_event' => 'required|string|in:' . implode(',', array_keys(NotificationSetting::TRIGGER_EVENTS)),
            'is_active' => 'boolean',
            'recipients' => 'required|array|min:1',
            'recipients.*.staff_member_id' => 'required|exists:staff_members,id',
            'recipients.*.notification_type' => 'required|string|in:' . implode(',', array_keys(NotificationRecipient::NOTIFICATION_TYPES)),
            'recipients.*.notification_data' => 'required|string|max:255',
        ]);

        // 通知設定を更新
        $notificationSetting->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'trigger_event' => $validated['trigger_event'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // 既存の通知受信者を削除
        $notificationSetting->recipients()->delete();

        // 新しい通知受信者を作成
        foreach ($validated['recipients'] as $recipientData) {
            $staffMember = StaffMember::find($recipientData['staff_member_id']);
            NotificationRecipient::create([
                'notification_setting_id' => $notificationSetting->id,
                'user_id' => $staffMember->user_id,
                'notification_type' => $recipientData['notification_type'],
                'notification_data' => $recipientData['notification_data'],
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.notification-settings.index')
            ->with('success', '通知設定を更新しました。');
    }

    // 通知設定削除
    public function destroy(NotificationSetting $notificationSetting)
    {
        $notificationSetting->delete();

        return redirect()->route('admin.notification-settings.index')
            ->with('success', '通知設定を削除しました。');
    }

    // 通知設定の有効/無効切り替え
    public function toggle(NotificationSetting $notificationSetting)
    {
        $notificationSetting->update([
            'is_active' => !$notificationSetting->is_active
        ]);

        $status = $notificationSetting->is_active ? '有効' : '無効';
        
        return redirect()->back()
            ->with('success', "通知設定を{$status}にしました。");
    }
}