<?php

namespace App\Http\Controllers;

use App\Models\Pickup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PickupController extends Controller
{
    // 集荷伝票撮影画面
    public function create(): Response
    {
        return Inertia::render('Pickup/Create');
    }

    // 集荷記録の保存
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slip_image' => 'required|image|max:10240', // 10MB
        ]);

        // 伝票画像の保存
        $slipPath = $request->file('slip_image')->store('pickup_slips', 'public');

        // 集荷記録の作成
        $pickup = Pickup::create([
            'slip_image' => $slipPath,
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

    // 集荷伝票の表示
    public function show(Pickup $pickup): Response
    {
        return Inertia::render('Pickup/Show', [
            'pickup' => $pickup,
            'slipUrl' => Storage::url($pickup->slip_image),
        ]);
    }
}