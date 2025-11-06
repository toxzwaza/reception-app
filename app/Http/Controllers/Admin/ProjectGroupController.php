<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProjectGroupController extends Controller
{
    /**
     * プロジェクトグループ一覧を表示
     */
    public function index(): Response
    {
        $projectGroups = ProjectGroup::with('users')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/ProjectGroups/Index', [
            'projectGroups' => $projectGroups,
        ]);
    }

    /**
     * プロジェクトグループ登録画面を表示
     */
    public function create(): Response
    {
        $users = User::where('del_flg', 0)
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/ProjectGroups/Create', [
            'users' => $users,
        ]);
    }

    /**
     * プロジェクトグループを保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:akioka_db.users,id',
        ]);

        $projectGroup = ProjectGroup::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        if (!empty($validated['user_ids'])) {
            $projectGroup->users()->attach($validated['user_ids']);
        }

        return Redirect::route('admin.project-groups.index')
            ->with('success', 'プロジェクトグループを登録しました。');
    }

    /**
     * プロジェクトグループ詳細を表示
     */
    public function show(ProjectGroup $projectGroup): Response
    {
        $projectGroup->load('users');

        return Inertia::render('Admin/ProjectGroups/Show', [
            'projectGroup' => $projectGroup,
        ]);
    }

    /**
     * プロジェクトグループ編集画面を表示
     */
    public function edit(ProjectGroup $projectGroup): Response
    {
        $projectGroup->load('users');

        $users = User::where('del_flg', 0)
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/ProjectGroups/Edit', [
            'projectGroup' => $projectGroup,
            'users' => $users,
        ]);
    }

    /**
     * プロジェクトグループを更新
     */
    public function update(Request $request, ProjectGroup $projectGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:akioka_db.users,id',
        ]);

        $projectGroup->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        // ユーザーの紐付けを更新
        if (isset($validated['user_ids'])) {
            $projectGroup->users()->sync($validated['user_ids']);
        } else {
            $projectGroup->users()->detach();
        }

        return Redirect::route('admin.project-groups.index')
            ->with('success', 'プロジェクトグループを更新しました。');
    }

    /**
     * プロジェクトグループを削除
     */
    public function destroy(ProjectGroup $projectGroup)
    {
        $projectGroup->delete();

        return Redirect::route('admin.project-groups.index')
            ->with('success', 'プロジェクトグループを削除しました。');
    }
}
