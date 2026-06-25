<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This importer must be run from CLI.');
}

require_once __DIR__ . '/../includes/db.php';

const TARGET_TOTAL = 100;
const TARGET_PER_TYPE = 50;
const LIST_PAGES_PER_SOURCE = 3;
const CAST_LIMIT = 10;
const TMDB_BASE_URL = 'https://api.themoviedb.org/3/';

$pdo = getDatabaseConnection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function tmdbRequest(string $path, array $params = []): array
{
    if (!defined('TMDB_API_KEY') || TMDB_API_KEY === '') {
        throw new RuntimeException('TMDB_API_KEY is missing in includes/config.php.');
    }

    $params = array_merge([
        'api_key' => TMDB_API_KEY,
        'language' => 'vi-VN',
    ], $params);

    $url = TMDB_BASE_URL . ltrim($path, '/') . '?' . http_build_query($params);
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 20,
            'ignore_errors' => true,
            'header' => "Accept: application/json\r\n",
        ],
    ]);

    $body = @file_get_contents($url, false, $context);

    if ($body === false) {
        throw new RuntimeException("TMDB request failed: {$path}");
    }

    $statusLine = $http_response_header[0] ?? '';
    if ($statusLine !== '' && !preg_match('/\s2\d\d\s/', $statusLine)) {
        throw new RuntimeException("TMDB request failed for {$path}: {$statusLine}");
    }

    $data = json_decode($body, true);
    if (!is_array($data)) {
        throw new RuntimeException("Invalid TMDB JSON response for {$path}");
    }

    return $data;
}

function slugify(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return 'item-' . bin2hex(random_bytes(4));
    }

    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if (is_string($ascii) && $ascii !== '') {
        $value = $ascii;
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value !== '' ? $value : 'item-' . bin2hex(random_bytes(4));
}

function normalizeDate(?string $date): ?string
{
    if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return null;
    }

    return $date;
}

function releaseYear(?string $date): ?int
{
    return $date ? (int) substr($date, 0, 4) : null;
}

function normalizeMovieStatus(string $tmdbType, array $detail, ?string $releaseDate): string
{
    if ($tmdbType === 'tv') {
        $status = strtolower((string) ($detail['status'] ?? ''));
        if (str_contains($status, 'ended') || str_contains($status, 'canceled')) {
            return 'completed';
        }

        return 'ongoing';
    }

    if ($releaseDate && $releaseDate > date('Y-m-d')) {
        return 'coming_soon';
    }

    return 'completed';
}

function collectCandidates(string $tmdbType, array $sources): array
{
    $items = [];

    foreach ($sources as $source) {
        for ($page = 1; $page <= LIST_PAGES_PER_SOURCE; $page++) {
            try {
                $response = tmdbRequest($source, ['page' => $page]);
            } catch (Throwable $exception) {
                fwrite(STDERR, "Skip source {$source} page {$page}: {$exception->getMessage()}\n");
                continue;
            }

            foreach (($response['results'] ?? []) as $item) {
                if (!is_array($item) || empty($item['id'])) {
                    continue;
                }

                $key = $tmdbType . ':' . (int) $item['id'];
                $items[$key] = [
                    'tmdb_id' => (int) $item['id'],
                    'tmdb_type' => $tmdbType,
                ];
            }
        }
    }

    return array_values($items);
}

function loadImportProgress(PDO $pdo): array
{
    $keys = [];
    $counts = [
        'movie' => 0,
        'tv' => 0,
    ];

    $statement = $pdo->query(
        'SELECT tmdb_id, tmdb_type
         FROM movies
         WHERE tmdb_id IS NOT NULL
           AND tmdb_type IS NOT NULL'
    );

    foreach ($statement->fetchAll() as $row) {
        $key = $row['tmdb_type'] . ':' . $row['tmdb_id'];
        $keys[$key] = true;

        if (isset($counts[$row['tmdb_type']])) {
            $counts[$row['tmdb_type']]++;
        }
    }

    return [
        'keys' => $keys,
        'counts' => $counts,
        'total' => array_sum($counts),
    ];
}

function rejectExistingItems(array $items, array $existingKeys): array
{
    return array_values(array_filter(
        $items,
        static fn(array $item): bool => !isset($existingKeys[$item['tmdb_type'] . ':' . $item['tmdb_id']])
    ));
}

