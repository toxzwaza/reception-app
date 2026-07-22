<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScreenPattern;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 受付端末の画面パターン管理。設置場所ごとに表示する導線の組み合わせを登録し、
 * 受付端末側は管理者ボタン＋画面切替パスワードでパターンを切り替える。
 */
class ScreenPatternController extends Controller
{
    // 入力バリデーション
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'features' => ['array'],
            'features.*' => ['string', 'in:' . implode(',', array_keys(ScreenPattern::FEATURES))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    // 導線の選択肢（キー => 表示名）
    private function featureOptions(): array
    {
        return collect(ScreenPattern::FEATURES)
            ->map(fn ($label, $key) => ['key' => $key, 'label' => $label])
            ->values()
            ->all();
    }

    // 一覧（＋画面切替パスワードの設定有無）
    public function index(): Response
    {
        $patterns = ScreenPattern::orderBy('sort_order')->orderBy('id')->get();

        return Inertia::render('Admin/ScreenPatterns/Index', [
            'patterns' => $patterns,
            'featureOptions' => $this->featureOptions(),
            'passwordIsSet' => (bool) Setting::get(Setting::SCREEN_SWITCH_PASSWORD),
        ]);
    }

    // 登録画面
    public function create(): Response
    {
        return Inertia::render('Admin/ScreenPatterns/Create', [
            'featureOptions' => $this->featureOptions(),
        ]);
    }

    // 登録
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['features'] = $validated['features'] ?? [];
        $validated['is_active'] = $request->boolean('is_active');

        ScreenPattern::create($validated);

        return Redirect::route('admin.screen-patterns.index')
            ->with('success', '画面パターンを登録しました。');
    }

    // 編集画面
    public function edit(ScreenPattern $screenPattern): Response
    {
        return Inertia::render('Admin/ScreenPatterns/Edit', [
            'pattern' => $screenPattern,
            'featureOptions' => $this->featureOptions(),
        ]);
    }

    // 更新
    public function update(Request $request, ScreenPattern $screenPattern)
    {
        $validated = $request->validate($this->rules());
        $validated['features'] = $validated['features'] ?? [];
        $validated['is_active'] = $request->boolean('is_active');

        $screenPattern->update($validated);

        return Redirect::route('admin.screen-patterns.index')
            ->with('success', '画面パターンを更新しました。');
    }

    // 削除
    public function destroy(ScreenPattern $screenPattern)
    {
        $screenPattern->delete();

        return Redirect::route('admin.screen-patterns.index')
            ->with('success', '画面パターンを削除しました。');
    }

    // 画面切替パスワードの設定
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:4', 'max:100'],
        ]);

        Setting::put(Setting::SCREEN_SWITCH_PASSWORD, Hash::make($validated['password']));

        return Redirect::route('admin.screen-patterns.index')
            ->with('success', '画面切替パスワードを設定しました。');
    }
}
