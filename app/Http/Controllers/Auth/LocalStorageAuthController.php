<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        // デバッグ用ログ
        Log::info('LocalStorageAuthController login request:', [
            'all_data' => $request->all(),
            'user_id' => $request->input('user_id'),
            'user_id_type' => gettype($request->input('user_id')),
            'password' => $request->has('password') ? 'provided' : 'missing',
        ]);

        // ユーザーが存在するかチェック
        $userId = $request->input('user_id');
        $userExists = User::where('id', $userId)->exists();
        Log::info('User exists check:', [
            'user_id' => $userId,
            'exists' => $userExists,
            'all_users_count' => User::count(),
            'active_users_count' => User::where('del_flg', 0)->count(),
        ]);

        $validated = $request->validate([
            'user_id' => 'required|integer',
            'password' => 'required|string',
        ]);

        $user = User::where('id', $validated['user_id'])
            ->where('del_flg', 0)
            ->first();

        Log::info('User found for login:', [
            'user_id' => $validated['user_id'],
            'user_found' => $user ? true : false,
            'user_id_from_db' => $user ? $user->id : null,
            'user_emp_no' => $user ? $user->emp_no : null,
            'stored_password' => $user ? $user->password : null,
        ]);

        if (!$user) {
            Log::warning('User not found or deleted:', [
                'user_id' => $validated['user_id'],
                'del_flg' => $user ? $user->del_flg : 'N/A',
            ]);
            throw ValidationException::withMessages([
                'password' => ['ユーザーが見つかりません。'],
            ]);
        }

        // パスワードを直接比較（ハッシュ化されていないため）
        $passwordCheck = $validated['password'] === $user->password;
        Log::info('Password check result:', [
            'password_check' => $passwordCheck,
            'input_password' => $validated['password'],
            'stored_password' => $user->password,
            'input_password_length' => strlen($validated['password']),
            'stored_password_length' => strlen($user->password),
        ]);

        if (!$passwordCheck) {
            throw ValidationException::withMessages([
                'password' => ['パスワードが正しくありません。'],
            ]);
        }

        // セッションにもuser_idを保存（Webリクエスト用）
        session(['localStorage_user_id' => $user->id]);
        
        // セッションを確実に保存
        session()->save();

        Log::info('Login successful, returning response:', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'session_saved' => true,
            'session_id' => session()->getId(),
        ]);

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

