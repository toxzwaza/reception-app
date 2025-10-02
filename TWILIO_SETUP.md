# Twilio 電話機能セットアップガイド

このガイドでは、Twilioを使用した電話機能の設定方法を説明します。

## 1. Twilio SDKのインストール

Composerを使用してTwilio PHP SDKをインストールします：

```bash
composer require twilio/sdk
```

## 2. Twilioアカウントの設定

### 2.1 Twilioアカウントの作成
1. [Twilio](https://www.twilio.com/)にアクセスし、アカウントを作成します
2. 無料トライアルアカウントでテストが可能です

### 2.2 必要な情報の取得
Twilioコンソールから以下の情報を取得します：

- **Account SID**: アカウント設定ページで確認できます
- **Auth Token**: アカウント設定ページで確認できます
- **Phone Number**: Twilioで電話番号を取得する必要があります

### 2.3 Twilio電話番号の取得

#### 米国の電話番号（テスト用）
- 無料トライアルでは米国の電話番号を取得できます
- コンソールから「Phone Numbers」→「Buy a Number」で取得

#### 日本の電話番号（本番用）
- 日本の電話番号を取得するには、有料アカウントが必要です
- 日本の電話番号からSMS送信が可能になります
- 料金については[Twilio料金ページ](https://www.twilio.com/ja-jp/pricing)を確認してください

## 3. 環境変数の設定

`.env`ファイルに以下の環境変数を追加します：

```env
# Twilio設定
TWILIO_ACCOUNT_SID=your_account_sid_here
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_PHONE_NUMBER=your_twilio_phone_number_here
```

### 設定例：
```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_PHONE_NUMBER=+15551234567
```

## 4. テストページへのアクセス

Twilioのセットアップが完了したら、以下のURLでテストページにアクセスできます：

```
http://your-domain/twilio-test
```

## 5. 機能一覧

### 5.1 電話発信
- 指定した電話番号に音声通話を発信
- TwiMLを使用した音声メッセージの再生
- 日本語音声合成に対応（`language="ja-JP"`）

### 5.2 SMS送信
- 指定した電話番号にSMSメッセージを送信
- 最大1600文字まで送信可能
- **注意**: 日本の電話番号へSMS送信するには、日本のTwilio電話番号が必要です

### 5.3 通話ステータス確認
- Call SIDを使用して通話のステータスを確認
- 通話時間や通話状態を取得

## 6. トラブルシューティング

### エラー: "Twilio credentials are not configured"
- `.env`ファイルの環境変数が正しく設定されているか確認してください
- `php artisan config:clear`を実行してキャッシュをクリアしてください

### 電話番号の形式エラー
- 電話番号は国際形式（E.164形式）で入力してください
- 日本: `+81901234567`（先頭の0を省略）
- 米国: `+15551234567`

### SMS送信エラー（日本）
- 日本の電話番号へSMSを送信するには、日本のTwilio電話番号が必要です
- 無料トライアルの米国番号からは日本へのSMS送信はできません

### 無料トライアルの制限
- 検証済みの電話番号にのみ発信可能です
- Twilioコンソールで電話番号を検証してください
- すべてのメッセージにトライアルメッセージが追加されます

## 7. 本番環境への移行

### 7.1 アカウントのアップグレード
1. Twilioコンソールでアカウントをアップグレード
2. クレジットカード情報を登録
3. 必要に応じて日本の電話番号を取得

### 7.2 セキュリティ設定
- `.env`ファイルがGit管理外にあることを確認（`.gitignore`に含める）
- 本番環境では環境変数を安全に管理
- Auth Tokenは絶対に公開しない

### 7.3 Webhook設定（オプション）
- 着信通話やSMS受信のWebhookを設定可能
- Laravelのルートで処理することができます

## 8. 料金について

Twilioは使用量に応じた課金システムです：

- **音声通話**: 分単位で課金（国・地域により異なる）
- **SMS**: 送信件数で課金（国・地域により異なる）
- **電話番号**: 月額料金（国・地域により異なる）

詳細は[Twilio料金ページ](https://www.twilio.com/ja-jp/pricing)をご確認ください。

## 9. 参考リンク

- [Twilio公式ドキュメント](https://www.twilio.com/docs)
- [Twilio PHP SDK](https://www.twilio.com/docs/libraries/php)
- [TwiML（音声応答）](https://www.twilio.com/docs/voice/twiml)
- [日本語音声合成](https://www.twilio.com/docs/voice/twiml/say/text-speech#available-languages-and-voices)

## 10. サポート

問題が発生した場合：
1. Twilioのログを確認（コンソールから確認可能）
2. Laravelのログを確認（`storage/logs/laravel.log`）
3. Twilioサポートに問い合わせ




