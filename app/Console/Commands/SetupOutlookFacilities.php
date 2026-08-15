<?php

namespace App\Console\Commands;

use App\Models\Facility;
use Illuminate\Console\Command;

class SetupOutlookFacilities extends Command
{
    protected $signature = 'outlook:setup-facilities';
    protected $description = 'facilities テーブルに Outlook Room/Equipment Mailbox のメールアドレスを設定';

    /**
     * 施設名の変更（2026-08-15 施設再編）。旧名称の行を新名称へリネームし、
     * schedule_events 等の facility_id 紐付けを維持する。
     */
    private const RENAMES = [
        '社長室'     => '大会議室',
        '応接室'     => '小会議室',
        '社員休憩室' => '引張試験室',
    ];

    /**
     * サイボウズで削除された施設。メールアドレスを解除して同期対象外にする
     * （行の削除は予定履歴の紐付きがあるため行わない）。
     */
    private const RETIRED = [
        '事務室面談テーブル',
        '技術室',
    ];

    private const FACILITY_MAP = [
        // 旧事務所
        '大会議室'              => 'meetingroom1@akioka-ltd.jp',
        '小会議室'              => 'meetingroom2@akioka-ltd.jp',
        '引張試験室'            => 'meetingroom4@akioka-ltd.jp',
        '二階食堂'              => 'meetingroom5@akioka-ltd.jp',
        // 新社屋（2026-08-15 追加）
        'solarieホール'         => 'meetingroom7@akioka-ltd.jp',
        '社長室'                => 'meetingroom8@akioka-ltd.jp',
        'Room 201'              => 'meetingroom9@akioka-ltd.jp',
        'Room 202'              => 'meetingroom10@akioka-ltd.jp',
        'Room 203'              => 'meetingroom11@akioka-ltd.jp',
        'Room 204'              => 'meetingroom12@akioka-ltd.jp',
        'ラボ'                  => 'meetingroom13@akioka-ltd.jp',
        '書庫'                  => 'meetingroom14@akioka-ltd.jp',
        'imonoラウンジ'         => 'meetingroom15@akioka-ltd.jp',
        'サーバー室'            => 'meetingroom16@akioka-ltd.jp',
        'RF'                    => 'meetingroom17@akioka-ltd.jp',
        // 社用車
        '社用車(ノア)'          => 'car1@akioka-ltd.jp',
        '社用車(プロボックス)'  => 'car2@akioka-ltd.jp',
    ];

    public function handle(): int
    {
        // 1. 名称変更（旧名称の行が残っている場合のみ）
        foreach (self::RENAMES as $old => $new) {
            $facility = Facility::where('name', $old)->first();
            if (!$facility) {
                continue;
            }
            if (Facility::where('name', $new)->exists()) {
                $this->warn("  ✗ リネーム先が既に存在するためスキップ: {$old} => {$new}");
                continue;
            }
            $facility->update(['name' => $new]);
            $this->info("  ✓ 名称変更: {$old} => {$new}");
        }

        // 2. 廃止施設のメールアドレス解除
        foreach (self::RETIRED as $name) {
            $facility = Facility::where('name', $name)->first();
            if ($facility && $facility->outlook_resource_email !== null) {
                $facility->update(['outlook_resource_email' => null]);
                $this->info("  ✓ 同期対象外に変更（廃止）: {$name}");
            }
        }

        // 3. メールアドレス設定（未登録の施設は新規作成）
        $updated = 0;
        $skipped = 0;
        $created = 0;

        foreach (self::FACILITY_MAP as $name => $email) {
            $facility = Facility::where('name', $name)->first();

            if (!$facility) {
                Facility::create(['name' => $name, 'outlook_resource_email' => $email]);
                $this->info("  ✓ 新規作成: {$name} => {$email}");
                $created++;
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
        $this->info("完了: 新規 {$created} / 更新 {$updated} / スキップ {$skipped}");

        return self::SUCCESS;
    }
}
