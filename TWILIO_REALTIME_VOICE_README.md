# 🎤 Twilioリアルタイム音声通話機能

ブラウザのマイク・スピーカーを使用した**双方向リアルタイム音声通話**を実現します。

---

## ✨ 機能

### 実装済み機能
- ✅ **ブラウザから電話発信** - 電話番号を入力して発信
- ✅ **リアルタイム双方向通話** - WebRTCによる高品質通話
- ✅ **マイク・スピーカー使用** - ブラウザから直接通話
- ✅ **ミュート機能** - 通話中にミュート/解除可能
- ✅ **通話時間表示** - リアルタイムで経過時間を表示
- ✅ **デバイス状態管理** - 接続状態を視覚的に表示
- ✅ **エラーハンドリング** - わかりやすいエラーメッセージ

### 技術スタック
- **フロントエンド**: Vue 3 + Twilio Voice SDK for JavaScript
- **バックエンド**: Laravel + Twilio PHP SDK
- **通信**: WebRTC (リアルタイム通信)
- **認証**: JWT Access Token

---

## 📦 作成されたファイル

### バックエンド
```
app/Http/Controllers/TwilioVoiceController.php   # 音声通話コントローラー
routes/web.php                                    # ルート追加
```

### フロントエンド
```
resources/js/Pages/Twilio/VoiceCall.vue          # 音声通話UI
package.json                                      # @twilio/voice-sdk追加
```

### ドキュメント
```
TWILIO_VOICE_SETUP.md                            # 詳細セットアップガイド
TWILIO_VOICE_QUICK_START.md                      # クイックスタート
TWILIO_REALTIME_VOICE_README.md                  # このファイル
```

### インストールスクリプト
```
install_twilio_voice.sh                          # Linux/Mac用
install_twilio_voice.bat                         # Windows用
```

---

## 🚀 クイックスタート

### 1. パッケージのインストール

**Windows:**
```bash
install_twilio_voice.bat
```

**Mac/Linux:**
```bash
chmod +x install_twilio_voice.sh
./install_twilio_voice.sh
```

**または手動で:**
```bash
npm install
```

---

### 2. Twilio設定

#### 2.1 API Keyを作成
1. https://console.twilio.com/us1/account/keys-credentials/api-keys
2. 「Create API key」→ SIDとSecretをメモ

#### 2.2 TwiML Appを作成
1. https://console.twilio.com/us1/develop/voice/manage/twiml-apps
2. 「Create new TwiML App」
3. Voice Request URL: `http://localhost:8000/twilio-voice/outgoing`

#### 2.3 環境変数を設定
`.env` に追加：
```env
# 既存のTwilio設定
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_PHONE_NUMBER=+15551234567

# リアルタイム音声通話用（新規追加）
TWILIO_API_KEY=SKxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_API_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_TWIML_APP_SID=APxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

### 3. 起動

```bash
# キャッシュクリア
php artisan config:clear

# サーバー起動（2つのターミナルで）
php artisan serve
npm run dev
```

---

### 4. アクセス

```
http://localhost:8000/twilio-voice
```

1. マイクアクセスを許可
2. デバイスが「準備完了」になるのを待つ
3. 電話番号を入力（例: `+81901234567`）
4. 「電話をかける」をクリック
5. 通話開始！

---

## 🔧 機能説明

### デバイス接続
- 自動的にTwilioデバイスに接続
- 接続状態を視覚的に表示（緑●= 準備完了）

### 電話発信
- 国際形式で電話番号を入力
- ボタンクリックで発信
- リアルタイムで通話状態を表示

### 通話中
- **ミュート/解除**: マイクのオン/オフ切り替え
- **通話時間**: リアルタイム表示
- **切断**: 通話終了ボタン

### エラーハンドリング
- わかりやすいエラーメッセージ
- 設定不備の検出
- デバイスエラーの通知

---

## 🌐 開発環境でのテスト（ngrok）

Twilioから到達可能なURLが必要な場合：

```bash
# ngrokインストール（初回のみ）
# Windows: choco install ngrok
# Mac: brew install ngrok

