<?php
$width = 200;
$height = 200;
$image = imagecreatetruecolor($width, $height);

// 透明背景
imagealphablending($image, false);
imagesavealpha($image, true);
$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $transparent);

// 赤い色
$red = imagecolorallocate($image, 220, 20, 60);

// 正方形の枠
imagefilledrectangle($image, 20, 20, 180, 180, $red);

// 内部を白で塗りつぶし
$white = imagecolorallocate($image, 255, 255, 255);
imagefilledrectangle($image, 30, 30, 170, 170, $white);

// 文字を描画（簡易的な印鑑風）
$font = 5; // 内蔵フォント
$text = '印';
$textColor = $red;
$textX = ($width - strlen($text) * imagefontwidth($font)) / 2;
$textY = ($height - imagefontheight($font)) / 2;
imagestring($image, $font, $textX, $textY, $text, $textColor);

// ディレクトリ作成
$dir = 'storage/app/public/stamp';
if (!file_exists($dir)) {
    mkdir($dir, 0755, true);
}

// PNGとして保存
imagepng($image, $dir . '/sealed.png');
imagedestroy($image);
echo "電子印画像を作成しました: {$dir}/sealed.png\n";
?>
