<?php
// Simple admin listing for uploaded payment proofs
// NOTE: This page is not access-protected. Add authentication before using in production.

$logFile = __DIR__ . '/../api/uploads/uploads.log';
$uploadsDir = __DIR__ . '/../api/uploads';

$entries = [];
if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $data = json_decode($line, true);
        if ($data) $entries[] = $data;
    }
}

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Uploaded Proofs - Admin</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .uploads-table { width:100%; border-collapse: collapse; }
        .uploads-table th, .uploads-table td { padding:10px; border-bottom:1px solid #eee; text-align:left; }
        .thumb { max-width:140px; max-height:90px; object-fit:cover; border-radius:6px; }
        .meta { font-size:0.9rem; color:#555; }
        .actions a { margin-right:8px; }
        .container { max-width:1100px; margin:30px auto; padding:0 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Uploaded Payment Proofs</h1>
        <p class="meta">Location: <?php echo esc($uploadsDir); ?> — <?php echo count($entries); ?> entries</p>

        <?php if (empty($entries)): ?>
            <p>No uploads yet.</p>
        <?php else: ?>
            <table class="uploads-table">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>File</th>
                        <th>Delegate</th>
                        <th>Registration Ref</th>
                        <th>Uploaded At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach (array_reverse($entries) as $e): 
                    $file = isset($e['file']) ? $e['file'] : '';
                    $filePath = realpath(__DIR__ . '/../' . ltrim($file, '/'));
                    $isImage = false;
                    if ($filePath && file_exists($filePath)) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $filePath);
                        finfo_close($finfo);
                        $isImage = strpos($mime, 'image/') === 0;
                    }
                ?>
                    <tr>
                        <td>
                            <?php if ($isImage && $filePath): ?>
                                <img src="/<?php echo esc($file); ?>" class="thumb" alt="proof">
                            <?php else: ?>
                                <div class="meta">(no preview)</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($file): ?>
                                <div><?php echo esc(basename($file)); ?></div>
                                <div class="meta"><?php echo isset($e['size']) ? number_format($e['size']/1024,2) . ' KB' : ''; ?></div>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc($e['delegateType'] ?? ''); ?></td>
                        <td><?php echo esc($e['registrationRef'] ?? ''); ?></td>
                        <td><?php echo esc($e['uploaded_at'] ?? ''); ?></td>
                        <td class="actions">
                            <?php if ($file): ?>
                                <a href="/<?php echo esc($file); ?>" target="_blank">Download</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
