<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * アカウント管理CSV（AccountList.csv）から社員番号とヨミの2列のみを読み取り、
 * users.name_kana に反映する。
 *
 * CSVには機密情報（パスワード等）が含まれるため、
 * 社員番号・ヨミ以外の列は読み取らず、内容の出力・ログ記録も行わない。
 */
class ImportStaffKana extends Command
{
    protected $signature = 'staff:import-kana {csv : AccountList.csv のパス}';

    protected $description = 'アカウント管理CSVのヨミを users.name_kana に一括取込（社員番号で突合）';

    public function handle(): int
    {
        $path = $this->argument('csv');

        if (!is_readable($path)) {
            $this->error("CSVファイルが読み取れません: {$path}");
            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        if ($header === false) {
            $this->error('CSVが空です。');
            fclose($handle);
            return self::FAILURE;
        }

        // BOM除去して列位置を特定
        $header = array_map(fn ($col) => preg_replace('/^\xEF\xBB\xBF/', '', (string) $col), $header);
        $empNoIndex = array_search('社員番号', $header, true);
        $kanaIndex = array_search('ヨミ', $header, true);

        if ($empNoIndex === false || $kanaIndex === false) {
            $this->error('ヘッダーに「社員番号」「ヨミ」列が見つかりません。');
            fclose($handle);
            return self::FAILURE;
        }

        $updated = 0;
        $notFound = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $empNo = trim((string) ($row[$empNoIndex] ?? ''));
            $kana = trim((string) ($row[$kanaIndex] ?? ''));

            if ($empNo === '' || $kana === '') {
                $skipped++;
                continue;
            }

            $affected = User::where('emp_no', $empNo)->update(['name_kana' => $kana]);
            $affected > 0 ? $updated++ : $notFound++;
        }

        fclose($handle);

        $this->info("取込完了: 更新 {$updated} 件 / 社員番号不一致 {$notFound} 件 / ヨミ空でスキップ {$skipped} 件");

        return self::SUCCESS;
    }
}
