<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$code = (string) ($_GET['c'] ?? '');

if ($code === '' || !preg_match(CODE_PATTERN, $code)) {
    render_404();
}

$stmt = db()->prepare('SELECT original_url FROM links WHERE short_code = ? LIMIT 1');
$stmt->execute([$code]);
$row = $stmt->fetch();

if ($row === false) {
    render_404();
}

$originalUrl = (string) $row['original_url'];

// Increment the click counter.
$stmt = db()->prepare('UPDATE links SET clicks = clicks + 1 WHERE short_code = ?');
$stmt->execute([$code]);

render_interstitial($originalUrl);

// ---------------------------------------------------------------------------
// Shared page styling (dark + cyan, matching macpanthor.com)
// ---------------------------------------------------------------------------
function theme_css(): string
{
    return <<<'CSS'
:root { --bg:#0d0d0d; --fg:#00e5ff; --text:#e6e6e6; --muted:#8a8a8a; }
* { margin:0; padding:0; box-sizing:border-box; }
body {
  background:var(--bg); color:var(--text);
  font-family:'Segoe UI', system-ui, -apple-system, 'Helvetica Neue', Arial, sans-serif;
  min-height:100vh; display:flex; align-items:center; justify-content:center;
  background-image: radial-gradient(circle at 50% 30%, rgba(0,229,255,0.08), transparent 55%);
}
.wrap { text-align:center; padding:2rem; max-width:720px; }
.logo { width:200px; height:auto; }
.pulse { animation:pulse 1.2s ease-in-out infinite; }
@keyframes pulse {
  0%,100% { transform:scale(1);    opacity:1; }
  50%     { transform:scale(1.12); opacity:0.65; }
}
.title { margin-top:1.75rem; font-size:1.3rem; font-weight:600; }
.msg { margin-top:0.6rem; color:var(--muted); font-size:0.95rem; }
.dest {
  display:inline-block; max-width:80vw; color:var(--fg);
  overflow:hidden; text-overflow:ellipsis; white-space:nowrap; vertical-align:bottom;
}
.sub { margin-top:1.25rem; font-size:0.85rem; color:var(--muted); }
.sub a { color:var(--fg); text-decoration:none; }
.sub a:hover { text-decoration:underline; }
.code404 { font-size:4.5rem; font-weight:700; color:var(--fg); letter-spacing:0.05em; }
CSS;
}

/**
 * Interstitial page: shows the logo with a pulsing animation, then redirects
 * with JS after 1200ms. A <noscript> meta-refresh covers users without JS.
 */
function render_interstitial(string $url): void
{
    $urlAttr = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $urlJs   = json_encode($url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $host    = (string) parse_url($url, PHP_URL_HOST);
    $host    = htmlspecialchars($host !== '' ? $host : $url, ENT_QUOTES, 'UTF-8');
    $css     = theme_css();

    header('Content-Type: text/html; charset=utf-8');

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<meta name="theme-color" content="#0d0d0d">
<link rel="icon" href="/assets/favicon.ico" sizes="any">
<title>Redirecting… · go.macpanthor.com</title>
<noscript><meta http-equiv="refresh" content="2;url={$urlAttr}"></noscript>
<style>{$css}</style>
</head>
<body>
  <div class="wrap">
    <img class="logo pulse" src="/assets/logo.webp" alt="&lt;SA /&gt;">
    <p class="title">Taking you there…</p>
    <p class="msg">Redirecting to <span class="dest" title="{$urlAttr}">{$host}</span></p>
    <p class="sub">If nothing happens, <a href="{$urlAttr}">click here</a>.</p>
  </div>
  <script>
    setTimeout(function () {
      window.location.replace({$urlJs});
    }, 1200);
  </script>
</body>
</html>
HTML;
    exit;
}

/**
 * Styled 404 page for unknown short codes.
 */
function render_404(): void
{
    http_response_code(404);
    $css = theme_css();

    header('Content-Type: text/html; charset=utf-8');

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<meta name="theme-color" content="#0d0d0d">
<link rel="icon" href="/assets/favicon.ico" sizes="any">
<title>404 · Link not found · go.macpanthor.com</title>
<style>{$css}</style>
</head>
<body>
  <div class="wrap">
    <img class="logo" src="/assets/logo.webp" alt="&lt;SA /&gt;">
    <p class="code404">404</p>
    <p class="title">Link not found</p>
    <p class="msg">This short link doesn't exist or may have been removed.</p>
    <p class="sub"><a href="/">Create a new short link</a></p>
  </div>
</body>
</html>
HTML;
    exit;
}
