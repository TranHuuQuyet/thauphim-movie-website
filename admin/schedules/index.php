<?php
declare(strict_types=1);

require_once __DIR__ . '/../_helpers.php';

function schedules_query(array $params): string
{
    $params = array_filter(
        $params,
        static fn(mixed $value): bool => $value !== null && $value !== ''
    );

    return empty($params) ? '' : '?' . http_build_query($params);
}

function schedules_index_url(array $params = []): string
{
    return admin_url('schedules/index.php' . schedules_query($params));
}

function schedules_redirect(array $params = []): never
{
    admin_redirect('schedules/index.php' . schedules_query($params));
}

function schedules_valid_date(string $date): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $dateObject = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

    return $dateObject instanceof DateTimeImmutable && $dateObject->format('Y-m-d') === $date;
}

function schedules_normalize_time(string $time, array &$errors): ?string
{
    $time = trim($time);

    if ($time === '') {
        return null;
    }

    if (!preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $matches)) {
        $errors[] = 'Giờ chiếu không hợp lệ.';
        return null;
    }

    $hour = (int) $matches[1];
    $minute = (int) $matches[2];
    $second = isset($matches[3]) ? (int) $matches[3] : 0;

    if ($hour > 23 || $minute > 59 || $second > 59) {
        $errors[] = 'Giờ chiếu không hợp lệ.';
        return null;
    }

    return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
}

