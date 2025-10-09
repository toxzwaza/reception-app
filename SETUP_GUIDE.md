# 受付システム セットアップガイド

## システム概要

このシステムは以下の4つの受付タイプに対応しています：

1. **アポイントありの方**
   - QRコード読み取りまたは受付番号（4桁）入力
   - 担当者へTeams通知を送信

2. **納品・集荷の方**
   - 納品または集荷を選択して各処理へ遷移

3. **面接の方**
   - 自動で面接担当者へTeams通知を送信

4. **アポイントなしの方**
   - 訪問者情報入力（名刺撮影機能付き）
   - 部署選択後、該当部署へTeams通知を送信

## セットアップ手順

### 1. 依存パッケージのインストール

```bash
# Composerパッケージのインストール
composer install

# npmパッケージのインストール
npm install
```

### 2. 環境変数の設定

`.env`ファイルに以下を追加：

```env
# Microsoft Teams Webhook URLs
TEAMS_DEFAULT_WEBHOOK_URL=https://outlook.office.com/webhook/your-webhook-url
TEAMS_INTERVIEW_WEBHOOK_URL=https://outlook.office.com/webhook/your-interview-webhook-url

# 部署別Webhook URL
TEAMS_DEPT_SALES_WEBHOOK_URL=https://outlook.office.com/webhook/your-sales-dept-webhook-url
TEAMS_DEPT_GENERAL_WEBHOOK_URL=https://outlook.office.com/webhook/your-general-dept-webhook-url
TEAMS_DEPT_ACCOUNTING_WEBHOOK_URL=https://outlook.office.com/webhook/your-accounting-dept-webhook-url
TEAMS_DEPT_HR_WEBHOOK_URL=https://outlook.office.com/webhook/your-hr-dept-webhook-url
TEAMS_DEPT_DEV_WEBHOOK_URL=https://outlook.office.com/webhook/your-dev-dept-webhook-url
TEAMS_DEPT_MARKETING_WEBHOOK_URL=https://outlook.office.com/webhook/your-marketing-dept-webhook-url
```

詳細は`TEAMS_SETUP.md`を参照してください。

### 3. データベースマイグレーション

```bash
php artisan migrate
```

### 4. ストレージリンクの作成

```bash
php artisan storage:link
```

### 5. アプリケーションキーの生成（初回のみ）

```bash
php artisan key:generate
```

### 6. フロントエンドのビルド

開発環境：
```bash
npm run dev
```

本番環境：
```bash
npm run build
```

### 7. サーバーの起動

```bash
php artisan serve
```

アプリケーションは http://localhost:8000 で利用できます。

## データベース構造

### visitors テーブルの新しいフィールド

- `department_id` - 部署ID（その他の訪問者用）
- `reception_number` - 受付番号（4桁、アポイントあり用）
- `visitor_type` - 訪問者タイプ（appointment, interview, other）
- `number_of_people` - 人数
- `purpose` - 要件

### staff_members テーブルの新しいフィールド

- `teams_webhook_url` - 担当者個別のTeams Webhook URL

## Teams通知の有効化

実際にTeams通知を送信するには、`app/Services/TeamsNotificationService.php`の`sendToWebhook`メソッドでHTTPリクエストのコメントアウトを解除してください。

```php
// コメントアウトを解除
$response = Http::post($webhookUrl, $message);
```

## 機能詳細

### 1. アポイントありの方（/appointment）

**左側: QRコード読み取り**
- カメラを使用してQRコードをスキャン
- QRコードデータから訪問者情報を取得してチェックイン

**右側: 受付番号入力**
- 4桁の受付番号を入力
- 番号から訪問者情報を取得してチェックイン

**通知機能:**
- チェックイン完了後、担当者へTeams通知を自動送信

### 2. 納品・集荷の方（/delivery-pickup/select）

- 「納品」または「集荷」を選択
- 納品: `/delivery/create`へ遷移
- 集荷: `/pickup/create`へ遷移

### 3. 面接の方（/interview）

- 自動で面接担当者へTeams通知を送信
- 待機メッセージを表示

### 4. アポイントなしの方（/other-visitor/create）

**訪問者情報入力:**
- 社名（名刺撮影可能）
- 氏名（名刺撮影可能）
- 人数
- 要件

**部署選択:**
- 6つの部署から選択
  - 営業部
  - 総務部
  - 経理部
  - 人事部
  - 開発部
  - マーケティング部

**通知機能:**
- 選択部署へ訪問者情報をTeams通知

## カメラ機能

名刺撮影やQRコード読み取りにカメラを使用します。

**対応ブラウザ:**
- Chrome（推奨）
- Edge
- Safari（iOS 11+）

**カメラアクセスの許可が必要です。**

## トラブルシューティング

### GD拡張が無効の場合

`php.ini`で以下を有効化：
```ini
extension=gd
```

### カメラが起動しない

1. ブラウザがHTTPS接続かlocalhost上で動作していることを確認
2. カメラアクセス権限が許可されているか確認
3. 他のアプリケーションがカメラを使用していないか確認

### Teams通知が届かない

1. Webhook URLが正しいか確認
2. `.env`変更後にキャッシュクリア: `php artisan config:clear`
3. ログ確認: `tail -f storage/logs/laravel.log`

## TODO / 今後の実装

### 必須実装項目

1. **事前登録訪問者データベース**
   - QRコードおよび受付番号での訪問者検索機能
   - 事前登録システムの構築

2. **部署マスタ管理**
   - Departmentモデルの作成
   - 部署ごとのWebhook URL管理画面

3. **名刺OCR機能**
   - 撮影した名刺から社名・氏名を自動抽出
   - Google Cloud Vision APIまたはAzure Computer Visionの統合

4. **訪問者履歴管理**
   - 訪問者詳細ページ
   - チェックアウト機能
   - 訪問履歴検索

### 推奨実装項目

- 訪問者情報のエクスポート機能（CSV/Excel）
- ダッシュボード（受付状況の可視化）
- 訪問者統計レポート
- メール通知機能（Teams通知の補完）
- 多言語対応（英語、中国語など）

## ライセンス

このプロジェクトのライセンス情報はここに記載してください。