# トンネル作成
ngrok http 8000
```

表示されたURL（例: `https://xxxx.ngrok-free.app`）を
**TwiML AppのVoice Request URL**に設定

---

## 📱 ブラウザ互換性

### サポートブラウザ
- ✅ Chrome（推奨）
- ✅ Edge
- ✅ Firefox
- ✅ Safari 11+

### 必要な機能
- WebRTC サポート
- マイクアクセス
- HTTPS（本番環境）

---

## ⚠️ トラブルシューティング

### 「Twilio設定が不完全です」
→ `.env` の6つの環境変数が全て設定されているか確認

### 「デバイスの初期化に失敗」
→ API Key/Secretを再確認

### マイクが動作しない
→ ブラウザのマイクアクセスを許可

### 設定確認エンドポイント
```
http://localhost:8000/twilio-voice/test
```
全て "configured" になっているか確認

---

## 🔐 セキュリティ

### 本番環境
- ✅ HTTPS必須
- ✅ ユーザー認証の追加を推奨
- ✅ トークン有効期限: 1時間
- ✅ `.env` ファイルをGit管理外に

---

## 💰 料金

### 通話料金
- **ブラウザ → 電話番号**: 通常の音声通話料金
- **日本**: 約 $0.013/分
- **米国**: 約 $0.013/分

### 無料
- トークン生成
- WebRTC接続
- デバイス登録

詳細: [Twilio料金](https://www.twilio.com/ja-jp/pricing/voice)

---

## 📚 ドキュメント

| ファイル | 内容 |
|---------|------|
| **TWILIO_VOICE_QUICK_START.md** | クイックスタート（3ステップ） |
| **TWILIO_VOICE_SETUP.md** | 詳細セットアップガイド |
| **TWILIO_SETUP.md** | 基本的なTwilio設定 |
| **TWILIO_ERROR_GUIDE.md** | エラー解決ガイド |
| **TWILIO_VERIFY_NUMBER.md** | 電話番号検証手順 |

---

## 🎯 既存のTwilio機能との違い

| 機能 | SMS/音声メッセージ<br>(`/twilio-test`) | リアルタイム通話<br>(`/twilio-voice`) |
|------|--------------------------------|----------------------------|
| **通話方向** | 一方向（メッセージ送信） | 双方向（会話可能） |
| **音声入力** | なし | マイク使用 |
| **音声出力** | 録音メッセージ再生 | スピーカーから相手の声 |
| **技術** | TwiML | WebRTC |
| **ユースケース** | 通知、自動案内 | カスタマーサポート、Web電話 |

---

## 🔗 エンドポイント

### ユーザー向け
- `GET /twilio-voice` - 音声通話ページ

### API
- `POST /twilio-voice/token` - アクセストークン生成
- `POST /twilio-voice/outgoing` - 発信時TwiML応答
- `POST /twilio-voice/incoming` - 着信時TwiML応答（未実装）
- `POST /twilio-voice/status` - 通話ステータスコールバック
- `GET /twilio-voice/test` - 設定確認

---

## 🚀 今後の拡張案

- [ ] 着信機能（ブラウザで電話を受ける）
- [ ] 通話録音
- [ ] 通話履歴のデータベース保存
- [ ] 複数デバイス対応
- [ ] ビデオ通話対応
- [ ] グループ通話
- [ ] 音量調整UI
- [ ] デバイス選択（マイク/スピーカー）

---

## 📞 使用例

### カスタマーサポート
- Webサイトから直接サポートに電話
- 待ち時間なしで接続

### リモート受付
- 来訪者がブラウザから内線に電話
- 受付スタッフ不要

### Web会議
- ブラウザベースの音声会議
- インストール不要

---

## 🆘 サポート

### ログ確認
```bash
# Laravel
tail -f storage/logs/laravel.log

# ブラウザ
F12 → Console タブ
```

### Twilioログ
https://console.twilio.com/us1/monitor/logs/calls

---

すべての設定が完了したら、リアルタイム音声通話をお楽しみください！🎉

質問や問題があれば、ドキュメントを参照するか、Twilioサポートにお問い合わせください。




