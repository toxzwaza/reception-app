<?php

namespace App\Http\Controllers;

use App\Models\Pickup;
use App\Services\PrintServerService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PickupController extends Controller
{
    // 集荷伝票撮影画面
    public function create(): Response
    {
        return Inertia::render('Pickup/Create');
    }

    // 集荷記録の保存
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slip_image' => 'required|image|max:10240', // 10MB
        ]);

        // 伝票画像の保存
        $slipPath = $request->file('slip_image')->store('pickup_slips', 'public');

        // 集荷記録の作成
        $pickup = Pickup::create([
            'slip_image' => $slipPath,
            'picked_up_at' => now(),
        ]);

        // QRコードURLの生成と保存
        $qrCodeUrl = route('pickup.show', $pickup);
        $pickup->update(['qr_code_url' => $qrCodeUrl]);

        // QRコード画像ファイルの生成と保存
        $qrCodePath = $this->generateQrCodeFile($pickup->id, $qrCodeUrl);
        $pickup->update(['qr_code_file_path' => $qrCodePath]);

        // QRコードの生成（画面表示用）
        $qrCode = QrCode::size(300)
            ->margin(2)
            ->generate($qrCodeUrl);

        // 通知を送信
        NotificationService::sendNotification('pickup_received', [
            'type' => 'pickup_received',
            'picked_up_at' => $pickup->picked_up_at->format('Y-m-d H:i:s'),
            'pickup_id' => $pickup->id,
        ]);

        return Inertia::render('Pickup/Complete', [
            'qrCode' => $qrCode,
            'pickup' => $pickup,
        ]);
    }

    // 集荷伝票の表示
    public function show(Pickup $pickup): Response
    {
        return Inertia::render('Pickup/Show', [
            'pickup' => $pickup,
            'slipUrl' => Storage::url($pickup->slip_image),
        ]);
    }

    // QRコード画像の表示
    public function qrCode(Pickup $pickup)
    {
        if (!$pickup->qr_code_file_path) {
            abort(404, 'QRコードが見つかりません');
        }

        $filePath = storage_path('app/public/' . $pickup->qr_code_file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'QRコードファイルが見つかりません');
        }

        return response()->file($filePath, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    // QRコードファイルの生成
    private function generateQrCodeFile($pickupId, $qrCodeUrl): string
    {
        // QRコードの保存ディレクトリを作成
        $qrDirectory = 'qr-codes';
        $fileName = "qr_pickup_{$pickupId}.svg";
        $filePath = "{$qrDirectory}/{$fileName}";

        // 既存のファイルがあれば削除
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        // QRコードを生成してSVGファイルとして保存
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($qrCodeUrl);

        // ディレクトリが存在しない場合は作成
        $fullDirectoryPath = storage_path('app/public/' . $qrDirectory);
        if (!file_exists($fullDirectoryPath)) {
            mkdir($fullDirectoryPath, 0755, true);
        }

        // SVGファイルとして保存
        $fullFilePath = storage_path('app/public/' . $filePath);
        file_put_contents($fullFilePath, $qrCode);
        
        // ファイルが作成されたか確認
        if (!file_exists($fullFilePath)) {
            throw new \Exception("Failed to create QR code file: {$fullFilePath}");
        }

        return $filePath;
    }

    // プリントサーバーにQRコードを送信
    public function print(Pickup $pickup, Request $request)
    {
        $validated = $request->validate([
            'document_info' => 'required|array',
            'document_info.document_type' => 'required|string',
            'document_info.timestamp' => 'required|string',
            'document_info.id' => 'required|integer'
        ]);

        try {
            // QRコード画像ファイルを読み込み
            if (!$pickup->qr_code_file_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'QRコードファイルが見つかりません。',
                    'error' => 'QR code file not found'
                ], 404);
            }

            $filePath = storage_path('app/public/' . $pickup->qr_code_file_path);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'QRコードファイルが見つかりません。',
                    'error' => 'QR code file does not exist'
                ], 404);
            }

            // QRコード画像をBase64エンコード
            $qrCodeImageData = base64_encode(file_get_contents($filePath));

            // プリントサーバーに送信
            $result = PrintServerService::sendQrCodeToPrintServer(
                $qrCodeImageData,
                $validated['document_info']
            );

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'プリントサーバーへの送信中にエラーが発生しました。',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 管理画面用：集荷伝票一覧
    public function adminIndex(Request $request)
    {
        $query = Pickup::query();

        // 検索フィルター
        if ($request->filled('date_from')) {
            $query->whereDate('picked_up_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('picked_up_at', '<=', $request->date_to);
        }

        // ソート
        $sortBy = $request->get('sort_by', 'picked_up_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $pickups = $query->paginate(20);

        return Inertia::render('Admin/Pickups/Index', [
            'pickups' => $pickups,
            'filters' => $request->only(['date_from', 'date_to', 'sort_by', 'sort_order'])
        ]);
    }

    // 管理画面用：集荷伝票詳細
    public function adminShow(Pickup $pickup)
    {
        return Inertia::render('Admin/Pickups/Show', [
            'pickup' => $pickup,
            'slipUrl' => Storage::url($pickup->slip_image),
            'qrCodeUrl' => $pickup->qr_code_file_path ? route('pickup.qr', $pickup) : null
        ]);
    }

    // 電子印押下
    public function applyDigitalSeal(Pickup $pickup, Request $request)
    {
        $validated = $request->validate([
            'seal_positions' => 'required|array',
            'seal_positions.*.x' => 'required|numeric',
            'seal_positions.*.y' => 'required|numeric',
            'seal_positions.*.width' => 'required|numeric',
            'seal_positions.*.height' => 'required|numeric',
            'seal_positions.*.rotation' => 'required|numeric',
            'seal_positions.*.opacity' => 'required|numeric|min:0|max:1',
            'staff_member_id' => 'nullable|exists:staff_members,id'
        ]);

        try {
            // 画像合成処理
            $sealedImagePath = $this->createSealedImage($pickup, $validated['seal_positions']);
            
            // 電子印の適用
            $updateData = [
                'sealed_slip_image' => $sealedImagePath,
                'sealed_at' => now()
            ];
            
            // staff_member_idが提供されている場合のみ更新
            if (!empty($validated['staff_member_id'])) {
                $updateData['staff_member_id'] = $validated['staff_member_id'];
            }
            
            $pickup->update($updateData);

            // Inertia.jsのレスポンス形式で返す
            return redirect()->back()->with('success', '電子印が正常に適用されました。');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'seal' => '電子印の適用に失敗しました: ' . $e->getMessage()
            ]);
        }
    }

    // 電子印付き画像の作成
    private function createSealedImage(Pickup $pickup, array $sealPositions): string
    {
        // 元画像のパス
        $originalImagePath = storage_path('app/public/' . $pickup->slip_image);
        
        // 電子印画像のパス
        $sealImagePath = storage_path('app/public/stamp/sealed.png');
        
        if (!file_exists($originalImagePath)) {
            throw new \Exception('元画像が見つかりません: ' . $originalImagePath);
        }
        
        if (!file_exists($sealImagePath)) {
            throw new \Exception('電子印画像が見つかりません: ' . $sealImagePath);
        }

        // 画像リソースの作成
        $originalImage = $this->createImageFromFile($originalImagePath);
        if (!$originalImage) {
            throw new \Exception('元画像の読み込みに失敗しました: ' . $originalImagePath);
        }

        $sealImage = $this->createImageFromFile($sealImagePath);
        if (!$sealImage) {
            throw new \Exception('電子印画像の読み込みに失敗しました: ' . $sealImagePath);
        }

        // 元画像のサイズ取得
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

        // 電子印を配置
        foreach ($sealPositions as $position) {
            $this->placeSealOnImage(
                $originalImage, 
                $sealImage, 
                $position, 
                $originalWidth, 
                $originalHeight
            );
        }

        // 保存用のファイル名生成
        $sealedImagePath = 'sealed_slips/sealed_' . $pickup->id . '_' . time() . '.jpg';
        $fullSealedImagePath = storage_path('app/public/' . $sealedImagePath);

        // ディレクトリ作成
        $directory = dirname($fullSealedImagePath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // 画像保存
        if (!imagejpeg($originalImage, $fullSealedImagePath, 90)) {
            throw new \Exception('電子印付き画像の保存に失敗しました');
        }

        // メモリ解放
        imagedestroy($originalImage);
        imagedestroy($sealImage);

        return $sealedImagePath;
    }

    // 電子印を画像に配置
    private function placeSealOnImage($originalImage, $sealImage, array $position, int $originalWidth, int $originalHeight): void
    {
        $x = (int) $position['x'];
        $y = (int) $position['y'];
        $width = (int) $position['width'];
        $height = (int) $position['height'];
        $rotation = (float) $position['rotation'];
        $opacity = (float) $position['opacity'];

        // 電子印画像のサイズ取得
        $sealWidth = imagesx($sealImage);
        $sealHeight = imagesy($sealImage);

        // 電子印をリサイズ
        $resizedSeal = imagecreatetruecolor($width, $height);
        imagealphablending($resizedSeal, false);
        imagesavealpha($resizedSeal, true);
        imagecopyresampled($resizedSeal, $sealImage, 0, 0, 0, 0, $width, $height, $sealWidth, $sealHeight);

        // 回転処理
        if ($rotation != 0) {
            $rotatedSeal = imagerotate($resizedSeal, $rotation, 0);
            imagedestroy($resizedSeal);
            $resizedSeal = $rotatedSeal;
        }

        // 透明度を適用して合成
        $this->imagecopymergeAlpha($originalImage, $resizedSeal, $x, $y, 0, 0, $width, $height, $opacity * 100);

        imagedestroy($resizedSeal);
    }

    // 透明度を考慮した画像合成
    private function imagecopymergeAlpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct): void
    {
        $cut = imagecreatetruecolor($src_w, $src_h);
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
        imagedestroy($cut);
    }

    // ファイルから画像リソースを作成（複数形式対応）
    private function createImageFromFile(string $filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }

        $mimeType = $imageInfo['mime'];
        
        switch ($mimeType) {
            case 'image/jpeg':
                return imagecreatefromjpeg($filePath);
            case 'image/png':
                return imagecreatefrompng($filePath);
            case 'image/gif':
                return imagecreatefromgif($filePath);
            case 'image/webp':
                return imagecreatefromwebp($filePath);
            default:
                return false;
        }
    }
}