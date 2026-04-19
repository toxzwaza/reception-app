# 192.168.210.90 — 納品書・集荷スキャン用 Raspberry Pi

## 概要

受付タブレット（reception-app）から配送業者が持ち込んだ納品書・集荷伝票をスキャンするための Raspberry Pi。Webカメラで書類を撮影し、輪郭検出で書類部分を切り出して、Base64 画像として receptions-app に返却する。

## ハードウェア

| 項目 | 値 |
|------|-----|
| 機種 | Raspberry Pi 4 Model B Rev 1.5 |
| OS | Debian 13 (trixie) / Linux 6.12.47 / aarch64 |
| CPU/Memory | 4 core / 3.7GB RAM / 2GB Swap |
| Disk | microSD 32GB（使用率 29%）|
| ホスト名 | Akioka-Raspberrypi4-14 |
| 温度 | 解析時 72.5℃（※要注意：ヒートシンク or ファン確認） |

### 接続デバイス

| USB/Network | 種類 | 備考 |
|------------|------|------|
| USB | Anker PowerConf C200 Webcam | 書類撮影用（メイン使用） |
| Network (airscan) | EPSON EW-M530F (192.168.210.127) | eSCLプロトコル経由 ※現状未使用 |

## ネットワーク

| 項目 | 値 |
|------|-----|
| IPv4 | 192.168.210.90/24 (wlan0) |
| IPv6 | fe80::44fb:5092:59b4:79b0/64 |
| eth0 | DOWN（未使用）|
| Default Gateway | 192.168.210.1 |

## 主要サービス

### reception-scan-camera.service

| 項目 | 値 |
|------|-----|
| 状態 | active (running) / enabled |
| ユーザー | to-murakami |
| 作業Dir | /home/to-murakami/Documents/reception-scan-camera |
| 実行 | /usr/bin/python3 main.py |
| Restart | always (10秒間隔) |
| 起動依存 | network.target |
| DISPLAY | :0（OpenCV画面表示用） |

### 確認コマンド
```bash
sudo systemctl status reception-scan-camera
sudo journalctl -u reception-scan-camera -f    # ログ追従
sudo systemctl restart reception-scan-camera   # 再起動
```

## アプリケーション仕様（main.py）

### エンドポイント（port 5001 / HTTPS）

| URI | Method | 用途 |
|-----|--------|------|
| `/start_scan` | POST | スキャンモード開始（書類検出→自動撮影） |
| `/stop_scan` | POST | スキャンモード停止 |
| `/get_scan_image` | GET | 最新スキャン画像を Base64 で返却 |
| `/video_feed` | GET | MJPEG 映像ストリーム（30FPS）|
| WebSocket: `scan_completed` | emit | スキャン完了時にクライアントへ通知 |

### スキャン処理フロー

1. reception-app から `POST /start_scan` で開始要求
2. Webカメラ映像から書類の四角形輪郭を検出（OpenCV findContours）
3. 書類が **2 秒間位置安定**（15px 以内）したら自動撮影
4. `scan_file.jpg` として保存
5. WebSocket `scan_completed` イベントを発火
6. クライアントが `GET /get_scan_image` で Base64 画像を取得

### 技術スタック

| 項目 | 値 |
|------|-----|
| Framework | Flask 3.1.1 + Flask-SocketIO 5.5.1 |
| CORS | flask-cors（全許可） |
| 画像処理 | OpenCV (cv2) + NumPy + PIL |
| 通信 | HTTPS (自己署名 or Let's Encrypt 不明) |
| 同期方式 | async_mode="threading" |

### SSL証明書

- 証明書: `/home/to-murakami/Documents/reception-scan-camera/certs/fullchain.pem`
- 秘密鍵: `/home/to-murakami/Documents/reception-scan-camera/certs/server.key`

※ 91番機と違い certbot が入っていない模様。証明書管理方法は要調査。

## 運用時の注意

### 気をつけること

- **温度 72.5℃ は高い**。ヒートシンク/ファン動作確認推奨（通常 Raspi 4 で 60℃前後が理想）
- reception-scan-camera.service は `DISPLAY=:0` を使う → デスクトップ環境（lightdm）起動中のみ動作
- カメラが他プロセス（cheese 等）で掴まれていると起動失敗

### ログ確認

```bash
sudo journalctl -u reception-scan-camera -n 100 --no-pager
```

### 再起動

```bash
# サービス単位
sudo systemctl restart reception-scan-camera

# 本体再起動（営業時間外推奨）
sudo reboot
```

## 既知の課題

1. **温度が高い（72.5℃）** - 冷却対策の確認が必要
2. **SSL証明書の有効期限管理方法が不明** - certbot 未導入、手動更新の可能性
3. **CPU使用率が高い** - `load average: 2.81` (4コア)、カメラループが242% CPU 消費 → 最適化余地あり

## 参考資料

- [保守レポート/20260416_初回解析.md](保守レポート/20260416_初回解析.md) - 全状態ダンプ
- [scripts/main.py](scripts/main.py) - メインアプリ（674行）
- [scripts/client_test.py](scripts/client_test.py) - テスト用クライアント
- [scripts/reception-scan-camera.service](scripts/reception-scan-camera.service) - systemd ユニット
