<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

$code = strtoupper(apiStringParam('code'));

if ($code === '' || !preg_match('/^[A-Z]{2}$/', $code)) {
    apiError('Ma quoc gia khong hop le.', 400);
}

try {
    $pdo = getDatabaseConnection();

    $countryStatement = $pdo->prepare(
        'SELECT id, code, name
         FROM countries
         WHERE code = :code
         LIMIT 1'
    );
    $countryStatement->execute(['code' => $code]);
    $country = $countryStatement->fetch();

    if (!$country) {
        apiError('Khong tim thay quoc gia.', 404);
    }
    $countryId = (int) $country['id'];

    $page = apiIntParam('page', 1, 1, 1000);
    $limit = apiIntParam('limit', 20, 1, 50); 
    $offset = ($page - 1) * $limit;
    $type = strtolower(apiStringParam('type'));
    if ($type === 'tv') {
        $type = 'series';
    }

    $whereClauses = ['m.country_id = :country_id'];
    $bindParams = ['country_id' => $countryId];
    if ($type !== '') {
        if (!in_array($type, ['movie', 'series'], true)) {
            apiError('Loai phim khong hop le.', 400);
        }
        $whereClauses[] = 'm.type = :type';
        $bindParams['type'] = $type;
    }
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
    $countStatement = $pdo->prepare(
        "SELECT COUNT(*) FROM movies m {$whereSql}"
    );
    $countStatement->execute($bindParams);
    $total = (int) $countStatement->fetchColumn();

    $movieStatement = $pdo->prepare(
        "SELECT
            m.*,
            c.code AS country_code,
            c.name AS country_name
         FROM movies m
         LEFT JOIN countries c ON c.id = m.country_id
         {$whereSql}
         ORDER BY m.release_date DESC, m.created_at DESC, m.id DESC
         LIMIT :limit OFFSET :offset"
    );

    foreach ($bindParams as $key => $value) {
        $movieStatement->bindValue(':' . $key, $value, PDO::PARAM_STR); 
    }
    $movieStatement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $movieStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $movieStatement->execute();
    $movies = array_map('apiMovieRow', $movieStatement->fetchAll());

    apiSuccess([
        'country' => [
            'id'   => $countryId,
            'code' => $country['code'],
            'name' => $country['name'],
        ],
        'movies' => $movies
    ], apiPaginationMeta($page, $limit, $total));

} catch (Throwable $exception) {
    apiServerError('Khong the tai danh sach phim theo quoc gia.', $exception);
}