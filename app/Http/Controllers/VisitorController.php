<?php

namespace App\Http\Controllers;

use App\Models\StaffMember;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VisitorController extends Controller
{
    // QRコード読取画面
    public function scanQr(): Response
    {
        return Inertia::render('Visitor/ScanQr');
    }

    // QRコードによるチェックイン処理
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'visitor_id' => 'required|exists:visitors,id',
            'company_name' => 'required|string|max:255',
            'visitor_name' => 'required|string|max:255',
            'staff_member_id' => 'required|exists:staff_members,id',
        ]);

        $visitor = Visitor::create([
            'company_name' => $validated['company_name'],
            'visitor_name' => $validated['visitor_name'],
            'staff_member_id' => $validated['staff_member_id'],
            'check_in_time' => now(),
        ]);

        // Teams通知の送信
        $this->sendTeamsNotification($visitor);

        return redirect()->route('visitor.complete');
    }

    // 手動入力画面
    public function create(): Response
    {
        $staffMembers = StaffMember::all(['id', 'name', 'department']);
        return Inertia::render('Visitor/Create', [
            'staffMembers' => $staffMembers,
        ]);
    }

    // 来訪者情報の保存（手動入力）
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'visitor_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'business_card_image' => 'nullable|image|max:5120', // 5MB
            'staff_member_id' => 'required|exists:staff_members,id',
        ]);

        // 名刺画像の保存処理
        if ($request->hasFile('business_card_image')) {
            $path = $request->file('business_card_image')->store('business_cards', 'public');
            $validated['business_card_image'] = $path;
        }

        $visitor = Visitor::create($validated + [
            'check_in_time' => now(),
        ]);

        // Teams通知の送信
        $this->sendTeamsNotification($visitor);

        return redirect()->route('visitor.complete');
    }

    // 完了画面
    public function complete(): Response
    {
        return Inertia::render('Visitor/Complete');
    }

    // Teams通知の送信
    private function sendTeamsNotification(Visitor $visitor)
    {
        $teamsService = new \App\Services\TeamsNotificationService();
        
        $checkinData = [
            'reception_number' => $visitor->reception_number,
            'company_name' => $visitor->company_name,
            'visitor_name' => $visitor->visitor_name,
            'staff_member_name' => $visitor->staffMember->name ?? '未設定',
        ];
        
        $teamsService->sendCheckinNotification($checkinData);
    }
}