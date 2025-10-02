# Twilio電話機能 クイックスタートガイド

## 📋 概要
このプロジェクトにTwilio電話機能のテスト環境が追加されました。

## 🚀 セットアップ（3ステップ）

### 1. Twilio SDKのインストール

**Windowsの場合:**
```bash
install_twilio.bat
```

**Mac/Linuxの場合:**
```bash
chmod +x install_twilio.sh
./install_twilio.sh
```

**または手動で:**
```bash
composer require twilio/sdk
```

### 2. 環境変数の設定

`.env` ファイルに以下を追加：

```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_PHONE_NUMBER=+15551234567
```

### 3. アクセス

ブラウザで以下のURLにアクセス：

```
http://localhost/twilio-test
```

## 📁 作成されたファイル

### バックエンド
- `app/Http/Controllers/TwilioTestController.php` - メインコントローラー
- `routes/web.php` - Twilioテストルートを追加

### フロントエンド
- `resources/js/Pages/Twilio/Test.vue` - テストUI

### ドキュメント
- `TWILIO_SETUP.md` - 詳細セットアップガイド
- `TWILIO_QUICK_START.md` - このファイル

### インストールスクリプト
- `install_twilio.sh` - Linux/Mac用
- `install_twilio.bat` - Windows用

## 🔧 機能

### ✅ 電話発信
- 任意の電話番号に音声通話を発信
- 日本語音声メッセージの再生
- 通話ステータスの確認

### ✅ SMS送信
- 任意の電話番号にSMS送信
- 最大1600文字対応

### ✅ ステータス確認
- Call SIDによる通話状態の確認
- 通話時間の取得

## 📱 テスト用電話番号の形式

国際形式（E.164）で入力してください：

- **日本**: `+81901234567` （先頭の0を削除）
- **米国**: `+15551234567`

## ⚠️ 重要な注意事項

### 無料トライアルアカウントの制限
- 検証済みの電話番号にのみ発信可能
- 日本へのSMS送信には日本の電話番号が必要（有料）
- すべての通話/SMSにトライアルメッセージが追加されます

### セキュリティ
- `.env` ファイルは絶対にGitにコミットしない
- Auth Tokenは他人に共有しない

## 📚 詳細ドキュメント

より詳しい情報は `TWILIO_SETUP.md` を参照してください：

- Twilioアカウントの作成方法
- 電話番号の取得方法
- 料金について
- トラブルシューティング
- Webhook設定
- 本番環境への移行

## 🆘 トラブルシューティング

### エラー: "Twilio credentials are not configured"
```bash
# キャッシュをクリア
php artisan config:clear
```

### 電話番号形式エラー
- 国際形式（+から始まる）で入力
- 例: +81901234567

### SDKが見つからない
```bash
composer require twilio/sdk
```

## 🔗 参考リンク

- [Twilio公式サイト](https://www.twilio.com/)
- [Twilio PHP SDK](https://www.twilio.com/docs/libraries/php)
- [Twilio料金](https://www.twilio.com/ja-jp/pricing)




