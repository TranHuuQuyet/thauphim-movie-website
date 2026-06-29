document.addEventListener("DOMContentLoaded", () => {
  document.title = "Quốc gia - ThauPhim";

  const API_BASE = "/api";
  const movieTitle = document.querySelector("#country-movies-title");
  const movieStatus = document.querySelector("#countryMovieStatus");
  const movieList = document.querySelector("#countryMovieList");

  if (!movieTitle || !movieStatus || !movieList) {
    return;
  }

  const configureSharedHeader = () => {
    const header = document.querySelector(".site-header");
    const menuToggle = document.querySelector(".menu-toggle");
    const menu = document.querySelector("#primary-menu");
    const themeToggle = document.querySelector("#themeToggle");

    const applyTheme = (isLight) => {
      document.body.classList.toggle("light-mode", isLight);
      document.body.classList.toggle("light-theme", isLight);
    };

    applyTheme(localStorage.getItem("theme") === "light");

    if (themeToggle) {
      themeToggle.addEventListener("click", () => {
        const isLight = !document.body.classList.contains("light-theme");
        applyTheme(isLight);
        localStorage.setItem("theme", isLight ? "light" : "dark");
      });
    }

    if (!header || !menuToggle || !menu) return;

    const closeMenu = () => {
      header.classList.remove("is-menu-open");
      menuToggle.setAttribute("aria-expanded", "false");
    };

    const applyScrollState = () => {
      header.classList.toggle("scrolled", window.scrollY > 80);
    };

    menuToggle.addEventListener("click", () => {
      const isOpen = header.classList.toggle("is-menu-open");
      menuToggle.setAttribute("aria-expanded", String(isOpen));
    });

    document.addEventListener("click", (event) => {
      if (!header.classList.contains("is-menu-open") || header.contains(event.target)) {
        return;
      }

      closeMenu();
    });

    menu.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", closeMenu);
    });

    window.addEventListener("resize", () => {
      if (window.innerWidth > 980) closeMenu();
    });

    window.addEventListener("scroll", applyScrollState, { passive: true });
    applyScrollState();
  };

  const fetchApi = async (url) => {
    const response = await fetch(url, {
      headers: {
        Accept: "application/json",
      },
    });
    const payload = await response.json().catch(() => null);

    if (!response.ok || !payload?.success) {
      throw new Error(payload?.message || "Internal API request failed");
    }

    return payload.data;
  };

  const renderLoadingState = () => {
    movieStatus.className = "country-movie-status is-loading";
    movieStatus.textContent = "Đang tải phim...";
    movieList.replaceChildren();

    const fragment = document.createDocumentFragment();
    for (let index = 0; index < 12; index += 1) {
      const skeleton = document.createElement("div");
      skeleton.className = "country-movie-skeleton";
      skeleton.setAttribute("aria-hidden", "true");
      skeleton.innerHTML =
        '<span class="country-movie-skeleton__poster"></span><span></span><span></span>';
      fragment.append(skeleton);
    }

    movieList.append(fragment);
  };

  const appendPosterFallback = (poster) => {
    poster.classList.add("has-no-poster");
    poster.replaceChildren();

    const fallback = document.createElement("i");
    fallback.className = "fa-solid fa-film";
    fallback.setAttribute("aria-hidden", "true");
    poster.append(fallback);
  };

  const createMovieCard = (movie) => {
    const article = document.createElement("article");
    article.className = "country-movie-card";

    const poster = document.createElement("div");
    poster.className = "country-movie-card__poster";

    if (movie.posterUrl) {
      const image = document.createElement("img");
      image.src = movie.posterUrl;
      image.alt = `Poster phim ${movie.title}`;
      image.loading = "lazy";
      image.width = 342;
      image.height = 513;
      image.addEventListener("error", () => appendPosterFallback(poster), { once: true });
      poster.append(image);
    } else {
      appendPosterFallback(poster);
    }

    const content = document.createElement("div");
    content.className = "country-movie-card__content";

    const title = document.createElement("h3");
    title.textContent = movie.title;

    const year = document.createElement("p");
    year.textContent = movie.releaseYear || movie.releaseDate?.slice(0, 4) || "Đang cập nhật";

    content.append(title, year);
    article.append(poster, content);
    return article;
  };

  const normalizeMovie = (movie) => ({
    id: movie.id,
    title: movie.title || movie.original_title || "Chưa có tên tiếng Việt",
    posterUrl: movie.poster_url || null,
    releaseDate: movie.release_date || "",
    releaseYear: movie.release_year || null,
  });

  const renderCountryError = () => {
    movieList.replaceChildren();
    movieTitle.textContent = "Không tìm thấy quốc gia";
    movieStatus.className = "country-movie-status is-error";
    movieStatus.innerHTML =
      '<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>Không có dữ liệu</strong><span>Hãy chọn lại một quốc gia trong menu.</span>';
  };

  const redirectToFirstCountry = async () => {
    renderLoadingState();

    try {
      const countries = await fetchApi(`${API_BASE}/countries.php`);
      const firstCountry = Array.isArray(countries)
        ? countries.find((country) => country.code)
        : null;

      if (!firstCountry) {
        renderCountryError();
        return;
      }

      window.location.replace(
        `${window.location.pathname}?code=${encodeURIComponent(firstCountry.code)}`,
      );
    } catch (error) {
      console.error("Không thể tải danh sách quốc gia:", error);
      renderCountryError();
    }
  };

  const loadMovies = async (countryCode) => {
    renderLoadingState();

    try {
      const data = await fetchApi(
        `${API_BASE}/movies-by-country.php?code=${encodeURIComponent(countryCode)}`,
      );
      const country = data.country || { code: countryCode, name: countryCode };
      const movies = Array.isArray(data.movies) ? data.movies.map(normalizeMovie) : [];

      movieTitle.textContent = `Phim ${country.name}`;
      document.title = `${country.name} - ThauPhim`;
      movieList.replaceChildren();

      if (movies.length === 0) {
        movieStatus.className = "country-movie-status is-empty";
        movieStatus.innerHTML =
          '<i class="fa-solid fa-clapperboard" aria-hidden="true"></i><strong>Chưa có phim phù hợp</strong><span>Hãy thử chọn một quốc gia khác.</span>';
        return;
      }

      const fragment = document.createDocumentFragment();
      movies.forEach((movie) => fragment.append(createMovieCard(movie)));
      movieList.append(fragment);
      movieStatus.textContent = `Đã tìm thấy ${movies.length} phim ${country.name}.`;
      movieStatus.className = "country-movie-status is-ready";
    } catch (error) {
      console.error("Không thể tải phim theo quốc gia:", error);
      movieList.replaceChildren();
      movieStatus.className = "country-movie-status is-error";
      movieStatus.innerHTML =
        '<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>Không thể tải danh sách phim</strong><span>Vui lòng kiểm tra kết nối và thử lại.</span>';
    }
  };

  configureSharedHeader();

  const requestedCode = new URLSearchParams(window.location.search).get("code");
  if (!requestedCode) {
    redirectToFirstCountry();
    return;
  }

  const countryCode = requestedCode.trim().toUpperCase();
  if (!/^[A-Z]{2}$/.test(countryCode)) {
    renderCountryError();
    return;
  }

  loadMovies(countryCode);
});
