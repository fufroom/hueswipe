<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/hueswipe/php_errors.log');

header('Content-Type: application/json');

$uploadDir = __DIR__ . "/uploads/";
$ipLogFile = __DIR__ . "/unique_ips.log";
$statsFile = __DIR__ . "/site_stats.log";

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$userIP = $_SERVER['REMOTE_ADDR'];
$uniqueIPs = file_exists($ipLogFile) ? file($ipLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

if (!in_array($userIP, $uniqueIPs)) {
    file_put_contents($ipLogFile, $userIP . PHP_EOL, FILE_APPEND);
}

$totalUploads = count(glob($uploadDir . "*.*"));
$totalDiskUsage = array_sum(array_map('filesize', glob($uploadDir . "*.*")));

if (!isset($_GET['image'])) {
    error_log("[ERROR] No image provided");
    echo json_encode(["success" => false, "error" => "No image provided"]);
    exit;
}

$imagePath = $uploadDir . basename($_GET['image']);

if (!file_exists($imagePath)) {
    error_log("[ERROR] File does not exist: $imagePath");
    echo json_encode(["success" => false, "error" => "File not found"]);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $imagePath);
finfo_close($finfo);

$allowedMimeTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
if (!in_array($mimeType, $allowedMimeTypes)) {
    error_log("[ERROR] Unsupported file format: $mimeType");
    echo json_encode(["success" => false, "error" => "Invalid file format"]);
    exit;
}

if ($mimeType === "image/webp") {
    $webpImage = imagecreatefromwebp($imagePath);
    if (!$webpImage) {
        error_log("[ERROR] Failed to convert WebP image");
        echo json_encode(["success" => false, "error" => "WebP conversion failed"]);
        exit;
    }
    $convertedPath = str_replace(".webp", ".png", $imagePath);
    imagepng($webpImage, $convertedPath);
    imagedestroy($webpImage);
    $imagePath = $convertedPath;
}

$image = @imagecreatefromstring(file_get_contents($imagePath));
if (!$image) {
    error_log("[ERROR] imagecreatefromstring() failed for $imagePath");
    echo json_encode(["success" => false, "error" => "Invalid image format"]);
    exit;
}

$width = imagesx($image);
$height = imagesy($image);
$aspectRatio = round($width / $height, 2);
$totalPixels = $width * $height;
$fileExtension = pathinfo($imagePath, PATHINFO_EXTENSION);
$fileSize = filesize($imagePath);

$colorCounts = [];
for ($y = 0; $y < $height; $y += 2) {
    for ($x = 0; $x < $width; $x += 2) {
        $rgb = imagecolorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $hex = sprintf("#%02X%02X%02X", $r, $g, $b);
        if (!isset($colorCounts[$hex])) {
            $colorCounts[$hex] = 0;
        }
        $colorCounts[$hex]++;
    }
}

arsort($colorCounts);

function rgbToHsl($r, $g, $b) {
    $r /= 255; $g /= 255; $b /= 255;
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $h = $s = $l = ($max + $min) / 2;
    if ($max == $min) {
        $h = $s = 0;
    } else {
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        if ($max == $r) {
            $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
        } elseif ($max == $g) {
            $h = ($b - $r) / $d + 2;
        } else {
            $h = ($r - $g) / $d + 4;
        }
        $h /= 6;
    }
    return [round($h * 360), round($s * 100, 2), round($l * 100, 2)];
}

$finalColors = [];
foreach (array_slice($colorCounts, 0, 20, true) as $hex => $count) {
    $rgb = sscanf($hex, "#%02X%02X%02X");
    $hsl = rgbToHsl($rgb[0], $rgb[1], $rgb[2]);
    $finalColors[] = [
        "hex" => $hex,
        "rgb" => ["r" => $rgb[0], "g" => $rgb[1], "b" => $rgb[2]],
        "hsl" => ["h" => $hsl[0], "s" => $hsl[1], "l" => $hsl[2]],
        "pixels" => $count,
        "percent" => round(($count / $totalPixels) * 100, 2)
    ];
}

$totalUniqueUsers = count(file($ipLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
file_put_contents($statsFile, json_encode([
    "unique_users" => $totalUniqueUsers,
    "total_uploads" => $totalUploads,
    "total_disk_usage" => $totalDiskUsage
]));

echo json_encode([
    "success" => true,
    "image_info" => [
        "width" => $width,
        "height" => $height,
        "aspect_ratio" => $aspectRatio,
        "file_type" => $fileExtension,
        "file_size" => $fileSize,
        "total_pixels" => $totalPixels,
        "unique_colors" => count($colorCounts)
    ],
    "site_stats" => [
        "unique_users" => $totalUniqueUsers,
        "total_uploads" => $totalUploads,
        "total_disk_usage" => round($totalDiskUsage / (1024 * 1024), 2) . " MB"
    ],
    "colors" => $finalColors
]);
?>
