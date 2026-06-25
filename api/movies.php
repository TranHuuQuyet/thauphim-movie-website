<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

try {
    $pdo = getDatabaseConnection();

    $page = apiIntParam('page', 1, 1, 1000);
    $limit = apiIntParam('limit', 20, 1, 50);
    $offset = ($page - 1) * $limit;

    $joins = 'LEFT JOIN countries c ON c.id = m.country_id';
    $where = [];
    $params = [];

    $type = strtolower(apiStringParam('type'));
    if ($type !== '') {
        if ($type === 'tv') {
            $type = 'series';
        }

        if (!in_array($type, ['movie', 'series'], true)) {
            apiError('Loai phim khong hop le.', 400);
        }

        $where[] = 'm.type = :type';
        $params['type'] = $type;
    }

    $genreId = filter_input(INPUT_GET, 'genre_id', FILTER_VALIDATE_INT);
    if ($genreId !== false && $genreId !== null && $genreId > 0) {
        $joins .= ' INNER JOIN movie_genres mg_filter ON mg_filter.movie_id = m.id';
        $where[] = 'mg_filter.genre_id = :genre_id';
        $params['genre_id'] = (int) $genreId;
    }

    $country = strtoupper(apiStringParam('country'));
    if ($country !== '') {
        if (preg_match('/^\d+$/', $country)) {
            $where[] = 'm.country_id = :country_id';
            $params['country_id'] = (int) $country;
        } elseif (preg_match('/^[A-Z]{2}$/', $country)) {
            $where[] = 'c.code = :country_code';
            $params['country_code'] = $country;
        } else {
            apiError('Quoc gia khong hop le.', 400);
        }
    }

    $year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
    if ($year !== false && $year !== null && $year >= 1900 && $year <= 2100) {
        $where[] = 'm.release_year = :year';
        $params['year'] = (int) $year;
    }

    $query = apiStringParam('q');
    if ($query !== '') {
        $where[] = '(m.title LIKE :q OR m.original_title LIKE :q OR m.description LIKE :q OR m.overview LIKE :q)';
        $params['q'] = '%' . $query . '%';
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sort = strtolower(apiStringParam('sort'));
    $orderSql = match ($sort) {
        'popular' => 'ORDER BY m.popularity DESC, m.vote_count DESC, m.id DESC',
        'top_rated' => 'ORDER BY m.vote_average DESC, m.vote_count DESC, m.id DESC',
        'most_viewed' => 'ORDER BY m.views DESC, m.id DESC',
        'newest', '' => 'ORDER BY m.release_date DESC, m.created_at DESC, m.id DESC',
        default => null,
    };

    if ($orderSql === null) {
        apiError('Sap xep khong hop le.', 400);
    }

    $countStatement = $pdo->prepare(
        "SELECT COUNT(DISTINCT m.id)
         FROM movies m
         {$joins}
         {$whereSql}"
    );
    $countStatement->execute($params);
    $total = (int) $countStatement->fetchColumn();

    $statement = $pdo->prepare(
        "SELECT
            m.*,
            c.code AS country_code,
            c.name AS country_name
         FROM movies m
         {$joins}
         {$whereSql}
         {$orderSql}
         LIMIT :limit OFFSET :offset"
    );

    foreach ($params as $key => $value) {
        $statement->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();

    $movies = array_map('apiMovieRow', $statement->fetchAll());

    apiSuccess($movies, apiPaginationMeta($page, $limit, $total));
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    apiError('Khong the tai danh sach phim.');
}
