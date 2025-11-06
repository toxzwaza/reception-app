<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facility;
use App\Models\ScheduleEvent;
use App\Models\User;
use Carbon\Carbon;

class FacilityScheduleTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 施設がない場合は作成
        $facilities = Facility::all();
        if ($facilities->isEmpty()) {
            $facilities = collect([
                Facility::create(['name' => '会議室A']),
                Facility::create(['name' => '会議室B']),
                Facility::create(['name' => '会議室C']),
            ]);
            $this->command->info('施設を作成しました。');
        }

        // ユーザーを取得（担当スタッフとして使用）
        $users = User::limit(3)->get();
        if ($users->isEmpty()) {
            $this->command->warn('ユーザーが存在しません。ユーザーを作成してから実行してください。');
            return;
        }

        // テスト用の予定を作成（今日から7日間）
        $today = Carbon::today();
        
        foreach ($facilities as $facility) {
            // 各施設に3〜5件の予定を作成
            $scheduleCount = rand(3, 5);
            
            for ($i = 0; $i < $scheduleCount; $i++) {
                // ランダムな日付（今日から7日以内）
                $date = $today->copy()->addDays(rand(0, 6));
                
                // ランダムな開始時刻（9:00〜16:00）
                $startHour = rand(9, 16);
                $startTime = $date->copy()->setTime($startHour, 0, 0);
                
                // 1〜3時間の予定
                $duration = rand(1, 3);
                $endTime = $startTime->copy()->addHours($duration);
                
                // 予定を作成（日付と時刻を分けて保存）
                $schedule = ScheduleEvent::create([
                    'facility_id' => $facility->id,
                    'date' => $date->format('Y-m-d'),
                    'title' => $this->getRandomTitle(),
                    'start_datetime' => $startTime->format('H:i'),  // 時刻のみ
                    'end_datetime' => $endTime->format('H:i'),      // 時刻のみ
                    'badge' => $this->getRandomBadge(),
                    'description_url' => null,
                ]);
                
                // ランダムなユーザーを参加者として追加
                $schedule->participants()->attach($users->random()->id);
                
                $this->command->info("予定を作成: {$facility->name} - {$schedule->title} ({$date->format('Y-m-d')} {$startTime->format('H:i')} - {$endTime->format('H:i')})");
            }
        }
        
        $this->command->info('テスト用の予定を作成しました。');
    }
    
    /**
     * ランダムなタイトルを生成
     */
    private function getRandomTitle(): string
    {
        $titles = [
            '営業戦略ミーティング',
            'プロジェクト進捗会議',
            '新製品開発会議',
            'クライアント打ち合わせ',
            '定例会議',
            '研修セッション',
            'チームビルディング',
            '予算会議',
            '採用面接',
            '技術検討会',
        ];
        
        return $titles[array_rand($titles)];
    }
    
    /**
     * ランダムなバッジを生成
     */
    private function getRandomBadge(): ?string
    {
        $badges = [
            '重要',
            '定例',
            '緊急',
            null,
            null, // nullの確率を高める
        ];
        
        return $badges[array_rand($badges)];
    }
}