function selectImportItems(array $movieItems, array $tvItems, array $progress): array
{
    $neededTotal = max(0, TARGET_TOTAL - (int) $progress['total']);
    if ($neededTotal === 0) {
        return [];
    }

    $movieItems = rejectExistingItems($movieItems, $progress['keys']);
    $tvItems = rejectExistingItems($tvItems, $progress['keys']);

    $neededMovies = max(0, TARGET_PER_TYPE - (int) $progress['counts']['movie']);
    $neededTv = max(0, TARGET_PER_TYPE - (int) $progress['counts']['tv']);

    $selectedMovies = array_slice($movieItems, 0, $neededMovies);
    $selectedTv = array_slice($tvItems, 0, $neededTv);
    $selected = array_merge($selectedMovies, $selectedTv);

    if (count($selected) < $neededTotal) {
        $selectedKeys = [];
        foreach ($selected as $item) {
            $selectedKeys[$item['tmdb_type'] . ':' . $item['tmdb_id']] = true;
        }

        foreach (array_merge($movieItems, $tvItems) as $item) {
            $key = $item['tmdb_type'] . ':' . $item['tmdb_id'];
            if (isset($selectedKeys[$key])) {
                continue;
            }

            $selected[] = $item;
            $selectedKeys[$key] = true;

            if (count($selected) >= $neededTotal) {
                break;
            }
        }
    }

    return array_slice($selected, 0, $neededTotal);
}

function loadTmdbCountryMap(): array
{
    try {
        $response = tmdbRequest('configuration/countries', ['language' => 'en-US']);
    } catch (Throwable $exception) {
        fwrite(STDERR, "Could not load TMDB countries: {$exception->getMessage()}\n");
        return [];
    }

    $map = [];
    foreach ($response as $country) {
        if (!is_array($country) || empty($country['iso_3166_1'])) {
            continue;
        }

        $code = strtoupper((string) $country['iso_3166_1']);
        $name = trim((string) ($country['native_name'] ?? $country['english_name'] ?? $code));
        $map[$code] = $name !== '' ? $name : $code;
    }

    return $map;
}

function upsertCountry(PDO $pdo, string $code, string $name): ?int
{
    $code = strtoupper(trim($code));
    if (!preg_match('/^[A-Z]{2}$/', $code)) {
        return null;
    }

    $name = trim($name) !== '' ? trim($name) : $code;

    $statement = $pdo->prepare(
        'INSERT INTO countries (code, name)
         VALUES (:code, :name)
         ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            id = LAST_INSERT_ID(id)'
    );
    $statement->execute([
        'code' => $code,
        'name' => $name,
    ]);

    return (int) $pdo->lastInsertId();
}

function upsertGenre(PDO $pdo, array $genre): ?int
{
    if (empty($genre['id']) || empty($genre['name'])) {
        return null;
    }

    $name = trim((string) $genre['name']);
    if ($name === '') {
        return null;
    }

    $statement = $pdo->prepare(
        'INSERT INTO genres (tmdb_genre_id, name, slug)
         VALUES (:tmdb_genre_id, :name, :slug)
         ON DUPLICATE KEY UPDATE
            tmdb_genre_id = VALUES(tmdb_genre_id),
            name = VALUES(name),
            slug = VALUES(slug),
            id = LAST_INSERT_ID(id)'
    );
    $statement->execute([
        'tmdb_genre_id' => (int) $genre['id'],
        'name' => $name,
        'slug' => slugify($name),
    ]);

    return (int) $pdo->lastInsertId();
}

function upsertActor(PDO $pdo, array $actor): ?int
{
    if (empty($actor['id']) || empty($actor['name'])) {
        return null;
    }

    $statement = $pdo->prepare(
        'INSERT INTO actors (tmdb_actor_id, name, avatar, profile_path, known_for_department)
         VALUES (:tmdb_actor_id, :name, :avatar, :profile_path, :known_for_department)
         ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            avatar = VALUES(avatar),
            profile_path = VALUES(profile_path),
            known_for_department = VALUES(known_for_department),
            id = LAST_INSERT_ID(id)'
    );

    $profilePath = $actor['profile_path'] ?? null;
    $statement->execute([
        'tmdb_actor_id' => (int) $actor['id'],
        'name' => (string) $actor['name'],
        'avatar' => $profilePath,
        'profile_path' => $profilePath,
        'known_for_department' => $actor['known_for_department'] ?? null,
    ]);

    return (int) $pdo->lastInsertId();
}

