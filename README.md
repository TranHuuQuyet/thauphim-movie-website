# ThauPhim

[![PHP 8+](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL / MariaDB](https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License: Proprietary](https://img.shields.io/badge/License-Proprietary-red.svg)](#license)

## Project Overview

ThauPhim is a server-rendered movie website built with plain PHP, MySQL/MariaDB, HTML, CSS, and JavaScript. It includes a public catalog, YouTube-based episode playback, member interactions, an administration panel, JSON endpoints used by the frontend, password-reset email support, and a CLI importer for TMDB metadata.

TMDB is used only by the CLI importer. Imported metadata is stored in MySQL and served by the PHP application. Playback URLs are managed separately in `episodes.youtube_url`; the importer does not provide or create playable episodes.

## Features

### Public catalog

- Homepage sections for featured, most-viewed, recent, movie, and series content.
- Search, pagination, and filtering by type, genre, country, and release year.
- Sorting by newest, popularity, rating, and view count.
- Movie detail pages with metadata, cast, genres, published episodes, ratings, comments, and related titles.
- Country, genre, actor, movie, and series listing pages.
- Responsive layout and a persisted light/dark theme preference.
- Published upcoming-release notifications in the site header.

### Accounts and playback

- Registration, login, logout, session authentication, and account locking.
- One-time password-reset tokens with a configurable expiry and SMTP or PHP `mail()` delivery.
- Free and premium memberships; premium titles require an active premium account.
- YouTube iframe playback with episode navigation.
- Watch progress and history for signed-in users.
- Favorites, comments, and one rating from 1 to 5 per user and movie.
- Playback issue reports stored as hidden `[Error]` comments for admin review.
- View counting when playback starts, limited to one view per movie in each PHP session.

### Administration

- Role-protected dashboard with catalog, user, episode, view, error, and chart summaries.
- Create, edit, list, and delete movies, episodes, genres, countries, and actors.
- Normalize supported YouTube watch, short, live, and embed URLs before saving episodes.
- Publish/unpublish episodes.
- Manage user membership and account status.
- Hide or delete comments and inspect/delete ratings.
- Create, edit, publish, filter, and delete upcoming release schedules.
- Review playback reports and mark them as open or fixed.
- CSRF protection on admin mutation forms.

### Data tooling

- Destructive full schema for a fresh database.
- Seed script for the default administrator.
- Idempotent upgrade scripts for password reset and upcoming-notification support on older databases.
- CLI importer for up to 100 TMDB titles: 50 movies and 50 TV series, including countries, genres, cast, and relationships.
- CLI diagnostics for password-reset mail configuration.

## Tech Stack

| Area | Technology |
| --- | --- |
| Backend | PHP 8.0+, PDO, PHP sessions |
| Database | MySQL or MariaDB, `utf8mb4` |
| Frontend | HTML5, CSS3, vanilla JavaScript |
| Dependency management | Composer |
| Email | PHPMailer `^7.1` for SMTP, with optional PHP `mail()` fallback |
| Metadata | TMDB API v3 |
| Playback | YouTube iframe and iframe Player API |
| Browser libraries | Swiper 12, Chart.js 4.5.1, Toastify, Font Awesome 6.5.2 |
| Web server | Apache/XAMPP with the project as the web root; the PHP development server also works locally |

Frontend libraries are loaded from CDNs. There is no JavaScript package manager or asset build step.

## Project Architecture

```text
Browser
  ├── server-rendered PHP pages
  └── vanilla JavaScript ──> /api/*.php
                                  │
PHP sessions ──> authentication   │
                                  ▼
                           PDO / MySQL
                                  ▲
TMDB API ──> tools/import_tmdb.php│
YouTube  ──> episode playback
SMTP     <── password-reset mail
```

### Folder Structure

The following structure was generated from the current repository. Composer internals and individual asset files are condensed for readability.

```text
thauphim-movie-website/
├── admin/
│   ├── actors/             # Actor CRUD
│   ├── comments/           # Comment moderation
│   ├── countries/          # Country CRUD
│   ├── episodes/           # Episode CRUD and publishing
│   ├── genres/             # Genre CRUD
│   ├── movies/             # Movie CRUD
│   ├── ratings/            # Rating management
│   ├── schedules/          # Upcoming-release schedules
│   ├── users/              # Membership and status management
│   ├── watch-errors/       # Playback report workflow
│   └── dashboard.php
├── api/
│   ├── _helpers.php
│   ├── actors.php
│   ├── comments.php
│   ├── countries.php
│   ├── episodes.php
│   ├── favorites.php
│   ├── genres.php
│   ├── movie-detail.php
│   ├── movies-by-country.php
│   ├── movies.php
│   ├── notifications.php
│   ├── ratings.php
│   ├── record-view.php
│   └── update-watch-history.php
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── database/
│   ├── notification_upgrade.sql
│   ├── password_reset_upgrade.sql
│   ├── schema.sql
│   └── seed.sql
├── deploy/
│   ├── DEPLOYMENT.md
│   ├── apache-vhost.example.conf
│   └── htaccess.example
├── includes/               # Configuration, database, auth, mail, and shared UI
├── pages/                  # Public catalog, account, detail, and watch pages
├── tmp/                    # Password-reset HTML fixtures
├── tools/
│   ├── diagnose_password_reset_mail.php
│   └── import_tmdb.php
├── vendor/                 # Composer dependencies; do not edit directly
├── composer.json
├── composer.lock
├── forgot-password.php
├── index.php
├── login.php
├── logout.php
├── README.md
├── register.php
├── reset-password.php
└── UPCOMING_MOVIES.md      # Detailed upcoming-notification notes
```

## Prerequisites

- PHP 8.0 or newer.
- PHP extensions:
  - `pdo_mysql` and `json` are required.
  - `openssl` is required for encrypted SMTP connections.
  - `iconv` and `mbstring` are recommended; the application has limited fallbacks where possible.
- MySQL or MariaDB.
- Apache with PHP support, or PHP's built-in development server.
- Composer 2 to install PHPMailer.
- A TMDB API key only if importing metadata.
- Outbound access to TMDB, YouTube, image hosts, CDNs, and the configured mail server for the corresponding features.

The local repository has been checked with PHP 8.2.12 and MariaDB client 10.4.32. No Docker, Docker Compose, Makefile, Node.js manifest, or test runner is included.

## Installation

```bash
git clone https://github.com/TranHuuQuyet/thauphim-movie-website.git
cd thauphim-movie-website
composer install
```

Create the runtime configuration from the provided template:

```bash
cp includes/config.example.php includes/config.php
```

PowerShell equivalent:

```powershell
Copy-Item includes\config.example.php includes\config.php
```

Edit `includes/config.php` and set at least:

```php
define("APP_URL", "http://localhost:8000/");
define("TMDB_API_KEY", "replace-with-your-tmdb-api-key");

define("DB_HOST", "localhost");
define("DB_PORT", 3306);
define("DB_NAME", "thauphim");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_CHARSET", "utf8mb4");
```

`APP_BASE_PATH` is detected by the example configuration and can be defined explicitly. For reliable operation, serve the application at the web root: several public templates and scripts still use root-relative `/assets`, `/api`, and `/pages` URLs.

Do not commit real database or mail credentials. The repository ignores `includes/config.local.php`, but `includes/config.php` itself is tracked.

## Environment Variables

There is no `.env` loader and no `.env.example`. Creating a `.env` file alone has no effect.

Database and TMDB settings are PHP constants configured in `includes/config.php`. The mail settings below can be supplied through actual process/server environment variables because `includes/config.example.php` reads them with `getenv()`:

| Variable | Default in example config | Purpose |
| --- | --- | --- |
| `MAIL_DRIVER` | `smtp` | `smtp` or `mail` |
| `MAIL_FROM` | Project mailbox placeholder | Sender address |
| `MAIL_FROM_NAME` | `ThauPhim` | Sender display name |
| `SMTP_HOST` | Project SMTP placeholder | SMTP server |
| `SMTP_PORT` | `465` | SMTP port |
| `SMTP_ENCRYPTION` | `ssl` | PHPMailer encryption value, such as `ssl`, `tls`, or empty |
| `SMTP_USERNAME` | Project mailbox placeholder | SMTP username |
| `SMTP_PASSWORD` | Placeholder | SMTP password |
| `PASSWORD_RESET_TTL_MINUTES` | `60` | Reset-token lifetime; application minimum is 5 minutes |

For local development, a safe way to provide only the SMTP password is:

```php
<?php
// includes/config.local.php
define("SMTP_PASSWORD", "your-real-smtp-password");
```

## Database Setup and Migration

### Fresh database

> **Warning:** `database/schema.sql` drops and recreates every application table. Do not run it against a database containing data you need to keep.

The schema creates and selects a database named `thauphim`. From the project root:

```bash
mysql -u root -p --default-character-set=utf8mb4 < database/schema.sql
mysql -u root -p --default-character-set=utf8mb4 < database/seed.sql
```

PowerShell with the XAMPP MySQL client:

```powershell
& "D:\Xampp\mysql\bin\mysql.exe" -u root -p --default-character-set=utf8mb4 -e "SOURCE database/schema.sql; SOURCE database/seed.sql;"
```

Keep `DB_NAME` set to `thauphim` unless the SQL files and runtime configuration are adjusted together.

### Existing database upgrades

There is no migration framework or migration history table. Apply only the upgrade required by an older installation:

```bash
mysql -u root -p thauphim < database/password_reset_upgrade.sql
mysql -u root -p thauphim < database/notification_upgrade.sql
```

- `password_reset_upgrade.sql` creates `password_resets` if it is missing.
- `notification_upgrade.sql` adds `schedules.show_time` if needed and creates `notification_reads`.
- A database created from the current `schema.sql` already contains both features.

### Optional TMDB import

Set `TMDB_API_KEY` first, then run:

```bash
php tools/import_tmdb.php
```

The importer:

- Runs only from the CLI.
- Stops when the database already contains the target total of 100 TMDB imports.
- Targets 50 movies and 50 TV series.
- Upserts movie metadata, countries, genres, actors, and relationships.
- Uses `(tmdb_id, tmdb_type)` to prevent duplicate imports.
- Does not create episodes or YouTube playback URLs.

## Running the Project

### XAMPP / Apache

Start Apache and MySQL, then configure a virtual host whose `DocumentRoot` is the repository root. Adapt [deploy/apache-vhost.example.conf](deploy/apache-vhost.example.conf) and map its `ServerName` in the local hosts file. With a host such as `thauphim.local`, open:

```text
http://thauphim.local/
http://thauphim.local/admin/dashboard.php
```

Serving the project only as `http://localhost/thauphim-movie-website/` is not fully supported because some frontend URLs are root-relative.

### PHP development server

From the project root:

```bash
php -S 127.0.0.1:8000
```

Then open `http://127.0.0.1:8000/`. The configured MySQL/MariaDB service must already be running.

## API Documentation

The API is internal, session-aware, and not versioned. Most endpoints return:

```json
{
  "success": true,
  "data": {},
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 100,
    "total_pages": 5
  }
}
```

Errors use:

```json
{
  "success": false,
  "message": "Error description"
}
```

`meta` is included only by paginated endpoints. `update-watch-history.php` is an older endpoint and returns a flat `success`/`message` response.

### Catalog endpoints

| Method | Endpoint | Parameters | Authentication |
| --- | --- | --- | --- |
| `GET` | `/api/movies.php` | `page`, `limit`, `type`, `genre_id`, `country`, `year`, `q`, `sort` | Public |
| `GET` | `/api/movie-detail.php` | `id` | Public |
| `GET` | `/api/genres.php` | `type`, `hide_empty` | Public |
| `GET` | `/api/countries.php` | `type`, `hide_empty` | Public |
| `GET` | `/api/actors.php` | `page`, `limit`, `q` | Public |
| `GET` | `/api/episodes.php` | `movie_id` | Public |
| `GET` | `/api/movies-by-country.php` | `code`, `page`, `limit`, `type` | Public |

`type` accepts `movie`, `series`, or the `tv` alias. `country` on `/api/movies.php` accepts a country ID or two-letter code. `sort` accepts `newest`, `popular`, `top_rated`, or `most_viewed`. Page size is limited to 50.

Only published episodes are returned by the episode and movie-detail APIs.

### Interaction endpoints

| Method | Endpoint | JSON body / query | Authentication |
| --- | --- | --- | --- |
| `GET` | `/api/favorites.php?movie_id=1` | Query parameter | Required |
| `POST` | `/api/favorites.php` | `{"movie_id": 1}` | Required |
| `GET` | `/api/comments.php?movie_id=1` | Query parameter | Public |
| `POST` | `/api/comments.php` | `{"movie_id": 1, "content": "..."}` | Required |
| `DELETE` | `/api/comments.php` | `{"comment_id": 1}` | Owner or admin |
| `GET` | `/api/ratings.php?movie_id=1` | Query parameter | Public |
| `POST` | `/api/ratings.php` | `{"movie_id": 1, "rating": 5}` | Required |
| `POST` | `/api/notifications.php` | `{"schedule_ids": [1, 2]}` | Required |
| `POST` | `/api/record-view.php` | `{"movie_id": 1, "episode_id": 2}` | Guest or authorized member |
| `POST` | `/api/update-watch-history.php` | `{"movie_id": 1, "episode_id": 2, "progress_seconds": 120}` | Required |

The view endpoint validates that the episode is published and playable, enforces premium access, and returns `data.counted`. It increments a movie only once per PHP session.

## Screenshots and Demo

No verified live demo URL or application screenshots are currently included in the repository.

| View | Status |
| --- | --- |
| Homepage | Screenshot placeholder |
| Movie detail and player | Screenshot placeholder |
| Admin dashboard | Screenshot placeholder |

<!-- Add project screenshots under assets/images/ and replace the placeholders above. -->

The current database relationship diagram is available here:

![ThauPhim database diagram](assets/images/database-diagram.png)

## Default Account

`database/seed.sql` creates one administrator:

| Field | Value |
| --- | --- |
| Username | `admin` |
| Email | `admin@thauphim.local` |
| Password | `admin123` |
| Role | `admin` |
| Membership | `premium` |
| Status | `active` |

Change this password immediately outside local development. The seed is intended for a fresh database and is not an idempotent account migration.

## Scripts and Tooling

| Command | Purpose |
| --- | --- |
| `composer install` | Install PHPMailer and generate Composer autoload files |
| `composer install --no-dev --optimize-autoloader` | Install production dependencies |
| `php tools/import_tmdb.php` | Import TMDB metadata |
| `php tools/diagnose_password_reset_mail.php user@example.com` | Inspect mail and user configuration without sending |
| `php tools/diagnose_password_reset_mail.php user@example.com --send` | Create a reset request and send a test email |
| `php tools/diagnose_password_reset_mail.php user@example.com --send --show-url` | Send and print the generated reset URL; use only in a trusted environment |

There is no `package.json`, so there are no npm, Yarn, or pnpm scripts. CSS and JavaScript are served directly without compilation.

## Deployment

See [deploy/DEPLOYMENT.md](deploy/DEPLOYMENT.md) for the repository's deployment checklist and [deploy/apache-vhost.example.conf](deploy/apache-vhost.example.conf) for an Apache virtual-host example.

Production checklist:

1. Use PHP 8.0+ with `pdo_mysql` and HTTPS.
2. Create a dedicated database and least-privilege database user.
3. Run the fresh schema and seed only on a new database, or apply the relevant upgrade SQL to an existing database.
4. Configure `APP_URL`, base path, database credentials, TMDB key, and mail settings.
5. Run `composer install --no-dev --optimize-autoloader`.
6. Configure the Apache document root and optionally adapt `deploy/htaccess.example`.
7. Add at least one published episode with a valid YouTube URL.
8. Test authentication, password reset, public APIs, playback, and `/admin/dashboard.php`.
9. Change or remove the seeded administrator password.
10. Disable debug output and prevent directory listing.

The example `.htaccess` sets basic response headers and disables directory indexes. It does not provide routing rules because the application uses direct `.php` paths.

## Known Limitations

- There is no automated test suite, CI configuration, migration runner, Docker setup, or frontend build pipeline.
- `database/schema.sql` is destructive and the upgrade SQL files are managed manually.
- Runtime configuration is PHP-based; there is no general `.env` loader. Database and TMDB values are not read from environment variables by the example config.
- Subdirectory deployment is inconsistent because several templates and scripts use root-relative asset, page, and API URLs.
- The homepage hero content and its linked movie ID are hard-coded.
- Some fallback image references (`assets/images/avatar-default.png` and `assets/images/default.jpg`) do not exist in the repository.
- TMDB import requires `allow_url_fopen` and outbound HTTPS; it imports metadata only.
- Playback depends on manually managed YouTube URLs and YouTube iframe availability.
- External browser libraries and several images are loaded from third-party CDNs/services.
- Watch history is available only to signed-in users. View counts are session-based, not unique-user analytics.
- Password-reset delivery depends on a correctly configured SMTP server or PHP `mail()`.
- The JSON API is internal, unversioned, session-authenticated, and has no documented rate limiting.
- Application screenshots and a verified live demo are not included.

## Future Improvements

- Add automated unit, integration, and browser tests with CI.
- Introduce ordered, reversible database migrations.
- Move all secrets and environment-specific settings to a consistent environment configuration layer.
- Replace the hard-coded homepage hero with admin-managed featured content.
- Add API versioning, CSRF policy documentation for JSON mutations, and rate limiting.
- Add first-party analytics if unique viewers or time-based reporting is required.
- Bundle or self-host critical frontend dependencies for deployments that cannot rely on CDNs.

## Contributors

Git history currently contains contributions under these normalized author names:

- Tran Huu Quyet
- Hoang Thanh Dat
- nhokmk00-png
- Nguyen Cam Tu
- Orange_Dev
- TranQuan231005
- Hhug19

See the repository's [contributors graph](https://github.com/TranHuuQuyet/thauphim-movie-website/graphs/contributors) for commit attribution.

## License

`composer.json` declares this project as `proprietary`. No standalone `LICENSE` file is included. Do not assume permission to redistribute, modify, or use the project beyond the rights granted by its owner.
