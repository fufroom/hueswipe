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
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));  // Normalize the extension to lowercase
$maxFileSize = 10 * 1024 * 1024; // 10MB limit

// Supported file types including HEIC and case-insensitive extensions
$supportedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tiff', 'svg', 'heic'];

// Check if the file type is supported
if (!in_array($ext, $supportedExtensions)) {
    echo json_encode(["success" => false, "error" => "Invalid file type. Supported formats: jpg, jpeg, png, webp, gif, bmp, tiff, svg, heic"]);
    exit;
}

// Check if file size exceeds the 10MB limit
if ($file['size'] > $maxFileSize) {
    echo json_encode(["success" => false, "error" => "File size exceeds 10MB. Please upload a smaller file."]);
    exit;
}

// Get the user's IP address
$userIP = $_SERVER['REMOTE_ADDR'];

// Get the current datetime
$datetime = date('Y-m-d-H-i-s');

// Construct the new filename
$filename = $userIP . '-' . $datetime . '.' . $ext;

// Set the target file path
$targetPath = $uploadDir . $filename;

// Check if the file already exists, and if so, handle it
$tempPath = $file['tmp_name'];

if (file_exists($targetPath)) {
    // If file already exists, generate a unique name by appending a random string
    $filename = $userIP . '-' . $datetime . '-' . uniqid() . '.' . $ext;
    $targetPath = $uploadDir . $filename;
}

if (!move_uploaded_file($tempPath, $targetPath)) {
    echo json_encode(["success" => false, "error" => "Failed to save file"]);
    exit;
}

echo json_encode(["success" => true, "file" => $targetPath]);
exit;
