<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyLanRelayToken
{
    /**
     * 受付横断連携の中継役（音楽サーバー）用の固定トークン認証。
     * Authorization: Bearer <LAN_RELAY_TOKEN> を照合する。
     */
    public function handle(Request $request, Closure $next)
    {
        $expected = config('lan_relay.token');
        $provided = (string) $request->bearerToken();

        // トークン未設定時は全リクエストを拒否する（設定漏れで無認証公開になるのを防ぐ）
        if (!is_string($expected) || $expected === '' || !hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
