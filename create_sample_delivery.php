<?php
/**
 * QR印字サンプル納品書の生成スクリプト（テスト用）
 *
 * - 実在する未紐付けの発注データ(InitialOrder)を1件選び、その「id」をQRに印字
 *   （本番の納品書QRには発注データの主キーID=数値が入るため、それを再現）
 * - 納品書の明細は選んだ発注の実データで描画
 * - storage/app/public/delivery_documents/ に保存し deliveries レコードを作成
 *
 * 実行: php create_sample_delivery.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Delivery;
use App\Models\InitialOrder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Common\ErrorCorrectionLevel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

$FONT = '/Library/Fonts/Arial Unicode.ttf';
if (!file_exists($FONT)) {
    fwrite(STDERR, "フォントが見つかりません: {$FONT}\n");
    exit(1);
}

// ---- 対象の発注データ（未紐付け）を1件選ぶ -----------------------------
$linked = DB::table('delivery_initial_order')->pluck('initial_order_id')->toArray();
$order = InitialOrder::whereNotIn('id', $linked)
    ->whereNotNull('order_no')
    ->orderBy('id', 'desc')
    ->first();

if (!$order) {
    fwrite(STDERR, "未紐付けの発注データが見つかりません\n");
    exit(1);
}

// QRに入れる値＝発注データの主キーID（数値）
$QR_CONTENT = (string) $order->id;

// 明細（発注の実データ。無い項目はダミー補完）
$ORDER_NO   = $order->order_no ?: '-';
$PRODUCT    = $order->name ?: '（品名未登録）';
$PART_NO    = $order->s_name ?: '-';
$QTY        = $order->quantity ?: 1;
$UNIT       = $order->order_unit ?: '個';
$UNIT_PRICE = $order->price ?: 0;
$ORDER_DATE = $order->order_date ? (string) $order->order_date : date('Y-m-d');
$ORDER_USER = $order->order_user ?: '';
$DELI_LOC   = $order->deli_location ?: '仕上げ場';

/** QRマトリクスをGD画像として生成（Imagick不要） */
function makeQrImage(string $data, int $moduleSize = 6, int $quiet = 4)
{
    $qr = Encoder::encode($data, ErrorCorrectionLevel::M(), Encoder::DEFAULT_BYTE_MODE_ECODING);
    $matrix = $qr->getMatrix();
    $dim = $matrix->getWidth();
    $px = ($dim + $quiet * 2) * $moduleSize;

    $img = imagecreatetruecolor($px, $px);
    $white = imagecolorallocate($img, 255, 255, 255);
    $black = imagecolorallocate($img, 0, 0, 0);
    imagefilledrectangle($img, 0, 0, $px, $px, $white);

    for ($y = 0; $y < $dim; $y++) {
        for ($x = 0; $x < $dim; $x++) {
            if ($matrix->get($x, $y)) {
                $x0 = ($x + $quiet) * $moduleSize;
                $y0 = ($y + $quiet) * $moduleSize;
                imagefilledrectangle($img, $x0, $y0, $x0 + $moduleSize - 1, $y0 + $moduleSize - 1, $black);
            }
        }
    }
    return [$img, $px];
}

// ---- 納品書画像の作成（実物レイアウトに準拠） --------------------------
$W = 1120;
$H = 820;
$canvas = imagecreatetruecolor($W, $H);
$white  = imagecolorallocate($canvas, 255, 255, 255);
$black  = imagecolorallocate($canvas, 20, 20, 20);
$gray   = imagecolorallocate($canvas, 120, 120, 120);
$lgray  = imagecolorallocate($canvas, 225, 225, 225);
imagefilledrectangle($canvas, 0, 0, $W, $H, $white);

// 納品日・タイトル
imagettftext($canvas, 15, 0, 40, 55, $black, $FONT, '納品日: ' . $ORDER_DATE);
$title = '納　品　書';
$tbox = imagettfbbox(26, 0, $FONT, $title);
$tw = $tbox[2] - $tbox[0];
imagettftext($canvas, 26, 0, (int)(($W - $tw) / 2), 55, $black, $FONT, $title);

// 宛先
imagettftext($canvas, 14, 0, 40, 100, $black, $FONT, '株式会社アキオカ');
imagettftext($canvas, 11, 0, 40, 125, $gray,  $FONT, '〒713-8103 倉敷市玉島乙島8252-35');
imagettftext($canvas, 11, 0, 40, 145, $gray,  $FONT, 'TEL:086-522-7686  FAX:086-522-7674');
if ($ORDER_USER !== '') {
    imagettftext($canvas, 11, 0, 40, 165, $gray, $FONT, '発注者: ' . $ORDER_USER);
}

// 明細表（注文No / 品名 / 品番）
$top = 220;
$rowH = 54;
$c0 = 40; $c1 = 400; $c2 = 760; $cEnd = $W - 40;
imagefilledrectangle($canvas, $c0, $top, $cEnd, $top + $rowH, $lgray);
imagettftext($canvas, 14, 0, $c0 + 90, $top + 35, $black, $FONT, '注文No');
imagettftext($canvas, 14, 0, $c1 + 130, $top + 35, $black, $FONT, '品名');
imagettftext($canvas, 14, 0, $c2 + 130, $top + 35, $black, $FONT, '品番');
$y = $top + $rowH;
imagettftext($canvas, 15, 0, $c0 + 20, $y + 36, $black, $FONT, $ORDER_NO);
imagettftext($canvas, 14, 0, $c1 + 20, $y + 36, $black, $FONT, $PRODUCT);
imagettftext($canvas, 14, 0, $c2 + 20, $y + 36, $black, $FONT, $PART_NO);
$y += $rowH;

