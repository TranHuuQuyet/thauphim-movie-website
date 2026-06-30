<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

try {
    $pdo = getDatabaseConnection();
    $type = strtolower(apiStringParam('type'));
    if ($type === 'tv') {
        $type = 'series';
    }

    $hideEmpty = filter_input(INPUT_GET, 'hide_empty', FILTER_VALIDATE_BOOLEAN);
    $sql = 'SELECT 
                c.id, 
                c.code, 
                c.name, 
                COUNT(m.id) AS movie_count
            FROM countries c';
    
    $params = [];

    if ($type !== '') {
        if (!in_array($type, ['movie', 'series'], true)) {
            apiError('Loai phim khong hop le.', 400);
        }
        $sql .= ' LEFT JOIN movies m ON m.country_id = c.id AND m.type = :type';
        $params['type'] = $type;
    } else {
        $sql .= ' LEFT JOIN movies m ON m.country_id = c.id';
    }

    $sql .= ' GROUP BY c.id, c.code, c.name';

    if ($hideEmpty) {
        $sql .= ' HAVING movie_count > 0';
    }

    $sql .= ' ORDER BY c.name ASC';

    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    $countries = array_map(static function (array $country): array {
        return [
            'id' => (int) $country['id'],
            'code' => $country['code'],
            'name' => $country['name'],
            'movie_count' => (int) $country['movie_count'],
        ];
    }, $statement->fetchAll());

    apiSuccess($countries);
} catch (Throwable $exception) {
    apiServerError('Khong the tai danh sach quoc gia.', $exception);
}
