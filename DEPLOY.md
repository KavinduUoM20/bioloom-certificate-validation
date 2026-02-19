# How to ship / deploy – Bioloom Certificate System

Easiest way: **create a zip on your PC, upload it to the server, unzip, then configure.**

---

## Step 1: Create the zip (on your computer)

1. **Install dependencies** so they’re included in the zip (no need to run Composer on the server):
   ```bash
   composer install
   ```

2. **Create the deployment zip:**
   ```bash
   php create-deploy-zip.php
   ```
   This creates **`bioloom-cert-verification.zip`** in the project folder, excluding `.git`, `.cursor`, logs, and a few other dev-only items. **Vendor is included** so the server doesn’t need Composer.

3. Optional: add your **template** and **config** if you use local overrides:
   - Ensure `template/certificate-template.png` (or `.jpg`) is present if you use a custom template.
   - If you want the server to use your mail/certificate config by default, make sure `config/mail.php` and `config/certificate.php` are set as you want (they are **not** in .gitignore for certificate; mail.php is). The zip script does **not** exclude these, so they will be in the zip. If you prefer not to ship real mail credentials, delete or empty `config/mail.php` before running the script and recreate it on the server.

---

## Step 2: Upload to the server

- **cPanel / Plesk:** Use **File Manager** → Upload `bioloom-cert-verification.zip` (e.g. into `public_html` or a subfolder like `public_html/certs`).
- **FTP/SFTP:** Upload the zip with FileZilla, WinSCP, or any FTP client to the folder that will be your site root (e.g. `public_html` or `htdocs/certs`).

---

## Step 3: Unzip on the server

- **cPanel:** File Manager → right‑click the zip → **Extract**.
- **SSH:**  
  ```bash
  cd /path/to/your/web/root
  unzip bioloom-cert-verification.zip
  ```
  If you uploaded into an empty folder, you may need to move contents up one level (e.g. `mv bioloom-cert-verification/* . && rmdir bioloom-cert-verification` if the zip had a top-level folder).

---

## Step 4: Configure

1. **Database**  
   - Create a MySQL database and user (e.g. via cPanel → MySQL® Databases).  
   - Import the schema:  
     - cPanel: phpMyAdmin → select the database → Import → choose `sql/schema.sql`.  
     - Or SSH: `mysql -u user -p database_name < sql/schema.sql`  
   - Edit **`config/db.php`** with the correct host, database name, user, and password (or set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` if you use env vars).

2. **Mail (optional)**  
   - Edit **`config/mail.php`** with your SMTP details so certificate emails are sent. If the file isn’t there, copy from `config/mail.example.php`.

3. **Certificate layout (optional)**  
   - If you use a custom template, place **`template/certificate-template.png`** (or `.jpg`) and adjust **`config/certificate.php`** (name area, font size) as needed.

---

## Step 5: Point the web server at the project

- **Subfolder (e.g. yourdomain.com/certs):**  
  No extra config if you unzipped into `public_html/certs`. Use:  
  `https://yourdomain.com/certs/register.php`  
  and certificate links will be like:  
  `https://yourdomain.com/certs/view-certificate.php?token=...`

- **Subdomain or main domain:**  
  Set the document root to the folder that contains `register.php`, `view-certificate.php`, etc. (e.g. in cPanel: Domains → document root, or in Apache/Nginx vhost).

Use **HTTPS** in production so certificate links and tokens are sent securely.

---

## Checklist

- [ ] Run `composer install` then `php create-deploy-zip.php`
- [ ] Upload `bioloom-cert-verification.zip` to the server
- [ ] Unzip in the desired web root
- [ ] Create MySQL database and import `sql/schema.sql`
- [ ] Edit `config/db.php` (or env) with DB credentials
- [ ] Edit `config/mail.php` for SMTP (optional)
- [ ] Add `template/certificate-template.png` and adjust `config/certificate.php` if needed
- [ ] Open `register.php` in the browser and test

After that, the app is shipped and ready to use.
