# 192.168.210.91 — QR ラベルプリンター用 Raspberry Pi

## 概要

受付タブレット（reception-app）から受け取った URL を元にロゴ入り QR コードを生成し、Brother PT-P750W 熱転写ラベルプリンターで印刷するための Raspberry Pi。4 分ごとにプリンターへ Keep-Alive 信号を送り、スリープ状態を回避している。

## ハードウェア

| 項目 | 値 |
|------|-----|
| 機種 | Raspberry Pi 4 Model B Rev 1.5 |
| OS | Debian 13 (trixie) / Linux 6.12.47 / aarch64 |
| CPU/Memory | 4 core / 7.6GB RAM / 2GB Swap |
| Disk | microSD 64GB（使用率 17%）|
| ホスト名 | Akioka-Raspberrypi4-16 |
| 温度 | 解析時 39.9℃ ✓ |

### 接続デバイス

| USB/Network | 種類 | 備考 |
|------------|------|------|
| USB | Brother PT-P750W（VID:0x04f9 PID:0x2062） | ラベルプリンター本体。24mm幅テープ使用 |
| USB Hub | VIA Labs VL813 | 接続中継用 |

※ 解析時点 `lsusb` には PT-P750W が表示されなかった（スリープ中の可能性あり）

## ネットワーク

| 項目 | 値 |
|------|-----|
| IPv4 | 192.168.210.91/24 (wlan0) |
| Default Gateway | 192.168.210.1 |

## 主要サービス

### 1. flask-app.service（メインアプリ）

| 項目 | 値 |
|------|-----|
| 状態 | active (running) / enabled |
| ユーザー | **root**（USB プリンター直接アクセスのため） |
| 作業Dir | /home/to-murakami/Documents |
| 実行 | /usr/bin/python3 app.py |
| Restart | always |

### 2. printer-keepalive.service（Keep-Alive）

| 項目 | 値 |
|------|-----|
| 状態 | active (running) / enabled |
| ユーザー | root |
| 実行 | /usr/bin/python3 printer_keepalive.py |
| 機能 | 4分ごとにESC i (0x1b 0x69) 送信 |

**注意**: app.py 内部にも同じ Keep-Alive スレッドが実装されており、**機能が重複している**。将来どちらかに統一するか検討対象。

### 確認コマンド

```bash
sudo systemctl status flask-app printer-keepalive
sudo journalctl -u flask-app -f
sudo systemctl restart flask-app
```

## アプリケーション仕様（app.py）

### エンドポイント（port 5000 / HTTPS）

| URI | Method | 用途 |
|-----|--------|------|
| `/` | GET | 管理画面（ステータス・設定・印刷履歴） |
| `/print` | POST | URL→QR生成→ラベル印刷（reception-app から呼ばれる） |
| `/test_print` | POST | テスト印刷（管理画面から操作） |
| `/status_check` | GET | プリンター接続確認 |
| `/update_config` | POST | 設定更新（カット有無・デバッグモード） |
| `/qr_history/<filename>` | GET | 過去生成 QR 画像取得 |

### `/print` リクエスト例

```json
{
  "url": "https://akioka-reception.cloud/delivery/123"
}
```

### QR 生成仕様

- ライブラリ: qrcode + PIL
- エラー訂正: `ERROR_CORRECT_H`（30% 復元）
- 中央にロゴ: `/home/to-murakami/Documents/ak_logo_black.png`
- ロゴ下に日付: `yy/mm/dd` 形式（DejaVuSans 96pt）
- 生成先: `/home/to-murakami/Documents/qr_history/qr_YYYYMMDD_HHMMSS.png`

### ラベル印刷仕様

| 項目 | 値 |
|------|-----|
| ライブラリ | brother_ql |
| プリンター識別子 | `usb://0x04f9:0x2062` |
| ラベル種別 | `pt24`（24mm幅テープ、config.json で変更可）|
| 変換閾値 | 70（白黒変換閾値） |
| バックエンド | pyusb |

### 設定ファイル（config.json）

```json
{
  "label_width": "24",
  "cut_enabled": true,
  "debug_mode": true
}
```

## SSL証明書

| 項目 | 値 |
|------|-----|
| 証明書 | `/home/to-murakami/Documents/certs/fullchain_91.pem` |
| 秘密鍵 | `/home/to-murakami/Documents/certs/server_91.key` |
| 管理 | **certbot**（インストール済み） |
| 更新スクリプト | `/home/to-murakami/Documents/update_cert.sh` |
| 自動更新 cron | `/etc/cron.d/certbot`（システム既定） |

## 運用時の注意

### Keep-Alive が重要な理由

Brother PT-P750W はしばらく無操作だと自動で電源OFFになる設計。無操作が続くと USB デバイスが認識されなくなり、印刷要求時に失敗する。`printer_keepalive.py`（+ app.py 内部スレッド）が 4 分ごとにダミーコマンドを送って回避している。

### 重複 Keep-Alive の扱い

`printer-keepalive.service`（独立プロセス） と `app.py 内のスレッド` が同時に Keep-Alive を送っている。**USB デバイス競合のリスクあり**。

- 影響: Keep-Alive 送信時に `usb.util.dispose_resources(dev)` で明示解放しているため、即座に競合は起きないはずだが、タイミング次第で印刷コマンドと衝突する可能性
- 推奨対応: 将来のリファクタで一元化（どちらかに統一）

### ログ確認

```bash
sudo journalctl -u flask-app -n 100 --no-pager
sudo journalctl -u printer-keepalive -n 30 --no-pager
tail -f /home/to-murakami/Documents/print_log.txt  # 印刷履歴
```

### 再起動

```bash
# Flask アプリのみ
sudo systemctl restart flask-app

# Keep-Alive のみ
sudo systemctl restart printer-keepalive

# 両方
sudo systemctl restart flask-app printer-keepalive

# 本体再起動
sudo reboot
```

### プリンター側の物理トラブル

| 症状 | 対処 |
|------|------|
| ラベルが出てこない | テープ残量確認 → プリンター電源OFF/ON → RPi再起動 |
| 不鮮明 | ヘッド汚れ → クリーニングテープで清掃 |
| USB未認識 | USBケーブル抜き差し → dmesg 確認 → keepalive service 再起動 |

## 既知の課題

1. **Keep-Alive 重複実装** - `printer-keepalive.service` と `app.py` 内スレッドで同じ処理
2. **SSLサーバ自己管理** - Flask の `ssl_context` で直接配信（nginx リバースプロキシ等なし）。パフォーマンス/セキュリティ上は Caddy or nginx 推奨
3. **DEBUG=True のまま本番稼働** - `config.json` の `debug_mode: true` でエラーが外部に見える可能性
4. **プリンターのファームウェア更新手順が未文書化**

## 参考資料

- [保守レポート/20260416_初回解析.md](保守レポート/20260416_初回解析.md) - 全状態ダンプ
- [scripts/app.py](scripts/app.py) - メインアプリ（362行）
- [scripts/printer_keepalive.py](scripts/printer_keepalive.py) - Keep-Aliveスクリプト
- [scripts/flask-app.service](scripts/flask-app.service) - systemd ユニット
- [scripts/printer-keepalive.service](scripts/printer-keepalive.service) - systemd ユニット
- [scripts/update_cert.sh](scripts/update_cert.sh) - SSL更新スクリプト
- Brother PT-P750W: https://www.brother.co.jp/product/printer/label/ptp750w/
- brother_ql: https://github.com/pklaus/brother_ql
