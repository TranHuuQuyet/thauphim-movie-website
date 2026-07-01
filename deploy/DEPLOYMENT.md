# ThauPhim Deployment

This file covers Task 5 deployment setup for PHP/MySQL hosting.

## Required hosting

- PHP 8.0 or newer with PDO MySQL enabled.
- MySQL or MariaDB.
- Apache with `mod_rewrite` optional, or any hosting that can serve PHP files.
- HTTPS enabled for the production domain.

## Domain/base path

Set `APP_BASE_PATH` in `includes/config.php`.

- Domain root example: `define("APP_BASE_PATH", "/");`
- Subfolder example: `define("APP_BASE_PATH", "/movie-site/");`

The local XAMPP default in this repository is:

```php
define("APP_BASE_PATH", "/");
define("APP_URL", "https://fnbstore.store/");
define("MAIL_DRIVER", "smtp");
define("MAIL_FROM", "thauphim@fnbstore.store");
define("SMTP_HOST", "smtp.fnbstore.store");
define("SMTP_PORT", 465);
define("SMTP_ENCRYPTION", "ssl");
define("SMTP_USERNAME", "thauphim@fnbstore.store");
define("SMTP_PASSWORD", "replace-with-smtp-password");
```

Do not commit the real SMTP password. Put it in the hosting environment as `SMTP_PASSWORD`, or create `includes/config.local.php` on the server:

```php
<?php
define("SMTP_PASSWORD", "your-real-smtp-password");
```

`MAIL_DRIVER=smtp` requires PHPMailer to be available through `vendor/autoload.php`. Install it with:

```powershell
composer install --no-dev
```

If PHPMailer is not installed and the hosting supports native PHP mail, set `MAIL_DRIVER` to `mail`.

## Deploy checklist

1. Create a MySQL database and user on hosting.
2. Copy `includes/config.example.php` to `includes/config.php`.
3. Set database credentials, `TMDB_API_KEY`, `APP_BASE_PATH`, `APP_URL`, and SMTP credentials.
4. Import `database/schema.sql`.
5. Import `database/seed.sql`.
6. For an existing database, import `database/password_reset_upgrade.sql` to add forgot-password support.
7. Run `composer install --no-dev` so SMTP email can load PHPMailer.
8. If hosting allows PHP CLI and outbound network, run `php tools/import_tmdb.php`.
9. If hosting blocks CLI/network, import data locally and upload an exported SQL dump.
10. Login with the seeded admin account, then change the admin password.
11. Open `/admin/dashboard.php` and verify unauthenticated users are redirected.
12. Add/publish at least one episode with a valid YouTube URL.
13. Test `/api/movies.php`, `/api/movie-detail.php?id=1`, `/api/episodes.php?movie_id=1`, and `/forgot-password.php`.

To diagnose password-reset email setup without sending an email:

```powershell
php tools/diagnose_password_reset_mail.php user@example.com
```

## Default admin

```text
Username: admin
Email: admin@thauphim.local
Password: admin123
```

Change this password after deployment.
