<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LocalStorageAuthController extends Controller
{
    /**
     * localStorage用のログイン処理
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user = User::where('id', $validated['user_id'])
            ->where('del_flg', 0)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'user_id' => ['ユーザーが見つかりません。'],
            ]);
        }

        // スタッフメンバーとして登録された社員のみログインを許可
        if (!StaffMember::where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user_id' => ['このユーザーには管理画面へのアクセス権限がありません。管理者にお問い合わせください。'],
            ]);
        }

        // セッションにuser_idを保存（Webリクエスト用）
        session(['localStorage_user_id' => $user->id]);
        session()->save();

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

    /**
     * セッションにuser_idを設定（既存のlocalStorage認証用）
     */
    public function setSessionUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        // ユーザーが存在するかチェック
        $user = User::where('id', $validated['user_id'])
            ->where('del_flg', 0)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'ユーザーが見つかりません'
            ], 404);
        }

        // セッションにuser_idを保存
        session(['localStorage_user_id' => $validated['user_id']]);
        
        // セッションを確実に保存
        session()->save();

        Log::info('Session user set:', [
            'user_id' => $validated['user_id'],
            'user_name' => $user->name,
            'session_id' => session()->getId(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'セッションにユーザー情報を保存しました'
        ]);
    }

    /**
     * パスワードハッシュテスト用（デバッグ用）
     */
    public function testPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'password' => 'required|string',
        ]);

        $user = User::where('id', $validated['user_id'])->first();
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'user_id' => $user->id,
            'emp_no' => $user->emp_no,
            'stored_password' => $user->password,
            'password_check' => $validated['password'] === $user->password,
            'input_password' => $validated['password'],
            'input_password_length' => strlen($validated['password']),
            'stored_password_length' => strlen($user->password),
        ]);
    }
}

