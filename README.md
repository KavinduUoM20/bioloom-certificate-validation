# Bioloom Islands Pvt Ltd – Certificate & Badge Issuing

PHP system to register recipients, store them in MySQL, and issue certificate links by email. Recipients open a token-based link to view and download their certificate as a PDF.

## Features

- **Single frontend page** (`register.php`): Form to add recipients (first name, last name, email, completion status) and list recent entries.
- **MySQL storage**: Table `certificate_recipients` with UUID, names, email, `completion_status`, `cert_issued`, token, dates.
- **Token-based links**: On insert, if `completion_status = 1`, a unique token is generated and stored; **no certificate data is sent in the URL**.
- **Email**: Sends one email with a “View certificate” link (e.g. `https://yoursite.com/view-certificate.php?token=...`).
- **Certificate view** (`view-certificate.php`): Verifies token in the backend, generates a PDF with the recipient’s name on your template, and outputs it **inline** (opens in a new tab; user can download).

## Requirements

- PHP 7.4+
- MySQL 5.7+ / MariaDB
- Composer (for dependencies)

## Setup

1. **Clone/copy** the project to your web root or a subfolder.

2. **Database**
   - Create DB and table:
     ```bash
     mysql -u root -p < sql/schema.sql
     ```
   - Or run the contents of `sql/schema.sql` in phpMyAdmin / your MySQL client.
   - Edit `config/db.php` with your MySQL credentials, or set env vars: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.

3. **SMTP (sending certificate links by email)**
   - **Where:** `config/mail.php` (copy from `config/mail.example.php` if you don’t have it).
   - **Steps:**
     1. Copy `config/mail.example.php` to `config/mail.php`.
     2. Open `config/mail.php` and set:
        - `enabled` → `true`
        - `host` → your SMTP server (e.g. `smtp.gmail.com`, `smtp.office365.com`)
        - `port` → usually `587` (TLS) or `465` (SSL)
        - `secure` → `'tls'` or `'ssl'`
        - `username` / `password` → your SMTP login
        - `from_email` / `from_name` → sender address and name
   - If `config/mail.php` doesn’t exist or `enabled` is false, the app falls back to PHP `mail()` (or env vars `SMTP_HOST`, `SMTP_USER`, `SMTP_PASS`, etc.).

4. **Dependencies**
   ```bash
   composer install
   ```
   This installs **TCPDF** (PDF generation) and **PHPMailer** (sending the certificate link email).

5. **Certificate template**
   - Add your certificate design as an **image** in `template/`:
     - **Filename:** `certificate-template.png` or `certificate-template.jpg`
   - The script uses it as a full-page background and draws the **recipient name in the centre** (see `template/README.md` for size and position tips).
   - If you only have a PDF template, you can later add FPDI and load that PDF in `includes/CertificatePdf.php` instead.

## How to run the app

- **Option A – PHP built-in server (quick local test)**  
  From the project folder:
  ```bash
  composer install
  php -S localhost:8080
  ```
  Then open: **http://localhost:8080/register.php**

- **Option B – XAMPP / WAMP / Laragon**  
  1. Put the project folder inside `htdocs` (XAMPP) or `www` (WAMP/Laragon).  
  2. Create the database and run `sql/schema.sql`.  
  3. Edit `config/db.php` (and `config/mail.php` for SMTP).  
  4. Run `composer install` in the project folder.  
  5. Open: **http://localhost/bioloom-cert-verification/register.php** (use your actual folder name).

- **Option C – Production (Apache/Nginx)**  
  Point the document root to this project (or a subfolder) and ensure PHP runs. Use **HTTPS** in production.

## Flow

1. **Register:** Open `register.php` → fill first name, last name, email → check “Completed” if they should receive the certificate link → submit.
2. **Backend:** Insert row (with new UUID and, if completed, a token). If completed, send email with link `view-certificate.php?token=...`.
3. **Recipient:** Clicks link → `view-certificate.php` checks token in DB, loads recipient name, generates PDF (template + name) and sends it with `Content-Disposition: inline` so it opens in a new tab and can be downloaded.

## Files

| File / folder           | Purpose |
|-------------------------|--------|
| `config/db.php`         | DB connection (PDO) |
| `includes/functions.php`| UUID/token helpers, `base_url()` |
| `includes/CertificatePdf.php` | PDF generation (TCPDF + template image + name) |
| `register.php`          | Frontend form + recent recipients list |
| `process-register.php`  | Insert into DB, send email if completed |
| `view-certificate.php`  | Token verification, PDF output |
| `sql/schema.sql`        | MySQL schema |
| `template/`             | Put `certificate-template.png` (or `.jpg`) here |

## Certificate template (what to use)

- **Image template (current):** Use a PNG or JPG of your certificate. The app uses **TCPDF** to place it as a full-page image and overlay the recipient name in the middle. Easiest and works well for most designs.
- **PDF template (optional):** If your design is only in PDF, use **FPDI** (`setasign/fpdi`) to load that PDF and add the name with TCPDF/FPDF on top. Requires a small change in `CertificatePdf.php` to use FPDI instead of a single image.

## Security notes

- Tokens are long random values stored in the DB; the link does not expose names or IDs.
- Always use **HTTPS** in production so the certificate link and PDF are not sent in the clear.
- Restrict `register.php` (and `process-register.php`) to admins only (e.g. login or IP allowlist) so only authorised staff can add recipients.
