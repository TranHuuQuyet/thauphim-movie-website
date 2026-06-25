const APP_ROOT = "/thauphim-movie-website/";
const MOVIES_API_ENDPOINT = `${APP_ROOT}api/movies.php`;
const IMAGE_ROOT = "https://image.tmdb.org/t/p/";
const FALLBACK_POSTER = `${APP_ROOT}assets/images/poster_movie.jpg`;
const FALLBACK_BACKDROP = `${APP_ROOT}assets/images/pic1.webp`;

const escapeHtml = (value = "") =>
  String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");

const movieTitle = (movie) => movie.title || movie.name || "Phim đang cập nhật";
const movieOriginalTitle = (movie) =>
  movie.original_title || movie.original_name || movieTitle(movie);
const movieDate = (movie) => movie.release_date || movie.first_air_date || "";
const movieYear = (movie) => movieDate(movie).slice(0, 4) || "Đang cập nhật";
const movieRating = (movie) =>
  Number.isFinite(movie.vote_average) ? movie.vote_average.toFixed(1) : "N/A";

const imageUrl = (path, size, fallback) => {
  const value = String(path || "").trim();

  if (!value) {
    return fallback;
  }

  if (/^(https?:)?\/\//i.test(value) || value.startsWith("/") || value.startsWith("data:")) {
    return value;
  }

  if (value.includes("/")) {
    return `${APP_ROOT}${value.replace(/^\/+/, "")}`;
  }

  return `${IMAGE_ROOT}${size}${value}`;
};

const moviePosterUrl = (movie, size = "w500") =>
  imageUrl(movie.poster_url || movie.poster || movie.poster_path, size, FALLBACK_POSTER);

const movieBackdropUrl = (movie, size = "original") =>
  imageUrl(movie.backdrop_url || movie.backdrop || movie.backdrop_path, size, FALLBACK_BACKDROP);

const stateMarkup = (type, text) => `
  <div class="ui-state ui-state--${type}">
    <i class="fa-solid ${
      type === "loading"
        ? "fa-spinner"
        : type === "error"
          ? "fa-triangle-exclamation"
          : "fa-film"
    }" aria-hidden="true"></i>
    <p>${escapeHtml(text)}</p>
  </div>
`;

const setState = (container, type, text, isSwiper = false) => {
  if (!container) return;

  const markup = stateMarkup(type, text);
  container.innerHTML = isSwiper
    ? `<div class="swiper-slide">${markup}</div>`
    : markup;
};

const initSwiper = (selector, options) => {
  if (typeof Swiper === "undefined" || !document.querySelector(selector)) {
    return null;
  }

  return new Swiper(selector, options);
};

const fetchMovies = async (params = {}) => {
  const url = new URL(MOVIES_API_ENDPOINT, window.location.origin);

  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== "") {
      url.searchParams.set(key, value);
    }
  });

  const response = await fetch(url);

  if (!response.ok) {
    throw new Error(`Movies API request failed: ${response.status}`);
  }

  const payload = await response.json();

  if (!payload.success || !Array.isArray(payload.data)) {
    throw new Error(payload.message || "Movies API response is invalid.");
  }

  return payload.data;
};

const movieCardMarkup = (movie, anchorId) => {
  const title = escapeHtml(movieTitle(movie));
  const original = escapeHtml(movieOriginalTitle(movie));
  const poster = moviePosterUrl(movie, "w500");
  const year = escapeHtml(movieYear(movie));
  const rating = escapeHtml(movieRating(movie));

  return `
    <article class="movie-grid-card">
      <a class="movie-card" href="#${anchorId}" aria-label="${title}">
        <img src="${poster}" alt="${title}" loading="lazy">
        <span class="movie-status">HD</span>
      </a>
      <div class="movie-card-info">
        <h3 class="movie-title">${title}</h3>
        <p class="movie-original">${original}</p>
        <div class="movie-card-meta">
          <span>${year}</span>
          <span>IMDb ${rating}</span>
        </div>
      </div>
    </article>
  `;
};

const swiperCardMarkup = (movie, anchorId) => `
  <div class="swiper-slide">
    ${movieCardMarkup(movie, anchorId)}
  </div>
`;

