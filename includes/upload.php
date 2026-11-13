<?php
/**
 * DENTAL Management System — Defense-in-Depth Image Upload Handler
 */

require_once __DIR__ . '/config.php';

/**
 * Validates, sanitizes, re-encodes, and safely stores an uploaded image.
 *
 * @param array  $file      The $_FILES['field'] array.
 * @param string $targetDir Absolute directory path where the validated file will be stored.
 * @return array ['success' => bool, 'filename' => ?string, 'error' => ?string]
 */
function handle_secure_image_upload(array $file, string $targetDir): array
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'filename' => null, 'error' => 'upload_invalid_params'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'filename' => null, 'error' => 'upload_failed'];
    }

    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'filename' => null, 'error' => 'upload_not_http_post'];
    }

    // 1. Enforce strict file size boundary (Max 2 MB)
    if (($file['size'] ?? 0) <= 0 || $file['size'] > MAX_UPLOAD_BYTES) {
        return ['success' => false, 'filename' => null, 'error' => 'upload_too_large'];
    }

    // 2. Reject dangerous or double-extension filenames upfront
    $originalName = basename((string)($file['name'] ?? ''));
    if (preg_match('/\.(php\d*|phtml|phar|exe|sh|bat|cmd|js|html?|svg|htaccess|user\.ini)/i', $originalName)) {
        return ['success' => false, 'filename' => null, 'error' => 'upload_forbidden_extension'];
    }

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    if (!in_array($ext, $allowedExtensions, true)) {
        return ['success' => false, 'filename' => null, 'error' => 'upload_invalid_format'];
    }

    // 3. Server-side MIME type verification via finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
    ];
    if (!isset($allowedMimes[$mime])) {
        return ['success' => false, 'filename' => null, 'error' => 'upload_invalid_mime'];
    }

    // 4. Image content signature & dimension verification
    $imgInfo = @getimagesize($file['tmp_name']);
    if ($imgInfo === false || !in_array($imgInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
        return ['success' => false, 'filename' => null, 'error' => 'upload_corrupted_image'];
    }

    // 5. Generate cryptographic random filename (never trust user-supplied filename)
    $safeExt = $allowedMimes[$mime];
    $newFilename = bin2hex(random_bytes(16)) . '.' . $safeExt;

    // Ensure destination directory exists
    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
        return ['success' => false, 'filename' => null, 'error' => 'upload_dir_error'];
    }

    $destinationPath = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $newFilename;

    // 6. Re-encode image via GD to destroy any polyglot payloads or EXIF script comments
    $saved = false;
    if ($imgInfo[2] === IMAGETYPE_JPEG && function_exists('imagecreatefromjpeg')) {
        $src = @imagecreatefromjpeg($file['tmp_name']);
        if ($src !== false) {
            $saved = imagejpeg($src, $destinationPath, 90);
            imagedestroy($src);
        }
    } elseif ($imgInfo[2] === IMAGETYPE_PNG && function_exists('imagecreatefrompng')) {
        $src = @imagecreatefrompng($file['tmp_name']);
        if ($src !== false) {
            imagealphablending($src, false);
            imagesavealpha($src, true);
            $saved = imagepng($src, $destinationPath, 6);
            imagedestroy($src);
        }
    }

    if (!$saved) {
        return ['success' => false, 'filename' => null, 'error' => 'upload_processing_failed'];
    }

    return ['success' => true, 'filename' => $newFilename, 'error' => null];
}
