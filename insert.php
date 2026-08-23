<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'error' => 'POST required.'], 405);
}

$url   = trim((string) ($_POST['url'] ?? ''));
$alias = trim((string) ($_POST['alias'] ?? ''));

if ($url === '') {
    json_response(['success' => false, 'error' => 'Please enter a URL.'], 422);
}

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    json_response(['success' => false, 'error' => 'That does not look like a valid URL.'], 422);
}

$scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
if (!in_array($scheme, ['http', 'https'], true)) {
    json_response(['success' => false, 'error' => 'Only http:// and https:// links are supported.'], 422);
}

if (strlen($url) > 2048) {
    json_response(['success' => false, 'error' => 'URL is too long (max 2048 characters).'], 422);
}

// Paths that the app itself uses — these can never be a short code.
$reserved = ['r', 'qr', 'insert', 'index', 'assets', 'qrcodes', 'api', 'admin'];

if ($alias !== '') {
    if (!preg_match(CODE_PATTERN, $alias)) {
        json_response(['success' => false, 'error' => 'Alias may only use letters, numbers, hyphens and underscores (max 10 chars).'], 422);
    }
    if (in_array(strtolower($alias), $reserved, true)) {
        json_response(['success' => false, 'error' => 'That alias is reserved.'], 422);
    }
    $code = $alias;
} else {
    $code = null;
}

try {
    $pdo = db();

    if ($code !== null) {
        // Custom alias: enforce uniqueness.
        $stmt = $pdo->prepare('SELECT id FROM links WHERE short_code = ? LIMIT 1');
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            json_response(['success' => false, 'error' => 'That custom alias is already taken.'], 409);
        }
    } else {
        // Auto-generate a random unique code.
        $code = generate_code();
    }

    $stmt = $pdo->prepare('INSERT INTO links (short_code, original_url) VALUES (?, ?)');
    $stmt->execute([$code, $url]);
} catch (\PDOException $e) {
    if ((string) $e->getCode() === '23000') {
        if ($alias !== '') {
            json_response(['success' => false, 'error' => 'That custom alias is already taken.'], 409);
        }
        // Auto-generated collision (astronomically unlikely) — retry once.
        try {
            $code = generate_code();
            $stmt = db()->prepare('INSERT INTO links (short_code, original_url) VALUES (?, ?)');
            $stmt->execute([$code, $url]);
        } catch (\PDOException $e2) {
            json_response(['success' => false, 'error' => 'Could not generate a unique code. Please try again.'], 500);
        }
    } else {
        json_response(['success' => false, 'error' => 'Database error. Please try again later.'], 500);
    }
}

json_response([
    'success'   => true,
    'code'      => $code,
    'short_url' => BASE_URL . '/' . $code,
    'qr_url'    => BASE_URL . '/qr.php?c=' . rawurlencode($code),
]);
