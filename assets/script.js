(function () {
  'use strict';

  var form = document.getElementById('shorten-form');
  var urlInput = document.getElementById('url');
  var aliasInput = document.getElementById('alias');
  var submitBtn = document.getElementById('submit-btn');
  var resultBox = document.getElementById('result');
  var shortUrlInput = document.getElementById('short-url');
  var qrImg = document.getElementById('qr-img');
  var copyBtn = document.getElementById('copy-btn');
  var copyStatus = document.getElementById('copy-status');
  var errorBox = document.getElementById('error');

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    hide(resultBox);
    hide(errorBox);
    submitBtn.disabled = true;
    submitBtn.textContent = 'Shortening…';

    var body = new URLSearchParams();
    body.set('url', urlInput.value.trim());
    body.set('alias', aliasInput.value.trim());

    fetch('insert.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString()
    })
      .then(function (res) {
        return res.json().catch(function () {
          return { success: false, error: 'Unexpected server response.' };
        }).then(function (data) {
          if (!res.ok || !data.success) {
            showError(data.error || 'Something went wrong.');
            return;
          }
          shortUrlInput.value = data.short_url;
          qrImg.src = 'qr.php?c=' + encodeURIComponent(data.code);
          copyStatus.textContent = '';
          show(resultBox);
        });
      })
      .catch(function () {
        showError('Network error — please try again.');
      })
      .finally(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Shorten URL';
      });
  });

  copyBtn.addEventListener('click', function () {
    var value = shortUrlInput.value;
    if (!value) return;

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(value).then(function () {
        copyStatus.textContent = 'Copied!';
      }).catch(fallbackCopy);
    } else {
      fallbackCopy();
    }

    function fallbackCopy() {
      shortUrlInput.select();
      shortUrlInput.setSelectionRange(0, 99999);
      try {
        document.execCommand('copy');
        copyStatus.textContent = 'Copied!';
      } catch (e) {
        copyStatus.textContent = 'Press Ctrl+C to copy';
      }
    }
  });

  function show(el) { el.classList.remove('hidden'); }
  function hide(el) { el.classList.add('hidden'); }
  function showError(msg) {
    errorBox.textContent = msg;
    show(errorBox);
  }
})();
