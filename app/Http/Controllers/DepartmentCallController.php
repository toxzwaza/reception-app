<?php

namespace App\Http\Controllers;

use App\Models\Group;
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
     * 部署選択画面（電話番号が登録済みの部署のみ・表示順）
     */
    public function select(): Response
    {
        return Inertia::render('DepartmentCall/Select', [
            'departments' => $this->callableDepartments(),
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
}
