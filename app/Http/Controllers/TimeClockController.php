<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class TimeClockController extends Controller
{
    /**
     * 出退勤打刻画面。
     * 未ログイン時はページ内でログインフォーム（部署→スタッフ選択）を表示する。
     */
    public function index(): Response
    {
        $groups = Group::select('id', 'name')->orderBy('name')->get();

        $users = User::active()
            ->select('id', 'name', 'emp_no', 'group_id')
            ->orderBy('emp_no')
            ->get();

        return Inertia::render('TimeClock/Index', [
            'groups' => $groups,
            'users' => $users,
        ]);
    }

    /**
     * 当日の出退勤状態確認画面。
     */
    public function status(): Response
    {
        $groups = Group::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('TimeClock/Status', [
            'groups' => $groups,
        ]);
    }
}
