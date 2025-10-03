<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * 受付番号でQRコード画像を生成し、ファイルパスを返す
     *
     * @param string $receptionNumber
     * @return string 生成されたQRコード画像のパス
     */
    public static function generateQrCodeImage(string $receptionNumber): string
    {
        // QRコードの保存ディレクトリを作成
        $qrDirectory = 'qr-codes';
        $fileName = "qr_{$receptionNumber}.svg";
        $filePath = "{$qrDirectory}/{$fileName}";

        // 既存のファイルがあれば削除
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        // QRコードを生成してSVGファイルとして保存
        $qrCode = QrCode::format('svg')
            ->size(200)
            ->margin(1)
            ->generate($receptionNumber);

        // ディレクトリが存在しない場合は作成
        $fullDirectoryPath = storage_path('app/public/' . $qrDirectory);
        if (!file_exists($fullDirectoryPath)) {
            mkdir($fullDirectoryPath, 0755, true);
        }

        // SVGをメール対応にクリーンアップして保存
        $cleanSvg = self::cleanSvgForEmail($qrCode);
        $fullFilePath = storage_path('app/public/' . $filePath);
        file_put_contents($fullFilePath, $cleanSvg);
        
        // ファイルが作成されたか確認
        if (!file_exists($fullFilePath)) {
            throw new \Exception("Failed to create QR code file: {$fullFilePath}");
        }

        return $filePath;
    }

    /**
     * QRコード画像のURLを取得
     *
     * @param string $filePath
     * @return string
     */
    public static function getQrCodeUrl(string $filePath): string
    {
        return Storage::url($filePath);
    }

    /**
     * QRコード画像をBase64エンコードしてdata URIとして取得
     *
     * @param string $filePath
     * @return string
     */
    public static function getQrCodeDataUri(string $filePath): string
    {
        $fullPath = storage_path('app/public/' . $filePath);
        if (file_exists($fullPath)) {
            $content = file_get_contents($fullPath);
            $mimeType = self::getMimeType($filePath);
            return "data:{$mimeType};base64," . base64_encode($content);
        }
        return '';
    }

    /**
     * ファイル拡張子からMIMEタイプを取得
     *
     * @param string $filePath
     * @return string
     */
    private static function getMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'png':
                return 'image/png';
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'gif':
                return 'image/gif';
            case 'svg':
                return 'image/svg+xml';
            default:
                return 'image/png';
        }
    }

    /**
     * QRコード画像ファイルを削除
     *
     * @param string $filePath
     * @return bool
     */
    public static function deleteQrCodeImage(string $filePath): bool
    {
        if (Storage::exists($filePath)) {
            return Storage::delete($filePath);
        }
        return true;
    }

    /**
     * SVGをメール対応のクリーンな形式に変換
     *
     * @param string $svg
     * @return string
     */
    private static function cleanSvgForEmail(string $svg): string
    {
        // XML宣言を除去
        $svg = preg_replace('/<\?xml[^>]*\?>/', '', $svg);
        
        // 既存のwidthとheight属性を除去してから新しい属性を追加
        $svg = preg_replace('/width="[^"]*"/', '', $svg);
        $svg = preg_replace('/height="[^"]*"/', '', $svg);
        
        // SVGタグにメール対応の属性を追加
        $svg = str_replace(
            '<svg',
            '<svg width="200" height="200" style="display: block; margin: 0 auto;"',
            $svg
        );
        
        return trim($svg);
    }

}
