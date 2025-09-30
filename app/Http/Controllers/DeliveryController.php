<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\StaffMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Facades\Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DeliveryController extends Controller
{
    // 納品書/受領書選択画面
    public function create(): Response
    {
        $staffMembers = StaffMember::all(['id', 'name', 'department']);
        return Inertia::render('Delivery/Create', [
            'staffMembers' => $staffMembers,
        ]);
    }

    // 書類撮影画面
    public function capture(Request $request): Response
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'delivery_type' => 'required|in:納品書,受領書',
            'staff_member_id' => 'required|exists:staff_members,id',
        ]);

        return Inertia::render('Delivery/Capture', $validated);
    }

    // 納品記録の保存
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'delivery_type' => 'required|in:納品書,受領書',
            'document_image' => 'required|image|max:10240', // 10MB
            'staff_member_id' => 'required|exists:staff_members,id',
        ]);

        // 書類画像の保存
        $documentPath = $request->file('document_image')->store('delivery_documents', 'public');
        $validated['document_image'] = $documentPath;

        // 電子印の合成処理
        $staffMember = StaffMember::find($validated['staff_member_id']);
        $sealedImagePath = $this->addSealToImage($documentPath, $staffMember->electronic_seal);
        $validated['sealed_document_image'] = $sealedImagePath;

        // 納品記録の作成
        $delivery = Delivery::create($validated + [
            'received_at' => now(),
        ]);

        // QRコードURLの生成と保存
        $qrCodeUrl = route('delivery.show', $delivery);
        $delivery->update(['qr_code_url' => $qrCodeUrl]);

        // QRコードの生成
        $qrCode = QrCode::size(300)
            ->margin(2)
            ->generate($qrCodeUrl);

        return Inertia::render('Delivery/Complete', [
            'qrCode' => $qrCode,
            'delivery' => $delivery,
        ]);
    }

    // 電子印付き書類の表示
    public function show(Delivery $delivery): Response
    {
        return Inertia::render('Delivery/Show', [
            'delivery' => $delivery,
            'sealedDocumentUrl' => Storage::url($delivery->sealed_document_image),
        ]);
    }

    // 電子印を画像に合成する処理
    private function addSealToImage(string $documentPath, string $sealPath): string
    {
        $image = Image::make(Storage::disk('public')->path($documentPath));
        $seal = Image::make(Storage::disk('public')->path($sealPath));

        // 電子印のサイズを調整（書類の幅の20%程度）
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
        $sealedPath = 'delivery_documents/sealed_' . basename($documentPath);
        Storage::disk('public')->put($sealedPath, $image->encode());

        return $sealedPath;
    }
}