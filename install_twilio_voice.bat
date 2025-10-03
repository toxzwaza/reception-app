@echo off
echo ========================================
echo Twilioリアルタイム音声通話セットアップ
echo ========================================
echo.

REM NPMパッケージをインストール
echo [1/3] NPMパッケージをインストール中...
call npm install

echo.
echo [2/3] 環境変数の設定が必要です
echo.
echo .envファイルに以下を追加してください:
echo.
echo TWILIO_API_KEY=SKxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
echo TWILIO_API_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
echo TWILIO_TWIML_APP_SID=APxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
echo.
echo API KeyとTwiML Appの作成方法は TWILIO_VOICE_SETUP.md を参照してください
echo.

echo [3/3] 次のステップ:
echo.
echo 1. Twilioコンソールで API Key を作成
echo    URL: https://console.twilio.com/us1/account/keys-credentials/api-keys
echo.
echo 2. Twilioコンソールで TwiML App を作成
echo    URL: https://console.twilio.com/us1/develop/voice/manage/twiml-apps
echo.
echo 3. .envファイルに環境変数を追加
echo.
echo 4. キャッシュをクリア: php artisan config:clear
echo.
echo 5. サーバー起動:
echo    - php artisan serve
echo    - npm run dev
echo.
echo 6. アクセス: http://localhost:8000/twilio-voice
echo.
echo ========================================
echo セットアップ完了！
echo ========================================
echo.
pause






