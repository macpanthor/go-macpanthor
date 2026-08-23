# go.macpanthor.com — URL Shortener & QR Code Generator

A self-hosted URL shortener and QR code generator for the `go.macpanthor.com`
subdomain.

Built with **PHP 8+** and **MySQL**, served behind **Apache**, with a dark
theme matching [macpanthor.com](https://macpanthor.com).
No third-party or ad-supported services — everything runs on your own host.

> **Live:** https://go.macpanthor.com
> **Repository:** [macpanthor/go-macpanthor](https://github.com/macpanthor/go-macpanthor)

## Features

- **Short links** — auto-generated 6-character alphanumeric codes or custom aliases (uniqueness-checked against the database)
- **Clean redirects** — `https://go.macpanthor.com/{code}` with no visible query string
- **Styled 404 page** for unknown codes
- **QR codes** — PNG, cyan-on-dark, 300 px, with disk caching
- **Click tracking** — atomic click counter per link
- **Secure by default** — PDO prepared statements, strict input validation, and reserved-alias protection

## Tech stack

| Layer      | Technology                                            |
| ---------- | ----------------------------------------------------- |
| Language   | PHP 8.0+                                              |
| Database   | MySQL 8+ (PDO, `pdo_mysql`)                           |
| Web server | Apache with `mod_rewrite`                             |
| QR library | `endroid/qr-code` (Composer) + `phpqrcode` fallback   |
| Frontend   | Vanilla HTML/CSS/JS (no frameworks, no CDNs)          |

## Requirements

- PHP 8.0+ with the `pdo_mysql` extension
- MySQL 8+
- Apache with `mod_rewrite` enabled
- Composer (recommended) — or the `phpqrcode` fallback if Composer isn't available
- PHP `gd` extension (only required for the `phpqrcode` fallback)

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/macpanthor/go-macpanthor.git go-macpanthor
cd go-macpanthor
```

### 2. Create the database

```bash
mysql -u root -p < schema.sql
```

This creates the `macpanthor_go` database and the `links` table.

### 3. Configure the application

Edit `config.php` and set your credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'macpanthor_go');
define('DB_USER', 'root');
define('DB_PASS', 'your-password');
define('BASE_URL', 'https://go.macpanthor.com'); // no trailing slash
```

### 4. Install the QR library

**Option A — Composer (recommended):**

```bash
composer install
```

**Option B — no Composer on the host:**

Copy the `phpqrcode` PHP files into `lib/phpqrcode/` so that
`lib/phpqrcode/qrlib.php` exists. See `lib/phpqrcode/README.md` for details.
`qr.php` detects it automatically.

### 5. Deploy

Point the document root of `go.macpanthor.com` at this folder (or copy the
files to it). Make sure `qrcodes/` is writable by PHP so QR disk caching works.

## Usage

| Action             | URL                                        |
| ------------------ | ------------------------------------------ |
| Shorten a URL      | `https://go.macpanthor.com/`               |
| Open a short link  | `https://go.macpanthor.com/{code}`         |
| Download a QR code | `https://go.macpanthor.com/qr.php?c={code}` |

## API reference

### Create a short link

```
POST /insert.php
Content-Type: application/x-www-form-urlencoded
```

| Field   | Required | Description                       |
| ------- | -------- | --------------------------------- |
| `url`   | Yes      | Long URL (`http`/`https`)         |
| `alias` | No       | Custom alias (`A-Za-z0-9_-`, ≤10) |

**Success response**

```json
{
  "success": true,
  "code": "aB3xY9",
  "short_url": "https://go.macpanthor.com/aB3xY9",
  "qr_url": "https://go.macpanthor.com/qr.php?c=aB3xY9"
}
```

**Error response**

```json
{ "success": false, "error": "..." }
```

| Status | Meaning                    |
| ------ | -------------------------- |
| `405`  | Non-POST request           |
| `409`  | Custom alias already taken |
| `422`  | Invalid input              |
| `500`  | Server/database error      |

## Project structure

```
go-macpanthor/
├── .htaccess               # mod_rewrite rules + branded 404
├── index.php               # Frontend (form, result, QR preview)
├── insert.php              # API: create a short link (POST)
├── r.php                   # Redirect handler (lookup + click counter)
├── qr.php                  # QR PNG generator
├── config.php              # Database credentials + app settings
├── schema.sql              # MySQL database schema
├── composer.json           # endroid/qr-code dependency
├── assets/
│   ├── logo.webp / logo.png   # <SA /> logo (webp + png fallback)
│   ├── favicon.ico            # Favicon (+ 16/32/180/192/512 px PNGs)
│   ├── site.webmanifest       # PWA manifest
│   ├── style.css              # Dark/cyan theme
│   └── script.js              # Form submit + copy-to-clipboard
├── lib/phpqrcode/
│   └── README.md           # Optional fallback library instructions
└── qrcodes/                # Cached QR PNGs (created automatically)
```

## Database schema

| Column         | Type                     | Notes         |
| -------------- | ------------------------ | ------------- |
| `id`           | `INT UNSIGNED` PK, AI    |               |
| `short_code`   | `VARCHAR(10)` UNIQUE     |               |
| `original_url` | `TEXT`                   |               |
| `clicks`       | `INT UNSIGNED` DEFAULT 0 |               |
| `created_at`   | `TIMESTAMP` DEFAULT now  |               |

## Security

- All queries use PDO prepared statements.
- Short codes and aliases are validated against a strict whitelist regex.
- Reserved words (`r`, `qr`, `insert`, `index`, `assets`, …) can't be used as aliases, so app routes are never shadowed.
- Output is escaped (`htmlspecialchars` / `json_encode`) on redirect pages.
- The click counter uses an atomic `UPDATE ... SET clicks = clicks + 1`.

## License

No license is currently specified. Add one before making the repository public.
