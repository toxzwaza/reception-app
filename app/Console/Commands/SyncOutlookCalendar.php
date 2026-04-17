<?php

namespace App\Console\Commands;

use App\Services\OutlookCalendarService;
use Illuminate\Console\Command;

class SyncOutlookCalendar extends Command
{
    protected $signature = 'outlook:sync-calendar {--days=30 : 同期対象の日数}';
    protected $description = 'Outlook (Graph API) から会議室予定を同期';

    public function handle(OutlookCalendarService $service): int
    {
        if (!$service->isConfigured()) {
            $this->warn('Graph API の認証情報が未設定です（GRAPH_TENANT_ID / GRAPH_CLIENT_ID / GRAPH_CLIENT_SECRET）');
            return self::FAILURE;
        }

        $days = (int) $this->option('days');
        $this->info("Outlook カレンダー同期開始（{$days}日分）...");

        $result = $service->syncAllFacilities($days);

        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $err) {
                $this->error("  ✗ {$err['facility']}: {$err['error']}");
            }
        }

        $this->info("完了: {$result['facilities']}施設 / {$result['total']}件の予定を同期");

        return empty($result['errors']) ? self::SUCCESS : self::FAILURE;
    }
}
