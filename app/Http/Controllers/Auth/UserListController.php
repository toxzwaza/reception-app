<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserListController extends Controller
{
    /**
     * ログイン画面用のユーザー一覧を取得
     */
    public function index(): JsonResponse
    {
        $users = User::where('del_flg', 0)
            ->orderBy('emp_no')
            ->get(['id', 'emp_no', 'name', 'email'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'emp_no' => $user->emp_no,
                    'name' => $user->name,
                    'email' => $user->email,
                    'display_name' => $user->emp_no . ' - ' . $user->name . ($user->email ? ' (' . $user->email . ')' : ''),
                ];
            });

        return response()->json($users);
    }
}
