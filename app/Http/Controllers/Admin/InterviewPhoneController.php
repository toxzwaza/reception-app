<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterviewPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class InterviewPhoneController extends Controller
{
    /**
     * 面接時の通話先電話番号一覧を表示
     */
    public function index(): Response
    {
        $phones = InterviewPhone::ordered()->paginate(20);

        return Inertia::render('Admin/InterviewPhones/Index', [
            'phones' => $phones,
        ]);
    }

    /**
     * 新規登録フォームを表示
     */
    public function create(): Response
    {
        return Inertia::render('Admin/InterviewPhones/Create');
    }

    /**
     * 新規電話番号を保存
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'extension_number' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);

        // display_orderが未指定の場合は最後に追加
        if (!isset($validated['display_order'])) {
            $validated['display_order'] = InterviewPhone::max('display_order') + 1;
        }

        InterviewPhone::create($validated);

        return Redirect::route('admin.interview-phones.index')
            ->with('success', '面接時の通話先電話番号を登録しました。');
    }

    /**
     * 詳細を表示
     */
    public function show(InterviewPhone $interviewPhone): Response
    {
        return Inertia::render('Admin/InterviewPhones/Show', [
            'phone' => $interviewPhone,
        ]);
    }

    /**
     * 編集フォームを表示
     */
    public function edit(InterviewPhone $interviewPhone): Response
    {
        return Inertia::render('Admin/InterviewPhones/Edit', [
            'phone' => $interviewPhone,
        ]);
    }

    /**
     * 電話番号情報を更新
     */
    public function update(Request $request, InterviewPhone $interviewPhone)
    {
        $validated = $request->validate([
            'department_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'extension_number' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);

        $interviewPhone->update($validated);

        return Redirect::route('admin.interview-phones.index')
            ->with('success', '電話番号情報を更新しました。');
    }

    /**
     * 電話番号を削除
     */
    public function destroy(InterviewPhone $interviewPhone)
    {
        $interviewPhone->delete();

        return Redirect::route('admin.interview-phones.index')
            ->with('success', '電話番号を削除しました。');
    }
}
