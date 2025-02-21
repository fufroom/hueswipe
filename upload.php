<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'upload_errors.log');

$response = [];

$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

if (!isset($_FILES['file'])) {
    echo json_encode(["success" => false, "error" => "No file received"]);
    exit;
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
    echo json_encode(["success" => false, "error" => "Invalid file type"]);
    exit;
}

$tempPath = $file['tmp_name'];
$newHash = hash_file('sha256', $tempPath);
$filename = basename($file['name']);
$targetPath = $uploadDir . $filename;

if (file_exists($targetPath)) {
    $existingHash = hash_file('sha256', $targetPath);
    if ($newHash === $existingHash) {
        unlink($targetPath);
    } else {
        $filename = uniqid() . '-' . $filename;
        $targetPath = $uploadDir . $filename;
    }
}

if (!move_uploaded_file($tempPath, $targetPath)) {
    echo json_encode(["success" => false, "error" => "Failed to save file"]);
    exit;
}

echo json_encode(["success" => true, "file" => $targetPath]);
exit;
