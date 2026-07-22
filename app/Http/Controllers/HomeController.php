<?php

namespace App\Http\Controllers;

use App\Models\ScreenPattern;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        // 受付端末が localStorage で選択した画面パターンで導線を絞り込むための一覧
        $screenPatterns = ScreenPattern::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'description', 'features', 'layout']);

        return Inertia::render('Home/Index', [
            'title' => '受付システム',
            'screenPatterns' => $screenPatterns,
        ]);
    }

    /**
     * 画面切替パスワードの照合（受付端末の管理者ボタンから呼ばれる）。
     */
    public function verifyScreenPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $stored = Setting::get(Setting::SCREEN_SWITCH_PASSWORD);

        // 未設定の場合は切り替え不可（管理画面でパスワード設定を促す）
        if (! $stored) {
            return response()->json([
                'ok' => false,
                'message' => '画面切替パスワードが未設定です。管理画面で設定してください。',
            ], 422);
        }

        if (! Hash::check($validated['password'], $stored)) {
            return response()->json([
                'ok' => false,
                'message' => 'パスワードが違います。',
            ], 422);
        }

        return response()->json(['ok' => true]);
    }
}
