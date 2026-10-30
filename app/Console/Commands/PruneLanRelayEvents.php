<?php

namespace App\Console\Commands;

use App\Models\LanRelayEvent;
use Illuminate\Console\Command;

/**
 * 保存期間（既定90日）を過ぎた来客イベントを削除する（受付横断連携 §6.2）
 */
class PruneLanRelayEvents extends Command
{
    protected $signature = 'lan-relay:prune {--days= : 保存日数（省略時は config lan_relay.retention_days）}';

    protected $description = '保存期間を過ぎた来客イベント（lan_relay_events）を削除する';

    public function handle()
    {
        $days = (int) ($this->option('days') ?: config('lan_relay.retention_days', 90));
        if ($days < 1) {
            $this->error('保存日数は1以上を指定してください。');

            return self::FAILURE;
        }

        $deleted = LanRelayEvent::where('occurred_at', '<', now()->subDays($days))->delete();

        $this->info("{$days}日より古い来客イベントを {$deleted} 件削除しました。");

        return self::SUCCESS;
    }
}
