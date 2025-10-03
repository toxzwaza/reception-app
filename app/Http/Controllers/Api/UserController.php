<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * ユーザー一覧を取得
     */
    public function index(Request $request)
    {
        // 削除されていないユーザーのみ、社員番号と名前を取得
        $users = User::where('del_flg', 0)
            ->select('id', 'emp_no', 'name')
            ->orderBy('emp_no')
            ->get();

        \Log::info('UserController index:', [
            'users_count' => $users->count(),
            'first_user' => $users->first(),
            'connection' => config('database.default'),
        ]);

        return response()->json($users);
    }

    /**
     * user_idでユーザー情報を取得
     */
    public function show(Request $request, $userId)
    {
        $user = User::where('id', $userId)
            ->where('del_flg', 0)
            ->select('id', 'emp_no', 'name')
            ->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }
}
