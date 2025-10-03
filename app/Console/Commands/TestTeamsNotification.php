<?php

namespace App\Console\Commands;

use App\Services\TeamsNotificationService;
use Illuminate\Console\Command;

class TestTeamsNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teams:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test notification to Teams';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending test notification to Teams...');

        $teamsService = new TeamsNotificationService();
        $result = $teamsService->sendTestNotification();

        if ($result) {
            $this->info('✅ Test notification sent successfully!');
        } else {
            $this->error('❌ Failed to send test notification. Check your webhook URL and logs.');
        }

        return $result ? 0 : 1;
    }
}
