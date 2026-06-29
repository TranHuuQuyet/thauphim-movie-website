document.addEventListener("DOMContentLoaded", () => {
    const API_BASE = "/api";
    const movieTitle = document.querySelector("#genre-movies-title");
    const movieStatus = document.querySelector("#genreMovieStatus");
    const movieList = document.querySelector("#genreMovieList");
    const genrePagination = document.querySelector("#genrePagination");
  
    if (!movieTitle || !movieStatus || !movieList) return;
  
    let currentPage = 1;
    const limit = 20;
    let genreId = "";
    let genreName = "";
  
    const createMovieCard = (movie) => {
        const card = document.createElement("div");
        card.className = "movie-card";
        const posterSrc = movie.poster_url || movie.poster || '/assets/images/no-poster.png';
        const movieType = movie.type === 'series' ? 'Phim Bộ' : 'Phim Lẻ';

        card.innerHTML = `
            <a href="/pages/movie-detail.php?id=${movie.id}">
                <div class="card-poster">
                    <img src="${posterSrc}" alt="${movie.title}" loading="lazy">
                    <span class="card-quality">${movieType}</span>
                </div>
                <div class="card-info">
                    <h3 class="movie-title">${movie.title}</h3>
                    <div class="movie-meta">
                        <span>${movie.release_year || 'N/A'}</span>
                        <span style="color: #e50914;"><i class="fa-solid fa-star"></i> ${parseFloat(movie.vote_average || 0).toFixed(1)}</span>
                    </div>
                </div>
            </a>
        `;
        return card;
    };
  
    const renderPagination = (totalPages) => {
        if (!genrePagination) return;
        genrePagination.innerHTML = "";
        if (totalPages <= 1) return;
  
        const ul = document.createElement("ul");
        ul.style.cssText = "display: flex; list-style: none; padding: 0; margin: 0; gap: 8px;";
  
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement("li");
            const btn = document.createElement("button");
            btn.textContent = i;
            btn.className = `page-link ${i === currentPage ? "active-page" : ""}`;
            btn.style.cssText = `padding: 8px 16px; border: 1px solid #333; background: ${i === currentPage ? '#e50914' : '#111'}; color: #fff; cursor: pointer; border-radius: 4px; transition: all 0.2s;`;
            
            btn.addEventListener("click", () => {
                currentPage = i;
                loadGenreMovies();
                window.scrollTo({ top: 0, behavior: "smooth" });
            });
            
            li.append(btn);
            ul.append(li);
        }
        genrePagination.append(ul);
    };
  
    const loadGenreMovies = async () => {
        movieList.replaceChildren();
        movieStatus.textContent = "Đang tải danh sách phim...";
        movieStatus.style.display = "block";
  
        try {
            if (!genreName) {
                const genreRes = await fetch(`${API_BASE}/genres.php`);
                const genreData = await genreRes.json();
                if (genreData.success) {
                    const currentGenre = genreData.data.find(g => g.id == genreId);
                    if (currentGenre) {
                        genreName = currentGenre.name;
                        movieTitle.textContent = `Chủ đề: ${genreName}`;
                        document.title = `${genreName} - ThauPhim`;
                    }
                }
            }
  
            const response = await fetch(`${API_BASE}/movies.php?genre_id=${genreId}&page=${currentPage}&limit=${limit}`);
            const payload = await response.json();
  
            if (!response.ok || !payload?.success) throw new Error("API Lỗi");
  
            const movies = payload.data || [];
            
            if (movies.length === 0) {
                movieStatus.innerHTML = `
                    <div style="padding: 40px; border: 1px dashed #333; border-radius: 8px;">
                        <i class="fa-solid fa-folder-open" style="font-size: 32px; color: #555; margin-bottom: 15px;"></i><br>
                        Chủ đề này hiện tại chưa có bộ phim nào.
                    </div>`;
                if (genrePagination) genrePagination.innerHTML = "";
                return;
            }
  
            movieStatus.style.display = "none";
            const fragment = document.createDocumentFragment();
            movies.forEach((movie) => fragment.append(createMovieCard(movie)));
            movieList.append(fragment);
  
            const meta = payload.meta || {};
            const totalPages = meta.total_pages || Math.ceil((meta.total || movies.length) / limit);
            renderPagination(totalPages);
  
        } catch (error) {
            console.error("Lỗi kết nối:", error);
            movieStatus.innerHTML = '<span style="color: #ff5c7a;">Không thể tải danh sách phim lúc này.</span>';
        }
    };
  
    const requestedId = new URLSearchParams(window.location.search).get("id");
    if (!requestedId) {
        window.location.href = "/"; // Nếu không có ID, văng về trang chủ
        return;
    }
  
    genreId = requestedId.trim();
    loadGenreMovies();
});