const loadTrendingMovies = async () => {
  const container = document.querySelector("#trendingMovie");
  if (!container) return;

  setState(container, "loading", "Đang tải phim nổi bật...", true);

  try {
    const movies = (await fetchMovies({
      type: "movie",
      sort: "popular",
      page: 1,
      limit: 18,
    })).filter(Boolean);

    if (!movies.length) {
      setState(container, "empty", "Chưa có phim nổi bật để hiển thị.", true);
      return;
    }

    container.innerHTML = movies
      .map((movie) => swiperCardMarkup(movie, "featured"))
      .join("");

    initSwiper(".movieSwiper", {
      slidesPerView: 3,
      spaceBetween: 24,
      loop: movies.length > 3,
      speed: 800,
      autoplay: {
        delay: 3200,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: ".movie-next",
        prevEl: ".movie-prev",
      },
      breakpoints: {
        0: { slidesPerView: 1.2, spaceBetween: 14 },
        560: { slidesPerView: 2.1, spaceBetween: 16 },
        900: { slidesPerView: 3, spaceBetween: 22 },
        1180: { slidesPerView: 4, spaceBetween: 24 },
      },
    });
  } catch (error) {
    console.error(error);
    setState(container, "error", "Không tải được phim nổi bật lúc này.", true);
  }
};

const loadTopMovies = async () => {
  const container = document.querySelector("#topMovies");
  if (!container) return;

  setState(container, "loading", "Đang tải phim top tuần...", true);

  try {
    const movies = (await fetchMovies({
      type: "movie",
      sort: "most_viewed",
      page: 1,
      limit: 10,
    })).filter(Boolean);

    if (!movies.length) {
      setState(container, "empty", "Chưa có phim top tuần để hiển thị.", true);
      return;
    }

    container.innerHTML = movies
      .map((movie, index) => {
        const title = escapeHtml(movieTitle(movie));
        const backdrop = movieBackdropUrl(movie, "w780");
        const overview = escapeHtml(
          movie.overview || "Nội dung phim đang được cập nhật.",
        );

        return `
          <div class="swiper-slide">
            <img src="${backdrop}" alt="${title}" loading="lazy">
            <div class="slide-content">
              <span class="movie-rank">${index + 1}</span>
              <h3>${title}</h3>
              <div class="movie-vote">
                <i class="fa-solid fa-star" aria-hidden="true"></i>
                ${escapeHtml(movieRating(movie))}
              </div>
              <a class="play-btn" href="#top-week">XEM NGAY</a>
              <p>${overview}</p>
            </div>
          </div>
        `;
      })
      .join("");

    initSwiper(".bannerSwiper", {
      loop: movies.length > 3,
      centeredSlides: true,
      slidesPerView: "auto",
      spaceBetween: 14,
      grabCursor: true,
      speed: 700,
      effect: "coverflow",
      coverflowEffect: {
        rotate: 0,
        stretch: 0,
        depth: 180,
        modifier: 1.8,
        slideShadows: false,
      },
      autoplay: {
        delay: 3600,
        disableOnInteraction: false,
      },
      pagination: {
        el: ".bannerSwiper .swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".bannerSwiper .swiper-button-next",
        prevEl: ".bannerSwiper .swiper-button-prev",
      },
    });
  } catch (error) {
    console.error(error);
    setState(container, "error", "Không tải được phim top tuần lúc này.", true);
  }
};

const loadNewMovies = async () => {
  const heroContainer = document.querySelector("#heroMovies");
  const thumbContainer = document.querySelector("#thumbMovies");

  if (!heroContainer || !thumbContainer) return;

  setState(heroContainer, "loading", "Đang tải phim mới gần đây...", true);
  thumbContainer.innerHTML = "";

  try {
    const movies = (await fetchMovies({
      type: "movie",
      sort: "newest",
      page: 1,
      limit: 10,
    })).filter(Boolean);

    if (!movies.length) {
      setState(heroContainer, "empty", "Chưa có phim mới để hiển thị.", true);
      return;
    }

    heroContainer.innerHTML = movies
      .map((movie) => {
        const title = escapeHtml(movieTitle(movie));
        const original = escapeHtml(movieOriginalTitle(movie));
        const backdrop = movieBackdropUrl(movie, "original");
        const overview = escapeHtml(
          movie.overview || "Nội dung phim đang được cập nhật.",
        );

        return `
          <div class="swiper-slide movie-hero-slide">
            <img class="movie-hero-bg" src="${backdrop}" alt="${title}" loading="lazy">
            <div class="movie-hero-content">
              <h2>${title}</h2>
              <p class="movie-original">${original}</p>

              <div class="movie-meta">
                <span>IMDb ${escapeHtml(movieRating(movie))}</span>
                <span>T18</span>
                <span>${escapeHtml(movieYear(movie))}</span>
                <span>Phim lẻ</span>
                <span>HD</span>
              </div>

              <p class="movie-summary">${overview}</p>

              <div class="movie-actions">
                <a class="watch-button" href="#new-movies" aria-label="Xem khu vực phim mới">
                  <i class="fa-solid fa-play" aria-hidden="true"></i>
                </a>
                <button type="button" data-ui-placeholder aria-label="Yêu thích UI tạm thời">
                  <i class="fa-solid fa-heart" aria-hidden="true"></i>
                </button>
                <button type="button" data-ui-placeholder aria-label="Chi tiết UI tạm thời">
                  <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                </button>
              </div>
            </div>
          </div>
        `;
      })
      .join("");

    thumbContainer.innerHTML = movies
      .map((movie) => {
        const title = escapeHtml(movieTitle(movie));
        const poster = moviePosterUrl(movie, "w300");

        return `
          <div class="swiper-slide">
            <img src="${poster}" alt="${title}" loading="lazy">
          </div>
        `;
      })
      .join("");

    const movieThumbs = initSwiper(".movieThumbSwiper", {
      slidesPerView: "auto",
      spaceBetween: 16,
      watchSlidesProgress: true,
      slideToClickedSlide: true,
    });

    initSwiper(".movieHeroSwiper", {
      effect: "fade",
      speed: 700,
      rewind: true,
      autoplay: {
        delay: 4200,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: ".hero-next",
        prevEl: ".hero-prev",
      },
      thumbs: movieThumbs ? { swiper: movieThumbs } : undefined,
    });
  } catch (error) {
    console.error(error);
    setState(heroContainer, "error", "Không tải được phim mới lúc này.", true);
    thumbContainer.innerHTML = "";
  }
};

