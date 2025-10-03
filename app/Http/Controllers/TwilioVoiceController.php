<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VoiceGrant;
use Twilio\TwiML\VoiceResponse;

/**
 * Twilioリアルタイム音声通話コントローラー
 * 
 * ブラウザからマイク・スピーカーを使用した双方向音声通話を実現
 */
class TwilioVoiceController extends Controller
{
    /**
     * 音声通話ページを表示
     */
    public function index(): Response
    {
        return Inertia::render('Twilio/VoiceCall');
    }

    /**
     * アクセストークンを生成
     * 
     * Twilio Device SDKがTwilioサービスに接続するために必要
     */
    public function generateToken(Request $request)
    {
        $validated = $request->validate([
            'identity' => 'nullable|string|max:255',
        ]);

        try {
            $accountSid = env('TWILIO_ACCOUNT_SID');
            $apiKey = env('TWILIO_API_KEY');
            $apiSecret = env('TWILIO_API_SECRET');
            $twimlAppSid = env('TWILIO_TWIML_APP_SID');

            if (!$accountSid || !$apiKey || !$apiSecret || !$twimlAppSid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Twilio設定が不完全です。環境変数を確認してください。',
                ], 500);
            }

            // ユーザー識別子（デフォルト: ランダム生成）
            $identity = $validated['identity'] ?? 'user_' . uniqid();

            // アクセストークンを生成
            $token = new AccessToken(
                $accountSid,
                $apiKey,
                $apiSecret,
                3600, // 1時間有効
                $identity
            );

            // Voice Grantを追加
            $voiceGrant = new VoiceGrant();
            $voiceGrant->setOutgoingApplicationSid($twimlAppSid);
            
            // 着信を許可する場合は以下も設定
            // $voiceGrant->setIncomingAllow(true);

            $token->addGrant($voiceGrant);

            Log::info('Twilio access token generated', [
                'identity' => $identity,
            ]);

            return response()->json([
                'success' => true,
                'token' => $token->toJWT(),
                'identity' => $identity,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate Twilio token', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'トークンの生成に失敗しました: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 発信時のTwiML応答を生成
     * 
     * Twilio Device SDKから発信する際にこのエンドポイントが呼ばれる
     */
    public function handleOutgoingCall(Request $request)
    {
        $phoneNumber = $request->input('To');
        $from = env('TWILIO_PHONE_NUMBER');

        Log::info('Outgoing call initiated', [
            'to' => $phoneNumber,
            'from' => $from,
        ]);

        $response = new VoiceResponse();

        if (!$phoneNumber) {
            $response->say('電話番号が指定されていません。', ['language' => 'ja-JP']);
        } else {
            // 電話番号に発信
            $dial = $response->dial('', ['callerId' => $from]);
            $dial->number($phoneNumber);
        }

        return response($response, 200)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * 着信時のTwiML応答を生成
     * 
     * 外部から着信があった場合の処理（オプション）
     */
    public function handleIncomingCall(Request $request)
    {
        $from = $request->input('From');
        $to = $request->input('To');

        Log::info('Incoming call received', [
            'from' => $from,
            'to' => $to,
        ]);

        $response = new VoiceResponse();

        // 特定のブラウザクライアントに転送
        $dial = $response->dial('');
        $dial->client('browser_client'); // ブラウザクライアントのID

        return response($response, 200)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * 通話ステータスのコールバック
     * 
     * 通話の状態変更時にTwilioから呼ばれる
     */
    public function callStatusCallback(Request $request)
    {
        $callSid = $request->input('CallSid');
        $callStatus = $request->input('CallStatus');
        $duration = $request->input('CallDuration');

        Log::info('Call status callback', [
            'call_sid' => $callSid,
            'status' => $callStatus,
            'duration' => $duration,
        ]);

        // ここで通話履歴をデータベースに保存することも可能

        return response()->json(['success' => true]);
    }

    /**
     * デバイスの機能をテスト
     */
    public function testDevice()
    {
        return response()->json([
            'success' => true,
            'message' => 'Device test endpoint',
            'config' => [
                'account_sid' => env('TWILIO_ACCOUNT_SID') ? 'configured' : 'missing',
                'api_key' => env('TWILIO_API_KEY') ? 'configured' : 'missing',
                'api_secret' => env('TWILIO_API_SECRET') ? 'configured' : 'missing',
                'twiml_app_sid' => env('TWILIO_TWIML_APP_SID') ? 'configured' : 'missing',
                'phone_number' => env('TWILIO_PHONE_NUMBER') ? 'configured' : 'missing',
            ]
        ]);
    }
}






