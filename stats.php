<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/hueswipe/php_errors.log');

header('Content-Type: application/json');

$ipLogFile = __DIR__ . "/unique_ips.log";
$uploadDir = __DIR__ . "/uploads/";

// Ensure log files exist
if (!file_exists($ipLogFile)) {
    touch($ipLogFile);
}
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Get stats
$uniqueUsers = count(file($ipLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
$totalUploads = count(glob($uploadDir . "*.*"));
$totalDiskUsage = array_sum(array_map('filesize', glob($uploadDir . "*.*")));

// Return JSON stats
echo json_encode([
    "success" => true,
    "site_stats" => [
        "unique_users" => $uniqueUsers,
        "total_uploads" => $totalUploads,
        "total_disk_usage" => round($totalDiskUsage / (1024 * 1024), 2) . " MB"
    ]
]);
exit;
