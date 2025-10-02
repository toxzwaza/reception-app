<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestAkiokaDbConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:akioka-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'akioka_dbデータベースへの接続をテストします';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('========================================');
        $this->info('akioka_db 接続テスト');
        $this->info('========================================');
        $this->newLine();

        // 1. 接続設定の確認
        $this->info('1. 接続設定の確認...');
        try {
            $config = config('database.connections.akioka_db');
            $this->line('   ホスト: ' . $config['host']);
            $this->line('   ポート: ' . $config['port']);
            $this->line('   データベース: ' . $config['database']);
            $this->line('   ユーザー名: ' . $config['username']);
            $this->comment('   ✓ 設定は正常に読み込まれました');
        } catch (\Exception $e) {
            $this->error('   ✗ 設定の読み込みに失敗: ' . $e->getMessage());
            return Command::FAILURE;
        }
        $this->newLine();

        // 2. データベース接続テスト
        $this->info('2. データベース接続テスト...');
        try {
            $pdo = DB::connection('akioka_db')->getPdo();
            $this->comment('   ✓ データベースに接続できました');
        } catch (\Exception $e) {
            $this->error('   ✗ データベース接続に失敗: ' . $e->getMessage());
            $this->newLine();
            $this->warn('対処方法:');
            $this->line('  1. .envファイルにakioka_db設定を追加してください');
            $this->line('  2. データベースが存在するか確認してください: CREATE DATABASE akioka_db;');
            $this->line('  3. ユーザー名とパスワードが正しいか確認してください');
            $this->line('  4. キャッシュをクリアしてください: php artisan config:clear');
            return Command::FAILURE;
        }
        $this->newLine();

        // 3. usersテーブルの存在確認
        $this->info('3. usersテーブルの存在確認...');
        try {
            $exists = DB::connection('akioka_db')
                ->getSchemaBuilder()
                ->hasTable('users');
            
            if ($exists) {
                $this->comment('   ✓ usersテーブルが存在します');
            } else {
                $this->error('   ✗ usersテーブルが存在しません');
                $this->newLine();
                $this->warn('対処方法:');
                $this->line('  AKIOKA_DB_SETUP.md の「データベース構造」セクションを参照してください');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('   ✗ テーブル確認に失敗: ' . $e->getMessage());
            return Command::FAILURE;
        }
        $this->newLine();

        // 4. カラム構造の確認
        $this->info('4. usersテーブルのカラム構造確認...');
        try {
            $columns = DB::connection('akioka_db')
                ->getSchemaBuilder()
                ->getColumnListing('users');
            
            $requiredColumns = [
                'id', 'emp_no', 'name', 'password', 'email',
                'del_flg', 'is_admin', 'created_at', 'updated_at'
            ];
            
            $missingColumns = array_diff($requiredColumns, $columns);
            
            if (empty($missingColumns)) {
                $this->comment('   ✓ 必須カラムがすべて存在します');
                $this->line('   カラム数: ' . count($columns));
            } else {
                $this->warn('   ⚠ 不足しているカラム: ' . implode(', ', $missingColumns));
            }
        } catch (\Exception $e) {
            $this->error('   ✗ カラム確認に失敗: ' . $e->getMessage());
        }
        $this->newLine();

        // 5. Userモデルでのデータ取得テスト
        $this->info('5. Userモデルでのデータ取得テスト...');
        try {
            $userCount = User::count();
            $this->comment('   ✓ Userモデルでデータを取得できました');
            $this->line('   登録ユーザー数: ' . $userCount);
            
            if ($userCount > 0) {
                $this->newLine();
                $this->info('   登録ユーザー一覧（最大5件）:');
                $users = User::limit(5)->get(['id', 'emp_no', 'name', 'email', 'is_admin', 'del_flg']);
                
                $headers = ['ID', '社員番号', '氏名', 'メール', '管理者', '削除'];
                $rows = $users->map(function($user) {
                    return [
                        $user->id,
                        $user->emp_no,
                        $user->name,
                        $user->email ?? '(なし)',
                        $user->is_admin ? 'Yes' : 'No',
                        $user->del_flg ? '削除済み' : '有効',
                    ];
                })->toArray();
                
                $this->table($headers, $rows);
            }
        } catch (\Exception $e) {
            $this->error('   ✗ データ取得に失敗: ' . $e->getMessage());
            return Command::FAILURE;
        }
        $this->newLine();

        // 6. 認証テスト
        $this->info('6. 認証機能の確認...');
        try {
            $testUser = User::where('del_flg', 0)->first();
            if ($testUser) {
                $this->comment('   ✓ 有効なユーザーが見つかりました');
                $this->line('   社員番号: ' . $testUser->emp_no);
                $this->line('   メール: ' . $testUser->email ?? '(なし)');
                $this->line('   ログイン可能: ' . ($testUser->del_flg == 0 ? 'Yes' : 'No'));
            } else {
                $this->warn('   ⚠ 有効なユーザーが見つかりませんでした（del_flg = 0）');
                $this->line('   ログイン用のユーザーを登録してください');
            }
        } catch (\Exception $e) {
            $this->error('   ✗ 認証確認に失敗: ' . $e->getMessage());
        }
        $this->newLine();

        // 最終結果
        $this->info('========================================');
        $this->info('✓ すべてのテストが完了しました！');
        $this->info('========================================');
        $this->newLine();
        $this->comment('akioka_dbデータベースへの接続は正常に動作しています。');
        $this->comment('ログイン画面（/login）からログインできます。');

        return Command::SUCCESS;
    }
}
