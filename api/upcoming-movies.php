<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

try {
    $pdo = getDatabaseConnection();

    $page = apiIntParam('page', 1, 1, 1000);
    $limit = apiIntParam('limit', 20, 1, 50);
    $offset = ($page - 1) * $limit;

    $sortBy = strtolower(apiStringParam('sort_by', 'release_date'));
    if (!in_array($sortBy, ['release_date', 'popularity', 'title', 'rating'], true)) {
        $sortBy = 'release_date';
    }

    $orderDir = strtoupper(apiStringParam('order', 'ASC'));
    if (!in_array($orderDir, ['ASC', 'DESC'], true)) {
        $orderDir = 'ASC';
    }

    $orderMap = [
        'release_date' => 's.release_date',
        'popularity' => 'm.popularity',
        'title' => 'm.title',
        'rating' => 'm.rating_average',
    ];

    $orderBy = $orderMap[$sortBy] ?? 's.release_date';

    $joins = 'LEFT JOIN countries c ON c.id = m.country_id';
    $where = [];
    $params = [];

    // Lấy phim sắp chiếu (status = 'coming_soon' hoặc ngày phát hành > hôm nay)
    $where[] = "(m.status = 'coming_soon' OR s.release_date > CURDATE())";

    // Filter by genre
    $genreId = filter_input(INPUT_GET, 'genre_id', FILTER_VALIDATE_INT);
    if ($genreId !== false && $genreId !== null && $genreId > 0) {
        $joins .= ' INNER JOIN movie_genres mg_filter ON mg_filter.movie_id = m.id';
        $where[] = 'mg_filter.genre_id = :genre_id';
        $params['genre_id'] = (int) $genreId;
    }

    // Filter by country
    $country = strtoupper(apiStringParam('country'));
    if ($country !== '') {
        if (preg_match('/^\d+$/', $country)) {
            $where[] = 'm.country_id = :country_id';
            $params['country_id'] = (int) $country;
        } elseif (preg_match('/^[A-Z]{2}$/', $country)) {
            $where[] = 'c.code = :country_code';
            $params['country_code'] = $country;
        }
    }

    // Filter by type
    $type = strtolower(apiStringParam('type'));
    if ($type !== '') {
        if ($type === 'tv') {
            $type = 'series';
        }

        if (in_array($type, ['movie', 'series'], true)) {
            $where[] = 'm.type = :type';
            $params['type'] = $type;
        }
    }

    // Search by title
    $search = apiStringParam('search');
    if ($search !== '') {
        $where[] = 'MATCH(m.title, m.original_title, m.description, m.overview) AGAINST(:search IN BOOLEAN MODE)';
        $params['search'] = $search;
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Get total count
    $countStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT m.id) as total
        FROM movies m
        {$joins}
        LEFT JOIN schedules s ON s.movie_id = m.id
        {$whereClause}
    ");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // Get movies
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            m.id, m.title, m.original_title, m.poster, m.backdrop, 
            m.release_date, m.type, m.quality, m.rating_average, 
            m.status, m.is_premium, m.popularity, m.runtime,
            GROUP_CONCAT(DISTINCT g.name SEPARATOR ',') as genres,
            c.code as country_code, c.name as country_name,
            COALESCE(s.release_date, m.release_date) as upcoming_date
        FROM movies m
        {$joins}
        LEFT JOIN movie_genres mg ON mg.movie_id = m.id
        LEFT JOIN genres g ON g.id = mg.genre_id
        LEFT JOIN schedules s ON s.movie_id = m.id
        {$whereClause}
        GROUP BY m.id
        ORDER BY {$orderBy} {$orderDir}
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->execute();

    $movies = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $genres = !empty($row['genres']) ? explode(',', $row['genres']) : [];

        $movie = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'original_title' => $row['original_title'],
            'poster' => $row['poster'] ? TMDB_IMAGE_BASE . 'w500' . $row['poster'] : null,
            'backdrop' => $row['backdrop'] ? TMDB_IMAGE_BASE . 'w1280' . $row['backdrop'] : null,
            'release_date' => $row['release_date'],
            'upcoming_date' => $row['upcoming_date'],
            'type' => $row['type'],
            'quality' => $row['quality'],
            'rating' => (float)$row['rating_average'],
            'status' => $row['status'],
            'is_premium' => (bool)$row['is_premium'],
            'popularity' => (float)$row['popularity'],
            'runtime' => $row['runtime'] ? (int)$row['runtime'] : null,
            'genres' => $genres,
            'country' => $row['country_code'] ? [
                'code' => $row['country_code'],
                'name' => $row['country_name'],
            ] : null,
        ];

        $movies[] = $movie;
    }

    $totalPages = (int)ceil($total / $limit);

    apiSuccess($movies, [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'total_pages' => $totalPages,
    ]);
} catch (Exception $e) {
    apiServerError('Loi khi lay danh sach phim sap chieu', $e);
}