function pickCountry(array $detail, string $tmdbType, array $countryMap): array
{
    if ($tmdbType === 'movie') {
        $country = $detail['production_countries'][0] ?? null;
        if (is_array($country) && !empty($country['iso_3166_1'])) {
            $code = strtoupper((string) $country['iso_3166_1']);
            $name = trim((string) ($country['name'] ?? $countryMap[$code] ?? $code));
            return [$code, $name !== '' ? $name : $code];
        }
    }

    $originCountry = $detail['origin_country'][0] ?? null;
    if (is_string($originCountry) && preg_match('/^[A-Z]{2}$/', $originCountry)) {
        return [$originCountry, $countryMap[$originCountry] ?? $originCountry];
    }

    return [null, null];
}

function upsertMovie(PDO $pdo, array $detail, string $tmdbType, ?int $countryId): int
{
    $isTv = $tmdbType === 'tv';
    $title = (string) ($isTv ? ($detail['name'] ?? '') : ($detail['title'] ?? ''));
    $originalTitle = (string) ($isTv ? ($detail['original_name'] ?? '') : ($detail['original_title'] ?? ''));
    $releaseDate = normalizeDate($isTv ? ($detail['first_air_date'] ?? null) : ($detail['release_date'] ?? null));
    $overview = trim((string) ($detail['overview'] ?? ''));
    $runtime = null;

    if ($isTv) {
        $episodeRunTime = $detail['episode_run_time'][0] ?? null;
        $runtime = is_numeric($episodeRunTime) ? (int) $episodeRunTime : null;
    } else {
        $runtime = !empty($detail['runtime']) ? (int) $detail['runtime'] : null;
    }

    if ($title === '') {
        $title = $originalTitle !== '' ? $originalTitle : 'Untitled TMDB #' . (int) $detail['id'];
    }

    $statement = $pdo->prepare(
        'INSERT INTO movies (
            tmdb_id, tmdb_type, title, original_title, overview, description,
            poster_path, backdrop_path, release_date, release_year, runtime,
            type, quality, country_id, status, is_premium, views,
            rating_average, rating_count, vote_average, vote_count, popularity,
            original_language, imported_at, last_synced_at
         ) VALUES (
            :tmdb_id, :tmdb_type, :title, :original_title, :overview, :description,
            :poster_path, :backdrop_path, :release_date, :release_year, :runtime,
            :type, :quality, :country_id, :status, 0, 0,
            0.00, 0, :vote_average, :vote_count, :popularity,
            :original_language, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
         )
         ON DUPLICATE KEY UPDATE
            original_title = VALUES(original_title),
            overview = VALUES(overview),
            poster_path = VALUES(poster_path),
            backdrop_path = VALUES(backdrop_path),
            release_date = VALUES(release_date),
            release_year = VALUES(release_year),
            runtime = VALUES(runtime),
            country_id = VALUES(country_id),
            vote_average = VALUES(vote_average),
            vote_count = VALUES(vote_count),
            popularity = VALUES(popularity),
            original_language = VALUES(original_language),
            last_synced_at = CURRENT_TIMESTAMP,
            id = LAST_INSERT_ID(id)'
    );

    $statement->execute([
        'tmdb_id' => (int) $detail['id'],
        'tmdb_type' => $tmdbType,
        'title' => $title,
        'original_title' => $originalTitle !== '' ? $originalTitle : null,
        'overview' => $overview !== '' ? $overview : null,
        'description' => $overview !== '' ? $overview : null,
        'poster_path' => $detail['poster_path'] ?? null,
        'backdrop_path' => $detail['backdrop_path'] ?? null,
        'release_date' => $releaseDate,
        'release_year' => releaseYear($releaseDate),
        'runtime' => $runtime,
        'type' => $isTv ? 'series' : 'movie',
        'quality' => 'HD',
        'country_id' => $countryId,
        'status' => normalizeMovieStatus($tmdbType, $detail, $releaseDate),
        'vote_average' => (float) ($detail['vote_average'] ?? 0),
        'vote_count' => (int) ($detail['vote_count'] ?? 0),
        'popularity' => (float) ($detail['popularity'] ?? 0),
        'original_language' => $detail['original_language'] ?? null,
    ]);

    return (int) $pdo->lastInsertId();
}

function syncMovieGenres(PDO $pdo, int $movieId, array $genres): int
{
    $pdo->prepare('DELETE FROM movie_genres WHERE movie_id = :movie_id')->execute([
        'movie_id' => $movieId,
    ]);

    $insert = $pdo->prepare(
        'INSERT IGNORE INTO movie_genres (movie_id, genre_id)
         VALUES (:movie_id, :genre_id)'
    );

    $count = 0;
    foreach ($genres as $genre) {
        if (!is_array($genre)) {
            continue;
        }

        $genreId = upsertGenre($pdo, $genre);
        if (!$genreId) {
            continue;
        }

        $insert->execute([
            'movie_id' => $movieId,
            'genre_id' => $genreId,
        ]);
        $count++;
    }

    return $count;
}

