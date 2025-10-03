# Microsoft Teams通知セットアップガイド

## 概要
受付システムでは、訪問者の受付時に担当者や部署へMicrosoft Teams通知を送信します。

## Teams Webhook URLの取得方法

1. Microsoft Teamsを開く
2. 通知を受け取りたいチャネルを選択
3. チャネル名の横の「...」をクリック
4. 「コネクタ」を選択
5. 「受信Webhook」を検索して「構成」をクリック
6. Webhook名を入力（例: 受付システム通知）
7. 「作成」をクリック
8. 表示されたWebhook URLをコピー

## 環境変数の設定

`.env`ファイルに以下の環境変数を追加してください：

```env
# デフォルトのWebhook URL（その他の訪問者用）
TEAMS_DEFAULT_WEBHOOK_URL=https://outlook.office.com/webhook/your-webhook-url

# 面接担当者用Webhook URL
TEAMS_INTERVIEW_WEBHOOK_URL=https://outlook.office.com/webhook/your-interview-webhook-url

# 部署別Webhook URL
TEAMS_DEPT_SALES_WEBHOOK_URL=https://outlook.office.com/webhook/your-sales-dept-webhook-url
TEAMS_DEPT_GENERAL_WEBHOOK_URL=https://outlook.office.com/webhook/your-general-dept-webhook-url
TEAMS_DEPT_ACCOUNTING_WEBHOOK_URL=https://outlook.office.com/webhook/your-accounting-dept-webhook-url
TEAMS_DEPT_HR_WEBHOOK_URL=https://outlook.office.com/webhook/your-hr-dept-webhook-url
TEAMS_DEPT_DEV_WEBHOOK_URL=https://outlook.office.com/webhook/your-dev-dept-webhook-url
TEAMS_DEPT_MARKETING_WEBHOOK_URL=https://outlook.office.com/webhook/your-marketing-dept-webhook-url
```

## 通知の有効化

`app/Services/TeamsNotificationService.php`ファイルで、実際のHTTP通信を有効にしてください：

```php
private function sendToWebhook(string $webhookUrl, array $message): void
{
    try {
        // コメントアウトを解除
        $response = Http::post($webhookUrl, $message);
        
        if ($response->successful()) {
            Log::info('Teams通知を送信しました', [
                'webhook_url' => $webhookUrl,
            ]);
        } else {
            Log::error('Teams通知の送信に失敗しました', [
                'webhook_url' => $webhookUrl,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    } catch (\Exception $e) {
        Log::error('Teams通知送信中にエラーが発生しました', [
            'webhook_url' => $webhookUrl,
            'error' => $e->getMessage()
        ]);
    }
}
```

## 通知の種類

### 1. アポイントアリの来訪者通知
- QRコードまたは受付番号で受付された訪問者の情報を担当者へ通知
- 送信先: 担当者のteams_webhook_url

### 2. 面接の来訪者通知
- 面接で来訪した訪問者の受付通知
- 送信先: TEAMS_INTERVIEW_WEBHOOK_URL

### 3. その他の来訪者通知
- 部署を指定した訪問者の情報を該当部署へ通知
- 送信先: 各部署のWebhook URL

## トラブルシューティング

### 通知が届かない場合

1. Webhook URLが正しく設定されているか確認
2. `.env`ファイルを変更した後、キャッシュをクリア:
   ```bash
   php artisan config:clear
   ```
3. ログファイル（`storage/logs/laravel.log`）を確認
4. Teamsのコネクタが有効になっているか確認

### エラーログの確認

```bash
tail -f storage/logs/laravel.log
```






