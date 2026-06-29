<?php
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/actor.css">
<main class="page-shell" aria-label="Danh sách diễn viên">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 40px 15px;">
        
        <section class="actor-header-section" style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h2 style="margin: 0; font-size: 24px; color: #fff;">Diễn viên nổi bật</h2>
            
            <div class="search-box" style="position: relative; min-width: 280px;">
                <input type="text" id="actorSearchInput" placeholder="Tìm tên diễn viên..." 
                       style="width: 100%; padding: 10px 15px; background: #1a1a1a; border: 1px solid #333; border-radius: 4px; color: #fff; font-size: 14px;">
            </div>
        </section>

        <div class="actor-status" id="actorStatus" role="status" aria-live="polite" style="text-align: center; color: #aaa; margin: 20px 0;"></div>
        
        <section class="actor-grid-section">
            <div class="actor-grid" id="actorList"></div>
        </section>

        <nav class="pagination-container" id="actorPagination" aria-label="Phân trang diễn viên" 
             style="margin-top: 40px; display: flex; justify-content: center;">
        </nav>

    </div>
</main>

<script src="/assets/js/actor.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>

