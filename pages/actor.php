<?php
require_once __DIR__ . '/../includes/config.php';

$pageStyles = ['assets/css/actor.css'];
$pageScripts = ['assets/js/actor.js'];

include __DIR__ . '/../includes/header.php';
?>

<main
    class="page-shell actor-page"
    aria-label="Danh sách diễn viên"
    data-actors-api="<?= htmlspecialchars(app_url('api/actors.php'), ENT_QUOTES, 'UTF-8') ?>"
>
    <div class="actor-container">
        <section class="actor-header-section" aria-labelledby="actorPageTitle">
            <h1 id="actorPageTitle">Diễn viên nổi bật</h1>

            <div class="actor-search">
                <label class="sr-only" for="actorSearchInput">Tìm tên diễn viên</label>
                <input
                    type="search"
                    id="actorSearchInput"
                    placeholder="Tìm tên diễn viên..."
                    autocomplete="off"
                >
            </div>
        </section>

        <div class="actor-status" id="actorStatus" role="status" aria-live="polite"></div>

        <section class="actor-grid-section" aria-label="Kết quả diễn viên">
            <div class="actor-grid" id="actorList"></div>
        </section>

        <nav class="pagination-container" id="actorPagination" aria-label="Phân trang diễn viên"></nav>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
