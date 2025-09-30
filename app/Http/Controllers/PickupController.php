<?php

namespace App\Http\Controllers;

use App\Models\Pickup;
use App\Models\StaffMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Facades\Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PickupController extends Controller
{
    // 集荷伝票撮影画面
    public function create(): Response
    {
        $staffMembers = StaffMember::all(['id', 'name', 'department']);
        return Inertia::render('Pickup/Create', [
            'staffMembers' => $staffMembers,
        ]);
    }

    // 集荷記録の保存
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'slip_image' => 'required|image|max:10240', // 10MB
            'staff_member_id' => 'required|exists:staff_members,id',
        ]);

        // 伝票画像の保存
        $slipPath = $request->file('slip_image')->store('pickup_slips', 'public');
        $validated['slip_image'] = $slipPath;

        // 電子印の合成処理
        $staffMember = StaffMember::find($validated['staff_member_id']);
        $sealedSlipPath = $this->addSealToImage($slipPath, $staffMember->electronic_seal);
        $validated['sealed_slip_image'] = $sealedSlipPath;

        // 集荷記録の作成
        $pickup = Pickup::create($validated + [
            'picked_up_at' => now(),
        ]);

        // QRコードURLの生成と保存
        $qrCodeUrl = route('pickup.show', $pickup);
        $pickup->update(['qr_code_url' => $qrCodeUrl]);

        // QRコードの生成
        $qrCode = QrCode::size(300)
            ->margin(2)
            ->generate($qrCodeUrl);

        return Inertia::render('Pickup/Complete', [
            'qrCode' => $qrCode,
            'pickup' => $pickup,
        ]);
    }

    // 電子印付き伝票の表示
    public function show(Pickup $pickup): Response
    {
        return Inertia::render('Pickup/Show', [
            'pickup' => $pickup,
            'sealedSlipUrl' => Storage::url($pickup->sealed_slip_image),
        ]);
    }

    // 電子印を画像に合成する処理
    private function addSealToImage(string $slipPath, string $sealPath): string
    {
        $image = Image::make(Storage::disk('public')->path($slipPath));
        $seal = Image::make(Storage::disk('public')->path($sealPath));

        // 電子印のサイズを調整（伝票の幅の20%程度）
        $sealWidth = (int)($image->width() * 0.2);
        $seal->resize($sealWidth, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        // 電子印を右下に配置
        $x = $image->width() - $seal->width() - 50;
        $y = $image->height() - $seal->height() - 50;

        // 電子印を合成
        $image->insert($seal, 'top-left', $x, $y);

        // 合成した画像を保存
        $sealedPath = 'pickup_slips/sealed_' . basename($slipPath);
        Storage::disk('public')->put($sealedPath, $image->encode());

        return $sealedPath;
    }
}