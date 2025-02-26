<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . "/php_errors.log");

ob_start();

$ipLogFile = __DIR__ . "/unique_ips.log";
$totalUploadsFile = __DIR__ . "/total_uploads.log";
$uploadDir = __DIR__ . "/uploads/";

foreach ([$ipLogFile, $totalUploadsFile] as $file) {
    if (!file_exists($file)) file_put_contents($file, $file === $totalUploadsFile ? "0" : "");
}

if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

$uniqueUsers = count(array_unique(array_filter(file($ipLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))));

$totalUploads = (int) file_get_contents($totalUploadsFile);

$totalDiskUsageMB = round(array_sum(array_map('filesize', glob($uploadDir . "*.*") ?: [])) / (1024 * 1024), 2) . " MB";

ob_end_clean();
echo json_encode([
    "success" => true,
    "site_stats" => compact("uniqueUsers", "totalUploads", "totalDiskUsageMB")
]);
exit;
