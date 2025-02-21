<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/hueswipe/php_errors.log');

header('Content-Type: application/json');

$startTime = microtime(true);

$uploadDir = __DIR__ . "/uploads/";

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!isset($_GET['image'])) {
    echo json_encode(["success" => false, "error" => "No image provided"]);
    exit;
}

$imagePath = $uploadDir . basename($_GET['image']);

if (!file_exists($imagePath)) {
    echo json_encode(["success" => false, "error" => "File not found"]);
    exit;
}

$image = @imagecreatefromstring(file_get_contents($imagePath));
if (!$image) {
    echo json_encode(["success" => false, "error" => "Invalid image format"]);
    exit;
}

$width = imagesx($image);
$height = imagesy($image);
$totalPixels = $width * $height;

// Function to convert RGB to HSL
function rgbToHsl($r, $g, $b) {
    $r /= 255; $g /= 255; $b /= 255;
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $h = $s = $l = ($max + $min) / 2;

    if ($max === $min) {
        $h = $s = 0;
    } else {
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        switch ($max) {
            case $r: $h = ($g - $b) / $d + ($g < $b ? 6 : 0); break;
            case $g: $h = ($b - $r) / $d + 2; break;
            case $b: $h = ($r - $g) / $d + 4; break;
        }
        $h /= 6;
    }

    return [
        "h" => round($h * 360),
        "s" => round($s * 100, 2),
        "l" => round($l * 100, 2)
    ];
}

// Color detection
$colorCounts = [];
for ($y = 0; $y < $height; $y += 2) {
    for ($x = 0; $x < $width; $x += 2) {
        $rgb = imagecolorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $hex = sprintf("#%02X%02X%02X", $r, $g, $b);

        if (!isset($colorCounts[$hex])) {
            $colorCounts[$hex] = ["count" => 0, "rgb" => compact("r", "g", "b")];
        }
        $colorCounts[$hex]["count"]++;
    }
}

// Sort colors by frequency
uasort($colorCounts, function ($a, $b) {
    return $b["count"] - $a["count"];
});

// Prepare final colors
$finalColors = [];
foreach (array_slice($colorCounts, 0, 20, true) as $hex => $data) {
    $hsl = rgbToHsl($data["rgb"]["r"], $data["rgb"]["g"], $data["rgb"]["b"]);
    $finalColors[] = [
        "hex" => $hex,
        "rgb" => $data["rgb"],
        "hsl" => $hsl,
        "pixels" => $data["count"]
    ];
}

$executionTime = round((microtime(true) - $startTime) * 1000, 2);

echo json_encode([
    "success" => true,
    "execution_time_ms" => $executionTime,
    "image_info" => [
        "width" => $width,
        "height" => $height,
        "total_pixels" => $totalPixels
    ],
    "colors" => $finalColors
]);
?>
