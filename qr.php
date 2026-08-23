<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$code = (string) ($_GET['c'] ?? '');

if ($code === '' || !preg_match(CODE_PATTERN, $code)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Invalid or missing short code.';
    exit;
}

// The QR always encodes the public short link.
$target = BASE_URL . '/' . $code;

// Optional disk cache: serve an existing PNG without regenerating it.
$cacheFile = null;
if (QR_CACHE_ENABLED) {
    if (!is_dir(QR_CACHE_DIR)) {
        @mkdir(QR_CACHE_DIR, 0755, true);
    }
    $cacheFile = QR_CACHE_DIR . '/' . $code . '.png';
    if (is_file($cacheFile)) {
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Content-Length: ' . (string) filesize($cacheFile));
        readfile($cacheFile);
        exit;
    }
}

$png = null;

$autoload = __DIR__ . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

if (class_exists('Endroid\QrCode\Builder\Builder')) {
    // Primary: endroid/qr-code via Composer (fully self-hosted).
    $result = \Endroid\QrCode\Builder\Builder::create()
        ->writer(new \Endroid\QrCode\Writer\PngWriter())
        ->data($target)
        ->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
        ->size(300)
        ->margin(10)
        ->foregroundColor(new \Endroid\QrCode\Color\Color(0, 229, 255))
        ->backgroundColor(new \Endroid\QrCode\Color\Color(13, 13, 13))
        ->validateResult(false)
        ->build();

    $png = $result->getString();
} elseif (is_file(__DIR__ . '/lib/phpqrcode/qrlib.php')) {
    // Fallback: single-file phpqrcode library (requires PHP GD).
    require_once __DIR__ . '/lib/phpqrcode/qrlib.php';
    ob_start();
    QRcode::png($target, false, QR_ECLEVEL_M, 10, 2, false, [13, 13, 13], [0, 229, 255]);
    $png = (string) ob_get_clean();
} else {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'QR library not installed. Run "composer install", or drop phpqrcode into /lib/phpqrcode/.';
    exit;
}

// Save to disk cache (optional).
if ($cacheFile !== null && is_string($png) && $png !== '') {
    @file_put_contents($cacheFile, $png);
}

header('Content-Type: image/png');
header('Cache-Control: public, max-age=31536000, immutable');
header('Content-Length: ' . (string) strlen($png));
echo $png;