const loadMovieGrid = async ({ selector, params, limit, anchorId }) => {
  const container = document.querySelector(selector);
  if (!container) return;

  setState(container, "loading", "Đang tải danh sách phim...");

  try {
    const movies = (await fetchMovies({
      ...params,
      limit,
    })).filter(Boolean);

    if (!movies.length) {
      setState(container, "empty", "Chưa có phim để hiển thị.");
      return;
    }

    container.innerHTML = movies
      .map((movie) => movieCardMarkup(movie, anchorId))
      .join("");
  } catch (error) {
    console.error(error);
    setState(container, "error", "Không tải được danh sách phim lúc này.");
  }
};

const setupHeader = () => {
  const header = document.querySelector(".site-header");
  const toggleButton = document.querySelector(".menu-toggle");
  const menu = document.querySelector("#primary-menu");

  if (header) {
    const updateHeaderState = () => {
      header.classList.toggle("scrolled", window.scrollY > 80);
    };

    updateHeaderState();
    window.addEventListener("scroll", updateHeaderState, { passive: true });
  }

  if (header && toggleButton && menu) {
    const closeMenu = () => {
      header.classList.remove("is-menu-open");
      toggleButton.setAttribute("aria-expanded", "false");
    };

    toggleButton.addEventListener("click", () => {
      const isOpen = header.classList.toggle("is-menu-open");
      toggleButton.setAttribute("aria-expanded", String(isOpen));
    });

    document.addEventListener("click", (event) => {
      if (
        !header.classList.contains("is-menu-open") ||
        header.contains(event.target)
      ) {
        return;
      }

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
  }
};

const applyStoredTheme = () => {
  if (localStorage.getItem("theme") === "light") {
    document.body.classList.add("light-mode");
  }
};

const setupThemeToggle = () => {
  const themeToggle = document.querySelector("#themeToggle");
  if (!themeToggle) return;

  themeToggle.addEventListener("click", () => {
    document.body.classList.toggle("light-mode");
    localStorage.setItem(
      "theme",
      document.body.classList.contains("light-mode") ? "light" : "dark",
    );
  });
};

const setupHomeSearch = () => {
  document.querySelectorAll("[data-home-search]").forEach((form) => {
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      document.querySelector("#featured")?.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });

      form.querySelector("input")?.blur();
    });
  });
};

const setupPlaceholderButtons = () => {
  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-ui-placeholder]");

    if (!button) {
      return;
    }

    button.classList.add("is-touched");
    window.setTimeout(() => button.classList.remove("is-touched"), 700);
  });
};

applyStoredTheme();

document.addEventListener("DOMContentLoaded", () => {
  setupHeader();
  setupThemeToggle();
  setupHomeSearch();
  setupPlaceholderButtons();

  loadTrendingMovies();
  loadTopMovies();
  loadNewMovies();
  loadMovieGrid({
    selector: "#singleMovies",
    params: {
      type: "movie",
      sort: "popular",
      page: 1,
    },
    limit: 12,
    anchorId: "single-movies",
  });
  loadMovieGrid({
    selector: "#seriesMovies",
    params: {
      type: "series",
      sort: "popular",
      page: 1,
    },
    limit: 12,
    anchorId: "series-movies",
  });
});
