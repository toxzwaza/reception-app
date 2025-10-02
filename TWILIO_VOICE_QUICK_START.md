# Twilioリアルタイム音声通話 クイックスタート

## 🎯 3ステップで始める

### ステップ1: パッケージのインストール
```bash
npm install
```

### ステップ2: Twilio設定

#### 2.1 API Keyを作成
1. https://console.twilio.com/us1/account/keys-credentials/api-keys にアクセス
2. 「Create API key」をクリック
3. SIDとSecretをメモ（⚠️ Secretは一度しか表示されません）

#### 2.2 TwiML Appを作成
1. https://console.twilio.com/us1/develop/voice/manage/twiml-apps にアクセス
2. 「Create new TwiML App」をクリック
3. 設定：
   - **Friendly Name**: `Voice Call App`
   - **Voice Request URL**: `http://localhost:8000/twilio-voice/outgoing`（開発環境）
   - **HTTP Method**: POST
4. App SIDをメモ

#### 2.3 環境変数を設定
`.env` に追加：
```env
TWILIO_API_KEY=SKxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_API_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_TWIML_APP_SID=APxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### ステップ3: 起動とテスト
```bash
# キャッシュクリア
php artisan config:clear

# サーバー起動（2つのターミナルで実行）
php artisan serve
npm run dev
```

アクセス:
```
http://localhost:8000/twilio-voice
```

---

## 💡 開発環境でテストする場合

### ngrokを使用
```bash
# ngrokインストール（初回のみ）
# Windows: choco install ngrok
# Mac: brew install ngrok

# トンネル作成
ngrok http 8000
```

表示されたURLを**TwiML AppのVoice Request URL**に設定：
```
https://xxxx.ngrok-free.app/twilio-voice/outgoing
```

---

## 🎤 使い方

1. **ページアクセス**: `http://localhost:8000/twilio-voice`
2. **マイク許可**: ブラウザのマイクアクセスを許可
3. **電話番号入力**: 国際形式（例: `+81901234567`）
4. **発信**: 「電話をかける」ボタンをクリック
5. **通話**: マイクで話す → 相手に届く！

---

## ⚠️ トラブルシューティング

### 「Twilio設定が不完全です」
→ `.env` の環境変数を確認 → `php artisan config:clear`

### 「デバイスの初期化に失敗」
→ API Key/Secretを再確認 → 正しい値を設定

### 「マイクが動作しない」
→ ブラウザのマイクアクセスを許可

### 設定確認
```
http://localhost:8000/twilio-voice/test
```
すべて "configured" になっているか確認

---

## 📚 詳細ドキュメント

- **TWILIO_VOICE_SETUP.md** - 詳細セットアップガイド
- **TWILIO_SETUP.md** - 基本的なTwilio設定
- **TWILIO_ERROR_GUIDE.md** - エラー解決ガイド

---

## ✨ できること

- ✅ ブラウザから電話発信
- ✅ リアルタイム双方向通話
- ✅ マイク・スピーカー使用
- ✅ ミュート機能
- ✅ 通話時間表示

---

準備ができたら、リアルタイム音声通話を楽しんでください！🎉




