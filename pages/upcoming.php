<?php
session_start();
require_once __DIR__ . "/../includes/config.php";
?>
<?php include __DIR__ . "/../includes/header.php"; ?>
<link rel="stylesheet" href="/assets/css/movies.css">
<link rel="stylesheet" href="/assets/css/upcoming.css">

<main class="page-shell" id="upcoming" aria-label="Nội dung chính">
    <div class="page-container">
        <!-- Hero Section -->
        <section class="upcoming-hero">
            <div class="hero-content">
                <h1>Phim Sắp Chiếu</h1>
                <p>Khám phá những bộ phim và chương trình truyền hình sắp được phát hành</p>
            </div>
        </section>

        <!-- Filters Section -->
        <section class="filters-section">
            <div class="filters-container">
                <div class="filter-group">
                    <label for="search-upcoming">Tìm kiếm:</label>
                    <input 
                        type="text" 
                        id="search-upcoming" 
                        placeholder="Nhập tên phim..."
                        class="search-input"
                    >
                </div>

                <div class="filter-group">
                    <label for="filter-type">Loại:</label>
                    <select id="filter-type" class="filter-select">
                        <option value="">Tất cả</option>
                        <option value="movie">Phim</option>
                        <option value="series">Phim bộ</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter-genre">Thể loại:</label>
                    <select id="filter-genre" class="filter-select">
                        <option value="">Tất cả</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter-country">Quốc gia:</label>
                    <select id="filter-country" class="filter-select">
                        <option value="">Tất cả</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sort-by">Sắp xếp:</label>
                    <select id="sort-by" class="filter-select">
                        <option value="release_date">Ngày phát hành</option>
                        <option value="popularity">Phổ biến nhất</option>
                        <option value="title">Tên phim (A-Z)</option>
                        <option value="rating">Đánh giá cao nhất</option>
                    </select>
                </div>

                <button id="clear-filters" class="btn-clear">Xóa bộ lọc</button>
            </div>
        </section>

        <!-- Movies Grid -->
        <section class="movies-list-section">
            <div class="movies-grid" id="upcoming-movies">
                <!-- Movies will be loaded here -->
            </div>
        </section>

        <!-- Loading Spinner -->
        <div id="loading-spinner" class="loading-spinner" style="display: none;">
            <div class="spinner"></div>
            <p>Đang tải...</p>
        </div>

        <!-- Pagination -->
        <nav class="pagination" id="pagination" style="display: none;">
            <button id="prev-page" class="pagination-btn">← Trang trước</button>
            <div class="page-info">
                <span id="page-number">1</span> / <span id="total-pages">1</span>
            </div>
            <button id="next-page" class="pagination-btn">Trang sau →</button>
        </nav>

        <!-- Empty State -->
        <div id="empty-state" class="empty-state" style="display: none;">
            <p>Không có phim nào phù hợp với tiêu chí tìm kiếm của bạn</p>
        </div>
    </div>
</main>

<script>
let currentPage = 1;
let currentFilters = {
    search: '',
    type: '',
    genre_id: '',
    country: '',
    sort_by: 'release_date',
    order: 'ASC'
};

const elements = {
    moviesGrid: document.getElementById('upcoming-movies'),
    loadingSpinner: document.getElementById('loading-spinner'),
    pagination: document.getElementById('pagination'),
    emptyState: document.getElementById('empty-state'),
    searchInput: document.getElementById('search-upcoming'),
    filterType: document.getElementById('filter-type'),
    filterGenre: document.getElementById('filter-genre'),
    filterCountry: document.getElementById('filter-country'),
    sortBy: document.getElementById('sort-by'),
    clearFiltersBtn: document.getElementById('clear-filters'),
    prevPageBtn: document.getElementById('prev-page'),
    nextPageBtn: document.getElementById('next-page'),
    pageNumber: document.getElementById('page-number'),
    totalPages: document.getElementById('total-pages'),
};

