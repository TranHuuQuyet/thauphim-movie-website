<?php
session_start();
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/db.php";

$_auth_needed = true;
require_once __DIR__ . "/../auth_check.php";

try {
    $pdo = getDatabaseConnection();

    // Get movie ID from request
    $movie_id = (int)filter_input(INPUT_GET, 'movie_id', FILTER_VALIDATE_INT);
    
    if (!$movie_id) {
        die("ID phim không hợp lệ");
    }

    // Get movie details
    $movie_stmt = $pdo->prepare("
        SELECT id, title, status, release_date FROM movies WHERE id = :id
    ");
    $movie_stmt->execute(['id' => $movie_id]);
    $movie = $movie_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$movie) {
        die("Phim không tồn tại");
    }

    // Get existing schedules
    $schedules_stmt = $pdo->prepare("
        SELECT id, release_date, note, is_published FROM schedules 
        WHERE movie_id = :movie_id
        ORDER BY release_date DESC
    ");
    $schedules_stmt->execute(['movie_id' => $movie_id]);
    $schedules = $schedules_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Lỗi: " . $e->getMessage());
}

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_schedule') {
            $release_date = $_POST['release_date'] ?? '';
            $note = $_POST['note'] ?? '';

            if (empty($release_date)) {
                $error = 'Vui lòng nhập ngày phát hành';
            } else {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO schedules (movie_id, release_date, note, is_published)
                        VALUES (:movie_id, :release_date, :note, :is_published)
                    ");
                    $stmt->execute([
                        'movie_id' => $movie_id,
                        'release_date' => $release_date,
                        'note' => $note ?: null,
                        'is_published' => 1
                    ]);
                    $message = 'Thêm lịch phát hành thành công';
                    
                    // Refresh schedules
                    $schedules_stmt = $pdo->prepare("
                        SELECT id, release_date, note, is_published FROM schedules 
                        WHERE movie_id = :movie_id
                        ORDER BY release_date DESC
                    ");
                    $schedules_stmt->execute(['movie_id' => $movie_id]);
                    $schedules = $schedules_stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $error = 'Lỗi: ' . $e->getMessage();
                }
            }
        } elseif ($_POST['action'] === 'update_status') {
            $status = $_POST['status'] ?? 'completed';
            
            if (!in_array($status, ['coming_soon', 'ongoing', 'completed'])) {
                $error = 'Trạng thái không hợp lệ';
            } else {
                try {
                    $stmt = $pdo->prepare("
                        UPDATE movies SET status = :status WHERE id = :id
                    ");
                    $stmt->execute([
                        'status' => $status,
                        'id' => $movie_id
                    ]);
                    $message = 'Cập nhật trạng thái thành công';
                    $movie['status'] = $status;
                } catch (Exception $e) {
                    $error = 'Lỗi: ' . $e->getMessage();
                }
            }
        }
    }
}
?>

<?php include __DIR__ . "/../layout_header.php"; ?>

<div class="admin-container">
    <?php include __DIR__ . "/../layout_sidebar.php"; ?>

    <main class="admin-content">
        <div class="page-header">
            <h1>Quản Lý Phim Sắp Chiếu</h1>
            <a href="/admin/movies/index.php" class="btn btn-secondary">← Quay lại</a>
        </div>

        <div class="movie-details-card">
            <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
            <p>
                <strong>Trạng thái:</strong>
                <span class="status-badge status-<?php echo $movie['status']; ?>">
                    <?php
                    $status_names = [
                        'coming_soon' => 'Sắp chiếu',
                        'ongoing' => 'Đang chiếu',
                        'completed' => 'Đã hoàn thành'
                    ];
                    echo $status_names[$movie['status']] ?? $movie['status'];
                    ?>
                </span>
            </p>
            <p><strong>Ngày phát hành:</strong> <?php echo date('d/m/Y', strtotime($movie['release_date'])); ?></p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Update Status Section -->
        <div class="card">
            <h3>Cập Nhật Trạng Thái</h3>
            <form method="POST" class="form-group">
                <input type="hidden" name="action" value="update_status">
                <div class="form-field">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="coming_soon" <?php echo $movie['status'] === 'coming_soon' ? 'selected' : ''; ?>>
                            Sắp chiếu
                        </option>
                        <option value="ongoing" <?php echo $movie['status'] === 'ongoing' ? 'selected' : ''; ?>>
                            Đang chiếu
                        </option>
                        <option value="completed" <?php echo $movie['status'] === 'completed' ? 'selected' : ''; ?>>
                            Đã hoàn thành
                        </option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </form>
        </div>

        <!-- Add Schedule Section -->
        <div class="card">
            <h3>Thêm Lịch Phát Hành</h3>
            <form method="POST" class="form-group">
                <input type="hidden" name="action" value="add_schedule">
                <div class="form-field">
                    <label for="release_date">Ngày phát hành:</label>
                    <input type="date" id="release_date" name="release_date" class="form-control" required>
                </div>
                <div class="form-field">
                    <label for="note">Ghi chú:</label>
                    <input type="text" id="note" name="note" class="form-control" placeholder="VD: Phát hành tại rạp chiếu">
                </div>
                <button type="submit" class="btn btn-primary">Thêm lịch</button>
            </form>
        </div>

        <!-- Schedules List -->
        <div class="card">
            <h3>Danh Sách Lịch Phát Hành</h3>
            <?php if (empty($schedules)): ?>
                <p class="text-muted">Chưa có lịch phát hành nào</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ngày phát hành</th>
                            <th>Ghi chú</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($schedule['release_date'])); ?></td>
                                <td><?php echo htmlspecialchars($schedule['note'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge <?php echo $schedule['is_published'] ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo $schedule['is_published'] ? 'Công bố' : 'Nháp'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/admin/schedules/delete.php?id=<?php echo $schedule['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                                        Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include __DIR__ . "/../layout_footer.php"; ?>
