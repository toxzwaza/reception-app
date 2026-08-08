<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 担当者呼出管理。
 * 受付画面の担当者ひらがな検索呼出で使用する
 * ヨミ（name_kana）と個人携帯番号（mobile_phone）を管理する。
 */
class StaffPhoneController extends Controller
{
    /**
     * 担当者一覧（ヨミ・携帯番号付き）
     */
    public function index(Request $request): Response
    {
        $keyword = trim((string) $request->query('keyword', ''));

        $staff = User::active()
            ->with('group:id,name')
            ->when($keyword !== '', function ($query) use ($keyword) {
                // ひらがな入力でもヨミ（カタカナ）にマッチさせる
                $katakana = mb_convert_kana($keyword, 'C');
                $query->where(function ($q) use ($keyword, $katakana) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('name_kana', 'like', "%{$katakana}%")
                        ->orWhere('emp_no', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('emp_no')
            ->get(['id', 'emp_no', 'name', 'name_kana', 'mobile_phone', 'call_search_flg', 'group_id']);

        return Inertia::render('Admin/StaffPhones/Index', [
            'staff' => $staff,
            'keyword' => $keyword,
        ]);
    }

    /**
     * ヨミ・携帯番号の編集画面
     */
    public function edit(User $user): Response
    {
        $user->load('group:id,name');

        return Inertia::render('Admin/StaffPhones/Edit', [
            'staff' => $user->only(['id', 'emp_no', 'name', 'name_kana', 'mobile_phone', 'call_search_flg']) + [
                'group_name' => $user->group?->name,
            ],
        ]);
    }

    /**
     * 受付の担当者検索への表示・非表示をトグル
     */
    public function toggleSearch(User $user)
    {
        $user->update(['call_search_flg' => !$user->call_search_flg]);

        return Redirect::route('admin.staff-phones.index')
            ->with('success', '「' . $user->name . '」を受付の担当者検索に' . ($user->call_search_flg ? '表示' : '非表示に') . 'しました。');
    }

    /**
     * ヨミ・携帯番号を更新
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name_kana' => ['nullable', 'string', 'max:100'],
            // E.164 を推奨しつつ、ハイフン入力も許容（部署電話番号管理と同基準）
            'mobile_phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s]+$/'],
            'call_search_flg' => ['required', 'boolean'],
        ], [
            'mobile_phone.regex' => '携帯番号は数字・+・-・空白のみで入力してください。',
        ], [
            'name_kana' => 'ヨミ',
            'mobile_phone' => '携帯番号',
        ]);

        $user->update([
            // ヨミはカタカナに正規化して保存（ひらがなで入力されてもOK）
            'name_kana' => isset($validated['name_kana']) && $validated['name_kana'] !== ''
                ? mb_convert_kana($validated['name_kana'], 'C')
                : null,
            'mobile_phone' => $validated['mobile_phone'] ?? null,
            'call_search_flg' => $validated['call_search_flg'],
        ]);

        return Redirect::route('admin.staff-phones.index')
            ->with('success', '「' . $user->name . '」の呼出情報を更新しました。');
    }
}
