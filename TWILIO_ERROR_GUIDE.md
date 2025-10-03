# Twilio エラー解決ガイド

よくあるTwilioのエラーと解決方法をまとめました。

---

## 📱 SMS・電話関連のエラー

### ❌ エラー: "The number +81XXXXXXXXX is unverified"

**完全なエラーメッセージ:**
```
[HTTP 400] Unable to create record: The number +81906182XXXX is unverified. 
Trial accounts cannot send messages to unverified numbers; verify +81906182XXXX 
at twilio.com/user/account/phone-numbers/verified, or purchase a Twilio number 
to send messages to unverified numbers
```

**原因:**
- 無料トライアルアカウントを使用している
- 送信先電話番号が未検証

**解決方法:**

#### 方法1: 電話番号を検証する（無料・推奨）
1. https://www.twilio.com/console/phone-numbers/verified にアクセス
2. 「Add a new Caller ID」をクリック
3. 電話番号を国際形式（+81...）で入力
4. 音声通話またはSMSで確認コードを受け取る
5. 確認コードを入力して検証完了

詳細は [`TWILIO_VERIFY_NUMBER.md`](./TWILIO_VERIFY_NUMBER.md) を参照

#### 方法2: 有料アカウントにアップグレード
- Twilioコンソールから「Upgrade」
- クレジットカード登録で任意の番号に送信可能

---

### ❌ エラー: "Twilio credentials are not configured"

**原因:**
- `.env`ファイルにTwilio認証情報が設定されていない

**解決方法:**
1. `.env`ファイルを開く
2. 以下を追加：
   ```env
   TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   TWILIO_PHONE_NUMBER=+15551234567
   ```
3. キャッシュをクリア：
   ```bash
   php artisan config:clear
   ```

---

### ❌ エラー: "Authenticate" (21003)

**原因:**
- Account SIDまたはAuth Tokenが間違っている

**解決方法:**
1. [Twilio Console](https://www.twilio.com/console) で正しい値を確認
2. `.env`ファイルの値を修正
3. スペースや改行が含まれていないか確認
4. `php artisan config:clear` を実行

---

### ❌ エラー: "Invalid 'From' Phone Number" (21606)

**原因:**
- Twilio電話番号が間違っている、または所有していない

**解決方法:**
1. [Phone Numbers](https://www.twilio.com/console/phone-numbers/incoming) で所有している番号を確認
2. `.env`の`TWILIO_PHONE_NUMBER`を正しい値に修正
3. 国際形式（+15551234567）で設定

---

### ❌ エラー: "Invalid 'To' Phone Number" (21211)

**原因:**
- 送信先電話番号の形式が間違っている

**解決方法:**
- 国際形式（E.164）で入力：
  - ✅ 正: `+81901234567`
  - ❌ 誤: `090-1234-5567`
  - ❌ 誤: `09012345567`
  - ❌ 誤: `81901234567`（+なし）

---

### ❌ エラー: "Message body is required" (21602)

**原因:**
- SMSメッセージが空

**解決方法:**
- メッセージ本文を入力してから送信

---

## 🔐 認証関連のエラー

### ❌ エラー: "Account not authorized" (20003)

**原因:**
- トライアルアカウントで制限された機能を使用しようとしている

**解決方法:**
- アカウントをアップグレード
- または制限内で使用（検証済み番号のみ）

---

### ❌ エラー: "Permission denied" (20404)

**原因:**
- アクセス権限がない機能を使用しようとしている

**解決方法:**
- Twilioコンソールでプロジェクト設定を確認
- 必要な機能が有効になっているか確認

---

## 💰 料金・クレジット関連のエラー

### ❌ エラー: "Insufficient credit"

**原因:**
- Twilioアカウントのクレジット残高が不足

**解決方法:**
1. [Billing](https://www.twilio.com/console/billing) ページで残高を確認
2. クレジットを追加購入

---

## 🌐 ネットワーク関連のエラー

### ❌ エラー: "Cannot read properties of null (reading 'content')"

**原因:**
- CSRFトークンの取得に失敗

**解決方法:**
- すでに修正済み（axiosを使用するように変更）
- ページを再読み込み

---

### ❌ エラー: "Network Error" / "Failed to fetch"

**原因:**
- インターネット接続の問題
- Laravelサーバーが起動していない

**解決方法:**
1. インターネット接続を確認
2. Laravelサーバーが起動しているか確認：
   ```bash
   php artisan serve
   ```

---

## 📞 電話番号形式のリファレンス

### 日本 (+81)
- ✅ 正: `+819012345678`
- ❌ 誤: `09012345678`（先頭の0を削除）

### 米国 (+1)
- ✅ 正: `+15551234567`
- ❌ 誤: `15551234567`（+が必要）

### 国際形式（E.164）の規則
```
+[国番号][市外局番（先頭の0なし）][電話番号]
```

---

## 🔍 デバッグ方法

### Laravelログを確認
```bash
tail -f storage/logs/laravel.log
```

### Twilioコンソールでログを確認
1. [Twilio Console](https://www.twilio.com/console) にログイン
2. 「Monitor」→「Logs」→「Messaging」または「Voice」
3. エラーの詳細を確認

### ブラウザの開発者ツール
1. F12キーを押す
2. 「Console」タブでJavaScriptエラーを確認
3. 「Network」タブでAPIリクエストを確認

---

## 📚 参考リンク

- [Twilioエラーコード一覧](https://www.twilio.com/docs/api/errors)
- [SMS送信のベストプラクティス](https://www.twilio.com/docs/sms/tutorials/how-to-send-sms-messages)
- [電話番号の検証](https://www.twilio.com/console/phone-numbers/verified)
- [Twilioサポート](https://support.twilio.com/)

---

## 💡 よくある質問

### Q: トライアルアカウントでできることは？
A: 
- ✅ 検証済み電話番号への送信（最大10件）
- ✅ すべての基本機能のテスト
- ❌ 未検証番号への送信
- ⚠️ メッセージにトライアル通知が追加

### Q: 本番環境で使うには？
A: 有料アカウントへのアップグレードが必須です

### Q: 日本の電話番号を取得するには？
A: 
1. 有料アカウントにアップグレード
2. Twilioコンソールで日本の電話番号を購入
3. 料金: 約$15/月程度

---

問題が解決しない場合は、Twilioサポートに問い合わせるか、Laravelのログを確認してください。






