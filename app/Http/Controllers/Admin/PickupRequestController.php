<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 集荷依頼の管理。事前に依頼を登録し、受付端末の集荷画面で一覧から選択して集荷する。
 */
class PickupRequestController extends Controller
{
    // 入力バリデーション
    private function rules(): array
    {
        return [
            'requester_name' => ['required', 'string', 'max:255'],
            'item' => ['required', 'string', 'max:255'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            // 問い合わせ先は表示用（内線表記なども許容するため自由入力）
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'memo' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function messages(): array
    {
        return [];
    }

    // 一覧
    public function index(): Response
    {
        $pickupRequests = PickupRequest::orderByRaw("status = 'completed'") // 未集荷を上に
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Admin/PickupRequests/Index', [
            'pickupRequests' => $pickupRequests,
        ]);
    }

    // 登録画面
    public function create(): Response
    {
        return Inertia::render('Admin/PickupRequests/Create');
    }

    // 登録
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $pickupRequest = PickupRequest::create($validated + ['status' => 'pending']);

        // 登録後は伝票発行画面へ
        return Redirect::route('admin.pickup-requests.slip', $pickupRequest->id);
    }

    // 編集画面
    public function edit(PickupRequest $pickupRequest): Response
    {
        return Inertia::render('Admin/PickupRequests/Edit', [
            'pickupRequest' => $pickupRequest,
        ]);
    }

    // 更新
    public function update(Request $request, PickupRequest $pickupRequest)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $pickupRequest->update($validated);

        return Redirect::route('admin.pickup-requests.index')
            ->with('success', '集荷依頼を更新しました。');
    }

    // 削除
    public function destroy(PickupRequest $pickupRequest)
    {
        $pickupRequest->delete();

        return Redirect::route('admin.pickup-requests.index')
            ->with('success', '集荷依頼を削除しました。');
    }

    // 集荷依頼伝票（印刷用）
    public function slip(PickupRequest $pickupRequest): Response
    {
        return Inertia::render('Admin/PickupRequests/Slip', [
            'pickupRequest' => $pickupRequest,
        ]);
    }
}