// 明細表2（納入場所 / 数量 / 単位 / 単価 / 金額）
$amount = $QTY * $UNIT_PRICE;
$d0 = 40; $d1 = 320; $d2 = 500; $d3 = 680; $d4 = 880; $dEnd = $W - 40;
imagefilledrectangle($canvas, $d0, $y, $dEnd, $y + $rowH, $lgray);
imagettftext($canvas, 13, 0, $d0 + 70, $y + 34, $black, $FONT, '納入場所');
imagettftext($canvas, 13, 0, $d1 + 50, $y + 34, $black, $FONT, '数量');
imagettftext($canvas, 13, 0, $d2 + 50, $y + 34, $black, $FONT, '単位');
imagettftext($canvas, 13, 0, $d3 + 55, $y + 34, $black, $FONT, '単価');
imagettftext($canvas, 13, 0, $d4 + 30, $y + 34, $black, $FONT, '金額(税抜価格)');
$y += $rowH;
imagettftext($canvas, 14, 0, $d0 + 20, $y + 36, $black, $FONT, $DELI_LOC);
imagettftext($canvas, 14, 0, $d1 + 20, $y + 36, $black, $FONT, number_format($QTY));
imagettftext($canvas, 14, 0, $d2 + 20, $y + 36, $black, $FONT, $UNIT);
imagettftext($canvas, 14, 0, $d3 + 20, $y + 36, $black, $FONT, number_format($UNIT_PRICE, 1));
imagettftext($canvas, 14, 0, $d4 + 20, $y + 36, $black, $FONT, number_format($amount));
$y += $rowH;
// 送料・合計
imagettftext($canvas, 14, 0, $d0 + 300, $y + 36, $black, $FONT, '送料');
imagettftext($canvas, 14, 0, $d4 + 20,  $y + 36, $black, $FONT, '0');
$y += $rowH;
imagettftext($canvas, 15, 0, $d0 + 280, $y + 36, $black, $FONT, '合計（税抜）');
imagettftext($canvas, 15, 0, $d4 + 20,  $y + 36, $black, $FONT, number_format($amount));
$y += $rowH;

// 罫線
for ($gy = $top; $gy <= $y; $gy += $rowH) {
    imageline($canvas, $c0, $gy, $cEnd, $gy, $gray);
}
imageline($canvas, $c0, $top, $c0, $y, $gray);
imageline($canvas, $cEnd, $top, $cEnd, $y, $gray);

// 右下にQR（発注データIDを印字）
list($qrImg, $qrPx) = makeQrImage($QR_CONTENT, 5, 4);
$qrDisp = 150;
$qrX = $W - 40 - $qrDisp;
$qrY = $H - 40 - $qrDisp;
imagecopyresampled($canvas, $qrImg, $qrX, $qrY, 0, 0, $qrDisp, $qrDisp, $qrPx, $qrPx);
imagedestroy($qrImg);
// テスト確認用の小さな注記（本番QRには無いが検証用に表示）
imagettftext($canvas, 9, 0, $qrX - 10, $qrY - 6, $gray, $FONT, 'QR=発注ID:' . $QR_CONTENT);

// ---- 保存 -------------------------------------------------------------
$fileName = Str::random(40) . '.jpg';
$relPath  = 'delivery_documents/' . $fileName;
$fullDir  = storage_path('app/public/delivery_documents');
if (!is_dir($fullDir)) {
    mkdir($fullDir, 0755, true);
}
$fullPath = storage_path('app/public/' . $relPath);
imagejpeg($canvas, $fullPath, 90);
imagedestroy($canvas);

// ---- Deliveryレコード作成（store()と同等） ----------------------------
$publicUrl = config('app.url') . '/storage/' . $relPath;
$delivery = Delivery::create([
    'delivery_type'  => '納品書',
    'document_image' => $publicUrl,
    'received_at'    => now(),
    'token'          => Str::random(40),
]);

$qrCodeUrl = route('delivery.show', ['delivery' => $delivery, 'token' => $delivery->token]);
$delivery->update(['qr_code_url' => $qrCodeUrl]);

$qrDir = storage_path('app/public/qr-codes');
if (!is_dir($qrDir)) {
    mkdir($qrDir, 0755, true);
}
$svg = QrCode::format('svg')->size(300)->margin(2)->generate($qrCodeUrl);
$svgRel = 'qr-codes/qr_delivery_' . $delivery->id . '.svg';
file_put_contents(storage_path('app/public/' . $svgRel), $svg);
$delivery->update(['qr_code_file_path' => $svgRel]);

echo "==== サンプル納品書を作成しました ====\n";
echo "Delivery ID       : {$delivery->id}\n";
echo "印字QRの中身       : {$QR_CONTENT}（= initial_order.id）\n";
echo "対応する発注       : id={$order->id} / 注文No={$ORDER_NO} / 品名={$PRODUCT}\n";
echo "書類画像           : {$publicUrl}\n";
echo "管理画面URL        : " . config('app.url') . "/admin/deliveries/{$delivery->id}\n";
