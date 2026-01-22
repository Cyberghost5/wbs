<?php
// Simple upload handler for payment proof
header('Content-Type: application/json; charset=utf-8');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$maxSize = 5 * 1024 * 1024; // 5MB
$allowed = [
    'image/jpeg' => 'jpg',
    'image/jpg' => 'jpg',
    'image/png' => 'png',
    'application/pdf' => 'pdf'
];

if (!isset($_FILES['proofFile'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['proofFile'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File upload error: ' . $file['error']]);
    exit;
}

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum allowed is 5MB.']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!array_key_exists($mime, $allowed)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unsupported file type. Allowed: JPG, PNG, PDF.']);
    exit;
}

$ext = $allowed[$mime];

$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit;
    }
}

$originalName = pathinfo($file['name'], PATHINFO_FILENAME);
$safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', $originalName);
try {
    $unique = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
} catch (Exception $e) {
    $unique = time() . '_' . bin2hex(openssl_random_pseudo_bytes(6)) . '.' . $ext;
}

$targetPath = $uploadDir . '/' . $unique;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    exit;
}

// Optionally store metadata (delegateType, registrationRef) if provided
$delegateType = isset($_POST['delegateType']) ? substr($_POST['delegateType'], 0, 50) : '';
$registrationRef = isset($_POST['registrationRef']) ? substr($_POST['registrationRef'], 0, 100) : '';

$meta = [
    'file' => 'api/uploads/' . $unique,
    'original' => $file['name'],
    'size' => $file['size'],
    'mime' => $mime,
    'delegateType' => $delegateType,
    'registrationRef' => $registrationRef,
    'uploaded_at' => date('c')
];

$logFile = $uploadDir . '/uploads.log';
file_put_contents($logFile, json_encode($meta) . PHP_EOL, FILE_APPEND | LOCK_EX);

echo json_encode(['success' => true, 'message' => 'File uploaded successfully', 'file' => $meta['file']]);
exit;

?>
