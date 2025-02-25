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

// Log initial memory usage
error_log("[DEBUG] Initial memory usage: " . memory_get_usage());

if (!isset($_GET['image'])) {
    echo json_encode(["success" => false, "error" => "No image provided"]);
    exit;
}

$imagePath = $uploadDir . basename($_GET['image']);

// Log the received image path for debugging
error_log("[DEBUG] Image path: $imagePath");

if (!file_exists($imagePath)) {
    echo json_encode(["success" => false, "error" => "File not found"]);
    exit;
}

// Log file size for debugging
$fileSize = filesize($imagePath);
error_log("[DEBUG] Image file size: $fileSize bytes");

$image = @imagecreatefromstring(file_get_contents($imagePath));
if (!$image) {
    echo json_encode(["success" => false, "error" => "Invalid image format"]);
    exit;
}

// Log memory usage after loading the image
error_log("[DEBUG] Memory usage after image loading: " . memory_get_usage());

$width = imagesx($image);
$height = imagesy($image);
$totalPixels = $width * $height;

// Log image dimensions
error_log("[DEBUG] Image dimensions: Width = $width, Height = $height");

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

// Function to round RGB values to a lower precision
function quantizeColor($r, $g, $b, $precision = 16) {
    $r = floor($r / $precision) * $precision;
    $g = floor($g / $precision) * $precision;
    $b = floor($b / $precision) * $precision;
    return [$r, $g, $b];
}

// Color detection with quantization and thresholding
$colorCounts = [];
$threshold = 30; // Distance threshold to treat colors as the same

for ($y = 0; $y < $height; $y += 2) {
    for ($x = 0; $x < $width; $x += 2) {
        $rgb = imagecolorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        // Quantize the color to reduce precision
        list($r, $g, $b) = quantizeColor($r, $g, $b);

        // Convert the color to hex for easy comparison
        $hex = sprintf("#%02X%02X%02X", $r, $g, $b);

        // Check if we already have a similar color in the list
        $foundSimilar = false;
        foreach ($colorCounts as $existingHex => $data) {
            $existingRgb = $data['rgb'];
            // Compute the Euclidean distance between the colors
            $distance = sqrt(pow($existingRgb['r'] - $r, 2) + pow($existingRgb['g'] - $g, 2) + pow($existingRgb['b'] - $b, 2));
            if ($distance < $threshold) {
                $colorCounts[$existingHex]['count']++;
                $foundSimilar = true;
                break;
            }
        }

        // If no similar color was found, add the new color
        if (!$foundSimilar) {
            $colorCounts[$hex] = ['count' => 1, 'rgb' => compact("r", "g", "b")];
        }
    }
}

// Sort colors by frequency
uasort($colorCounts, function ($a, $b) {
    return $b["count"] - $a["count"];
});

// Prepare final colors
$finalColors = [];
foreach (array_slice($colorCounts, 0, 20, true) as $hex => $data) {
    $hsl = rgbToHsl($data["rgb"]['r'], $data["rgb"]['g'], $data["rgb"]['b']);
    $finalColors[] = [
        "hex" => $hex,
        "rgb" => $data["rgb"],
        "hsl" => $hsl,
        "pixels" => $data["count"]
    ];
}

// Log color data for debugging
error_log("[DEBUG] Final color data: " . json_encode($finalColors));

$executionTime = round((microtime(true) - $startTime) * 1000, 2);

// Log execution time
error_log("[DEBUG] Execution time: $executionTime ms");

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
