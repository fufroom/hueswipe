<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/hueswipe/php_errors.log');

ob_start();

$ipLogFile = __DIR__ . "/unique_ips.log";
$totalUploadsFile = __DIR__ . "/total_uploads.log";
$uploadDir = __DIR__ . "/uploads/";

// Ensure necessary files exist
if (!file_exists($ipLogFile)) {
    touch($ipLogFile);
}
if (!file_exists($totalUploadsFile)) {
    file_put_contents($totalUploadsFile, "0");
}
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ✅ Read total unique users (filter out duplicates and empty lines)
$ipList = file($ipLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$uniqueUsers = count(array_filter(array_unique($ipList), function ($ip) {
    return !empty(trim($ip)); // Ensure valid IPs are counted
}));

// ✅ Read total uploaded images from `total_uploads.log`
$totalUploads = (int) file_get_contents($totalUploadsFile);

// ✅ Calculate total disk usage safely
$uploadFiles = glob($uploadDir . "*.*");
$totalDiskUsage = $uploadFiles ? array_sum(array_map('filesize', $uploadFiles)) : 0;
$totalDiskUsageMB = round($totalDiskUsage / (1024 * 1024), 2) . " MB";

ob_end_clean();
echo json_encode([
    "success" => true,
    "site_stats" => [
        "unique_users" => $uniqueUsers,
        "total_uploads" => $totalUploads,
        "total_disk_usage" => $totalDiskUsageMB
    ]
]);
exit;
