# Raspberry Pi 管理ドキュメント — 受付システム関連

受付システム（reception-app）と連携する 2 台の Raspberry Pi を管理する。

## 管理対象一覧

| IP | 用途 | ホスト名 | Raspi | 主要サービス | ポート |
|----|------|---------|-------|-------------|--------|
| 192.168.210.90 | **納品書・集荷スキャン** | Akioka-Raspberrypi4-14 | Raspberry Pi 4B (4GB) | reception-scan-camera.service | 5001 (HTTPS) |
| 192.168.210.91 | **QRラベルプリンター** | Akioka-Raspberrypi4-16 | Raspberry Pi 4B (8GB) | flask-app.service + printer-keepalive.service | 5000 (HTTPS) |

### 共通情報

| 項目 | 値 |
|------|-----|
| OS | Debian 13 (trixie) / aarch64 |
| Python | 3.13.5 |
| SSH User | to-murakami |
| SSH Pass | Murakami0819 |
| VNC | port 5900（wayvnc） |

## フォルダ構成

```
RASPBERRY_PI/
├── README.md                       ← 本ファイル
├── scripts/                        ← 両RPi操作用スクリプト
│   ├── ssh_analyze_raspi.py       ← 両RPiの状態一括取得（読み取り専用）
│   └── ssh_fetch_scripts.py       ← 主要スクリプトをローカルに取得
├── 192.168.210.90_scanner/         ← スキャナー用RPi
│   ├── README.md
│   ├── 保守レポート/
│   │   ├── 20260416_初回解析.md
│   │   └── 20260416_追加情報.md
│   └── scripts/                    ← 本体から取得したスクリプト（参照・バージョン管理用）
│       ├── main.py
│       ├── client_test.py
│       └── reception-scan-camera.service
└── 192.168.210.91_printer/         ← プリンター用RPi
    ├── README.md
    ├── 保守レポート/
    └── scripts/
        ├── app.py
        ├── printer_keepalive.py
        ├── flask-app.service
        ├── printer-keepalive.service
        └── update_cert.sh
```

## reception-app との連携

### 環境変数（reception-app/.env）

```
SCAN_TOOL_IP=192.168.210.90
SCAN_TOOL_PORT=5001
SCAN_TOOL_PROTOCOL=https      # 本番
# SCAN_TOOL_PROTOCOL=http     # ローカル開発時は場合による

VITE_SCAN_TOOL_IP=192.168.210.90
VITE_SCAN_TOOL_PORT=5001
VITE_SCAN_TOOL_PROTOCOL=https
```

プリンター(91)はフロント側から直接呼ばれず、スキャナー(90) 経由 or 専用フロー（調査中）。

## 運用原則

- **本番稼働中の機器**。設計変更は村上さん承認必須
- スクリプト修正時は SSH で直接編集せず、リポジトリ管理 → デプロイの流れを推奨
- 再起動が必要な場合は受付業務時間外（営業時間外）に実施
- SSL証明書（certbot 管理）の有効期限に注意
