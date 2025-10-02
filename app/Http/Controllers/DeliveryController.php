<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DeliveryController extends Controller
{
    // 納品書/受領書選択画面
    public function create(): Response
    {
        return Inertia::render('Delivery/Create');
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
            'delivery_type' => 'required|in:納品書,受領書',
            'document_image' => 'required|image|max:10240', // 10MB
        ]);

        // 書類画像の保存
        $documentPath = $request->file('document_image')->store('delivery_documents', 'public');
        $validated['document_image'] = $documentPath;

        // 納品記録の作成
        $delivery = Delivery::create([
            'delivery_type' => $validated['delivery_type'],
            'document_image' => $documentPath,
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

    // 納品書類の表示
    public function show(Delivery $delivery): Response
    {
        return Inertia::render('Delivery/Show', [
            'delivery' => $delivery,
            'documentUrl' => Storage::url($delivery->document_image),
        ]);
    }
}