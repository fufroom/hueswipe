<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/hueswipe/php_errors.log');

header('Content-Type: application/json');

// Start the timer to measure execution time
$startTime = microtime(true);

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

// Determine the MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $imagePath);
finfo_close($finfo);

// Supported MIME types
$allowedMimeTypes = ["image/jpeg", "image/png", "image/gif", "image/webp", "image/heic"];
if (!in_array($mimeType, $allowedMimeTypes)) {
    error_log("[ERROR] Unsupported file format: $mimeType");
    echo json_encode(["success" => false, "error" => "Invalid file format"]);
    exit;
}

// Handle WebP conversion
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

// Handle HEIC files (assuming you have a conversion method or external tool like ImageMagick or an API)
if ($mimeType === "image/heic") {
    // Example using Imagick for HEIC to PNG conversion
    try {
        $imagick = new Imagick($imagePath);
        $imagick->setImageFormat('png');
        $convertedPath = str_replace(".heic", ".png", $imagePath);
        $imagick->writeImage($convertedPath);
        $imagePath = $convertedPath;
        $imagick->clear();
        $imagick->destroy();
    } catch (Exception $e) {
        error_log("[ERROR] Failed to convert HEIC image: " . $e->getMessage());
        echo json_encode(["success" => false, "error" => "HEIC conversion failed"]);
        exit;
    }
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

// Collect colors aggressively by sampling every 2nd pixel
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

// Group similar colors by calculating a simple Euclidean distance in RGB space
function rgbDistance($rgb1, $rgb2) {
    $r1 = ($rgb1 >> 16) & 0xFF;
    $g1 = ($rgb1 >> 8) & 0xFF;
    $b1 = $rgb1 & 0xFF;

    $r2 = ($rgb2 >> 16) & 0xFF;
    $g2 = ($rgb2 >> 8) & 0xFF;
    $b2 = $rgb2 & 0xFF;

    return sqrt(pow($r2 - $r1, 2) + pow($g2 - $g1, 2) + pow($b2 - $b1, 2));
}

// Merge similar colors aggressively
$finalColors = [];
$colorKeys = array_keys($colorCounts);
foreach ($colorKeys as $color) {
    $merged = false;
    foreach ($finalColors as &$finalColor) {
        if (rgbDistance(hex2rgb($color), hex2rgb($finalColor['hex'])) < 50) {
            $finalColor['pixels'] += $colorCounts[$color];
            $merged = true;
            break;
        }
    }
    if (!$merged) {
        $finalColors[] = [
            'hex' => $color,
            'pixels' => $colorCounts[$color]
        ];
    }
}

usort($finalColors, function ($a, $b) {
    return $b['pixels'] - $a['pixels'];
});

function hex2rgb($hex) {
    $rgb = sscanf($hex, "#%02x%02x%02x");
    return ($rgb[0] << 16) + ($rgb[1] << 8) + $rgb[2];
}

// End of script timer
$endTime = microtime(true);
$executionTime = round(($endTime - $startTime) * 1000, 2); // milliseconds

// Prepare response
echo json_encode([
    "success" => true,
    "execution_time_ms" => $executionTime,
    "image_info" => [
        "width" => $width,
        "height" => $height,
        "aspect_ratio" => $aspectRatio,
        "file_type" => $fileExtension,
        "file_size" => $fileSize,
        "total_pixels" => $totalPixels,
        "unique_colors" => count($colorCounts)
    ],
    "colors" => $finalColors,
    "site_stats" => [
        "unique_users" => count(file($ipLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)),
        "total_uploads" => $totalUploads,
        "total_disk_usage" => round($totalDiskUsage / (1024 * 1024), 2) . " MB"
    ]
]);

?>
