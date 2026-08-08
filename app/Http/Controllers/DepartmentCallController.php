<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 受付画面からの部署内線発信。
 * 部署電話番号管理（groups.phone_number）に登録された番号へ、
 * 受付端末フロントの Twilio Device から発信する。
 * 電話番号が未登録の部署は選択肢に表示しない。
 */
class DepartmentCallController extends Controller
{
    /**
     * 電話番号が登録済みの部署一覧（表示順）
     */
    private function callableDepartments()
    {
        return Group::whereNotNull('phone_number')
            ->where('phone_number', '!=', '')
            ->orderByRaw('display_order IS NULL, display_order ASC')
            ->orderBy('id')
            ->get(['id', 'name', 'phone_number']);
    }

    /**
     * 担当者検索用の一覧。
     * セキュリティ配慮：電話番号そのものは受付画面に渡さず、
     * 「携帯あり」「部署電話あり」のフラグのみ返す（番号は発信時にサーバー側で解決）。
     */
    private function searchableStaff()
    {
        return User::active()
            ->with('group:id,name,phone_number')
            ->orderBy('name_kana')
            ->get(['id', 'name', 'name_kana', 'mobile_phone', 'group_id'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'name_kana' => $user->name_kana,
                'group_name' => $user->group?->name,
                'has_mobile' => !empty($user->mobile_phone),
                'group_has_phone' => !empty($user->group?->phone_number),
            ])
            ->values();
    }

    /**
     * 部署選択画面（左：部署一覧／右：担当者ひらがな検索）
     */
    public function select(): Response
    {
        return Inertia::render('DepartmentCall/Select', [
            'departments' => $this->callableDepartments(),
            'staff' => $this->searchableStaff(),
        ]);
    }

    /**
     * 担当者への発信画面。
     * type=mobile：個人携帯へ発信／type=department：所属部署の電話へ発信。
     */
    public function staffCall(User $user, Request $request): Response
    {
        $type = $request->query('type', 'mobile');
        $user->load('group:id,name,phone_number');

        $backToSelect = fn (string $error) => Inertia::render('DepartmentCall/Select', [
            'departments' => $this->callableDepartments(),
            'staff' => $this->searchableStaff(),
            'error' => $error,
        ]);

        if ($user->del_flg) {
            return $backToSelect('選択された担当者は現在呼び出せません。');
        }

        if ($type === 'mobile') {
            if (empty($user->mobile_phone)) {
                return $backToSelect('選択された担当者には携帯番号が登録されていません。');
            }
            $phone = $user->mobile_phone;
            $label = '携帯';
        } else {
            if (empty($user->group?->phone_number)) {
                return $backToSelect('選択された担当者の部署には電話番号が登録されていません。');
            }
            $phone = $user->group->phone_number;
            $label = '部署';
        }

        return Inertia::render('DepartmentCall/StaffCall', [
            'staffInfo' => [
                'id' => $user->id,
                'name' => $user->name,
                'group_name' => $user->group?->name,
                'phone_number' => $phone,
                'call_type' => $label,
            ],
        ]);
    }

    /**
     * 発信画面（選択された部署へ自動発信）
     */
    public function call(Group $group): Response
    {
        // 電話番号未登録の部署は発信させず、選択画面へ戻す
        if (empty($group->phone_number)) {
            return Inertia::render('DepartmentCall/Select', [
                'departments' => $this->callableDepartments(),
                'error' => '選択された部署には電話番号が登録されていません。',
            ]);
        }

        return Inertia::render('DepartmentCall/Call', [
            'groupInfo' => $group->only(['id', 'name', 'phone_number']),
        ]);
    }

    /**
     * タクシー呼び出し（通知設定管理に登録したタクシー会社番号へ発信）
     */
    public function taxi(): Response
    {
        $taxi = NotificationSetting::where('trigger_event', 'taxi_call')->first();
        $phone = $taxi->settings['phone_number'] ?? null;

        return Inertia::render('DepartmentCall/Taxi', [
            'phoneNumber' => $phone,
        ]);
    }
}
