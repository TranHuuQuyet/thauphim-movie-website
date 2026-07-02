document.addEventListener("DOMContentLoaded", () => {
    const config = window.MOVIE_LIST_CONFIG || {};
    const APP_ROOT = window.APP_BASE_PATH || "/";
    const API_BASE = `${APP_ROOT}api`;
    const MOVIES_API = `${API_BASE}/movies.php`;
    const FALLBACK_POSTER = `${APP_ROOT}assets/images/poster_movie.jpg`;

    const movieList = document.querySelector("#movieList");
    const movieStatus = document.querySelector("#movieStatus");
    const moviePagination = document.querySelector("#moviePagination");
    const movieSearchInput = document.querySelector("#movieSearchInput");

    if (!movieList) return;

    const movieType = config.type || "movie";
    const emptyText = config.emptyText || "Không tìm thấy phim nào phù hợp.";

    let currentPage = 1;
    const limit = 12;
    let searchQuery = "";
    let searchTimeout = null;

    const setStatus = (message, state = "") => {
        if (!movieStatus) return;
        movieStatus.textContent = message;
        movieStatus.className = `movie-list-load-status${state ? ` is-${state}` : ""}`;
    };

    const movieTitle = (movie) =>
        movie.title || movie.name || "Phim đang cập nhật";

    const movieOriginalTitle = (movie) => {
        const original = movie.original_title || movie.original_name || "";
        return original && original !== movieTitle(movie) ? original : "";
    };

    const movieYear = (movie) => {
        if (movie.release_year) return String(movie.release_year);
        const date = movie.release_date || movie.first_air_date || "";
        return date ? String(date).slice(0, 4) : "Đang cập nhật";
    };

    const movieRating = (movie) => {
        const rating = Number(movie.vote_average || 0);
        return Number.isFinite(rating) && rating > 0 ? rating.toFixed(1) : "N/A";
    };

    const moviePosterUrl = (movie) => {
        const value = String(
            movie.poster_url || movie.poster || movie.poster_path || ""
        ).trim();

        if (!value) return FALLBACK_POSTER;

        if (/^(https?:)?\/\//i.test(value) || value.startsWith("/") || value.startsWith("data:")) {
            return value;
        }

        return `${APP_ROOT}${value.replace(/^\/+/, "")}`;
    };

    const movieDetailUrl = (movie) => {
        const movieId = Number(movie?.id || movie?.movie_id || 0);
        return movieId
            ? `${APP_ROOT}pages/movie-detail.php?id=${encodeURIComponent(movieId)}`
            : "#";
    };

    const createMovieCard = (movie) => {
        const article = document.createElement("article");
        article.className = "movie-list-card";

        const link = document.createElement("a");
        link.className = "movie-list-card__link";
        link.href = movieDetailUrl(movie);
        link.setAttribute("aria-label", movieTitle(movie));

        const posterWrap = document.createElement("div");
        posterWrap.className = "movie-list-poster";

        const image = document.createElement("img");
        image.src = moviePosterUrl(movie);
        image.alt = movieTitle(movie);
        image.loading = "lazy";
        image.width = 500;
        image.height = 750;
        image.addEventListener(
            "error",
            () => {
                image.src = FALLBACK_POSTER;
            },
            { once: true }
        );

        posterWrap.append(image);

        const info = document.createElement("div");
        info.className = "movie-card-info";

        const title = document.createElement("h3");
        title.className = "movie-title";
        title.textContent = movieTitle(movie);

        const original = document.createElement("p");
        original.className = "movie-original";
        original.textContent = movieOriginalTitle(movie) || " ";

        const meta = document.createElement("div");
        meta.className = "movie-card-meta";

        const year = document.createElement("span");
        year.textContent = movieYear(movie);

        const rating = document.createElement("span");
        rating.textContent = `IMDb ${movieRating(movie)}`;

        meta.append(year, rating);
        info.append(title, original, meta);
        link.append(posterWrap, info);
        article.append(link);

        return article;
    };

    const getPaginationItems = (totalPages) => {
        const pages = new Set([1, totalPages]);
        const start = Math.max(2, currentPage - 1);
        const end = Math.min(totalPages - 1, currentPage + 1);

        for (let page = start; page <= end; page += 1) {
            pages.add(page);
        }

        if (currentPage <= 2) {
            pages.add(2);
            pages.add(3);
        }

        if (currentPage >= totalPages - 1) {
            pages.add(totalPages - 2);
            pages.add(totalPages - 1);
        }

        return [...pages]
            .filter((page) => page >= 1 && page <= totalPages)
            .sort((a, b) => a - b)
            .reduce((items, page, index, sortedPages) => {
                if (index > 0 && page - sortedPages[index - 1] > 1) {
                    items.push("ellipsis");
                }
                items.push(page);
                return items;
            }, []);
    };

    const createPaginationButton = (label, page, options = {}) => {
        const li = document.createElement("li");
        const btn = document.createElement("button");
        const isCurrent = page === currentPage;

        btn.type = "button";
        btn.textContent = label;
        btn.className = `page-link${isCurrent ? " active-page" : ""}`;
        btn.disabled = Boolean(options.disabled) || isCurrent;

        if (isCurrent) {
            btn.setAttribute("aria-current", "page");
        }

        btn.addEventListener("click", () => {
            if (btn.disabled) return;
            currentPage = page;
            loadMovies();
            window.scrollTo({ top: 0, behavior: "smooth" });
        });

        li.append(btn);
        return li;
    };

    const createPaginationEllipsis = () => {
        const li = document.createElement("li");
        const ellipsis = document.createElement("span");
        ellipsis.className = "page-ellipsis";
        ellipsis.textContent = "...";
        ellipsis.setAttribute("aria-hidden", "true");
        li.append(ellipsis);
        return li;
    };

    const renderPagination = (totalItems) => {
        if (!moviePagination) return;

        moviePagination.innerHTML = "";
        const totalPages = Math.ceil(totalItems / limit);

        if (totalPages <= 1) return;

        const ul = document.createElement("ul");
        ul.className = "pagination-list";

        ul.append(
            createPaginationButton("Trước", Math.max(1, currentPage - 1), {
                disabled: currentPage === 1,
            })
        );

        getPaginationItems(totalPages).forEach((item) => {
            ul.append(
                item === "ellipsis"
                    ? createPaginationEllipsis()
                    : createPaginationButton(String(item), item)
            );
        });

        ul.append(
            createPaginationButton("Sau", Math.min(totalPages, currentPage + 1), {
                disabled: currentPage === totalPages,
            })
        );

        moviePagination.append(ul);
    };

    const loadMovies = async () => {
        movieList.replaceChildren();
        setStatus("Đang tải danh sách phim...", "loading");

        try {
            const url = `${MOVIES_API}?type=${encodeURIComponent(movieType)}&sort=popular&page=${currentPage}&limit=${limit}&q=${encodeURIComponent(searchQuery)}`;

            const response = await fetch(url, {
                headers: { Accept: "application/json" },
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok || !payload?.success) {
                throw new Error(payload?.message || "Internal movies API request failed");
            }

            const movies = Array.isArray(payload.data) ? payload.data : [];

            if (movies.length === 0) {
                setStatus(emptyText, "empty");
                if (moviePagination) moviePagination.innerHTML = "";
                return;
            }

            const fragment = document.createDocumentFragment();
            movies.forEach((movie) => fragment.append(createMovieCard(movie)));
            movieList.append(fragment);

            setStatus(
                searchQuery
                    ? `Tìm thấy ${payload.meta?.total || movies.length} kết quả.`
                    : "",
                "ready"
            );

            if (payload.meta && payload.meta.total) {
                renderPagination(payload.meta.total);
            } else {
                renderPagination(movies.length);
            }
        } catch (error) {
            console.error("Không thể tải danh sách phim:", error);
            movieList.replaceChildren();
            setStatus("Không thể kết nối danh sách phim. Vui lòng thử lại.", "error");
        }
    };

    if (movieSearchInput) {
        movieSearchInput.addEventListener("input", (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchQuery = e.target.value.trim();
                currentPage = 1;
                loadMovies();
            }, 500);
        });
    }

    loadMovies();
});