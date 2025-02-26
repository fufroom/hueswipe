<?php
require_once __DIR__ . "/userDetails.php";


header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . "/php_errors.log"); // Per-app logging

ob_start();

$uploadDir = __DIR__ . "/uploads/";
$logFile = __DIR__ . "/upload_log.csv";
$ipLogFile = __DIR__ . "/unique_ips.log";
$totalUploadsFile = __DIR__ . "/total_uploads.log";

foreach ([$uploadDir, $logFile, $ipLogFile, $totalUploadsFile] as $file) {
    if (!file_exists($file)) {
        if ($file === $uploadDir) {
            mkdir($uploadDir, 0777, true);
        } else {
            $defaultContent = $file === $logFile ? 
                "IP Address,Original Filename,New Filename,Date-Time,Location,ISP,Browser,OS,Device Type,Proxy,Mobile,Hosting,File Type Valid\n" : 
                ($file === $totalUploadsFile ? "0" : "");
            file_put_contents($file, $defaultContent, LOCK_EX);
        }
    }
}

$userIP = $_SERVER['REMOTE_ADDR'] ?? "0.0.0.0";
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? "Unknown";
$datetime = date('Y-m-d H:i:s');
$origFilename = $_FILES['file']['name'] ?? "Unknown";
$ext = strtolower(pathinfo($origFilename, PATHINFO_EXTENSION)) ?: "unknown";
$filename = "$userIP-" . date('Y-m-d-H-i-s') . ".$ext";
$targetPath = "$uploadDir/$filename";
$tempPath = $_FILES['file']['tmp_name'] ?? null;

if (!file_exists($ipLogFile) || !str_contains(file_get_contents($ipLogFile), $userIP)) {
    file_put_contents($ipLogFile, "$userIP\n", FILE_APPEND | LOCK_EX);
}

try {
    $userDetails = getUserDetails($userIP, $userAgent);
} catch (Exception $e) {
    error_log("[ERROR] UserDetails.php failed: " . $e->getMessage());
    $userDetails = [
        "location" => "Unknown", "isp" => "Unknown", "browser" => "Unknown",
        "os" => "Unknown", "device_type" => "Unknown", "proxy" => "Unknown",
        "mobile" => "Unknown", "hosting" => "Unknown"
    ];
}

$logData = [
    "IP" => $userIP,
    "Original Filename" => $origFilename,
    "New Filename" => $filename,
    "Date-Time" => $datetime,
    "Location" => $userDetails["location"] ?? "Unknown",
    "ISP" => $userDetails["isp"] ?? "Unknown",
    "Browser" => $userDetails["browser"] ?? "Unknown",
    "OS" => $userDetails["os"] ?? "Unknown",
    "Device Type" => $userDetails["device_type"] ?? "Unknown",
    "Proxy" => $userDetails["proxy"] ? "Yes" : "No",
    "Mobile" => $userDetails["mobile"] ? "Yes" : "No",
    "Hosting" => $userDetails["hosting"] ? "Yes" : "No",
    "File Type Valid" => "Yes"
];

$maxFileSize = 10 * 1024 * 1024;
$supportedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tiff', 'svg', 'heic'];
$fileValid = in_array($ext, $supportedExtensions);

if (!$fileValid) {
    $logData["File Type Valid"] = "No";
    error_log("[WARNING] Unsupported file type uploaded: $origFilename ($ext)");
}

if ($_FILES['file']['size'] > $maxFileSize) {
    error_log("[ERROR] File size exceeds limit: $origFilename (" . $_FILES['file']['size'] . " bytes)");
    file_put_contents($logFile, '"' . implode('","', $logData) . "\"\n", FILE_APPEND | LOCK_EX);
    ob_end_clean();
    exit(json_encode(["success" => false, "error" => "File size exceeds 10MB", "file_stored" => true]));
}

if (file_exists($targetPath)) {
    $filename = "$userIP-" . date('Y-m-d-H-i-s') . '-' . uniqid() . ".$ext";
    $targetPath = "$uploadDir/$filename";
}

if (!move_uploaded_file($tempPath, $targetPath)) {
    error_log("[ERROR] Failed to save file: $targetPath");
    file_put_contents($logFile, '"' . implode('","', $logData) . "\"\n", FILE_APPEND | LOCK_EX);
    ob_end_clean();
    exit(json_encode(["success" => false, "error" => "Failed to save file", "file_stored" => false]));
}

file_put_contents($logFile, '"' . implode('","', $logData) . "\"\n", FILE_APPEND | LOCK_EX);

if (is_writable($totalUploadsFile)) {
    $totalUploads = trim(file_get_contents($totalUploadsFile));
    $totalUploads = is_numeric($totalUploads) ? (int) $totalUploads : 3;
    file_put_contents($totalUploadsFile, (string) ++$totalUploads, LOCK_EX);
} else {
    error_log("[ERROR] Cannot update total_uploads.log. Check file permissions.");
}

ob_end_clean();
exit(json_encode([
    "success" => $fileValid,
    "message" => $fileValid ? "File uploaded successfully" : "Unsupported file type uploaded",
    "file_stored" => true,
    "file" => $targetPath
]));
