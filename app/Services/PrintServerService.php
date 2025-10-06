<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrintServerService
{
    /**
     * プリントサーバーのベースURL
     */
    private const PRINT_SERVER_BASE_URL = 'http://localhost:8080'; // 開発環境用、本番では設定ファイルから取得

    /**
     * QRコード画像をプリントサーバーに送信
     *
     * @param string $qrCodeImageData Base64エンコードされたQRコード画像データ
     * @param array $documentInfo 書類情報
     * @return array 送信結果
     */
    public static function sendQrCodeToPrintServer(string $qrCodeImageData, array $documentInfo): array
    {
        try {
            Log::info('プリントサーバーへの送信開始', [
                'image_data_size' => strlen($qrCodeImageData),
                'document_info' => $documentInfo
            ]);

            // プリントサーバーが利用可能かチェック
            if (!self::isPrintServerAvailable()) {
                return [
                    'success' => false,
                    'message' => 'プリントサーバーに接続できません。接続を確認してください。',
                    'error' => 'Print server not available'
                ];
            }

            // プリントサーバーに送信
            $response = Http::timeout(30)->post(self::PRINT_SERVER_BASE_URL . '/print/qr-code', [
                'qr_code_image' => $qrCodeImageData, // Base64エンコードされた画像データ
                'document_type' => $documentInfo['document_type'] ?? 'Unknown',
                'timestamp' => $documentInfo['timestamp'] ?? now()->toISOString(),
                'document_id' => $documentInfo['id'] ?? null,
                'print_options' => [
                    'size' => 'A4',
                    'orientation' => 'portrait',
                    'quality' => 'high',
                    'format' => 'svg' // QRコードはSVG形式
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('プリントサーバーへの送信成功', [
                    'response' => $responseData
                ]);

                // プリントサーバーからの印刷完了ステータスをチェック
                if (isset($responseData['status']) && $responseData['status'] === 'success') {
                    return [
                        'success' => true,
                        'message' => '印刷が正常に完了しました。',
                        'status' => 'completed',
                        'data' => $responseData
                    ];
                } else {
                    return [
                        'success' => true,
                        'message' => 'プリントサーバーに正常に送信されました。',
                        'status' => 'sent',
                        'data' => $responseData
                    ];
                }
            } else {
                Log::error('プリントサーバーへの送信失敗', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'プリントサーバーへの送信に失敗しました。',
                    'error' => $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('プリントサーバー送信エラー', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'プリントサーバーへの送信中にエラーが発生しました。',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * プリントサーバーが利用可能かチェック
     *
     * @return bool
     */
    private static function isPrintServerAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get(self::PRINT_SERVER_BASE_URL . '/health');
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('プリントサーバーのヘルスチェック失敗', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * プリントサーバーの接続状態を取得
     *
     * @return array
     */
    public static function getPrintServerStatus(): array
    {
        try {
            $response = Http::timeout(5)->get(self::PRINT_SERVER_BASE_URL . '/status');
            
            if ($response->successful()) {
                return [
                    'connected' => true,
                    'status' => 'online',
                    'data' => $response->json()
                ];
            } else {
                return [
                    'connected' => false,
                    'status' => 'offline',
                    'error' => 'Server responded with status: ' . $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'status' => 'offline',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * プリントサーバーの設定を取得
     *
     * @return array
     */
    public static function getPrintServerConfig(): array
    {
        return [
            'base_url' => self::PRINT_SERVER_BASE_URL,
            'endpoints' => [
                'health' => '/health',
                'status' => '/status',
                'print_qr' => '/print/qr-code',
                'print_document' => '/print/document'
            ],
            'timeout' => 10,
            'retry_attempts' => 3
        ];
    }
}
