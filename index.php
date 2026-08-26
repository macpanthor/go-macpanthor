<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#0d0d0d">
  <title>go.macpanthor.com — URL Shortener</title>
  <link rel="icon" href="/assets/favicon.ico" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
  <link rel="manifest" href="/assets/site.webmanifest">
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <main class="container">
    <picture>
      <source srcset="/assets/logo.webp" type="image/webp">
      <img src="/assets/logo.png" alt="&lt;SA /&gt;" class="brand">
    </picture>

    <h1>URL Shortener</h1>
    <p class="tagline">Self-hosted short links &amp; QR codes for macpanthor.com</p>

    <form id="shorten-form" class="card" autocomplete="off">
      <label for="url">Long URL</label>
      <input type="url" id="url" name="url" placeholder="https://macpanthor.com/…" required>

      <label for="alias">Custom alias <span class="muted">(optional)</span></label>
      <input type="text" id="alias" name="alias" placeholder="e.g. launch"
             maxlength="10" pattern="[A-Za-z0-9_-]*">

      <button type="submit" id="submit-btn">Shorten URL</button>
    </form>

    <div id="error" class="card error hidden" role="alert"></div>

    <div id="result" class="card hidden">
      <label for="short-url">Your short link</label>
      <div class="short-url-row">
        <input type="text" id="short-url" readonly>
        <button type="button" id="copy-btn">Copy</button>
      </div>
      <span id="copy-status" class="muted"></span>

      <label>QR code</label>
      <img id="qr-img" src="" alt="QR code" class="qr">
      <button type="button" id="download-btn" class="download-btn" disabled>Download QR code</button>
    </div>
  </main>

  <footer class="footer">
    <span>&lt;SA /&gt; &middot; macpanthor.com</span>
  </footer>

  <script src="/assets/script.js"></script>
</body>
</html>
