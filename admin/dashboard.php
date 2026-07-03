<?php

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

$totalMovies = (int) $pdo->query('SELECT COUNT(*) FROM movies')->fetchColumn();
$totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalComments = (int) $pdo->query('SELECT COUNT(*) FROM comments')->fetchColumn();
$totalEpisodes = (int) $pdo->query('SELECT COUNT(*) FROM episodes')->fetchColumn();
$totalPublishedEpisodes = (int) $pdo->query('SELECT COUNT(*) FROM episodes WHERE is_published = 1')->fetchColumn();
$totalViews = (int) $pdo->query('SELECT COALESCE(SUM(views), 0) FROM movies')->fetchColumn();

$totalWatchErrors = (int) $pdo->query("
    SELECT COUNT(*)
    FROM comments
    WHERE content LIKE '[Error]%'
")->fetchColumn();

$totalFixedWatchErrors = (int) $pdo->query("
    SELECT COUNT(*)
    FROM comments
    WHERE content LIKE '[Error]%'
        AND content LIKE '%[Fixed]%'
")->fetchColumn();

$totalOpenWatchErrors = max(0, $totalWatchErrors - $totalFixedWatchErrors);

$latestOpenWatchErrors = $pdo->query("
    SELECT
        comments.id,
        comments.content,
        comments.status AS comment_status,
        comments.created_at,
        users.username,
        movies.title AS movie_title
    FROM comments
    INNER JOIN users ON comments.user_id = users.id
    INNER JOIN movies ON comments.movie_id = movies.id
    WHERE comments.content LIKE '[Error]%'
        AND comments.content NOT LIKE '%[Fixed]%'
    ORDER BY comments.created_at DESC
    LIMIT 2
")->fetchAll();

$latestMovies = $pdo->query('
    SELECT id, title, release_year, type, views
    FROM movies
    ORDER BY id ASC
    LIMIT 5
')->fetchAll();

$topViewedMovies = $pdo->query('
    SELECT id, title, views AS total
    FROM movies
    WHERE views > 0
    ORDER BY views DESC, title ASC, id ASC
    LIMIT 10
')->fetchAll();

$topFavoriteMovies = $pdo->query('
    SELECT movies.id, movies.title, COUNT(favorites.id) AS total
    FROM favorites
    INNER JOIN movies ON movies.id = favorites.movie_id
    GROUP BY movies.id, movies.title
    ORDER BY total DESC, movies.title ASC, movies.id ASC
    LIMIT 10
')->fetchAll();

$topCommentedMovies = $pdo->query("
    SELECT movies.id, movies.title, COUNT(comments.id) AS total
    FROM comments
    INNER JOIN movies ON movies.id = comments.movie_id
    WHERE comments.status = 'visible'
    GROUP BY movies.id, movies.title
    ORDER BY total DESC, movies.title ASC, movies.id ASC
    LIMIT 10
")->fetchAll();

$moviesByType = $pdo->query('
    SELECT type AS label, COUNT(*) AS total
    FROM movies
    GROUP BY type
    ORDER BY type ASC
')->fetchAll();

$usersByMembership = $pdo->query('
    SELECT membership AS label, COUNT(*) AS total
    FROM users
    GROUP BY membership
    ORDER BY membership ASC
')->fetchAll();

$moviesByCountry = $pdo->query('
    SELECT countries.name AS label, COUNT(movies.id) AS total
    FROM countries
    INNER JOIN movies ON movies.country_id = countries.id
    GROUP BY countries.id, countries.name
    ORDER BY total DESC, countries.name ASC
    LIMIT 8
')->fetchAll();

$chartData = [
    'topViews' => [
        'labels' => array_column($topViewedMovies, 'title'),
        'values' => array_map('intval', array_column($topViewedMovies, 'total')),
    ],
    'topFavorites' => [
        'labels' => array_column($topFavoriteMovies, 'title'),
        'values' => array_map('intval', array_column($topFavoriteMovies, 'total')),
    ],
    'topComments' => [
        'labels' => array_column($topCommentedMovies, 'title'),
        'values' => array_map('intval', array_column($topCommentedMovies, 'total')),
    ],
    'movieTypes' => [
        'labels' => array_column($moviesByType, 'label'),
        'values' => array_map('intval', array_column($moviesByType, 'total')),
    ],
    'memberships' => [
        'labels' => array_column($usersByMembership, 'label'),
        'values' => array_map('intval', array_column($usersByMembership, 'total')),
    ],
    'countries' => [
        'labels' => array_column($moviesByCountry, 'label'),
        'values' => array_map('intval', array_column($moviesByCountry, 'total')),
    ],
];

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/layout_header.php';
include __DIR__ . '/layout_sidebar.php';
?>
<main class="admin-content">
    <div class="page-header">
        <h1>Dashboard</h1>
    </div>

    <div class="dashboard-cards">
        <div class="dashboard-card">
            <span>Total movies</span>
            <h2><?= $totalMovies ?></h2>
        </div>
        <div class="dashboard-card">
            <span>Users</span>
            <h2><?= $totalUsers ?></h2>
        </div>
        <div class="dashboard-card">
            <span>Comments</span>
            <h2><?= $totalComments ?></h2>
        </div>
        <div class="dashboard-card">
            <span>Views</span>
            <h2><?= number_format($totalViews) ?></h2>
        </div>
        <div class="dashboard-card">
            <span>Episodes</span>
            <h2><?= $totalEpisodes ?></h2>
        </div>
        <div class="dashboard-card">
            <span>Published episodes</span>
            <h2><?= $totalPublishedEpisodes ?></h2>
        </div>

        <div class="dashboard-card">
            <span>Watch errors</span>
            <h2><?= number_format($totalOpenWatchErrors) ?></h2>
        </div>

        <div class="dashboard-card">
            <span>Fixed errors</span>
            <h2><?= number_format($totalFixedWatchErrors) ?></h2>
        </div>
    </div>

    <section class="admin-table dashboard-watch-errors" id="watch-errors">
        <div class="admin-section-heading">
            <div>
                <h2>Latest watch errors</h2>
            </div>

            <a class="btn btn-secondary" href="<?= admin_url('watch-errors/watch-errors.php') ?>">
                View all
            </a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Movie</th>
                    <th>Status</th>
                    <th>Content</th>
                    <th>Created at</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($latestOpenWatchErrors)): ?>
                    <tr>
                        <td colspan="7" class="muted">No open watch errors found.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($latestOpenWatchErrors as $error): ?>
                    <tr>
                        <td><?= (int) $error['id'] ?></td>
                        <td><?= admin_e($error['username']) ?></td>
                        <td><?= admin_e($error['movie_title']) ?></td>
                        <td><span class="badge badge-warning">Open</span></td>
                        <td class="watch-error-preview"><?= admin_e($error['content']) ?></td>
                        <td><?= admin_e($error['created_at']) ?></td>

                        <td>
                            <form class="inline-form" method="post"
                                action="<?= admin_url('watch-errors/update-errors.php') ?>">
                                <?= admin_csrf_input() ?>
                                <input type="hidden" name="id" value="<?= (int) $error['id'] ?>">
                                <input type="hidden" name="action" value="fix">
                                <input type="hidden" name="return" value="dashboard.php#watch-errors">
                                <button class="btn btn-info" type="submit">Mark as fixed</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="dashboard-charts dashboard-charts--rankings" aria-label="Top movie charts">
        <article class="chart-card chart-card--views">
            <div class="chart-card__header">
                <div>
                    <span>Audience activity</span>
                    <h2>Top movies by views</h2>
                </div>
                <i class="fa-solid fa-eye" aria-hidden="true"></i>
            </div>
            <div class="chart-container chart-container--ranking">
                <canvas id="topViewsChart"></canvas>
                <p class="chart-empty-state" id="topViewsChartStatus" hidden></p>
            </div>
        </article>

        <article class="chart-card chart-card--favorites">
            <div class="chart-card__header">
                <div>
                    <span>Viewer interest</span>
                    <h2>Top movies by favorites</h2>
                </div>
                <i class="fa-solid fa-heart" aria-hidden="true"></i>
            </div>
            <div class="chart-container chart-container--ranking">
                <canvas id="topFavoritesChart"></canvas>
                <p class="chart-empty-state" id="topFavoritesChartStatus" hidden></p>
            </div>
        </article>

        <article class="chart-card chart-card--comments chart-card--wide">
            <div class="chart-card__header">
                <div>
                    <span>Community discussion</span>
                    <h2>Top movies by visible comments</h2>
                </div>
                <i class="fa-solid fa-comments" aria-hidden="true"></i>
            </div>
            <div class="chart-container chart-container--ranking">
                <canvas id="topCommentsChart"></canvas>
                <p class="chart-empty-state" id="topCommentsChartStatus" hidden></p>
            </div>
        </article>
    </section>

    <section class="dashboard-charts" aria-label="Dashboard charts">
        <article class="chart-card chart-card--types">
            <div class="chart-card__header">
                <div>
                    <span>Content library</span>
                    <h2>Movies by type</h2>
                </div>
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div class="chart-container chart-container--doughnut">
                <canvas id="movieTypesChart"></canvas>
            </div>
        </article>

        <article class="chart-card chart-card--memberships">
            <div class="chart-card__header">
                <div>
                    <span>Audience</span>
                    <h2>Users by membership</h2>
                </div>
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="chart-container chart-container--doughnut">
                <canvas id="membershipsChart"></canvas>
            </div>
        </article>

        <article class="chart-card chart-card--countries chart-card--wide">
            <div class="chart-card__header">
                <div>
                    <span>Catalog coverage</span>
                    <h2>Movies by country</h2>
                </div>
                <i class="fa-solid fa-chart-column"></i>
            </div>
            <div class="chart-container">
                <canvas id="countriesChart"></canvas>
            </div>
        </article>
    </section>

    <section class="admin-table">
        <h2>Movies</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Year</th>
                    <th>Type</th>
                    <th>Views</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latestMovies as $movie): ?>
                    <tr>
                        <td><?= (int) $movie['id'] ?></td>
                        <td><?= admin_e($movie['title']) ?></td>
                        <td><?= admin_e($movie['release_year'] ?? '') ?></td>
                        <td><?= admin_e($movie['type']) ?></td>
                        <td><?= number_format((int) $movie['views']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
<script>
    const dashboardChartData = <?= json_encode(
        $chartData,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;

    const setChartStatus = (canvasId, statusId, message) => {
        const canvas = document.getElementById(canvasId);
        const status = document.getElementById(statusId);

        if (canvas) {
            canvas.hidden = true;
        }

        if (status) {
            status.textContent = message;
            status.hidden = false;
        }
    };

    if (typeof Chart === 'undefined') {
        [
            ['topViewsChart', 'topViewsChartStatus'],
            ['topFavoritesChart', 'topFavoritesChartStatus'],
            ['topCommentsChart', 'topCommentsChartStatus'],
        ].forEach(([canvasId, statusId]) => {
            setChartStatus(canvasId, statusId, 'Chart.js could not be loaded. Please refresh the page.');
        });
    } else {
    Chart.defaults.color = '#667085';
    Chart.defaults.font.family = 'Arial, Helvetica, sans-serif';
    Chart.defaults.animation.duration = 700;
    Chart.defaults.animation.easing = 'easeOutQuart';
    Chart.defaults.plugins.tooltip.backgroundColor = '#111827';
    Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
    Chart.defaults.plugins.tooltip.bodyColor = '#e5e7eb';
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.displayColors = true;

    const centerTotalPlugin = {
        id: 'centerTotal',
        afterDraw(chart, args, options) {
            if (chart.config.type !== 'doughnut') {
                return;
            }

            const { ctx, chartArea } = chart;
            const total = chart.data.datasets[0].data.reduce((sum, value) => sum + Number(value), 0);
            const centerX = (chartArea.left + chartArea.right) / 2;
            const centerY = (chartArea.top + chartArea.bottom) / 2;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#101828';
            ctx.font = '700 26px Arial';
            ctx.fillText(total.toLocaleString(), centerX, centerY - 7);
            ctx.fillStyle = '#98a2b3';
            ctx.font = '12px Arial';
            ctx.fillText(options.label || 'Total', centerX, centerY + 16);
            ctx.restore();
        }
    };

    const doughnutOptions = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        layout: {
            padding: 6
        },
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 9,
                    boxHeight: 9,
                    padding: 16,
                    usePointStyle: true,
                    font: { weight: '600' }
                }
            }
        }
    };

    const createRankingChart = ({
        canvasId,
        statusId,
        data,
        label,
        color,
        unit
    }) => {
        if (!data.labels.length) {
            setChartStatus(canvasId, statusId, `No ${unit} data available yet.`);
            return;
        }

        new Chart(document.getElementById(canvasId), {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label,
                    data: data.values,
                    backgroundColor: color,
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 24
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: items => items[0]?.label || '',
                            label: context => ` ${Number(context.raw).toLocaleString()} ${unit}`
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            callback: value => Number(value).toLocaleString()
                        },
                        grid: { color: '#eef2f6' },
                        border: { display: false }
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#344054',
                            font: { weight: '600' },
                            callback(value) {
                                const title = this.getLabelForValue(value);
                                return title.length > 28 ? `${title.slice(0, 28)}...` : title;
                            }
                        }
                    }
                }
            }
        });
    };

    createRankingChart({
        canvasId: 'topViewsChart',
        statusId: 'topViewsChartStatus',
        data: dashboardChartData.topViews,
        label: 'Views',
        color: '#2563eb',
        unit: 'views'
    });

    createRankingChart({
        canvasId: 'topFavoritesChart',
        statusId: 'topFavoritesChartStatus',
        data: dashboardChartData.topFavorites,
        label: 'Favorites',
        color: '#dc6803',
        unit: 'favorites'
    });

    createRankingChart({
        canvasId: 'topCommentsChart',
        statusId: 'topCommentsChartStatus',
        data: dashboardChartData.topComments,
        label: 'Comments',
        color: '#039855',
        unit: 'comments'
    });

    new Chart(document.getElementById('movieTypesChart'), {
        type: 'doughnut',
        data: {
            labels: dashboardChartData.movieTypes.labels,
            datasets: [{
                data: dashboardChartData.movieTypes.values,
                backgroundColor: ['#0f62fe', '#f4c430', '#12b76a', '#7f56d9'],
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverOffset: 6
            }]
        },
        options: {
            ...doughnutOptions,
            plugins: {
                ...doughnutOptions.plugins,
                centerTotal: { label: 'Titles' }
            }
        },
        plugins: [centerTotalPlugin]
    });

    new Chart(document.getElementById('membershipsChart'), {
        type: 'doughnut',
        data: {
            labels: dashboardChartData.memberships.labels,
            datasets: [{
                data: dashboardChartData.memberships.values,
                backgroundColor: ['#98a2b3', '#f79009', '#12b76a'],
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverOffset: 6
            }]
        },
        options: {
            ...doughnutOptions,
            plugins: {
                ...doughnutOptions.plugins,
                centerTotal: { label: 'Users' }
            }
        },
        plugins: [centerTotalPlugin]
    });

    new Chart(document.getElementById('countriesChart'), {
        type: 'bar',
        data: {
            labels: dashboardChartData.countries.labels,
            datasets: [{
                label: 'Movies',
                data: dashboardChartData.countries.values,
                backgroundColor: [
                    '#2563eb', '#3b82f6', '#0ea5e9', '#06b6d4',
                    '#14b8a6', '#10b981', '#22c55e', '#84cc16'
                ],
                borderWidth: 0,
                borderRadius: 7,
                borderSkipped: false,
                maxBarThickness: 32
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => ` ${Number(context.raw).toLocaleString()} movies`
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: '#eef2f6' },
                    border: { display: false }
                },
                y: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        color: '#344054',
                        font: { weight: '600' },
                        callback(value) {
                            const label = this.getLabelForValue(value);
                            return label.length > 28 ? label.slice(0, 28) + '…' : label;
                        }
                    }
                }
            }
        }
    });
    }
</script>
<?php include __DIR__ . '/layout_footer.php'; ?>
