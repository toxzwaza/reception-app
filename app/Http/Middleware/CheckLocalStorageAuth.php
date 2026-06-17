<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLocalStorageAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ログインページや認証関連ページの場合はlocalStorageのチェックをスキップ
        if ($request->is('login') || $request->is('login/*') || 
            $request->is('register') || $request->is('register/*') ||
            $request->is('forgot-password') || $request->is('forgot-password/*') ||
            $request->is('reset-password') || $request->is('reset-password/*')) {
            return $next($request);
        }

        // localStorage 由来の値（クエリパラメータ・ヘッダー）を優先する。
        // セッションには別ユーザーの古い値が残ることがあるため、最後のフォールバックに留める。
        $incoming = $request->query('user_id') ?? $request->header('X-User-ID');
        $userId = $incoming ?? $request->session()->get('localStorage_user_id');

        // localStorage 由来の値が来たら、セッションを常に最新へ同期する
        // （別ユーザーでログインし直した際に古い session 値が残り続けるのを防ぐ）
        if ($incoming && (string) $incoming !== (string) $request->session()->get('localStorage_user_id')) {
            $request->session()->put('localStorage_user_id', $incoming);
            \Log::info('Synced session user_id from localStorage (query/header):', [
                'user_id' => $incoming,
                'session_id' => $request->session()->getId(),
            ]);
        }
        
        \Log::info('CheckLocalStorageAuth middleware:', [
            'url' => $request->url(),
            'query_user_id' => $request->query('user_id'),
            'session_user_id' => $request->session()->get('localStorage_user_id'),
            'header_user_id' => $request->header('X-User-ID'),
            'final_user_id' => $userId,
            'is_ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson(),
        ]);
        
        if (!$userId) {
            // APIリクエストの場合はJSONを返す
            if ($request->expectsJson()) {
                return response()->json([
                    'requires_auth' => true,
                    'redirect_to' => route('login')
                ], 401);
            }
            // Webリクエストの場合はログインページにリダイレクト
            return redirect()->route('login');
        }

        // user_idが存在する場合、そのユーザーが有効かチェック
        $user = \App\Models\User::where('id', $userId)
            ->where('del_flg', 0)
            ->first();

        // スタッフメンバーとして登録された社員のみ管理画面へのアクセスを許可する
        $isStaff = $user && \App\Models\StaffMember::where('user_id', $user->id)->exists();

        if (!$user || !$isStaff) {
            // APIリクエストの場合はJSONを返す
            if ($request->expectsJson()) {
                return response()->json([
                    'requires_auth' => true,
                    'redirect_to' => route('login'),
                    'message' => $user ? '管理画面へのアクセス権限がありません' : 'ユーザーが見つかりません'
                ], 401);
            }
            // Webリクエストの場合はログインページにリダイレクト
            return redirect()->route('login');
        }

        // リクエストにユーザー情報を追加
        $request->merge(['user_id' => $userId]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}