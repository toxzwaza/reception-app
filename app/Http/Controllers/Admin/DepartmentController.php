<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 部署（groups）の代表電話番号を管理する画面。
 * アポなし来訪時に、受付端末フロントの Twilio Device から
 * この番号へ自動発信するために使用する（OtherVisitor/Complete.vue）。
 */
class DepartmentController extends Controller
{
    /**
     * 部署一覧（電話番号付き）
     */
    public function index(): Response
    {
        $departments = Group::orderBy('id')
            ->withCount('users')
            ->get(['id', 'name', 'phone_number']);

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments,
        ]);
    }

    /**
     * 部署電話番号の編集画面
     */
    public function edit(Group $department): Response
    {
        return Inertia::render('Admin/Departments/Edit', [
            'department' => $department->only(['id', 'name', 'phone_number']),
        ]);
    }

    /**
     * 部署電話番号を更新
     */
    public function update(Request $request, Group $department)
    {
        $validated = $request->validate([
            // E.164 を推奨しつつ、内線・ハイフン入力も許容（+・数字・空白・ハイフンのみ）
            'phone_number' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
        ], [
            'phone_number.regex' => '電話番号は数字・+・-・空白のみで入力してください。',
        ], [
            'phone_number' => '電話番号',
        ]);

        $department->update([
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        return Redirect::route('admin.departments.index')
            ->with('success', '「' . $department->name . '」の電話番号を更新しました。');
    }
}
