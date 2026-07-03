(() => {
    if (window.__thauHeaderMenuReady) {
        return;
    }
    window.__thauHeaderMenuReady = true;
    const initHeaderMenu = () => {
        const header = document.querySelector(".site-header");
        const toggleButton = document.querySelector(".menu-toggle");
        const menu = document.querySelector("#primary-menu");

        if (!header || !toggleButton || !menu) { return; }

        const closeMenu = () => {
            header.classList.remove("is-menu-open");
            toggleButton.setAttribute("aria-expanded", "false");
        };
        const updateHeaderState = () => {
            header.classList.toggle("scrolled", window.scrollY > 80);
        };
        updateHeaderState();
        window.addEventListener("scroll", updateHeaderState, {
            passive: true,
        });

        toggleButton.addEventListener(
            "click",
            (event) => {
                event.preventDefault();
                event.stopImmediatePropagation();
                const isOpen = header.classList.toggle("is-menu-open");
                toggleButton.setAttribute("aria-expanded", String(isOpen));
            },
            true
        );

        document.addEventListener("click", (event) => {
            if (
                !header.classList.contains("is-menu-open") ||
                header.contains(event.target)
            ) return;
            closeMenu();
        });

        menu.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", closeMenu);
        });

        window.addEventListener("resize", () => {
            if (window.innerWidth > 980) {
                closeMenu();
            }
        });
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initHeaderMenu, {
            once: true,
        });
        return;
    }

    initHeaderMenu();
})();

(() => {
    if (window.__thauHeaderSearchReady) {
        return;
    }
    window.__thauHeaderSearchReady = true;
    const initHeaderSearch = () => {
        const form = document.querySelector("[data-header-search]");

        if (!form) {
            return;
        }

        const input = form.querySelector('input[name="q"]');
        const panel = form.querySelector("[data-search-suggestions]");

        if (!input || !panel) {
            return;
        }

        const apiUrl = form.dataset.searchApi || "/api/movies.php";
        const detailPage = form.dataset.detailPage || "/pages/movie-detail.php";
        const fallbackPoster = form.dataset.fallbackPoster || "/assets/images/poster_movie.jpg";
        const tmdbRoot = "https://image.tmdb.org/t/p/";

        let timer = 0;
        let controller = null;
        const escapeHtml = (value = "") =>
            String(value)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

        const closePanel = () => {
            panel.hidden = true;
            panel.innerHTML = "";
            input.setAttribute("aria-expanded", "false");
        };
        const showState = (message) => {
            panel.hidden = false;
            panel.innerHTML = `<div class="search-suggestion-state">${escapeHtml(message)}</div>`;
            input.setAttribute("aria-expanded", "true");
        };
        const getMovieTitle = (movie) => {
            return movie.title || movie.name || "Phim đang cập nhật";
        };
        const getMovieYear = (movie) => {
            const rawDate = movie.release_year || movie.release_date || movie.first_air_date || "";
            return String(rawDate).slice(0, 4) || "Đang cập nhật";
        };

        const getMoviePoster = (movie) => {
            const rawPoster = movie.poster_url || movie.poster_path || movie.poster || "";
            const poster = String(rawPoster).trim();

            if (!poster) {
                return fallbackPoster;
            }
            if (/^(https?:)?\/\//i.test(poster) || poster.startsWith("data:")) {
                return poster;
            }
            if (poster.startsWith("/")) {
                return poster;
            }
            if (poster.includes("/")) {
                return `/${poster.replace(/^\/+/, "")}`;
            }

            return `${tmdbRoot}w92${poster}`;
        };

        const getMovieUrl = (movie) => {
            const movieId = movie.id || movie.movie_id;
            return `${detailPage}?id=${encodeURIComponent(movieId)}`;
        };

        const renderMovies = (movies) => {
            if (!movies.length) {
                showState("Không tìm thấy phim phù hợp.");
                return;
            }

            panel.hidden = false;
            input.setAttribute("aria-expanded", "true");
            panel.innerHTML = movies.map((movie) => {
                const title = getMovieTitle(movie);
                const originalTitle = movie.original_title || movie.original_name || "";
                const year = getMovieYear(movie);
                const typeLabel = movie.type === "series" ? "Phim bộ" : "Phim lẻ";

                return `
                    <a class="search-suggestion-item" href="${escapeHtml(getMovieUrl(movie))}" role="option">
                        <img
                            class="search-suggestion-poster"
                            src="${escapeHtml(getMoviePoster(movie))}"
                            alt=""
                            loading="lazy"
                            onerror="this.src='${escapeHtml(fallbackPoster)}'"
                        >
                        <span class="search-suggestion-body">
                            <strong class="search-suggestion-title">${escapeHtml(title)}</strong>
                            ${originalTitle ? `<span class="search-suggestion-original">${escapeHtml(originalTitle)}</span>` : ""}
                            <span class="search-suggestion-meta">${escapeHtml(typeLabel)} • ${escapeHtml(year)}</span>
                        </span>
                    </a>
                `;
            }).join("");
        };

        const runSearch = async () => {
            const keyword = input.value.trim();
            if (keyword.length < 1) {
                closePanel();
                return;
            }
            if (controller) {
                controller.abort();
            }

            controller = new AbortController();

            const url = new URL(apiUrl, window.location.origin);
            url.searchParams.set("q", keyword);
            url.searchParams.set("limit", "8");
            url.searchParams.set("search_mode", "suggest");
            showState("Đang tìm phim...");
            try {
                const response = await fetch(url, {
                    signal: controller.signal,
                    credentials: "same-origin",
                    headers: {
                        Accept: "application/json",
                    },
                });
                const payload = await response.json();
                if (!response.ok || !payload.success || !Array.isArray(payload.data)) {
                    throw new Error(payload.message || "Search failed");
                }
                renderMovies(payload.data.filter(Boolean));
            } catch (error) {
                if (error.name === "AbortError") {
                    return;
                }

                showState("Không tải được gợi ý phim.");
            }
        };

        input.addEventListener("input", () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(runSearch, 220);
        });

        input.addEventListener("focus", () => {
            if (input.value.trim()) {
                runSearch();
            }
        });

        input.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                closePanel();
            }
        });

        form.addEventListener("submit", () => {
            closePanel();
        });
        document.addEventListener("click", (event) => {
            if (!form.contains(event.target)) {
                closePanel();
            }
        });
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initHeaderSearch, {
            once: true,
        });
        return;
    }
    initHeaderSearch();
})();