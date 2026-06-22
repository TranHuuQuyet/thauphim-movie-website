<?php
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="/thauphim-movie-website/assets/css/actor.css">
<section class="actor-section">
    <h2>Diễn viên nổi bật</h2>
    <div class="actor-grid" id="actorList"></div>
</section>

<script>
const TMDB_API_KEY = "<?= TMDB_API_KEY ?>";
</script>

<script src="/thauphim-movie-website/assets/js/actor.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>