function schedules_format_date(?string $date): string
{
    $date = trim((string) $date);

    if ($date === '') {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp === false ? $date : date('d/m/Y', $timestamp);
}

function schedules_format_time(?string $time): string
{
    $time = trim((string) $time);

    return $time === '' ? '-' : substr($time, 0, 5);
}

$filterMovieId = admin_nullable_int($_GET['movie_id'] ?? null);
if ($filterMovieId !== null && $filterMovieId <= 0) {
    $filterMovieId = null;
}

$statusFilter = (string) ($_GET['status'] ?? '');
if (!in_array($statusFilter, ['published', 'draft'], true)) {
    $statusFilter = '';
}

$dateScope = (string) ($_GET['date_scope'] ?? 'all');
if (!in_array($dateScope, ['all', 'upcoming', 'past'], true)) {
    $dateScope = 'all';
}

$filterParams = [];
if ($filterMovieId !== null) {
    $filterParams['movie_id'] = $filterMovieId;
}
if ($statusFilter !== '') {
    $filterParams['status'] = $statusFilter;
}
if ($dateScope !== 'all') {
    $filterParams['date_scope'] = $dateScope;
}

$movies = $pdo->query('
    SELECT id, title
    FROM movies
    ORDER BY title ASC
')->fetchAll();
$moviesById = [];
foreach ($movies as $movie) {
    $moviesById[(int) $movie['id']] = (string) $movie['title'];
}

$errors = [];
$successMap = [
    'created' => 'Thêm lịch chiếu thành công.',
    'updated' => 'Cập nhật lịch chiếu thành công.',
    'deleted' => 'Xóa lịch chiếu thành công.',
];
$success = $successMap[(string) ($_GET['success'] ?? '')] ?? '';
$submittedScheduleForm = false;
$editingScheduleId = admin_get_id($_GET);
$formSchedule = [
    'id' => 0,
    'movie_id' => $filterMovieId ?? '',
    'release_date' => '',
    'show_time' => '',
    'note' => '',
    'is_published' => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_verify_csrf();

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'save_schedule') {
        $submittedScheduleForm = true;
        $scheduleId = admin_get_id(['id' => $_POST['schedule_id'] ?? null]);
        $movieId = $filterMovieId ?? admin_nullable_int($_POST['movie_id'] ?? null);
        $releaseDate = trim((string) ($_POST['release_date'] ?? ''));
        $showTime = schedules_normalize_time((string) ($_POST['show_time'] ?? ''), $errors);
        $note = trim((string) ($_POST['note'] ?? ''));
        $isPublished = (string) ($_POST['is_published'] ?? '0') === '1' ? 1 : 0;

        $formSchedule = [
            'id' => $scheduleId,
            'movie_id' => $movieId ?? '',
            'release_date' => $releaseDate,
            'show_time' => $showTime !== null ? substr($showTime, 0, 5) : trim((string) ($_POST['show_time'] ?? '')),
            'note' => $note,
            'is_published' => $isPublished,
        ];

        if ($movieId === null || $movieId <= 0 || !isset($moviesById[$movieId])) {
            $errors[] = 'Vui lòng chọn phim hợp lệ.';
        }

        if (!schedules_valid_date($releaseDate)) {
            $errors[] = 'Ngày chiếu phải đúng định dạng YYYY-MM-DD.';
        }

        if ($scheduleId > 0) {
            $stmt = $pdo->prepare('SELECT id FROM schedules WHERE id = ? LIMIT 1');
            $stmt->execute([$scheduleId]);

            if (!$stmt->fetch()) {
                $errors[] = 'Không tìm thấy lịch chiếu cần sửa.';
            }
        }

        if (empty($errors)) {
            if ($scheduleId > 0) {
                $stmt = $pdo->prepare('
                    UPDATE schedules
                    SET movie_id = ?, release_date = ?, show_time = ?, note = ?, is_published = ?
                    WHERE id = ?
                ');
                $stmt->execute([
                    $movieId,
                    $releaseDate,
                    $showTime,
                    $note !== '' ? $note : null,
                    $isPublished,
                    $scheduleId,
                ]);

                $redirectParams = $filterParams;
                $redirectParams['success'] = 'updated';
                schedules_redirect($redirectParams);
            }

            $stmt = $pdo->prepare('
                INSERT INTO schedules (movie_id, release_date, show_time, note, is_published)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $movieId,
                $releaseDate,
                $showTime,
                $note !== '' ? $note : null,
                $isPublished,
            ]);

            $redirectParams = $filterParams;
            $redirectParams['success'] = 'created';
            schedules_redirect($redirectParams);
        }
    } elseif ($action === 'delete_schedule') {
        $scheduleId = admin_get_id($_POST);

        if ($scheduleId > 0) {
            $stmt = $pdo->prepare('DELETE FROM schedules WHERE id = ?');
            $stmt->execute([$scheduleId]);
        }

        $redirectParams = $filterParams;
        $redirectParams['success'] = 'deleted';
        schedules_redirect($redirectParams);
    } else {
        $errors[] = 'Thao tác không hợp lệ.';
    }
}

if (!$submittedScheduleForm && $editingScheduleId > 0) {
    $stmt = $pdo->prepare('
        SELECT id, movie_id, release_date, show_time, note, is_published
        FROM schedules
        WHERE id = ?
        LIMIT 1
    ');
    $stmt->execute([$editingScheduleId]);
    $editSchedule = $stmt->fetch();

    if ($editSchedule) {
        if ($filterMovieId !== null && (int) $editSchedule['movie_id'] !== $filterMovieId) {
            $errors[] = 'Lịch chiếu không thuộc phim đang lọc.';
        } else {
            $formSchedule = [
                'id' => (int) $editSchedule['id'],
                'movie_id' => (int) $editSchedule['movie_id'],
                'release_date' => (string) $editSchedule['release_date'],
                'show_time' => schedules_format_time($editSchedule['show_time'] ?? null) !== '-'
                    ? schedules_format_time($editSchedule['show_time'] ?? null)
                    : '',
                'note' => (string) ($editSchedule['note'] ?? ''),
                'is_published' => !empty($editSchedule['is_published']) ? 1 : 0,
            ];
        }
    } else {
        $errors[] = 'Không tìm thấy lịch chiếu cần sửa.';
    }
}

$where = [];
$params = [];

if ($filterMovieId !== null) {
    $where[] = 'schedules.movie_id = ?';
    $params[] = $filterMovieId;
}

if ($statusFilter === 'published') {
    $where[] = 'schedules.is_published = 1';
} elseif ($statusFilter === 'draft') {
    $where[] = 'schedules.is_published = 0';
}

if ($dateScope === 'upcoming') {
    $where[] = 'schedules.release_date >= CURDATE()';
} elseif ($dateScope === 'past') {
    $where[] = 'schedules.release_date < CURDATE()';
}

$whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
$stmt = $pdo->prepare("
    SELECT
        schedules.id,
        schedules.movie_id,
        schedules.release_date,
        schedules.show_time,
        schedules.note,
        schedules.is_published,
        movies.title AS movie_title,
        movies.status AS movie_status
    FROM schedules
    INNER JOIN movies ON movies.id = schedules.movie_id
    {$whereSql}
    ORDER BY schedules.id ASC
");
$stmt->execute($params);
$schedules = $stmt->fetchAll();

$pageTitle = 'Admin Lịch chiếu';
include __DIR__ . '/../layout_header.php';
include __DIR__ . '/../layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Lịch chiếu</h1>
        <a class="btn btn-secondary" href="<?= admin_e(admin_url('movies/index.php')) ?>">Movies</a>
    </div>

    <?php admin_render_messages($errors, $success); ?>

    <form class="admin-form" method="get" action="<?= admin_e(admin_url('schedules/index.php')) ?>">
        <div class="form-grid">
            <div class="form-row">
                <label for="filter_movie_id">Phim</label>
                <select id="filter_movie_id" name="movie_id">
                    <option value="">Tất cả phim</option>
                    <?php foreach ($movies as $movie): ?>
                        <option value="<?= (int) $movie['id'] ?>" <?= $filterMovieId === (int) $movie['id'] ? 'selected' : '' ?>>
                            <?= admin_e($movie['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label for="filter_status">Trạng thái</label>
                <select id="filter_status" name="status">
                    <option value="">Tất cả</option>
                    <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Công bố</option>
                    <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Nháp</option>
                </select>
            </div>
            <div class="form-row">
                <label for="filter_date_scope">Thời gian</label>
                <select id="filter_date_scope" name="date_scope">
                    <option value="all" <?= $dateScope === 'all' ? 'selected' : '' ?>>Tất cả</option>
                    <option value="upcoming" <?= $dateScope === 'upcoming' ? 'selected' : '' ?>>Từ hôm nay</option>
                    <option value="past" <?= $dateScope === 'past' ? 'selected' : '' ?>>Đã qua</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit"><i class="fa-solid fa-filter"></i> Lọc</button>
            <a class="btn btn-secondary" href="<?= admin_e(admin_url('schedules/index.php')) ?>">Reset</a>
        </div>
    </form>

    <form class="admin-form" method="post" action="<?= admin_e(schedules_index_url($filterParams)) ?>" style="margin-top: 18px;">
        <?= admin_csrf_input() ?>
        <input type="hidden" name="action" value="save_schedule">
        <input type="hidden" name="schedule_id" value="<?= (int) $formSchedule['id'] ?>">

        <h2><?= (int) $formSchedule['id'] > 0 ? 'Sửa lịch chiếu' : 'Thêm lịch chiếu' ?></h2>
        <div class="form-grid">
            <div class="form-row">
                <label for="movie_id">Phim</label>
                <?php if ($filterMovieId !== null): ?>
                    <input type="hidden" name="movie_id" value="<?= (int) $filterMovieId ?>">
                    <input id="movie_id" type="text" value="<?= admin_e($moviesById[$filterMovieId] ?? 'Phim không tồn tại') ?>" disabled>
                <?php else: ?>
                    <select id="movie_id" name="movie_id" required>
                        <option value="">Chọn phim</option>
                        <?php foreach ($movies as $movie): ?>
                            <option value="<?= (int) $movie['id'] ?>" <?= (int) $formSchedule['movie_id'] === (int) $movie['id'] ? 'selected' : '' ?>>
                                <?= admin_e($movie['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="form-row">
                <label for="release_date">Ngày chiếu</label>
                <input id="release_date" name="release_date" type="date" value="<?= admin_e($formSchedule['release_date']) ?>" required>
            </div>
            <div class="form-row">
                <label for="show_time">Giờ chiếu</label>
                <input id="show_time" name="show_time" type="time" step="60" value="<?= admin_e($formSchedule['show_time']) ?>">
            </div>
            <div class="form-row">
                <label for="is_published">Trạng thái</label>
                <select id="is_published" name="is_published">
                    <option value="1" <?= !empty($formSchedule['is_published']) ? 'selected' : '' ?>>Công bố</option>
                    <option value="0" <?= empty($formSchedule['is_published']) ? 'selected' : '' ?>>Nháp</option>
                </select>
            </div>
            <div class="form-row form-row--full">
                <label for="note">Ghi chú</label>
                <input id="note" name="note" type="text" value="<?= admin_e($formSchedule['note']) ?>" placeholder="VD: Phát hành tại rạp chiếu">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit">
                <i class="fa-solid fa-floppy-disk"></i>
                <?= (int) $formSchedule['id'] > 0 ? 'Cập nhật' : 'Thêm lịch' ?>
            </button>
            <?php if ((int) $formSchedule['id'] > 0): ?>
                <a class="btn btn-secondary" href="<?= admin_e(schedules_index_url($filterParams)) ?>">Hủy sửa</a>
            <?php endif; ?>
        </div>
    </form>

    <section class="admin-table" style="margin-top: 18px;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Phim</th>
                    <th>Ngày</th>
                    <th>Giờ</th>
                    <th>Ghi chú</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $schedule): ?>
                    <tr>
                        <td><?= (int) $schedule['id'] ?></td>
                        <td><?= admin_e($schedule['movie_title']) ?></td>
                        <td><?= admin_e(schedules_format_date($schedule['release_date'] ?? null)) ?></td>
                        <td><?= admin_e(schedules_format_time($schedule['show_time'] ?? null)) ?></td>
                        <td class="truncate-cell"><?= admin_e($schedule['note'] ?? '') ?></td>
                        <td>
                            <span class="badge <?= !empty($schedule['is_published']) ? 'badge-success' : 'badge-muted' ?>">
                                <?= !empty($schedule['is_published']) ? 'Công bố' : 'Nháp' ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-secondary" href="<?= admin_e(schedules_index_url(array_merge($filterParams, ['id' => (int) $schedule['id']]))) ?>">
                                    <i class="fa-solid fa-pen"></i> Sửa
                                </a>
                                <form class="inline-form" method="post" action="<?= admin_e(schedules_index_url($filterParams)) ?>" onsubmit="return confirm('Xóa lịch chiếu này?');">
                                    <?= admin_csrf_input() ?>
                                    <input type="hidden" name="action" value="delete_schedule">
                                    <input type="hidden" name="id" value="<?= (int) $schedule['id'] ?>">
                                    <button class="btn-danger" type="submit">
                                        <i class="fa-solid fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="7" class="muted">Chưa có lịch chiếu nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include __DIR__ . '/../layout_footer.php'; ?>
