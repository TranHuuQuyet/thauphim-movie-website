<?php
declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

function apiActorProfileUrl(?string $avatar, ?string $profilePath): ?string
{
    $avatar = trim((string) $avatar);
    $profilePath = trim((string) $profilePath);

    if ($avatar !== '' && $profilePath !== '' && $avatar === $profilePath && str_starts_with($profilePath, '/')) {
        return TMDB_IMAGE_BASE . 'w500' . $profilePath;
    }

    return apiImageUrl($avatar !== '' ? $avatar : null, $profilePath !== '' ? $profilePath : null, 'w500');
}

try {
    $pdo = getDatabaseConnection();

    $page = apiIntParam('page', 1, 1, 1000);
    $limit = apiIntParam('limit', 20, 1, 50);
    $offset = ($page - 1) * $limit;
    $query = apiStringParam('q');

    $whereSql = '';
    $params = [];
    if ($query !== '') {
        $whereSql = 'WHERE a.name LIKE :q';
        $params['q'] = '%' . $query . '%';
    }

    $countStatement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM actors a
         {$whereSql}"
    );
    $countStatement->execute($params);
    $total = (int) $countStatement->fetchColumn();

    $statement = $pdo->prepare(
        "SELECT
            a.id,
            a.tmdb_actor_id,
            a.name,
            a.avatar,
            a.profile_path,
            a.biography,
            a.known_for_department,
            COUNT(ma.movie_id) AS movie_count
         FROM actors a
         LEFT JOIN movie_actors ma ON ma.actor_id = a.id
         {$whereSql}
         GROUP BY a.id, a.tmdb_actor_id, a.name, a.avatar, a.profile_path, a.biography, a.known_for_department
         ORDER BY movie_count DESC, a.name ASC
         LIMIT :limit OFFSET :offset"
    );

    foreach ($params as $key => $value) {
        $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
    }
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();

    $actors = array_map(static function (array $actor): array {
        return [
            'id' => (int) $actor['id'],
            'tmdb_actor_id' => isset($actor['tmdb_actor_id']) ? (int) $actor['tmdb_actor_id'] : null,
            'name' => $actor['name'],
            'avatar' => $actor['avatar'],
            'profile_path' => $actor['profile_path'],
            'profile_url' => apiActorProfileUrl($actor['avatar'], $actor['profile_path']),
            'biography' => $actor['biography'],
            'known_for_department' => $actor['known_for_department'],
            'movie_count' => (int) $actor['movie_count'],
        ];
    }, $statement->fetchAll());

    apiSuccess($actors, apiPaginationMeta($page, $limit, $total));
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    apiError('Khong the tai danh sach dien vien.');
}
