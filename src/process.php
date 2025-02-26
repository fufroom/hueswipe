<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

$logFile = __DIR__ . "/php_errors.log";
ini_set('error_log', $logFile);

header('Content-Type: application/json');

$startTime = hrtime(true);

$uploadDir = __DIR__ . "/uploads/";

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!isset($_GET['image'])) {
    exit(json_encode(["success" => false, "error" => "No image provided"]));
}

$imagePath = $uploadDir . basename($_GET['image']);

if (!file_exists($imagePath)) {
    exit(json_encode(["success" => false, "error" => "File not found"]));
}

$image = @imagecreatefromstring(file_get_contents($imagePath));
if (!$image) {
    exit(json_encode(["success" => false, "error" => "Invalid image format"]));
}

$width = imagesx($image);
$height = imagesy($image);
$totalPixels = $width * $height;

function quantizeColor(int $r, int $g, int $b, int $precision = 16): array {
    return [floor($r / $precision) * $precision, floor($g / $precision) * $precision, floor($b / $precision) * $precision];
}

$colorCounts = [];

for ($y = 0; $y < $height; $y += 2) {
    for ($x = 0; $x < $width; $x += 2) {
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

$executionTime = round((hrtime(true) - $startTime) / 1e6, 2);

echo json_encode([
    "success" => true,
    "execution_time_ms" => $executionTime,
    "image_info" => compact("width", "height", "totalPixels"),
    "colors" => $finalColors
]);
?>
