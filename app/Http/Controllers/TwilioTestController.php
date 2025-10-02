<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

/**
 * Twilio電話機能テストコントローラー
 * 
 * 使用前に以下のコマンドでTwilio SDKをインストールしてください：
 * composer require twilio/sdk
 * 
 * 詳細なセットアップ手順は TWILIO_SETUP.md を参照してください。
 */

class TwilioTestController extends Controller
{
    /**
     * Twilio電話機能のテストページを表示
     */
    public function index(): Response
    {
        return Inertia::render('Twilio/Test');
    }

    /**
     * 電話を発信する
     */
    public function makeCall(Request $request)
    {
        $validated = $request->validate([
            'to_number' => 'required|string',
            'from_number' => 'nullable|string',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            $accountSid = env('TWILIO_ACCOUNT_SID');
            $authToken = env('TWILIO_AUTH_TOKEN');
            $twilioNumber = $validated['from_number'] ?? env('TWILIO_PHONE_NUMBER');

            if (!$accountSid || !$authToken || !$twilioNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Twilio credentials are not configured. Please check your .env file.',
                ], 500);
            }

            // Twilio SDKを使用して電話を発信
            $client = new \Twilio\Rest\Client($accountSid, $authToken);

            // TwiMLを使用してメッセージを再生
            $twiml = '<Response><Say language="ja-JP">' . 
                     ($validated['message'] ?? 'これはテストコールです。') . 
                     '</Say></Response>';

            $call = $client->calls->create(
                $validated['to_number'], // To
                $twilioNumber, // From
                [
                    'twiml' => $twiml
                ]
            );

            Log::info('Twilio call initiated', [
                'call_sid' => $call->sid,
                'to_number' => $validated['to_number'],
                'status' => $call->status
            ]);

            return response()->json([
                'success' => true,
                'message' => '電話の発信に成功しました',
                'data' => [
                    'call_sid' => $call->sid,
                    'status' => $call->status,
                    'to' => $validated['to_number'],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Twilio call failed', [
                'error' => $e->getMessage(),
                'to_number' => $validated['to_number']
            ]);

            return response()->json([
                'success' => false,
                'message' => '電話の発信に失敗しました: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 電話のステータスを確認する
     */
    public function checkCallStatus(Request $request)
    {
        $validated = $request->validate([
            'call_sid' => 'required|string',
        ]);

        try {
            $accountSid = env('TWILIO_ACCOUNT_SID');
            $authToken = env('TWILIO_AUTH_TOKEN');

            $client = new \Twilio\Rest\Client($accountSid, $authToken);
            $call = $client->calls($validated['call_sid'])->fetch();

            return response()->json([
                'success' => true,
                'data' => [
                    'sid' => $call->sid,
                    'status' => $call->status,
                    'duration' => $call->duration,
                    'start_time' => $call->startTime,
                    'end_time' => $call->endTime,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch call status', [
                'error' => $e->getMessage(),
                'call_sid' => $validated['call_sid']
            ]);

            return response()->json([
                'success' => false,
                'message' => 'ステータスの取得に失敗しました: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * SMSを送信する
     */
    public function sendSms(Request $request)
    {
        $validated = $request->validate([
            'to_number' => 'required|string',
            'message' => 'required|string|max:1600',
        ]);

        try {
            $accountSid = env('TWILIO_ACCOUNT_SID');
            $authToken = env('TWILIO_AUTH_TOKEN');
            $twilioNumber = env('TWILIO_PHONE_NUMBER');

            if (!$accountSid || !$authToken || !$twilioNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Twilio credentials are not configured.',
                ], 500);
            }

            $client = new \Twilio\Rest\Client($accountSid, $authToken);

            $message = $client->messages->create(
                $validated['to_number'],
                [
                    'from' => $twilioNumber,
                    'body' => $validated['message']
                ]
            );

            Log::info('Twilio SMS sent', [
                'message_sid' => $message->sid,
                'to_number' => $validated['to_number'],
                'status' => $message->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SMSの送信に成功しました',
                'data' => [
                    'message_sid' => $message->sid,
                    'status' => $message->status,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Twilio SMS failed', [
                'error' => $e->getMessage(),
                'to_number' => $validated['to_number']
            ]);

            return response()->json([
                'success' => false,
                'message' => 'SMSの送信に失敗しました: ' . $e->getMessage(),
            ], 500);
        }
    }
}

