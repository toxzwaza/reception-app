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
    protected $signature = 'teams:test {email? : 送信先メールアドレス（AkiTalk Bridge で解決）}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AkiTalk Bridge 経由でテストのTeams通知を送信する（宛先メール指定可）';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $this->info($email
            ? "テスト通知を送信します（宛先: {$email}）..."
            : '宛先未指定のためテスト通知は送信されません（宛先メールを引数で指定してください）...');

        $teamsService = new TeamsNotificationService();
        $result = $teamsService->sendTestNotification($email);

        if ($result) {
            $this->info('✅ 送信処理が完了しました。Teams をご確認ください。');
        } else {
            $this->error('❌ 送信に失敗しました。AkiTalk Bridge 設定とログを確認してください。');
        }

        return $result ? 0 : 1;
    }
}
