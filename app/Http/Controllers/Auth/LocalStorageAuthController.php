<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LocalStorageAuthController extends Controller
{
    /**
     * localStorage用のログイン処理
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => 'required|string',
        ]);

        $user = User::where('id', $validated['user_id'])
            ->where('del_flg', 0)
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['パスワードが正しくありません。'],
            ]);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'emp_no' => $user->emp_no,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
        ]);
    }

    /**
     * localStorage内のuser_idを検証
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user = User::where('id', $validated['user_id'])
            ->where('del_flg', 0)
            ->first();

        if (!$user) {
            return response()->json([
                'valid' => false,
                'message' => 'ユーザーが見つからないか、無効です。',
            ], 401);
        }

        return response()->json([
            'valid' => true,
            'user' => [
                'id' => $user->id,
                'emp_no' => $user->emp_no,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
        ]);
    }

    /**
     * ログアウト（localStorageクリア用）
     */
    public function logout(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'ログアウトしました。',
        ]);
    }
}

