document.addEventListener("DOMContentLoaded", () => {
  document.title = "Quốc gia - ThauPhim";

  const countries =
    typeof TMDB_COUNTRIES !== "undefined" && Array.isArray(TMDB_COUNTRIES)
      ? TMDB_COUNTRIES
      : [];
  const movieStatus = document.querySelector("#countryMovieStatus");
  const movieList = document.querySelector("#countryMovieList");

  if (!movieStatus || !movieList) {
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

  const renderLoadingState = () => {
    movieStatus.className = "country-movie-status is-loading";
    movieStatus.textContent = "";
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

  const createMovieCard = (movie) => {
    const article = document.createElement("article");
    article.className = "country-movie-card";

    const poster = document.createElement("div");
    poster.className = "country-movie-card__poster";

    if (movie.posterPath) {
      const image = document.createElement("img");
      image.src = `https://image.tmdb.org/t/p/w342${movie.posterPath}`;
      image.alt = `Poster phim ${movie.title}`;
      image.loading = "lazy";
      image.width = 342;
      image.height = 513;
      poster.append(image);
    } else {
      const fallback = document.createElement("i");
      fallback.className = "fa-solid fa-film";
      fallback.setAttribute("aria-hidden", "true");
      poster.classList.add("has-no-poster");
      poster.append(fallback);
    }

    const content = document.createElement("div");
    content.className = "country-movie-card__content";

    const title = document.createElement("h3");
    title.textContent = movie.title;

    const year = document.createElement("p");
    year.textContent = movie.releaseDate ? movie.releaseDate.slice(0, 4) : "Đang cập nhật";

    content.append(title, year);
    article.append(poster, content);
    return article;
  };

  const normalizeMovie = (movie) => ({
    id: movie.id,
    title: movie.title || movie.original_title || "Chưa có tên tiếng Việt",
    posterPath: movie.poster_path,
    releaseDate: movie.release_date || "",
  });

  const renderCountryError = () => {
    movieList.replaceChildren();
    movieStatus.className = "country-movie-status is-error";
    movieStatus.innerHTML =
      '<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>Không có dữ liệu</strong><span>Hãy chọn lại một quốc gia trong menu.</span>';
  };

  const loadMovies = async (country) => {
    renderLoadingState();
    document.title = `${country.name} - ThauPhim`;

    const params = new URLSearchParams({
      api_key: TMDB_API_KEY,
      language: "vi-VN",
      sort_by: "popularity.desc",
      include_adult: "false",
      page: "1",
      with_origin_country: country.code,
    });

    try {
      const response = await fetch(`https://api.themoviedb.org/3/discover/movie?${params}`);

      if (!response.ok) {
        throw new Error("TMDB request failed");
      }

      const data = await response.json();
      const movies = (data.results || []).map(normalizeMovie).slice(0, 12);
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
      movieStatus.className = "country-movie-status";
      movieStatus.textContent = "";
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
    const firstCountry = countries[0];
    if (firstCountry?.code) {
      window.location.replace(`${window.location.pathname}?code=${encodeURIComponent(firstCountry.code)}`);
      return;
    }

    renderCountryError();
    return;
  }

  const selectedCountry = countries.find(
    (country) => country.code === requestedCode.toUpperCase(),
  );

  if (!selectedCountry) {
    renderCountryError();
    return;
  }

  loadMovies(selectedCountry);
});
