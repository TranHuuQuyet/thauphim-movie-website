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
```

## Deploy checklist

1. Create a MySQL database and user on hosting.
2. Copy `includes/config.example.php` to `includes/config.php`.
3. Set database credentials, `TMDB_API_KEY`, and `APP_BASE_PATH`.
4. Import `database/schema.sql`.
5. Import `database/seed.sql`.
6. If hosting allows PHP CLI and outbound network, run `php tools/import_tmdb.php`.
7. If hosting blocks CLI/network, import data locally and upload an exported SQL dump.
8. Login with the seeded admin account, then change the admin password.
9. Open `/admin/dashboard.php` and verify unauthenticated users are redirected.
10. Add/publish at least one episode with a valid YouTube URL.
11. Test `/api/movies.php`, `/api/movie-detail.php?id=1`, and `/api/episodes.php?movie_id=1`.

## Default admin

```text
Username: admin
Email: admin@thauphim.local
Password: admin123
```

Change this password after deployment.
