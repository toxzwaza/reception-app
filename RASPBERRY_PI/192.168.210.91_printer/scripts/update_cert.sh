#!/bin/bash
CERT_DIR="/home/to-murakami/Documents"
CERT_PATH="$CERT_DIR/cert.pem"
KEY_PATH="$CERT_DIR/key.pem"
COMMON_NAME="192.168.210.91"   # ← Flaskのアクセスアドレスに変更可
DAYS=90                        # 有効期限（日数）

echo "🔄 証明書を再生成中..."
openssl req -x509 -newkey rsa:2048 -nodes \
  -keyout "$KEY_PATH" \
  -out "$CERT_PATH" \
  -days "$DAYS" \
  -subj "/C=JP/ST=Okayama/L=Kurashiki/O=Akioka/CN=$COMMON_NAME"

# Flaskをsystemdで動かしている場合は再起動
if systemctl list-units --full -all | grep -q "flask-app.service"; then
  echo "♻️ Flaskサービスを再起動します..."
  sudo systemctl restart flask-app.service
fi

echo "✅ 自己署名証明書を更新しました: $CERT_PATH"
