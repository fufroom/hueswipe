<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'upload_errors.log');

ob_start(); // Prevent unexpected output

$response = [];

$uploadDir = 'uploads/';
$logFile = 'upload_log.csv';
$ipLogFile = 'unique_ips.log';
$totalUploadsFile = 'total_uploads.log'; // Stores the total number of images uploaded

// Ensure directories and files exist
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
if (!file_exists($logFile)) {
    file_put_contents($logFile, "IP Address,Original Filename,New Filename,Date-Time,Location,ISP,Browser,OS\n", FILE_APPEND);
}
if (!file_exists($ipLogFile)) {
    touch($ipLogFile);
}
if (!file_exists($totalUploadsFile)) {
    file_put_contents($totalUploadsFile, "0");
}

// Get user IP
$userIP = $_SERVER['REMOTE_ADDR'];
$datetime = date('Y-m-d H:i:s');
$origFilename = isset($_FILES['file']['name']) ? $_FILES['file']['name'] : "Unknown";

// Generate a safe filename
$ext = strtolower(pathinfo($origFilename, PATHINFO_EXTENSION));  
$filename = $userIP . '-' . date('Y-m-d-H-i-s') . '.' . $ext;
$targetPath = $uploadDir . $filename;

$tempPath = isset($_FILES['file']['tmp_name']) ? $_FILES['file']['tmp_name'] : null;

// Defaults for logging
$location = "Unknown";
$isp = "Unknown";
$browser = "Unknown Browser";
$os = "Unknown OS";

// Update unique IPs log
$existingIPs = file_exists($ipLogFile) ? file($ipLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
if (!in_array($userIP, $existingIPs)) {
    file_put_contents($ipLogFile, "$userIP\n", FILE_APPEND);
}

// Attempt to get location and ISP info
try {
    $geoData = @file_get_contents("http://ip-api.com/json/$userIP?fields=country,city,isp,status");
    if ($geoData) {
        $geoJson = json_decode($geoData, true);
        if (isset($geoJson['status']) && $geoJson['status'] === 'success') {
            $location = isset($geoJson['city'], $geoJson['country']) ? $geoJson['city'] . ", " . $geoJson['country'] : "Unknown";
            $isp = isset($geoJson['isp']) ? $geoJson['isp'] : "Unknown";
        }
    }
} catch (Exception $e) {
    error_log("[ERROR] Failed to retrieve location data: " . $e->getMessage());
}

// Attempt to get browser and OS info
try {
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
    
    if (preg_match('/MSIE|Trident/', $userAgent)) {
        $browser = "Internet Explorer";
    } elseif (preg_match('/Edge/', $userAgent)) {
        $browser = "Microsoft Edge";
    } elseif (preg_match('/Chrome/', $userAgent)) {
        $browser = "Google Chrome";
    } elseif (preg_match('/Firefox/', $userAgent)) {
        $browser = "Mozilla Firefox";
    } elseif (preg_match('/Safari/', $userAgent)) {
        $browser = "Apple Safari";
    }

    if (preg_match('/Windows/', $userAgent)) {
        $os = "Windows";
    } elseif (preg_match('/Macintosh|Mac OS/', $userAgent)) {
        $os = "Mac OS";
    } elseif (preg_match('/Linux/', $userAgent)) {
        $os = "Linux";
    } elseif (preg_match('/Android/', $userAgent)) {
        $os = "Android";
    } elseif (preg_match('/iPhone|iPad|iOS/', $userAgent)) {
        $os = "iOS";
    }
} catch (Exception $e) {
    error_log("[ERROR] Failed to retrieve browser/OS data: " . $e->getMessage());
}

// Handle file upload
if (!$tempPath) {
    error_log("[ERROR] No file received.");
    file_put_contents($logFile, "\"$userIP\",\"$origFilename\",\"FAILED\",\"$datetime\",\"$location\",\"$isp\",\"$browser\",\"$os\"\n", FILE_APPEND);
    ob_end_clean();
    echo json_encode(["success" => false, "error" => "No file received"]);
    exit;
}

// Check for supported file types and size
$maxFileSize = 10 * 1024 * 1024; // 10MB
$supportedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tiff', 'svg', 'heic'];

// Ensure file type is supported
if (!in_array($ext, $supportedExtensions)) {
    $errorMessage = "Invalid file type. Supported types are: jpg, jpeg, png, webp, gif, bmp, tiff, svg, heic.";
    error_log("[ERROR] Unsupported file type: $ext");
    file_put_contents($logFile, "\"$userIP\",\"$origFilename\",\"FAILED\",\"$datetime\",\"$location\",\"$isp\",\"$browser\",\"$os\"\n", FILE_APPEND);
    ob_end_clean();
    echo json_encode(["success" => false, "error" => $errorMessage]);
    exit;
}

// Check file size
if ($_FILES['file']['size'] > $maxFileSize) {
    $errorMessage = "File size exceeds 10MB.";
    error_log("[ERROR] File size exceeds limit: " . $_FILES['file']['size']);
    file_put_contents($logFile, "\"$userIP\",\"$origFilename\",\"FAILED\",\"$datetime\",\"$location\",\"$isp\",\"$browser\",\"$os\"\n", FILE_APPEND);
    ob_end_clean();
    echo json_encode(["success" => false, "error" => $errorMessage]);
    exit;
}

// Ensure filename is unique
if (file_exists($targetPath)) {
    $filename = $userIP . '-' . date('Y-m-d-H-i-s') . '-' . uniqid() . '.' . $ext;
    $targetPath = $uploadDir . $filename;
}

// Move file to target directory
if (!move_uploaded_file($tempPath, $targetPath)) {
    $errorMessage = "Failed to save the file to the server. Check permissions or directory availability.";
    error_log("[ERROR] Failed to save file: $targetPath");
    file_put_contents($logFile, "\"$userIP\",\"$origFilename\",\"FAILED\",\"$datetime\",\"$location\",\"$isp\",\"$browser\",\"$os\"\n", FILE_APPEND);
    ob_end_clean();
    echo json_encode(["success" => false, "error" => $errorMessage]);
    exit;
}

// Log successful upload
$logEntry = "\"$userIP\",\"$origFilename\",\"$filename\",\"$datetime\",\"$location\",\"$isp\",\"$browser\",\"$os\"\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

// Ensure file exists and is readable
if (!file_exists($totalUploadsFile)) {
    file_put_contents($totalUploadsFile, "3"); // Start at 3 if missing
}

// Read current count safely
$totalUploads = file_get_contents($totalUploadsFile);
$totalUploads = is_numeric(trim($totalUploads)) ? (int) trim($totalUploads) : 3;

// Increment and overwrite with the new count
$totalUploads++;
file_put_contents($totalUploadsFile, (string) $totalUploads, LOCK_EX);


ob_end_clean();
echo json_encode(["success" => true, "file" => $targetPath]);
exit;
