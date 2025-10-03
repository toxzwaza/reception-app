# Twilioリアルタイム音声通話 セットアップガイド

ブラウザのマイク・スピーカーを使用した**双方向リアルタイム音声通話**の設定方法です。

---

## 📋 概要

### できること
- ✅ ブラウザから電話番号への発信
- ✅ マイク・スピーカーを使った双方向通話
- ✅ リアルタイムの音声会話
- ✅ ミュート機能
- ✅ 通話時間の表示

### 技術スタック
- **フロントエンド**: Twilio Voice SDK for JavaScript (WebRTC)
- **バックエンド**: Laravel + Twilio PHP SDK
- **認証**: JWT Access Token

---

## 🚀 セットアップ手順

### ステップ1: 依存パッケージのインストール

#### NPMパッケージをインストール
```bash
npm install
```

Twilio Voice SDK (`@twilio/voice-sdk`) は既に `package.json` に含まれています。

#### Composer (PHP) パッケージ
Twilio SDKが既にインストールされていることを確認：
```bash
composer show twilio/sdk
```

なければインストール：
```bash
composer require twilio/sdk
```

---

### ステップ2: Twilio API KeyとSecretの作成

リアルタイム通話には、Account SID/Auth Tokenとは別に**API Key**が必要です。

#### 2.1 TwilioコンソールでAPI Keyを作成

