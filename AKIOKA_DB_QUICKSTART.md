# akioka_db データベース接続 クイックスタートガイド

## 🚀 5ステップで完了！

### ステップ1: .env ファイルに設定を追加

`.env`ファイルを開き、以下を追加してください：

```env
# akioka_db データベース設定
AKIOKA_DB_DATABASE=akioka_db
AKIOKA_DB_USERNAME=root
AKIOKA_DB_PASSWORD=
```

**注意**: 
- データベースホストやポートがメインと異なる場合は、`AKIOKA_DB_HOST`と`AKIOKA_DB_PORT`も追加してください
- 同じサーバーの場合は、上記3つの設定のみでOKです

---

### ステップ2: データベースの作成（まだ作成していない場合）

MySQLにログインして、以下のコマンドを実行：

```sql
CREATE DATABASE akioka_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

### ステップ3: usersテーブルの準備

#### オプションA: 既存のデータベースにusersテーブルがある場合

そのまま使用できます。スキップして次のステップへ。

#### オプションB: 新規にテーブルを作成する場合

`AKIOKA_DB_SETUP.md`の「データベース構造」セクションのSQLを実行してください。

#### オプションC: 既存のusersテーブルをコピーする場合

```bash
# 現在のデータベースからエクスポート
mysqldump -u root -p reception_app users > users_backup.sql

# akioka_dbにインポート
mysql -u root -p akioka_db < users_backup.sql
```

---

### ステップ4: キャッシュのクリア

```bash
php artisan config:clear
php artisan cache:clear
```

---

### ステップ5: 接続テスト

```bash
php artisan test:akioka-db
```

このコマンドで以下をチェックします：
- ✅ 接続設定
- ✅ データベース接続
- ✅ usersテーブルの存在
- ✅ カラム構造
- ✅ データ取得
- ✅ 認証機能

---

## ✅ 完了！

すべてのテストが成功したら、ブラウザで`/login`にアクセスしてログインできます。

---

## 🔧 トラブルシューティング

### エラー: "Unknown database 'akioka_db'"

```sql
CREATE DATABASE akioka_db;
```

### エラー: "Access denied"

`.env`のユーザー名とパスワードを確認してください：
```env
AKIOKA_DB_USERNAME=正しいユーザー名
AKIOKA_DB_PASSWORD=正しいパスワード
```

### エラー: "Table 'akioka_db.users' doesn't exist"

usersテーブルを作成してください（ステップ3を参照）

### キャッシュが効いている

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 📚 詳細情報

詳しい設定方法やトラブルシューティングは、以下のファイルを参照してください：
- `AKIOKA_DB_SETUP.md` - 完全な設定ガイド
- `LOGIN_INFO.md` - ログイン情報とテストユーザー

---

## 🎯 チェックリスト

設定が完了したら、以下を確認してください：

- [ ] `.env`ファイルに`AKIOKA_DB_DATABASE`を追加した
- [ ] データベース`akioka_db`が存在する
- [ ] `users`テーブルが存在する
- [ ] `php artisan test:akioka-db`が成功した
- [ ] `/login`からログインできた

すべてチェックできたら完了です！ 🎉



