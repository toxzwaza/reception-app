<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\InitialOrder;
use App\Services\PrintServerService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DeliveryController extends Controller
{
    // 納品書/受領書選択画面
    public function create(): Response
    {
        return Inertia::render('Delivery/Create');
    }

    // 書類撮影画面
    public function capture(Request $request): Response
    {
        $validated = $request->validate([
            'delivery_type' => 'required|in:納品書,受領書',
            'staff_member_id' => 'required|exists:staff_members,id',
        ]);

        return Inertia::render('Delivery/Capture', $validated);
    }

    // 納品記録の保存
    public function store(Request $request)
    {
        $validated = $request->validate([
            'delivery_type' => 'required|in:納品書,受領書',
            'document_image' => 'required|image|max:10240', // 10MB
        ]);

        // 書類画像の保存
        $documentPath = $request->file('document_image')->store('delivery_documents', 'public');
        $validated['document_image'] = $documentPath;

        // 納品記録の作成
        $delivery = Delivery::create([
            'delivery_type' => $validated['delivery_type'],
            'document_image' => $documentPath,
            'received_at' => now(),
        ]);

        // QRコードURLの生成と保存
        $qrCodeUrl = route('delivery.show', $delivery);
        $delivery->update(['qr_code_url' => $qrCodeUrl]);

        // QRコード画像ファイルの生成と保存
        $qrCodePath = $this->generateQrCodeFile($delivery->id, $qrCodeUrl);
        $delivery->update(['qr_code_file_path' => $qrCodePath]);

        // QRコードの生成（画面表示用）
        $qrCode = QrCode::size(300)
            ->margin(2)
            ->generate($qrCodeUrl);

        // 通知を送信
        NotificationService::sendNotification('delivery_received', [
            'type' => 'delivery_received',
            'delivery_type' => $delivery->delivery_type,
            'received_at' => $delivery->received_at->format('Y-m-d H:i:s'),
            'delivery_id' => $delivery->id,
        ]);

        return Inertia::render('Delivery/Complete', [
            'qrCode' => $qrCode,
            'delivery' => $delivery,
        ]);
    }

    // 納品書類の表示
    public function show(Delivery $delivery): Response
    {
        return Inertia::render('Delivery/Show', [
            'delivery' => $delivery,
            'sealedDocumentUrl' => Storage::url($delivery->document_image),
        ]);
    }

    // QRコード画像の表示
    public function qrCode(Delivery $delivery)
    {
        if (!$delivery->qr_code_file_path) {
            abort(404, 'QRコードが見つかりません');
        }

        $filePath = storage_path('app/public/' . $delivery->qr_code_file_path);

        if (!file_exists($filePath)) {
            abort(404, 'QRコードファイルが見つかりません');
        }

        return response()->file($filePath, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    // QRコードファイルの生成
    private function generateQrCodeFile($deliveryId, $qrCodeUrl): string
    {
        // QRコードの保存ディレクトリを作成
        $qrDirectory = 'qr-codes';
        $fileName = "qr_delivery_{$deliveryId}.svg";
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
    public function print(Delivery $delivery, Request $request)
    {
        $validated = $request->validate([
            'document_info' => 'required|array',
            'document_info.document_type' => 'required|string',
            'document_info.timestamp' => 'required|string',
            'document_info.id' => 'required|integer'
        ]);

        try {
            // QRコード画像ファイルを読み込み
            if (!$delivery->qr_code_file_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'QRコードファイルが見つかりません。',
                    'error' => 'QR code file not found'
                ], 404);
            }

            $filePath = storage_path('app/public/' . $delivery->qr_code_file_path);

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

    // 管理画面用：納品書・受領書一覧
    public function adminIndex(Request $request)
    {
        $query = Delivery::query();

        // 検索フィルター
        if ($request->filled('delivery_type')) {
            $query->where('delivery_type', $request->delivery_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('received_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('received_at', '<=', $request->date_to);
        }

        // ソート
        $sortBy = $request->get('sort_by', 'received_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $deliveries = $query->paginate(20);

        return Inertia::render('Admin/Deliveries/Index', [
            'deliveries' => $deliveries,
            'filters' => $request->only(['delivery_type', 'date_from', 'date_to', 'sort_by', 'sort_order'])
        ]);
    }

    // 管理画面用：納品書・受領書詳細
    public function adminShow(Delivery $delivery)
    {
        $linkedOrder = null;
        
        // 紐づけ済み発注データがある場合は取得
        if ($delivery->initial_order_id) {
            $order = InitialOrder::find($delivery->initial_order_id);
            if ($order) {
                // Stockデータも取得
                $stock = \App\Models\Stock::find($order->stock_id);
                if ($stock) {
                    $order->img_path = $stock->img_path;
                    $order->stock_id = $stock->id;
                }
                $linkedOrder = $order;
            }
        }

        return Inertia::render('Admin/Deliveries/Show', [
            'delivery' => $delivery,
            'documentUrl' => Storage::url($delivery->document_image),
            'qrCodeUrl' => $delivery->qr_code_file_path ? route('delivery.qr', $delivery) : null,
            'linkedOrder' => $linkedOrder,
        ]);
    }

    // 発注データを紐づける
    public function linkOrder(Delivery $delivery, Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:initial_orders,id',
            'delivery_type' => 'required|in:partial,complete',
            'signage_display' => 'required|in:show,hide',
        ]);

        try {
            $order = InitialOrder::findOrFail($validated['order_id']);

            // delifile_pathを設定: https://akioka-reception.cloud/ + delivery.document_image
            $delifilePath = 'https://akioka-reception.cloud/' . $delivery->document_image;

            $order->delifile_path = $delifilePath;

            // 完納の場合はreceive_flgを1に設定
            if ($validated['delivery_type'] === 'complete') {
                $order->receive_flg = 1;
            }
            // サイネージディスプレイの設定（必要に応じて保存処理を追加）
            $signageDisplay = $validated['signage_display']; // 'show' or 'hide'
            if ($signageDisplay === 'hide') {
                $order->receipt_flg = 1;
            }

            $order->save();

            // delivery.initial_order_idにorder.idを格納
            $delivery->initial_order_id = $validated['order_id'];
            $delivery->save();

            return redirect()->back()->with('success', '発注データを紐づけました。');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => '発注データの紐づけに失敗しました: ' . $e->getMessage()
            ]);
        }
    }

    // 発注データの紐づけを解除
    public function unlinkOrder(Delivery $delivery)
    {
        try {
            $delivery->initial_order_id = null;
            $delivery->save();

            return redirect()->back()->with('success', '発注データの紐づけを解除しました。');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => '発注データの紐づけ解除に失敗しました: ' . $e->getMessage()
            ]);
        }
    }

    // 電子印押下
    public function applyDigitalSeal(Delivery $delivery, Request $request)
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
            $sealedImagePath = $this->createSealedImage($delivery, $validated['seal_positions']);

            // 電子印の適用
            $updateData = [
                'sealed_document_image' => $sealedImagePath,
                'sealed_at' => now()
            ];

            // staff_member_idが提供されている場合のみ更新
            if (!empty($validated['staff_member_id'])) {
                $updateData['staff_member_id'] = $validated['staff_member_id'];
            }

            $delivery->update($updateData);

            // Inertia.jsのレスポンス形式で返す
            return redirect()->back()->with('success', '電子印が正常に適用されました。');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'seal' => '電子印の適用に失敗しました: ' . $e->getMessage()
            ]);
        }
    }

    // 電子印付き画像の作成
    private function createSealedImage(Delivery $delivery, array $sealPositions): string
    {
        // 元画像のパス
        $originalImagePath = storage_path('app/public/' . $delivery->document_image);

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
        $sealedImagePath = 'sealed_documents/sealed_' . $delivery->id . '_' . time() . '.jpg';
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
