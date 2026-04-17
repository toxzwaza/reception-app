<?php

namespace App\Console\Commands;

use App\Models\Facility;
use Illuminate\Console\Command;

class SetupOutlookFacilities extends Command
{
    protected $signature = 'outlook:setup-facilities';
    protected $description = 'facilities テーブルに Outlook Room/Equipment Mailbox のメールアドレスを設定';

    private const FACILITY_MAP = [
        '社長室'                => 'meetingroom1@akioka-ltd.jp',
        '応接室'                => 'meetingroom2@akioka-ltd.jp',
        '事務室面談テーブル'     => 'meetingroom3@akioka-ltd.jp',
        '社員休憩室'            => 'meetingroom4@akioka-ltd.jp',
        '二階食堂'              => 'meetingroom5@akioka-ltd.jp',
        '技術室'                => 'meetingroom6@akioka-ltd.jp',
        '社用車(ノア)'          => 'car1@akioka-ltd.jp',
        '社用車(プロボックス)'  => 'car2@akioka-ltd.jp',
    ];

    public function handle(): int
    {
        $updated = 0;
        $skipped = 0;
        $notFound = 0;

        foreach (self::FACILITY_MAP as $name => $email) {
            $facility = Facility::where('name', $name)->first();

            if (!$facility) {
                $this->warn("  ✗ 施設が見つかりません: {$name}");
                $notFound++;
                continue;
            }

            if ($facility->outlook_resource_email === $email) {
                $this->line("  - 変更なし: {$name}");
                $skipped++;
                continue;
            }

            $facility->update(['outlook_resource_email' => $email]);
            $this->info("  ✓ {$name} => {$email}");
            $updated++;
        }

        $this->newLine();
        $this->info("完了: 更新 {$updated} / スキップ {$skipped} / 未発見 {$notFound}");

        return self::SUCCESS;
    }
}