1. [Twilio Console](https://console.twilio.com/) にログイン

2. 左メニューから **「Account」** → **「API keys & tokens」** をクリック

3. **「Create API key」** ボタンをクリック

4. API Keyの設定：
   - **Friendly name**: `Voice API Key`（任意の名前）
   - **Key type**: `Standard`
   - **Create API Key** をクリック

5. **重要**: 表示される情報をメモ
   ```
   SID: SKxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   Secret: xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   ```
   ⚠️ **Secret は一度しか表示されません！必ず保存してください**

---

### ステップ3: TwiML Appの作成

TwiML Appは、通話のルーティングを制御します。

#### 3.1 TwiML Appを作成

1. [Twilio Console](https://console.twilio.com/) にログイン

2. 左メニューから **「Voice」** → **「TwiML Apps」** をクリック
   - または直接アクセス: https://console.twilio.com/us1/develop/voice/manage/twiml-apps

3. **「Create new TwiML App」** または **「+」** ボタンをクリック

4. TwiML Appの設定：
   ```
   Friendly Name: Voice Call App
   
   Voice Configuration:
   ├─ Request URL (発信時): https://your-domain.com/twilio-voice/outgoing
   └─ HTTP Method: POST
   
   Status Callback URL (オプション): https://your-domain.com/twilio-voice/status
   ```

5. **「Save」** をクリック

6. **App SID をメモ**
   ```
   APxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   ```

---

### ステップ4: 環境変数の設定

`.env` ファイルに以下を追加：

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

### 設定値の取得場所

| 環境変数 | 取得場所 |
|---------|---------|
| `TWILIO_ACCOUNT_SID` | [Console Dashboard](https://console.twilio.com/) |
| `TWILIO_AUTH_TOKEN` | [Console Dashboard](https://console.twilio.com/) |
| `TWILIO_PHONE_NUMBER` | [Phone Numbers](https://console.twilio.com/us1/develop/phone-numbers/manage/incoming) |
| `TWILIO_API_KEY` | ステップ2で作成した SID |
| `TWILIO_API_SECRET` | ステップ2で作成した Secret |
| `TWILIO_TWIML_APP_SID` | ステップ3で作成した App SID |

---

### ステップ5: キャッシュのクリアと再起動

```bash
# Laravelキャッシュをクリア
php artisan config:clear
php artisan cache:clear

# 開発サーバーを再起動
php artisan serve
```

フロントエンドも再起動：
```bash
npm run dev
```

---

### ステップ6: 動作確認

1. **テストページにアクセス**
   ```
   http://localhost/twilio-voice
   ```

2. **マイクアクセスを許可**
   - 初回アクセス時、ブラウザからマイクアクセスの許可を求められます
   - 「許可」をクリック

3. **デバイス状態を確認**
   - 緑色の●が表示されれば準備完了

4. **電話番号を入力して発信**
   - 国際形式で入力（例: `+81901234567`）
   - トライアルアカウントの場合は検証済み番号のみ

5. **通話を楽しむ！**
   - マイクで話す → 相手に届く
   - 相手の声 → スピーカーから聞こえる

---

## 🔧 本番環境への移行

### ngrokを使用した開発環境でのテスト

ローカル開発環境をTwilioから到達可能にするには、ngrokを使用します：

#### 1. ngrokのインストール
```bash
# Windows (Chocolatey)
choco install ngrok

# Mac (Homebrew)
brew install ngrok

# または https://ngrok.com/ からダウンロード
```

#### 2. ngrokでトンネルを作成
```bash
ngrok http 8000
```

#### 3. 表示されたURLをメモ
```
Forwarding: https://xxxx-xx-xxx-xx-xxx.ngrok-free.app -> http://localhost:8000
```

#### 4. TwiML AppのURLを更新
1. [TwiML Apps](https://console.twilio.com/us1/develop/voice/manage/twiml-apps) にアクセス
2. 作成したAppをクリック
3. Voice Request URLを更新：
   ```
   https://xxxx-xx-xxx-xx-xxx.ngrok-free.app/twilio-voice/outgoing
   ```
4. Save

#### 5. ngrok URLで動作確認
```
https://xxxx-xx-xxx-xx-xxx.ngrok-free.app/twilio-voice
```

---

## 📱 ブラウザの互換性

### サポートされているブラウザ
- ✅ Google Chrome（推奨）
- ✅ Microsoft Edge
- ✅ Firefox
- ✅ Safari 11+

### 必要な機能
- WebRTC サポート
- マイクアクセス
- HTTPS接続（本番環境）

---

## ❓ トラブルシューティング

### エラー: "Twilio設定が不完全です"
**原因**: 環境変数が設定されていない

**解決方法**:
1. `.env` ファイルを確認
2. すべての環境変数が設定されているか確認
3. `php artisan config:clear` を実行

---

### エラー: "デバイスの初期化に失敗しました"
**原因**: API KeyまたはSecretが間違っている

**解決方法**:
1. Twilioコンソールで API Key/Secret を再確認
2. `.env` の値を修正
3. `php artisan config:clear` を実行

---

### エラー: "Access token expired"
**原因**: トークンの有効期限切れ（1時間）

**解決方法**:
- ページをリロードして新しいトークンを取得

---

### マイクが動作しない
**原因**: ブラウザのマイクアクセスが許可されていない

**解決方法**:
1. ブラウザのアドレスバー左側の🔒または🔊をクリック
2. マイクアクセスを「許可」に変更
3. ページをリロード

---

### 音声が聞こえない
**原因**: スピーカー/ヘッドホンの設定

**解決方法**:
1. デバイスの音量を確認
2. 正しい出力デバイスが選択されているか確認
3. 別のスピーカー/ヘッドホンを試す

---

### 通話が接続されない
**原因**: TwiML AppのURLが間違っている

**解決方法**:
1. TwiML AppのVoice Request URLを確認
2. URLが正しく、HTTPSであることを確認（本番環境）
3. エンドポイントが応答しているか確認：
   ```bash
   curl -X POST https://your-domain.com/twilio-voice/outgoing
   ```

---

## 🔐 セキュリティのベストプラクティス

### 本番環境
1. **HTTPS必須**: 本番環境では必ずHTTPSを使用
2. **認証の追加**: 必要に応じてユーザー認証を追加
3. **トークンの有効期限**: 短い有効期限を設定（デフォルト: 1時間）
4. **API Secretの保護**: `.env` ファイルをGit管理外に
5. **IP制限**: 必要に応じてTwilioのIP制限を設定

---

## 💰 料金について

### リアルタイム音声通話の料金
- **ブラウザ → 電話番号**: 通常の音声通話料金
- **トークン生成**: 無料
- **WebRTC接続**: 無料

### 料金の目安
- 日本への通話: 約 $0.013/分
- 米国への通話: 約 $0.013/分

詳細: [Twilio料金ページ](https://www.twilio.com/ja-jp/pricing/voice)

---

## 📚 参考リンク

- [Twilio Voice SDK for JavaScript](https://www.twilio.com/docs/voice/sdks/javascript)
- [TwiML Apps](https://www.twilio.com/docs/voice/twiml/apps)
- [Access Tokens](https://www.twilio.com/docs/iam/access-tokens)
- [WebRTC について](https://webrtc.org/)

---

## 🆘 サポート

問題が発生した場合：

1. **Laravelログを確認**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **ブラウザコンソールを確認**
   - F12 → Console タブ

3. **Twilioログを確認**
   - [Monitor > Logs](https://console.twilio.com/us1/monitor/logs/calls)

4. **設定確認エンドポイント**
   ```
   http://localhost/twilio-voice/test
   ```
   すべての設定が "configured" になっているか確認

---

すべての設定が完了したら、リアルタイム音声通話をお楽しみください！🎉
