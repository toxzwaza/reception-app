<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyDisplayToken
{
    /**
     * 事務所ディスプレイ表示システム用の固定トークン認証。
     * X-Display-Token ヘッダー（またはクエリ token）を DISPLAY_API_TOKEN と照合する。
     */
    public function handle(Request $request, Closure $next)
    {
        $expected = config('services.display.token');
        $provided = $request->header('X-Display-Token') ?? $request->query('token', '');

        // トークン未設定時は全リクエストを拒否する（設定漏れで無認証公開になるのを防ぐ）
        if (!is_string($expected) || $expected === '' || !hash_equals($expected, (string) $provided)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
