<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . "/php_errors.log");

header('Content-Type: application/json');

$startTime = hrtime(true);
$uploadDir = __DIR__ . "/uploads/";
$uploadWebPath = "/uploads/"; // ✅ This is the correct web-accessible path

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!isset($_GET['image'])) {
    exit(json_encode(["success" => false, "error" => "No image provided"]));
}

// ✅ FIXED: Ensure the correct web-accessible path is used
$imageName = basename($_GET['image']); 
$imagePath = $uploadDir . $imageName;
$imageWebPath = $uploadWebPath . $imageName; // ✅ Relative path for frontend

if (!file_exists($imagePath)) {
    exit(json_encode(["success" => false, "error" => "File not found", "image_path" => $imageWebPath]));
}

// Resize large images to avoid memory overload
$maxWidth = 512;
$maxHeight = 512;

list($origWidth, $origHeight) = getimagesize($imagePath);
$scale = min($maxWidth / $origWidth, $maxHeight / $origHeight, 1);
$newWidth = floor($origWidth * $scale);
$newHeight = floor($origHeight * $scale);

$image = imagecreatetruecolor($newWidth, $newHeight);
$source = imagecreatefromstring(file_get_contents($imagePath));
imagecopyresampled($image, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
imagedestroy($source);

$width = imagesx($image);
$height = imagesy($image);
$totalPixels = $width * $height;

// Adjust sampling rate dynamically based on image size
$sampleRate = max(1, floor(min($width, $height) / 100));

function quantizeColor(int $r, int $g, int $b, int $precision = 16): array {
    return [floor($r / $precision) * $precision, floor($g / $precision) * $precision, floor($b / $precision) * $precision];
}

$colorCounts = [];

for ($y = 0; $y < $height; $y += $sampleRate) {
    for ($x = 0; $x < $width; $x += $sampleRate) {
        $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
        [$r, $g, $b] = quantizeColor($color['red'], $color['green'], $color['blue']);
        $hex = sprintf("#%02X%02X%02X", $r, $g, $b);
        $colorCounts[$hex] = ($colorCounts[$hex] ?? 0) + 1;
    }
}

uasort($colorCounts, fn($a, $b) => $b <=> $a);

$finalColors = array_map(fn($hex, $count) => ["hex" => $hex, "pixels" => $count], 
                         array_keys(array_slice($colorCounts, 0, 20, true)), 
                         array_values(array_slice($colorCounts, 0, 20, true)));

imagedestroy($image); // Free memory after processing

$executionTime = round((hrtime(true) - $startTime) / 1e6, 2);

echo json_encode([
    "success" => true,
    "execution_time_ms" => $executionTime,
    "image_info" => compact("width", "height", "totalPixels"),
    "image_url" => $imageWebPath, // ✅ Send correct web-accessible URL
    "colors" => $finalColors
]);
?>
