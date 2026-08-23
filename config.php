<?php
/**
 * go.macpanthor.com — URL shortener configuration.
 * Edit the values below to match your hosting environment.
 */
declare(strict_types=1);

// ---------------------------------------------------------------------------
// Database
// ---------------------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'macpanthor_go');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---------------------------------------------------------------------------
// App
// ---------------------------------------------------------------------------
define('BASE_URL', 'https://go.macpanthor.com'); // no trailing slash

// Characters used for auto-generated codes (alphanumeric).
define('CODE_CHARSET', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
define('CODE_LENGTH', 6);

// Alias validation — allowed characters and max length (must match the DB column).
define('CODE_PATTERN', '/^[A-Za-z0-9_-]{1,10}$/');

// Optional disk caching for generated QR PNGs (recommended).
define('QR_CACHE_ENABLED', true);
define('QR_CACHE_DIR', __DIR__ . '/qrcodes');

// ---------------------------------------------------------------------------
// PDO connection (created lazily)
// ---------------------------------------------------------------------------
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    return $pdo;
}

/**
 * Generate a random, unique short code.
 */
function generate_code(): string
{
    $chars = CODE_CHARSET;
    $max   = strlen($chars) - 1;
    $pdo   = db();

    do {
        $code = '';
        for ($i = 0; $i < CODE_LENGTH; $i++) {
            $code .= $chars[random_int(0, $max)];
        }

        $stmt   = $pdo->prepare('SELECT id FROM links WHERE short_code = ? LIMIT 1');
        $stmt->execute([$code]);
        $exists = (bool) $stmt->fetch();
    } while ($exists);

    return $code;
}

/**
 * Emit a JSON response and stop.
 */
function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}
