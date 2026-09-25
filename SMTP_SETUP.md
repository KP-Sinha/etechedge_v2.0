# Standalone SMTP Setup & Testing Guide for EtechEdge

This guide explains how to configure, test, and deploy the prepared standalone SMTP email functionality for future server-side use.

---

## 1. Important Architecture & Security Notice

- **NOT CONNECTED TO THE CURRENT WEBSITE:**  
  The current website is a static GitHub Pages website. None of the HTML pages (`index.html`, `contact.html`, etc.) or client scripts (`script.js`) connect to or reference any PHP files.
- **ZERO CLIENT-SIDE EXPOSURE:**  
  SMTP credentials never enter HTML, JavaScript, browser network calls, or public GitHub pages.
- **DUMMY CREDENTIALS ONLY IN REPO:**  
  The files in this repository use clearly marked dummy placeholder values (`smtp.example.com`).
- **NEVER COMMIT REAL CREDENTIALS:**  
  Real passwords must reside exclusively in a server-side `.env` file or environment variables on your PHP host. `.env` is already protected in `.gitignore`.
- **DIFFERENT SENDER & RECIPIENT ACCOUNTS:**  
  The SMTP authentication account (`SMTP_USERNAME` / `MAIL_FROM_ADDRESS`) and the destination inbox (`MAIL_TO_ADDRESS = techgrowza@gmail.com`) can be completely different accounts.

---

## 2. File Overview & Locations

All standalone SMTP files are located in the project root:

| File | Purpose | Connected to Website? |
|---|---|---|
| `smtp_config.php` | Server-side configuration array loading from environment variables or dummy fallback defaults | **NO** |
| `smtp_mailer.php` | Standalone email delivery function using **PHPMailer** | **NO** |
| `smtp_test.php` | Independent browser-accessible diagnostic test page for XAMPP / local testing | **NO** |
| `.env.example` | Template demonstrating environment variable keys with dummy values | **NO** |
| `.gitignore` | Prevents `.env`, `vendor/`, and local server log files from being committed | N/A |

---

## 3. Installing PHPMailer via Composer

When deploying to a PHP server or local XAMPP, install PHPMailer via Composer inside the project root:

```bash
composer require phpmailer/phpmailer
```

This generates `vendor/autoload.php`, which `smtp_mailer.php` automatically detects and loads.

> **Note:** The current GitHub Pages website does NOT depend on Composer or `vendor/`.

---

## 4. Gmail SMTP Support Details

If using Gmail SMTP to send inquiries:

- **SMTP Host:** `smtp.gmail.com`
- **SMTP Port:** `587`
- **Encryption:** `tls` (STARTTLS)
- **SMTP Username:** The Gmail account used for authentication (e.g. `your-company@gmail.com`)
- **SMTP Password:** A Google **App Password** (16 characters, generated in your Google Account under Security -> 2-Step Verification -> App passwords). *Never use your personal account password.*
- **Sender (From):** Must match the authenticated Gmail account.
- **Recipient (To):** `techgrowza@gmail.com` (or any other receiving inbox).
- **Reply-To:** The website visitor's email address (so clicking Reply in your inbox responds to the visitor).

---

## 5. Local Testing with XAMPP

1. **Copy/Link Project into XAMPP**:
   Place the project folder into your XAMPP web root (typically `C:\xampp\htdocs\etechedge`).
2. **Install PHPMailer in XAMPP**:
   Inside `C:\xampp\htdocs\etechedge`, run:
   ```bash
   composer require phpmailer/phpmailer
   ```
3. **Create Local `.env`**:
   Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
4. **Add Real SMTP Credentials to `.env`**:
   ```ini
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USERNAME=your-auth-account@gmail.com
   SMTP_PASSWORD=your-16-char-app-password
   SMTP_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your-auth-account@gmail.com
   MAIL_FROM_NAME=EtechEdge Solutions
   MAIL_TO_ADDRESS=techgrowza@gmail.com
   MAIL_TO_NAME=EtechEdge Inquiries
   ```
5. **Run XAMPP Apache**:
   Start Apache in the XAMPP Control Panel.
6. **Open the Test Utility**:
   Navigate in your browser to:
   ```
   http://localhost/etechedge/smtp_test.php
   ```
7. **Execute Diagnostic Test**:
   - The test page displays your active server configuration with the password securely masked.
   - Enter your test recipient and simulated visitor email, then click **"Execute SMTP Diagnostic Test"**.
   - With dummy credentials, it displays an informative failure notice.
   - With valid SMTP credentials, it delivers a test email directly to `techgrowza@gmail.com`.

---

## 6. Live PHP Server Deployment

When moving to a PHP-compatible live hosting environment (cPanel, AWS EC2, DigitalOcean, VPS, etc.):

1. Upload `smtp_config.php`, `smtp_mailer.php`, and the `vendor/` directory to your server.
2. Configure your environment variables via your host's control panel or a protected `.env` file.
3. Replace dummy values with your production credentials:
   - **Host:** `smtp.gmail.com` or custom SMTP host
   - **Port:** `587` (TLS) or `465` (SSL)
   - **Username:** Authenticated account username
   - **Password:** App Password or mailbox password
   - **To:** `techgrowza@gmail.com`
4. Test delivery using `smtp_test.php`, and remove or restrict access to `smtp_test.php` once verified in production.
