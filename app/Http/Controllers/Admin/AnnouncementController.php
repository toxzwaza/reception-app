<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    /**
     * お知らせ一覧を表示
     */
    public function index(): Response
    {
        $announcements = Announcement::ordered()->paginate(20);

        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => $announcements,
        ]);
    }

    /**
     * 新規登録フォームを表示
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Announcements/Create');
    }

    /**
     * 新規お知らせを保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,warning,error',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);

        // display_orderが未指定の場合は最後に追加
        if (!isset($validated['display_order'])) {
            $validated['display_order'] = Announcement::max('display_order') + 1;
        }

        Announcement::create($validated);

        return Redirect::route('admin.announcements.index')
            ->with('success', 'お知らせを登録しました。');
    }

    /**
     * 詳細を表示
     */
    public function show(Announcement $announcement): Response
    {
        return Inertia::render('Admin/Announcements/Show', [
            'announcement' => $announcement,
        ]);
    }

    /**
     * 編集フォームを表示
     */
    public function edit(Announcement $announcement): Response
    {
        return Inertia::render('Admin/Announcements/Edit', [
            'announcement' => $announcement,
        ]);
    }

    /**
     * お知らせを更新
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,warning,error',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);

        $announcement->update($validated);

        return Redirect::route('admin.announcements.index')
            ->with('success', 'お知らせを更新しました。');
    }

    /**
     * お知らせを削除
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return Redirect::route('admin.announcements.index')
            ->with('success', 'お知らせを削除しました。');
    }
}
