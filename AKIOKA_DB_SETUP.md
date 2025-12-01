# akioka_db データベース接続設定ガイド

## 概要

このアプリケーションは、`users`テーブルを別データベース（`akioka_db`）から取得するように設定されています。

## 設定方法

### 1. .env ファイルに以下の設定を追加

`.env`ファイルを開き、以下の設定を追加してください：

```env
# ========================================
# akioka_db データベース接続設定
# （Userテーブル専用）
# ========================================

# データベースホスト（デフォルトのDB_HOSTと同じ場合は省略可）
AKIOKA_DB_HOST=127.0.0.1

# データベースポート（デフォルトのDB_PORTと同じ場合は省略可）
AKIOKA_DB_PORT=3306

# データベース名
AKIOKA_DB_DATABASE=akioka_db

# データベースユーザー名（デフォルトのDB_USERNAMEと同じ場合は省略可）
AKIOKA_DB_USERNAME=root

# データベースパスワード（デフォルトのDB_PASSWORDと同じ場合は省略可）
AKIOKA_DB_PASSWORD=

# データベースソケット（必要な場合のみ）
# AKIOKA_DB_SOCKET=
```

### 2. 設定例

#### 例1: 同じサーバーの別データベース
メインのデータベースと同じサーバーにある場合：

```env
# メインのデータベース設定
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reception_app
DB_USERNAME=root
DB_PASSWORD=secret

# akioka_db設定（同じサーバー、同じ認証情報）
AKIOKA_DB_DATABASE=akioka_db
# HOST, PORT, USERNAME, PAスワードは省略可（自動的にデフォルト値を使用）
```

#### 例2: 別サーバーのデータベース
別のサーバーにある場合：

```env
# メインのデータベース設定
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reception_app
DB_USERNAME=root
DB_PASSWORD=secret

# akioka_db設定（別サーバー）
AKIOKA_DB_HOST=192.168.1.100
AKIOKA_DB_PORT=3306
AKIOKA_DB_DATABASE=akioka_db
AKIOKA_DB_USERNAME=akioka_user
AKIOKA_DB_PASSWORD=akioka_password
```

### 3. キャッシュをクリア

設定変更後、以下のコマンドを実行してキャッシュをクリアしてください：

```bash
php artisan config:clear
php artisan cache:clear
```

## データベース構造

`akioka_db`データベースには、以下の構造の`users`テーブルが必要です：

```sql
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `emp_no` varchar(6) NOT NULL COMMENT '社員番号',
  `name` varchar(255) NOT NULL COMMENT '氏名',
  `password` text NOT NULL COMMENT 'パスワード',
  `email` varchar(255) DEFAULT NULL COMMENT 'メールアドレス',
  `gender_flg` tinyint(4) NOT NULL DEFAULT 0 COMMENT '性別 0:男性 1:女性',
  `group_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'グループID',
  `position_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT '役職ID',
  `process_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'プロセスID',
  `is_admin` tinyint(4) NOT NULL DEFAULT 0 COMMENT '管理者フラグ',
  `dispatch_flg` tinyint(4) NOT NULL DEFAULT 0 COMMENT '派遣フラグ',
  `part_flg` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'パートフラグ',
  `always_order_flg` tinyint(4) NOT NULL DEFAULT 0 COMMENT '常時発注フラグ',
  `duty_flg` tinyint(4) NOT NULL DEFAULT 0 COMMENT '当直フラグ',
  `fax_folder_name` varchar(255) DEFAULT NULL COMMENT 'FAXフォルダ名',
  `del_flg` tinyint(4) NOT NULL DEFAULT 0 COMMENT '削除フラグ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_emp_no_unique` (`emp_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 接続の仕組み

### Userモデルの設定

`app/Models/User.php`では、以下のように接続が指定されています：

```php
class User extends Authenticatable
{

    // ...
}
```

### 他のモデルとの関係

- **User モデル**: `akioka_db`データベースを使用
- **その他のモデル**（StaffMember, Visitor, Appointment等）: デフォルトのデータベースを使用

この設定により、認証に使用するユーザー情報のみ別データベースから取得し、その他のアプリケーションデータは通常のデータベースに保存されます。

## トラブルシューティング

### エラー: "SQLSTATE[HY000] [1049] Unknown database 'akioka_db'"

**原因**: `akioka_db`データベースが存在しない

**解決方法**:
```sql
CREATE DATABASE akioka_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### エラー: "SQLSTATE[HY000] [1045] Access denied"

**原因**: データベースの認証情報が間違っている

**解決方法**: `.env`ファイルの以下の項目を確認
- `AKIOKA_DB_USERNAME`
- `AKIOKA_DB_PASSWORD`

### エラー: "SQLSTATE[42S02] Table 'akioka_db.users' doesn't exist"

**原因**: `users`テーブルが存在しない

**解決方法**: 上記のSQL文を使用してテーブルを作成

### 接続テスト

以下のコマンドで接続をテストできます：

```bash
php artisan tinker
```

Tinkerで以下を実行：
```php
// 接続テスト
DB::connection('akioka_db')->getPdo();

// ユーザー一覧を取得
User::all();

// 特定のユーザーを検索
User::where('emp_no', '000001')->first();
```

## 注意事項

1. **セキュリティ**: `.env`ファイルは絶対にGitにコミットしないでください
2. **バックアップ**: データベース設定変更前に、必ずバックアップを取ってください
3. **権限**: データベースユーザーに適切な権限（SELECT, INSERT, UPDATE, DELETE）が付与されていることを確認してください

## よくある質問（FAQ）

### Q1: 既存のusersテーブルのデータを移行する必要がありますか？

A: はい。現在のデータベースの`users`テーブルのデータを`akioka_db`にコピーする必要があります。

```bash
# データをエクスポート
mysqldump -u root -p reception_app users > users.sql

# データをインポート
mysql -u root -p akioka_db < users.sql
```

### Q2: デフォルトのデータベースに戻すには？

A: `app/Models/User.php`から以下の行を削除またはコメントアウトしてください：

```php
// protected $connection = 'akioka_db';
```

### Q3: 複数のデータベースを使用することでパフォーマンスは低下しますか？

A: 同じサーバー内であれば、パフォーマンスへの影響はほぼありません。別サーバーの場合は、ネットワーク遅延が発生する可能性があります。

## サポート

問題が解決しない場合は、以下の情報を添えてお問い合わせください：
- エラーメッセージの全文
- `.env`ファイルの設定（パスワードは除く）
- `php artisan config:cache`実行後の結果



