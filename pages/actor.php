<?php
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/actor.css">
<section class="actor-section">
    <h2>Diễn viên nổi bật</h2>
    <div class="actor-status" id="actorStatus" role="status" aria-live="polite"></div>
    <div class="actor-grid" id="actorList"></div>
</section>

<script src="/assets/js/actor.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
