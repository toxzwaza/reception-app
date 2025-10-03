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

        // localStorageのuser_idをチェック（クエリパラメータ、セッション、ヘッダーの順で確認）
        $userId = $request->query('user_id') 
            ?? $request->session()->get('localStorage_user_id') 
            ?? $request->header('X-User-ID');
        
        // クエリパラメータでuser_idが渡された場合はセッションにも保存
        if ($request->query('user_id') && !$request->session()->get('localStorage_user_id')) {
            $request->session()->put('localStorage_user_id', $request->query('user_id'));
            $userId = $request->query('user_id');
            \Log::info('Set session user_id from query parameter:', [
                'user_id' => $userId,
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

        if (!$user) {
            // APIリクエストの場合はJSONを返す
            if ($request->expectsJson()) {
                return response()->json([
                    'requires_auth' => true,
                    'redirect_to' => route('login'),
                    'message' => 'ユーザーが見つかりません'
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