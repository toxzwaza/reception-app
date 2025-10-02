# ログイン情報

## 管理画面アクセス方法

管理画面には以下のURLからアクセスできます：
```
http://localhost/login
または
http://localhost/admin/dashboard
```

## ログイン認証について

このシステムでは、既存のusersテーブルを使用してログイン認証を行います。

### ログイン方法

以下の2つの方法でログインできます：
1. **社員番号**でログイン
2. **メールアドレス**でログイン

### テストユーザーアカウント

#### 1. 管理者ユーザー
- **社員番号**: `000001`
- **メールアドレス**: `admin@example.com`
- **パスワード**: `password`
- **権限**: 管理者

#### 2. 一般ユーザー（男性）
- **社員番号**: `000002`
- **メールアドレス**: `yamada@example.com`
- **パスワード**: `password`
- **権限**: 一般

#### 3. 一般ユーザー（女性）
- **社員番号**: `000003`
- **メールアドレス**: `sato@example.com`
- **パスワード**: `password`
- **権限**: 一般

## usersテーブル構造

| カラム名 | 型 | 説明 |
|---------|-----|------|
| id | bigint(20) | 主キー |
| emp_no | varchar(6) | 社員番号（ユニーク） |
| name | varchar(255) | 氏名 |
| password | text | パスワード（ハッシュ化） |
| email | varchar(255) | メールアドレス |
| gender_flg | tinyint(4) | 性別（0:男性、1:女性） |
| group_id | bigint(20) | グループID |
| position_id | bigint(20) | 役職ID |
| process_id | bigint(20) | プロセスID |
| is_admin | tinyint(4) | 管理者フラグ（0:一般、1:管理者） |
| dispatch_flg | tinyint(4) | 派遣フラグ |
| part_flg | tinyint(4) | パートフラグ |
| always_order_flg | tinyint(4) | 常時発注フラグ |
| duty_flg | tinyint(4) | 当直フラグ |
| fax_folder_name | varchar(255) | FAXフォルダ名 |
| del_flg | tinyint(4) | 削除フラグ（0:有効、1:削除済み） |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |

## セキュリティ機能

### 1. 削除済みユーザーのログイン制限
`del_flg = 1`のユーザーはログインできません。

### 2. レート制限
連続してログインに失敗すると、一時的にアカウントがロックされます。
- 失敗回数制限: 5回
- ロック時間: 失敗後から一定時間

### 3. パスワードハッシュ化
パスワードはBcryptでハッシュ化されて保存されます。

## データベースのリセット（開発時のみ）

テストデータを再作成する場合：

```bash
# すべてのテーブルを削除して再作成
php artisan migrate:fresh

# テストユーザーを作成
php artisan db:seed --class=TestUserSeeder

# テストスタッフメンバーを作成
php artisan db:seed --class=TestStaffMemberSeeder
```

## 管理画面の機能

ログイン後、以下の機能にアクセスできます：

1. **ダッシュボード** (`/admin/dashboard`)
   - お知らせ表示
   - 統計情報表示
   - 機能メニュー

2. **事前アポイント登録** (`/admin/appointments`)
   - アポイントの一覧表示
   - 新規登録
   - 編集・削除
   - 受付番号自動生成

3. **面接時の通話先電話番号** (`/admin/interview-phones`)
   - 電話番号の一覧表示
   - 新規登録
   - 編集・削除
   - 表示順設定

4. **お知らせ管理** (`/admin/announcements`)
   - お知らせの一覧表示
   - 新規登録
   - 編集・削除
   - 表示期間設定

## 注意事項

- 本番環境では、テストユーザーアカウントを削除するか、パスワードを変更してください。
- セキュリティのため、強固なパスワードを設定してください。
- 定期的にパスワードを変更することを推奨します。



