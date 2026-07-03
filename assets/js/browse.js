document.addEventListener("DOMContentLoaded", () => {
  const filterForm = document.querySelector("#filterForm");
  const movieList = document.querySelector("#browseMovieList");
  const movieStatus = document.querySelector("#browseMovieStatus");
  const paginationContainer = document.querySelector("#browsePagination");
  const resultTitle = document.querySelector("#browseResultTitle");

  if (!filterForm || !movieList || !movieStatus || !paginationContainer || !resultTitle) {
    return;
  }

  const typeSelect = filterForm.querySelector('select[name="type"]');
  const genreSelect = filterForm.querySelector('select[name="genre"]');
  const countrySelect = filterForm.querySelector('select[name="country"]');
  const yearSelect = filterForm.querySelector('select[name="year"]');
  const sortSelect = filterForm.querySelector('select[name="sort"]');
  const clearButton = filterForm.querySelector(".btn-clear-filter");
  const apiUrl = filterForm.dataset.apiUrl;
  const detailBase = filterForm.dataset.detailBase;
  const fallbackPoster = filterForm.dataset.fallbackPoster;
  const limit = 12;

  const urlParams = new URLSearchParams(window.location.search);
  let currentPage = Math.max(1, Number.parseInt(urlParams.get("page") || "1", 10) || 1);
  let searchKeyword = urlParams.get("q") || "";

  typeSelect.value = urlParams.get("type") || "";
  genreSelect.value = urlParams.get("genre") || urlParams.get("genre_id") || "";
  countrySelect.value = urlParams.get("country") || "";
  yearSelect.value = urlParams.get("year") || urlParams.get("release_year") || "";
  sortSelect.value = urlParams.get("sort") || "newest";

  const buildPageParams = (page = currentPage) => {
    const params = new URLSearchParams();

    if (searchKeyword.trim()) params.set("q", searchKeyword.trim());
    if (typeSelect.value) params.set("type", typeSelect.value);
    if (genreSelect.value) params.set("genre", genreSelect.value);
    if (countrySelect.value) params.set("country", countrySelect.value);
    if (yearSelect.value) params.set("year", yearSelect.value);
    if (sortSelect.value) params.set("sort", sortSelect.value);
    params.set("page", String(page));

    return params;
  };

  const updateBrowserUrl = () => {
    const params = buildPageParams();
    window.history.pushState({}, "", `${window.location.pathname}?${params.toString()}`);
  };

  const setStatus = (message = "", state = "") => {
    movieStatus.textContent = message;
    movieStatus.className = `browse-status${state ? ` is-${state}` : ""}`;
    movieStatus.hidden = message === "";
  };

  const createMovieCard = (movie) => {
    const article = document.createElement("article");
    article.className = "movie-card";

    const link = document.createElement("a");
    link.className = "movie-link";
    link.href = `${detailBase}?id=${encodeURIComponent(movie.id)}`;

    const posterWrapper = document.createElement("div");
    posterWrapper.className = "poster-wrapper";

    const poster = document.createElement("img");
    poster.src = movie.poster_url || movie.poster || fallbackPoster;
    poster.alt = `Poster phim ${movie.title || "chưa có tên"}`;
    poster.loading = "lazy";
    poster.width = 342;
    poster.height = 513;
    poster.addEventListener(
      "error",
      () => {
        if (poster.src !== fallbackPoster) {
          poster.src = fallbackPoster;
        }
      },
      { once: true },
    );

    const badge = document.createElement("span");
    badge.className = "movie-badge";
    badge.textContent = movie.type === "series" ? "Phim bộ" : "Phim lẻ";

    const meta = document.createElement("div");
    meta.className = "movie-meta";

    const title = document.createElement("h2");
    title.className = "movie-title";
    title.textContent = movie.title || "Chưa có tên";
    title.title = movie.title || "Chưa có tên";

    const subInfo = document.createElement("div");
    subInfo.className = "movie-sub-info";

    const year = document.createElement("span");
    year.textContent = movie.release_year || "Đang cập nhật";

    const views = document.createElement("span");
    views.textContent = `${Number(movie.views || 0).toLocaleString("vi-VN")} lượt xem`;

    subInfo.append(year, views);
    meta.append(title, subInfo);
    posterWrapper.append(poster, badge);
    link.append(posterWrapper, meta);
    article.append(link);

    return article;
  };

  const renderPagination = (totalPages) => {
    paginationContainer.replaceChildren();

    if (totalPages <= 1) {
      return;
    }

    const list = document.createElement("ul");
    list.className = "pagination-list";

    for (let page = 1; page <= totalPages; page += 1) {
      const item = document.createElement("li");
      const link = document.createElement("a");
      link.className = `page-link${page === currentPage ? " is-active" : ""}`;
      link.href = `?${buildPageParams(page).toString()}`;
      link.textContent = String(page);

      if (page === currentPage) {
        link.setAttribute("aria-current", "page");
      }

      link.addEventListener("click", (event) => {
        event.preventDefault();
        currentPage = page;
        loadMovies();
        window.scrollTo({ top: 0, behavior: "smooth" });
      });

      item.append(link);
      list.append(item);
    }

    paginationContainer.append(list);
  };

  const loadMovies = async () => {
    updateBrowserUrl();
    movieList.replaceChildren();
    paginationContainer.replaceChildren();
    setStatus("Đang tải danh sách phim...", "loading");

    const apiParams = new URLSearchParams();
    const cleanKeyword = searchKeyword.trim();

    if (cleanKeyword) {
      apiParams.set("q", cleanKeyword);
      apiParams.set("keyword", cleanKeyword);
      apiParams.set("search", cleanKeyword);
    }
    if (typeSelect.value) apiParams.set("type", typeSelect.value);
    if (genreSelect.value) apiParams.set("genre_id", genreSelect.value);
    if (countrySelect.value) apiParams.set("country", countrySelect.value);
    if (yearSelect.value) {
      apiParams.set("year", yearSelect.value);
      apiParams.set("release_year", yearSelect.value);
    }
    if (sortSelect.value) apiParams.set("sort", sortSelect.value);
    apiParams.set("page", String(currentPage));
    apiParams.set("limit", String(limit));

    try {
      const response = await fetch(`${apiUrl}?${apiParams.toString()}`, {
        headers: { Accept: "application/json" },
      });
      const payload = await response.json().catch(() => null);

      if (!response.ok || !payload?.success) {
        throw new Error(payload?.message || "Không thể tải danh sách phim");
      }

      const movies = Array.isArray(payload.data) ? payload.data : [];
      const meta = payload.meta || {};
      const totalMovies = Number(meta.total || movies.length);

      resultTitle.textContent = cleanKeyword
        ? `Kết quả cho "${cleanKeyword}" (${totalMovies} phim)`
        : `Danh sách phim tổng hợp (${totalMovies} phim)`;

      if (movies.length === 0) {
        setStatus("Không có phim nào phù hợp với các bộ lọc đã chọn.", "empty");
        return;
      }

      const fragment = document.createDocumentFragment();
      movies.forEach((movie) => fragment.append(createMovieCard(movie)));
      movieList.append(fragment);
      setStatus();
      renderPagination(Number(meta.total_pages) || Math.ceil(totalMovies / limit));
    } catch (error) {
      console.error(error);
      setStatus("Đã xảy ra lỗi khi tải phim. Vui lòng thử lại.", "error");
    }
  };

  [typeSelect, genreSelect, countrySelect, yearSelect, sortSelect].forEach((field) => {
    field.addEventListener("change", () => {
      currentPage = 1;
      loadMovies();
    });
  });

  filterForm.addEventListener("submit", (event) => event.preventDefault());

  clearButton.addEventListener("click", () => {
    searchKeyword = "";
    typeSelect.value = "";
    genreSelect.value = "";
    countrySelect.value = "";
    yearSelect.value = "";
    sortSelect.value = "newest";
    currentPage = 1;
    loadMovies();
  });

  loadMovies();
});
