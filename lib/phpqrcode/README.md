# phpqrcode fallback (only needed if Composer isn't available)

`qr.php` automatically uses **endroid/qr-code** whenever `vendor/autoload.php`
exists. If you cannot run Composer on the host, drop the single-file
**phpqrcode** library here instead.

## Install

1. Download phpqrcode:
   - https://github.com/t0k4rt/phpqrcode
   - or the original sourceforge mirror (LGPL)
2. Copy **all** `.php` files from that library into this folder so that
   `lib/phpqrcode/qrlib.php` exists.
3. Make sure the PHP **GD** extension is enabled — phpqrcode requires it.

## Expected result

```
lib/phpqrcode/
├── qrlib.php        <- required entry point
├── qrconst.php
├── qrconfig.php
├── qrtools.php
├── qrinput.php
├── qrbitstream.php
├── qrsplit.php
├── qrrscode.php
├── qrmask.php
├── qrencode.php
└── qrimage.php
```

No other changes are needed — `qr.php` detects the library automatically.
