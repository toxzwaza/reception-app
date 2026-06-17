<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        // 部署一覧
        $groups = \App\Models\Group::select('id', 'name')->orderBy('name')->get();

        // スタッフメンバー登録済みの社員のみをログイン候補にする（部署情報付き）
        $registeredUserIds = \App\Models\StaffMember::pluck('user_id');
        $staffMembers = \App\Models\User::select('id', 'name', 'emp_no', 'group_id')
            ->whereIn('id', $registeredUserIds)
            ->where('del_flg', 0)
            ->orderBy('name')
            ->get();

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'groups' => $groups,
            'staffMembers' => $staffMembers,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // スタッフメンバーとして登録された社員のみ管理画面へのログインを許可する
        $user = Auth::user();
        if (!\App\Models\StaffMember::where('user_id', $user->id)->exists()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'このアカウントには管理画面へのアクセス権限がありません。管理者にお問い合わせください。',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // localStorage認証用のセッションキーもクリア
        $request->session()->forget('localStorage_user_id');
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // ログアウト後はログイン画面へ
        return redirect()->route('login');
    }

    /**
     * API用ログアウト（localStorageクリア用）
     */
    public function apiLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully',
            'clear_localstorage' => true
        ]);
    }
}
