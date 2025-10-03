# Windows環境でngrokをインストールする方法

## 🎯 方法1: 公式サイトから直接ダウンロード（推奨）

### 手順

#### 1. ngrokをダウンロード
1. ブラウザで https://ngrok.com/download にアクセス
2. 「Windows (64-bit)」をクリックしてダウンロード
3. ZIPファイルがダウンロードされます

#### 2. ZIPを解凍
1. ダウンロードした `ngrok-v3-stable-windows-amd64.zip` を解凍
2. `ngrok.exe` ファイルが出てきます

#### 3. 実行可能な場所に移動（オプション）
以下のいずれかの方法で、どこからでもngrokを実行できるようにします：

**方法A: プロジェクトフォルダに配置**
```powershell
# ngrok.exe を現在のプロジェクトフォルダに移動
# d:\WEBAPPLICATION\reception-app\ngrok.exe
```

**方法B: PATHに追加**
1. `C:\ngrok` フォルダを作成
2. `ngrok.exe` をそこに移動
3. システム環境変数のPATHに `C:\ngrok` を追加

**方法C: そのまま使用**
- ダウンロードフォルダから直接実行（フルパス指定）

#### 4. ngrokアカウントを作成（無料）
1. https://dashboard.ngrok.com/signup にアクセス
2. アカウントを作成（Google/GitHubでも可）

#### 5. 認証トークンを設定
1. https://dashboard.ngrok.com/get-started/your-authtoken にアクセス
2. Authtokenをコピー
3. 以下のコマンドを実行（ngrok.exeがある場所で）：
   ```powershell
   .\ngrok.exe config add-authtoken YOUR_AUTHTOKEN
   ```

#### 6. ngrokを起動
```powershell
# プロジェクトフォルダにngrok.exeがある場合
.\ngrok.exe http 8000

# PATHに追加した場合
ngrok http 8000

# フルパス指定の場合
C:\Users\YourName\Downloads\ngrok.exe http 8000
```

#### 7. URLを確認
コンソールに表示されるURL（例: `https://xxxx-xx-xxx.ngrok-free.app`）をコピー

#### 8. TwiML Appに設定
1. https://console.twilio.com/us1/develop/voice/manage/twiml-apps にアクセス
2. Voice Request URLに設定：
   ```
   https://xxxx-xx-xxx.ngrok-free.app/twilio-voice/outgoing
   ```

---

## 🎯 方法2: Chocolateyをインストールしてから使用

### Chocolateyのインストール

1. **PowerShellを管理者権限で開く**
   - スタートメニューで「PowerShell」を右クリック
   - 「管理者として実行」を選択

2. **実行ポリシーを確認**
   ```powershell
   Get-ExecutionPolicy
   ```
   
   `Restricted` の場合は変更：
   ```powershell
   Set-ExecutionPolicy AllSigned
   ```

3. **Chocolateyをインストール**
   ```powershell
   Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
   ```

4. **PowerShellを再起動**

5. **ngrokをインストール**
   ```powershell
   choco install ngrok
   ```

6. **認証設定**
   ```powershell
   ngrok config add-authtoken YOUR_AUTHTOKEN
   ```

---

## 🎯 方法3: Scoopを使用（軽量代替）

### Scoopのインストール

1. **PowerShellで実行**
   ```powershell
   Set-ExecutionPolicy RemoteSigned -Scope CurrentUser
   irm get.scoop.sh | iex
   ```

2. **ngrokをインストール**
   ```powershell
   scoop install ngrok
   ```

3. **認証設定**
   ```powershell
   ngrok config add-authtoken YOUR_AUTHTOKEN
   ```

---

## 🚀 使い方

### 基本的な使用方法

```powershell
# Laravelサーバーが起動している状態で
ngrok http 8000
```

### 表示される情報

```
Session Status                online
Account                       your-email@example.com
Version                       3.x.x
Region                        Japan (jp)
Forwarding                    https://xxxx-xx-xxx.ngrok-free.app -> http://localhost:8000
```

### 便利なオプション

```powershell
# 特定のサブドメインを使用（有料プラン）
ngrok http 8000 --subdomain=myapp

# 特定の地域を指定
ngrok http 8000 --region=jp

# 認証を追加
ngrok http 8000 --basic-auth="username:password"
```

---

## 💡 ngrokの管理画面

ngrok起動中にブラウザで以下にアクセスすると、リクエストの詳細が見れます：

```
http://127.0.0.1:4040
```

ここで以下が確認できます：
- すべてのHTTPリクエスト
- レスポンス内容
- リクエスト/レスポンスのヘッダー
- デバッグ情報

---

## ⚠️ 注意点

### 無料プランの制限
- セッション時間: 2時間まで
- 同時接続: 1つまで
- カスタムドメイン: 不可

### セキュリティ
- ngrokのURLは公開されるため、開発用途のみで使用
- 本番環境では使用しない
- 必要に応じて認証を追加

---

## 🔄 ngrokの停止

```
Ctrl + C
```

---

## 📚 参考リンク

- [ngrok公式サイト](https://ngrok.com/)
- [ngrokダウンロード](https://ngrok.com/download)
- [ngrokドキュメント](https://ngrok.com/docs)
- [Chocolatey公式](https://chocolatey.org/)
- [Scoop公式](https://scoop.sh/)

---

## 🎯 推奨方法まとめ

| 方法 | 難易度 | 推奨度 |
|------|--------|--------|
| **公式サイトからダウンロード** | ⭐ 簡単 | ⭐⭐⭐⭐⭐ |
| Chocolatey経由 | ⭐⭐ やや複雑 | ⭐⭐⭐ |
| Scoop経由 | ⭐⭐ やや複雑 | ⭐⭐⭐⭐ |

**初めての方は「方法1: 公式サイトからダウンロード」が最も簡単です！**






