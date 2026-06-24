<?php
require_once __DIR__ . '/db.php';

// Lay danh sach phim theo bo loc, tim kiem, sap xep va phan trang
function getFilteredMovies($params, $limit = 12, $offset = 0) {
    $pdo = getDatabaseConnection();

    // Kiem tra du lieu phan trang
    $limit = (int) $limit;
    $offset = (int) $offset;

    if ($limit <= 0) {
        $limit = 12;
    }
    if ($offset < 0) {
        $offset = 0;
    }

    $sql = "SELECT DISTINCT m.* FROM movies m";
    
    if (!empty($params['genre_id'])) {
        $sql .= " INNER JOIN movie_genres mg ON m.id = mg.movie_id";
    }
    
    if (!empty($params['country'])) {
        $sql .= " INNER JOIN countries c ON m.country_id = c.id";
    }

    $whereClauses = [];
    $executeParams = [];

    // Tim kiem ten phim
    if (!empty($params['q'])) {
        $whereClauses[] = "m.title LIKE :q";
        $executeParams[':q'] = '%' . $params['q'] . '%';
    }

    // Loc theo loai phim
    if (!empty($params['type'])) {
        $whereClauses[] = "m.type = :type";
        $executeParams[':type'] = $params['type'];
    }

    // Loc theo the loai
    if (!empty($params['genre_id'])) {
        $whereClauses[] = "mg.genre_id = :genre_id";
        $executeParams[':genre_id'] = $params['genre_id'];
    }

    // Loc theo quoc gia
    if (!empty($params['country'])) {
        $whereClauses[] = "c.code = :country_code";
        $executeParams[':country_code'] = $params['country'];
    }

    // Loc theo nam phat hanh
    if (!empty($params['release_year'])) {
        $whereClauses[] = "m.release_year = :release_year";
        $executeParams[':release_year'] = $params['release_year'];
    }

    // Rap noi dieu kien
    if (count($whereClauses) > 0) {
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
    }

    // Sap xep
    $sort = $params['sort'] ?? '';

    $allowedSort = ['newest', 'most_viewed'];

    if (!in_array($sort, $allowedSort)) {
        $sort = '';
    }

    if ($sort === 'most_viewed') {
        $sql .= " ORDER BY m.views DESC, m.created_at DESC";
    } elseif ($sort === 'newest') {
        $sql .= " ORDER BY m.created_at DESC";
    } else {
        $sql .= " ORDER BY m.id DESC"; 
    }

    // Phan trang
    $sql .= " LIMIT :limit OFFSET :offset";
    
    try {
        $stmt = $pdo->prepare($sql);
        
        foreach ($executeParams as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(); 
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

// Dem tong so phim theo bo loc
function countFilteredMovies($params) {
    $pdo = getDatabaseConnection();

    $sql = "SELECT COUNT(DISTINCT m.id) FROM movies m";
    
    if (!empty($params['genre_id'])) {
        $sql .= " INNER JOIN movie_genres mg ON m.id = mg.movie_id";
    }
    
    if (!empty($params['country'])) {
        $sql .= " INNER JOIN countries c ON m.country_id = c.id";
    }

    $whereClauses = [];
    $executeParams = [];

    if (!empty($params['q'])) {
        $whereClauses[] = "m.title LIKE :q";
        $executeParams[':q'] = '%' . $params['q'] . '%';
    }

    if (!empty($params['type'])) {
        $whereClauses[] = "m.type = :type";
        $executeParams[':type'] = $params['type'];
    }

    if (!empty($params['genre_id'])) {
        $whereClauses[] = "mg.genre_id = :genre_id";
        $executeParams[':genre_id'] = $params['genre_id'];
    }

    if (!empty($params['country'])) {
        $whereClauses[] = "c.code = :country_code";
        $executeParams[':country_code'] = $params['country'];
    }

    if (!empty($params['release_year'])) {
        $whereClauses[] = "m.release_year = :release_year";
        $executeParams[':release_year'] = $params['release_year'];
    }

    if (count($whereClauses) > 0) {
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($executeParams);
        return (int) $stmt->fetchColumn(); 
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return 0;
    }
}

// Lay danh sach the loai
function getAllGenres() {
    $pdo = getDatabaseConnection();

    try {
        $stmt = $pdo->query("
        SELECT id, name
        FROM genres 
        ORDER BY name ASC
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

// Lay danh sach quoc gia
function getAllCountriesFromDB() {
    $pdo = getDatabaseConnection();
    try {
        $stmt = $pdo->query("
        SELECT id, code, name
        FROM countries 
        ORDER BY name ASC
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}