function syncMovieActors(PDO $pdo, int $movieId, array $credits): int
{
    $pdo->prepare('DELETE FROM movie_actors WHERE movie_id = :movie_id')->execute([
        'movie_id' => $movieId,
    ]);

    $insert = $pdo->prepare(
        'INSERT IGNORE INTO movie_actors (movie_id, actor_id, character_name, cast_order)
         VALUES (:movie_id, :actor_id, :character_name, :cast_order)'
    );

    $count = 0;
    foreach (array_slice($credits['cast'] ?? [], 0, CAST_LIMIT) as $actor) {
        if (!is_array($actor)) {
            continue;
        }

        $actorId = upsertActor($pdo, $actor);
        if (!$actorId) {
            continue;
        }

        $insert->execute([
            'movie_id' => $movieId,
            'actor_id' => $actorId,
            'character_name' => $actor['character'] ?? null,
            'cast_order' => isset($actor['order']) ? (int) $actor['order'] : null,
        ]);
        $count++;
    }

    return $count;
}

$movieSources = [
    'movie/popular',
    'movie/top_rated',
    'movie/now_playing',
    'movie/upcoming',
    'trending/movie/week',
];

$tvSources = [
    'tv/popular',
    'tv/top_rated',
    'tv/on_the_air',
    'trending/tv/week',
];

$progress = loadImportProgress($pdo);

if ((int) $progress['total'] >= TARGET_TOTAL) {
    echo "Existing import count: {$progress['total']} ({$progress['counts']['movie']} movie, {$progress['counts']['tv']} tv).\n";
    echo "Target already reached. Nothing to import.\n";
    exit(0);
}

echo "Collecting TMDB candidates...\n";
$movieItems = collectCandidates('movie', $movieSources);
$tvItems = collectCandidates('tv', $tvSources);
$items = selectImportItems($movieItems, $tvItems, $progress);
$countryMap = $items ? loadTmdbCountryMap() : [];

echo "Existing import count: {$progress['total']} ({$progress['counts']['movie']} movie, {$progress['counts']['tv']} tv).\n";
echo 'Selected ' . count($items) . " new items for import.\n";

$stats = [
    'movies' => 0,
    'tv' => 0,
    'genres' => 0,
    'actors' => 0,
    'failed' => 0,
];

foreach ($items as $index => $item) {
    $tmdbId = (int) $item['tmdb_id'];
    $tmdbType = (string) $item['tmdb_type'];
    $detailPath = $tmdbType === 'tv' ? "tv/{$tmdbId}" : "movie/{$tmdbId}";
    $creditsPath = $tmdbType === 'tv' ? "tv/{$tmdbId}/credits" : "movie/{$tmdbId}/credits";

    try {
        $detail = tmdbRequest($detailPath);
        $credits = tmdbRequest($creditsPath);

        [$countryCode, $countryName] = pickCountry($detail, $tmdbType, $countryMap);
        $countryId = $countryCode ? upsertCountry($pdo, $countryCode, (string) $countryName) : null;

        $pdo->beginTransaction();
        $movieId = upsertMovie($pdo, $detail, $tmdbType, $countryId);
        $stats['genres'] += syncMovieGenres($pdo, $movieId, $detail['genres'] ?? []);
        $stats['actors'] += syncMovieActors($pdo, $movieId, $credits);
        $pdo->commit();

        $stats[$tmdbType === 'tv' ? 'tv' : 'movies']++;
        $title = $detail[$tmdbType === 'tv' ? 'name' : 'title'] ?? ('TMDB #' . $tmdbId);
        echo sprintf("[%03d/%03d] Imported %s #%d: %s\n", $index + 1, count($items), $tmdbType, $tmdbId, $title);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $stats['failed']++;
        fwrite(STDERR, "Failed {$tmdbType} #{$tmdbId}: {$exception->getMessage()}\n");
    }
}

echo "\nImport finished.\n";
echo "Movies: {$stats['movies']}\n";
echo "TV series: {$stats['tv']}\n";
echo "Genre links: {$stats['genres']}\n";
echo "Actor links: {$stats['actors']}\n";
echo "Failed: {$stats['failed']}\n";
