document.addEventListener("DOMContentLoaded", () => {
    const API_BASE = "/thauphim-movie-website/api";
    
    const filterForm = document.querySelector("#filterForm");
    const keywordInput = document.querySelector('input[name="q"]');
    const typeSelect = document.querySelector('select[name="type"]');
    const genreSelect = document.querySelector('select[name="genre"]');
    const countrySelect = document.querySelector('select[name="country"]');
    const yearSelect = document.querySelector('select[name="year"]');
    const sortSelect = document.querySelector('select[name="sort"]');
    const btnClear = document.querySelector(".btn-clear-filter");
  
    const movieList = document.querySelector("#browseMovieList");
    const movieStatus = document.querySelector("#browseMovieStatus");
    const paginationContainer = document.querySelector("#browsePagination");
    const resultTitle = document.querySelector("#browseResultTitle");
  
    let currentPage = 1;
    const limit = 12; 
  
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("page")) currentPage = parseInt(urlParams.get("page"));
    if (keywordInput && urlParams.has("q")) keywordInput.value = urlParams.get("q");
    if (typeSelect && urlParams.has("type")) typeSelect.value = urlParams.get("type");
    
    if (genreSelect) {
        const initialGenre = urlParams.get("genre") || urlParams.get("genre_id");
        if (initialGenre) genreSelect.value = initialGenre;
    }
    
    if (countrySelect && urlParams.has("country")) countrySelect.value = urlParams.get("country");
    
    if (yearSelect) {
        const initialYear = urlParams.get("year") || urlParams.get("release_year");
        if (initialYear) yearSelect.value = initialYear;
    }
    
    if (sortSelect && urlParams.has("sort")) sortSelect.value = urlParams.get("sort");
  
    const updateBrowserURL = () => {
        const params = new URLSearchParams();
        if (keywordInput.value) params.set("q", keywordInput.value);
        if (typeSelect.value) params.set("type", typeSelect.value);
        if (genreSelect.value) params.set("genre", genreSelect.value);
        if (countrySelect.value) params.set("country", countrySelect.value);
        if (yearSelect.value) params.set("year", yearSelect.value);
        if (sortSelect.value) params.set("sort", sortSelect.value);
        params.set("page", currentPage);
        
        window.history.pushState({}, "", `${window.location.pathname}?${params.toString()}`);
    };
  
    const createMovieCard = (movie) => {
        const card = document.createElement("div");
        card.className = "movie-card";
        card.style.cssText = "background: #111; border-radius: 6px; overflow: hidden; border: 1px solid #222; position: relative;";
        
        const posterSrc = movie.poster_url || movie.poster || '/thauphim-movie-website/assets/images/default.jpg';
        const movieType = movie.type === 'series' ? 'Phim Bộ' : 'Phim Lẻ';
        const views = (movie.views || 0).toLocaleString('vi-VN');
  
        card.innerHTML = `
            <a href="/thauphim-movie-website/pages/movie-detail.php?id=${movie.id}" style="text-decoration: none; color: inherit; display: block;">
                <div class="poster-wrapper" style="position: relative; padding-top: 145%; background: #222; overflow: hidden;">
                    <img src="${posterSrc}" alt="${movie.title}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                    <span style="position: absolute; top: 8px; left: 8px; background: rgba(229, 9, 20, 0.9); color: #fff; padding: 3px 6px; font-size: 11px; font-weight: bold; border-radius: 3px; text-transform: uppercase;">
                        ${movieType}
                    </span>
                </div>
                <div class="movie-meta" style="padding: 12px;">
                    <h3 style="margin: 0 0 5px 0; font-size: 15px; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${movie.title}">
                        ${movie.title}
                    </h3>
                    <div style="display: flex; justify-content: space-between; align-items: center; color: #888; font-size: 12px;">
                        <span>${movie.release_year || 'N/A'}</span>
                        <span>Lượt xem: ${views}</span>
                    </div>
                </div>
            </a>
        `;
        return card;
    };
  
    const renderPagination = (totalPages) => {
        paginationContainer.innerHTML = "";
        if (totalPages <= 1) return;
  
        const ul = document.createElement("ul");
        ul.style.cssText = "display: flex; list-style: none; padding: 0; margin: 0; gap: 8px;";
  
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement("li");
            const a = document.createElement("a");
            a.href = "#";
            a.textContent = i;
            a.className = `page-link ${i === currentPage ? 'active-page' : ''}`;
            
            a.addEventListener("click", (e) => {
                e.preventDefault();
                currentPage = i;
                loadMoviesAPI();
                window.scrollTo({ top: 0, behavior: "smooth" });
            });
            
            li.append(a);
            ul.append(li);
        }
        paginationContainer.append(ul);
    };
  
    const loadMoviesAPI = async () => {
        updateBrowserURL(); 
        
        movieList.innerHTML = "";
        movieStatus.textContent = "Đang tìm kiếm phim...";
        movieStatus.style.display = "block";
        paginationContainer.innerHTML = "";
  
        try {
            const apiParams = new URLSearchParams();
            if (keywordInput.value) {
                const queryValue = keywordInput.value.trim();
                apiParams.set("q", queryValue);
                apiParams.set("keyword", queryValue);
                apiParams.set("search", queryValue);
            }
            if (typeSelect.value) apiParams.set("type", typeSelect.value);
            if (genreSelect.value) apiParams.set("genre_id", genreSelect.value); 
            if (countrySelect.value) apiParams.set("country", countrySelect.value);
            
            if (yearSelect.value) {
                apiParams.set("year", yearSelect.value);
                apiParams.set("release_year", yearSelect.value);
            }
            
            if (sortSelect.value) apiParams.set("sort", sortSelect.value);
            apiParams.set("page", currentPage);
            apiParams.set("limit", limit);
  
            const response = await fetch(`${API_BASE}/movies.php?${apiParams.toString()}`);
            const payload = await response.json();

            console.log("Dữ liệu kết quả tìm kiếm API trả về:", payload);
  
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || "Lỗi khi lấy dữ liệu phim");
            }
  
            const movies = payload.data || [];
            const meta = payload.meta || {};
            const totalMovies = meta.total || movies.length;
            
            if (keywordInput.value) {
                resultTitle.textContent = `Kết quả tìm kiếm cho: "${keywordInput.value}" (${totalMovies} phim)`;
            } else {
                resultTitle.textContent = `Danh sách phim tổng hợp (${totalMovies} phim)`;
            }
  
            if (movies.length === 0) {
                movieStatus.innerHTML = `
                    <div class="no-results-box" style="padding: 40px 20px; background: #111; border-radius: 8px; border: 1px dashed #333;">
                        <p style="color: #aaa; font-size: 16px; margin: 0;">Không có bộ phim nào khớp với các bộ lọc của bạn.</p>
                    </div>
                `;
                return;
            }
  
            movieStatus.style.display = "none";
            const fragment = document.createDocumentFragment();
            movies.forEach(movie => fragment.append(createMovieCard(movie)));
            movieList.append(fragment);
  
            renderPagination(meta.total_pages || Math.ceil(totalMovies / limit));
  
        } catch (error) {
            console.error(error);
            movieStatus.textContent = "Đã xảy ra lỗi khi tải phim. Vui lòng thử lại.";
            movieStatus.style.color = "#ff5c7a";
        }
    };
  
    const autoSubmitFields = [typeSelect, genreSelect, countrySelect, yearSelect, sortSelect];
    autoSubmitFields.forEach(field => {
        if (field) {
            field.addEventListener("change", () => {
                currentPage = 1; 
                loadMoviesAPI();
            });
        }
    });
  
    let searchTimeout;
    if (keywordInput) {
        keywordInput.addEventListener("input", () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                loadMoviesAPI();
            }, 500); 
        });
  
        filterForm.addEventListener("submit", (e) => {
            e.preventDefault(); 
        });
    }
  
    // Nút Xóa Lọc
    if (btnClear) {
        btnClear.addEventListener("click", (e) => {
            e.preventDefault();
            if (keywordInput) keywordInput.value = "";
            if (typeSelect) typeSelect.value = "";
            if (genreSelect) genreSelect.value = "";
            if (countrySelect) countrySelect.value = "";
            if (yearSelect) yearSelect.value = "";
            if (sortSelect) sortSelect.value = "newest";
            currentPage = 1;
            loadMoviesAPI();
        });
    }
  
    loadMoviesAPI();
  });