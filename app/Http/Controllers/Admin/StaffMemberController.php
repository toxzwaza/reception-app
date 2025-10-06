<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StaffMemberController extends Controller
{
    // スタッフ一覧
    public function index(): Response
    {
        $staffMembers = StaffMember::with('user')
            ->get()
            ->sortBy(function ($staffMember) {
                return $staffMember->user ? $staffMember->user->name : '';
            })
            ->values();

        // 手動でページネーション
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $staffMembers->slice($offset, $perPage);
        
        $paginatedStaffMembers = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $staffMembers->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        return Inertia::render('Admin/StaffMembers/Index', [
            'staffMembers' => $paginatedStaffMembers,
        ]);
    }

    // スタッフ作成画面
    public function create(): Response
    {
        // まだスタッフメンバーに登録されていないユーザーを取得
        $existingStaffUserIds = StaffMember::pluck('user_id')->toArray();
        $availableUsers = User::whereNotIn('id', $existingStaffUserIds)
            ->where('del_flg', 0)
            ->select('id', 'name', 'email', 'emp_no')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/StaffMembers/Create', [
            'availableUsers' => $availableUsers,
        ]);
    }

    // スタッフ保存
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required',
        ]);

        // ユーザーが既にスタッフメンバーに登録されていないかチェック
        $existingStaff = StaffMember::where('user_id', $validated['user_id'])->first();
        if ($existingStaff) {
            return redirect()->back()->withErrors([
                'user_id' => 'このユーザーは既にスタッフメンバーに登録されています。'
            ]);
        }

        StaffMember::create($validated);

        return redirect()->route('admin.staff-members.index')
            ->with('success', 'スタッフメンバーを登録しました。');
    }

    // スタッフ詳細
    public function show(StaffMember $staffMember): Response
    {
        $staffMember->load('user');

        return Inertia::render('Admin/StaffMembers/Show', [
            'staffMember' => $staffMember,
        ]);
    }

    // スタッフ編集画面
    public function edit(StaffMember $staffMember): Response
    {
        $staffMember->load('user');

        return Inertia::render('Admin/StaffMembers/Edit', [
            'staffMember' => $staffMember,
        ]);
    }

    // スタッフ更新
    public function update(Request $request, StaffMember $staffMember)
    {
        // StaffMemberはuser_idのみなので、更新処理は不要
        // 必要に応じて、ユーザー情報の更新はUserモデルで行う
        
        return redirect()->route('admin.staff-members.index')
            ->with('success', 'スタッフメンバー情報は更新されました。');
    }

    // スタッフ削除
    public function destroy(StaffMember $staffMember)
    {
        $staffMember->delete();

        return redirect()->route('admin.staff-members.index')
            ->with('success', 'スタッフメンバーを削除しました。');
    }
}