// Load genres and countries on page load
async function initializeFilters() {
    try {
        // Load genres
        const genresRes = await fetch('/api/genres.php?limit=100');
        const genresData = await genresRes.json();
        if (genresData.success && genresData.data) {
            genresData.data.forEach(genre => {
                const option = document.createElement('option');
                option.value = genre.id;
                option.textContent = genre.name;
                elements.filterGenre.appendChild(option);
            });
        }

        // Load countries
        const countriesRes = await fetch('/api/countries.php?limit=100');
        const countriesData = await countriesRes.json();
        if (countriesData.success && countriesData.data) {
            countriesData.data.forEach(country => {
                const option = document.createElement('option');
                option.value = country.code;
                option.textContent = country.name;
                elements.filterCountry.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading filters:', error);
    }
}

// Load upcoming movies
async function loadUpcomingMovies(page = 1) {
    elements.loadingSpinner.style.display = 'flex';
    elements.emptyState.style.display = 'none';
    elements.pagination.style.display = 'none';
    elements.moviesGrid.innerHTML = '';

    try {
        const params = new URLSearchParams({
            page: page.toString(),
            limit: '20',
            ...currentFilters
        });

        const response = await fetch(`/api/upcoming-movies.php?${params}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Lỗi khi tải phim');
        }

        if (data.data.length === 0) {
            elements.emptyState.style.display = 'block';
        } else {
            renderMovies(data.data);
            updatePagination(data.meta);
        }
    } catch (error) {
        console.error('Error loading movies:', error);
        elements.emptyState.style.display = 'block';
        elements.emptyState.textContent = 'Có lỗi khi tải dữ liệu. Vui lòng thử lại.';
    } finally {
        elements.loadingSpinner.style.display = 'none';
    }
}

// Render movies grid
function renderMovies(movies) {
    elements.moviesGrid.innerHTML = movies.map(movie => `
        <article class="movie-card">
            <a href="/pages/movie-detail.php?id=${movie.id}" class="movie-card-link">
                <div class="movie-poster">
                    <img 
                        src="${movie.poster || '/assets/images/no-poster.png'}" 
                        alt="${movie.title}"
                        loading="lazy"
                    >
                    ${movie.is_premium ? '<span class="badge-premium">Premium</span>' : ''}
                    ${movie.rating > 0 ? `<span class="badge-rating">${movie.rating.toFixed(1)}</span>` : ''}
                </div>
                <div class="movie-info">
                    <h3 class="movie-title">${movie.title}</h3>
                    <p class="movie-meta">
                        <span>${movie.type === 'series' ? 'Phim bộ' : 'Phim'}</span>
                        ${movie.quality ? `<span>${movie.quality}</span>` : ''}
                    </p>
                    ${movie.upcoming_date ? `
                        <p class="movie-release">
                            <strong>Phát hành:</strong> ${new Date(movie.upcoming_date).toLocaleDateString('vi-VN')}
                        </p>
                    ` : ''}
                    ${movie.genres.length > 0 ? `
                        <p class="movie-genres">${movie.genres.slice(0, 2).join(', ')}</p>
                    ` : ''}
                </div>
            </a>
        </article>
    `).join('');
}

// Update pagination
function updatePagination(meta) {
    if (meta.total_pages > 1) {
        elements.pagination.style.display = 'flex';
        elements.pageNumber.textContent = meta.page;
        elements.totalPages.textContent = meta.total_pages;
        elements.prevPageBtn.disabled = meta.page === 1;
        elements.nextPageBtn.disabled = meta.page === meta.total_pages;
    }
}

// Event listeners
elements.searchInput.addEventListener('debounce', () => {
    currentFilters.search = elements.searchInput.value;
    currentPage = 1;
    loadUpcomingMovies(1);
});

// Debounce search
let searchTimeout;
elements.searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentFilters.search = elements.searchInput.value;
        currentPage = 1;
        loadUpcomingMovies(1);
    }, 500);
});

elements.filterType.addEventListener('change', () => {
    currentFilters.type = elements.filterType.value;
    currentPage = 1;
    loadUpcomingMovies(1);
});

elements.filterGenre.addEventListener('change', () => {
    currentFilters.genre_id = elements.filterGenre.value;
    currentPage = 1;
    loadUpcomingMovies(1);
});

elements.filterCountry.addEventListener('change', () => {
    currentFilters.country = elements.filterCountry.value;
    currentPage = 1;
    loadUpcomingMovies(1);
});

elements.sortBy.addEventListener('change', () => {
    currentFilters.sort_by = elements.sortBy.value;
    currentPage = 1;
    loadUpcomingMovies(1);
});

elements.clearFiltersBtn.addEventListener('click', () => {
    elements.searchInput.value = '';
    elements.filterType.value = '';
    elements.filterGenre.value = '';
    elements.filterCountry.value = '';
    elements.sortBy.value = 'release_date';
    currentFilters = {
        search: '',
        type: '',
        genre_id: '',
        country: '',
        sort_by: 'release_date',
        order: 'ASC'
    };
    currentPage = 1;
    loadUpcomingMovies(1);
});

elements.prevPageBtn.addEventListener('click', () => {
    if (currentPage > 1) {
        currentPage--;
        loadUpcomingMovies(currentPage);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});

elements.nextPageBtn.addEventListener('click', () => {
    currentPage++;
    loadUpcomingMovies(currentPage);
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    initializeFilters();
    loadUpcomingMovies(1);
});
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>
