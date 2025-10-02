<?php

namespace App\Http\Controllers;

use App\Models\InterviewPhone;
use App\Services\TeamsNotificationService;
use Inertia\Inertia;
use Inertia\Response;

class InterviewController extends Controller
{
    protected $teamsNotification;

    public function __construct(TeamsNotificationService $teamsNotification)
    {
        $this->teamsNotification = $teamsNotification;
    }

    // 面接受付画面
    public function index(): Response
    {
        // 面接担当者へ通知を送信
        $this->teamsNotification->notifyInterviewArrival();
        
        // 有効な面接用電話番号を取得
        $interviewPhones = InterviewPhone::active()->ordered()->get();
        
        return Inertia::render('Interview/Index', [
            'interviewPhones' => $interviewPhones,
        ]);
    }
}

