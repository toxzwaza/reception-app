<?php

namespace Database\Seeders;

use App\Models\ReceptionTerminal;
use Illuminate\Database\Seeder;

/**
 * 受付タブレット3台の初期登録（受付横断連携 §3.1、2026-10-30 村上さん指定）。
 * 既に同じ番号がある場合は変更しない（管理画面での変更を上書きしない）。
 *
 * php artisan db:seed --class=ReceptionTerminalSeeder
 */
class ReceptionTerminalSeeder extends Seeder
{
    public function run()
    {
        $terminals = [
            ['number' => 1, 'name' => '新社屋 来客玄関', 'camera_src' => 'nvr3'],
            ['number' => 2, 'name' => '新社屋 業者入口', 'camera_src' => 'cam1'],
            ['number' => 3, 'name' => '旧社屋 事務所入口', 'camera_src' => 'cam2'],
        ];

        foreach ($terminals as $terminal) {
            ReceptionTerminal::firstOrCreate(
                ['number' => $terminal['number']],
                ['name' => $terminal['name'], 'camera_src' => $terminal['camera_src'], 'enabled' => true]
            );
        }
    }
}
