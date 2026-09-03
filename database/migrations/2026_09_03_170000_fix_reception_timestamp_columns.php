<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 受付系の日時列（received_at / picked_up_at / check_in_time）のタイムゾーン起因のズレを是正する。
 *
 * 背景:
 *  本番DBは AWS RDS（MySQL time_zone=UTC）だが、アプリは Asia/Tokyo。
 *  これらの列は DEFAULT/ON UPDATE current_timestamp() で「DB時計(=UTC)」が書き込まれ、
 *  さらに行更新のたびに ON UPDATE でUTCへ上書きされていた。
 *  Laravel はその値をJSTとして解釈するため、管理画面の表示が9時間手前にずれていた
 *  （開発環境はローカルMySQLがJSTのため露見しない）。
 *
 * 対応:
 *  1) DB時計による書き込み・更新時上書きを停止し、以後はアプリ(now())が入れた値のみ保持する。
 *  2) 既存データは、これら3列が設計上「レコード作成時刻」と同一instant（store()等で created と
 *     同時に now() 設定）である点を利用し、created_at 基準に復元する。
 *     → UTC混在・ON UPDATEドリフト・一括汚染（2026-07-06のバルク上書き）をまとめて是正。
 *
 * 注記:
 *  RDS akioka_db は他アプリと共有のため、接続タイムゾーンの変更（真の根本解）は全アプリ同時移行が
 *  必要で今回は採らない。本マイグレーションは受付アプリ専用テーブルのみを対象とし他アプリに影響しない。
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) DB時計(current_timestamp)による書き込み・更新時上書きを停止
        DB::statement("ALTER TABLE `deliveries` MODIFY `received_at`   TIMESTAMP NULL DEFAULT NULL");
        DB::statement("ALTER TABLE `pickups`    MODIFY `picked_up_at`  TIMESTAMP NULL DEFAULT NULL");
        DB::statement("ALTER TABLE `visitors`   MODIFY `check_in_time` TIMESTAMP NULL DEFAULT NULL");

        // 2) 既存データ補正（created_at 基準で復元）
        DB::statement("UPDATE `deliveries` SET `received_at`   = `created_at`");
        DB::statement("UPDATE `pickups`    SET `picked_up_at`  = `created_at`");
        DB::statement("UPDATE `visitors`   SET `check_in_time` = `created_at`"); // 現状すでに一致のため実質no-op
    }

    public function down(): void
    {
        // 列定義のみ復元（補正済みデータは戻さない）
        DB::statement("ALTER TABLE `deliveries` MODIFY `received_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        DB::statement("ALTER TABLE `pickups`    MODIFY `picked_up_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        DB::statement("ALTER TABLE `visitors`   MODIFY `check_in_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
};
