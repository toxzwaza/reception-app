<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use App\Models\StaffMember;
use App\Services\TeamsNotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Group;

class OtherVisitorController extends Controller
{
    protected $teamsNotification;

    public function __construct(TeamsNotificationService $teamsNotification)
    {
        $this->teamsNotification = $teamsNotification;
    }
    // 訪問者情報入力画面
    public function create(): Response
    {
        return Inertia::render('OtherVisitor/Create');
    }

    // 部署選択画面
    public function selectDepartment(Request $request): Response
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'visitor_name' => 'required|string|max:255',
            'number_of_people' => 'required|integer|min:1',
            'purpose' => 'required|string',
            'business_card_image' => 'nullable|file|image|max:5120', // 5MB
        ]);

        // 部署一覧を取得
        $groups = $this->getGroups();

        return Inertia::render('OtherVisitor/SelectDepartment', [
            'visitorData' => $validated,
            'departments' => $groups,
        ]);
    }

    // 訪問者情報の保存
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'visitor_name' => 'required|string|max:255',
            'number_of_people' => 'required|integer|min:1',
            'purpose' => 'required|string',
            'group_id' => 'required|integer',
            'business_card_image' => 'nullable|file|image|max:5120',
        ]);

        // 名刺画像の保存
        $businessCardPath = null;
        if ($request->hasFile('business_card_image')) {
            $businessCardPath = $request->file('business_card_image')->store('business_cards', 'public');
        }

        // 訪問者情報を保存
        $visitor = Visitor::create([
            'company_name' => $validated['company_name'],
            'visitor_name' => $validated['visitor_name'],
            'number_of_people' => $validated['number_of_people'],
            'purpose' => $validated['purpose'],
            'group_id' => $validated['group_id'],
            'staff_member_id' => null, // その他の訪問者は特定のスタッフメンバーを持たない
            'business_card_image' => $businessCardPath,
            'visitor_type' => 'other',
            'check_in_time' => now(),
        ]);

        // 選択部署へTeams通知を送信
        $this->teamsNotification->notifyDepartmentVisitor($visitor, $validated['group_id']);

        return Inertia::render('OtherVisitor/Complete', [
            'visitorInfo' => $visitor,
        ]);
    }

    // 部署一覧を取得
    private function getGroups(): array
    {
        // TODO: 実際のデータベースから取得

        $groups = Group::select('id', 'name')->where('phone_number', "!=" , null )->get();

        return $groups->toArray();

        // return [
        //     ['id' => 1, 'name' => '営業部'],
        //     ['id' => 2, 'name' => '総務部'],
        //     ['id' => 3, 'name' => '経理部'],
        //     ['id' => 4, 'name' => '人事部'],
        //     ['id' => 5, 'name' => '開発部'],
        //     ['id' => 6, 'name' => 'マーケティング部'],
        // ];
    }

}

