<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\PickupRequest;
use App\Models\StaffMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
            'requester_group_id' => ['nullable', 'integer', 'exists:groups,id'],
            'item' => ['required', 'string', 'max:255'],
            'item_image' => ['nullable', 'image', 'max:10240'], // 物品画像（10MB）
            'storage_location' => ['nullable', 'string', 'max:255'],
            // 問い合わせ先は表示用（内線表記なども許容するため自由入力）
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'memo' => ['nullable', 'string', 'max:1000'],
        ];
    }

    // 依頼者の選択肢（部署一覧＝電話番号付き / 担当者候補＝スタッフ登録済みユーザー）
    private function selectableData(): array
    {
        $registeredUserIds = StaffMember::pluck('user_id');
        $staffMembers = User::select('id', 'name', 'group_id')
            ->whereIn('id', $registeredUserIds)
            ->orderBy('name')
            ->get();

        $groups = Group::select('id', 'name', 'phone_number')
            ->orderByRaw('display_order IS NULL, display_order ASC')
            ->orderBy('id')
            ->get();

        return ['staffMembers' => $staffMembers, 'groups' => $groups];
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
        return Inertia::render('Admin/PickupRequests/Create', $this->selectableData());
    }

    // 登録
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        if ($request->hasFile('item_image')) {
            $validated['item_image'] = $request->file('item_image')->store('pickup_request_items', 'public');
        } else {
            unset($validated['item_image']);
        }

        $pickupRequest = PickupRequest::create($validated + ['status' => 'pending']);

        // 登録後は伝票発行画面へ
        return Redirect::route('admin.pickup-requests.slip', $pickupRequest->id);
    }

    // 編集画面
    public function edit(PickupRequest $pickupRequest): Response
    {
        return Inertia::render('Admin/PickupRequests/Edit', array_merge(
            [
                'pickupRequest' => $pickupRequest,
                'itemImageUrl' => $pickupRequest->item_image ? Storage::url($pickupRequest->item_image) : null,
            ],
            $this->selectableData()
        ));
    }

    // 更新
    public function update(Request $request, PickupRequest $pickupRequest)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        if ($request->hasFile('item_image')) {
            if ($pickupRequest->item_image) {
                Storage::disk('public')->delete($pickupRequest->item_image);
            }
            $validated['item_image'] = $request->file('item_image')->store('pickup_request_items', 'public');
        } else {
            unset($validated['item_image']); // 画像が送られていなければ既存のまま
        }

        $pickupRequest->update($validated);

        return Redirect::route('admin.pickup-requests.index')
            ->with('success', '集荷依頼を更新しました。');
    }

    // 削除
    public function destroy(PickupRequest $pickupRequest)
    {
        if ($pickupRequest->item_image) {
            Storage::disk('public')->delete($pickupRequest->item_image);
        }
        $pickupRequest->delete();

        return Redirect::route('admin.pickup-requests.index')
            ->with('success', '集荷依頼を削除しました。');
    }

    // 集荷依頼伝票（印刷用）
    public function slip(PickupRequest $pickupRequest): Response
    {
        $group = $pickupRequest->requester_group_id
            ? Group::find($pickupRequest->requester_group_id)
            : null;

        return Inertia::render('Admin/PickupRequests/Slip', [
            'pickupRequest' => $pickupRequest,
            'departmentName' => $group?->name,
            'itemImageUrl' => $pickupRequest->item_image ? Storage::url($pickupRequest->item_image) : null,
        ]);
    }
}
