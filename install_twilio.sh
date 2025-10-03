#!/bin/bash

echo "========================================"
echo "Twilio SDK インストールスクリプト"
echo "========================================"
echo ""

# Twilio SDKをインストール
echo "Twilio PHP SDKをインストール中..."
composer require twilio/sdk

echo ""
echo "========================================"
echo "インストール完了！"
echo "========================================"
echo ""
echo "次のステップ:"
echo "1. .envファイルに以下の環境変数を追加してください："
echo ""
echo "   TWILIO_ACCOUNT_SID=your_account_sid_here"
echo "   TWILIO_AUTH_TOKEN=your_auth_token_here"
echo "   TWILIO_PHONE_NUMBER=your_twilio_phone_number_here"
echo ""
echo "2. 詳細なセットアップ手順は TWILIO_SETUP.md を参照してください"
echo ""
echo "3. テストページにアクセス: http://your-domain/twilio-test"
echo ""






