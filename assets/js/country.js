document.addEventListener("DOMContentLoaded", () => {
  document.title = "Quốc gia - ThauPhim";

  const countries = [
    { code: "VN", name: "Việt Nam", region: "Đông Nam Á" },
    { code: "KR", name: "Hàn Quốc", region: "Đông Á" },
    { code: "CN", name: "Trung Quốc", region: "Đông Á" },
    { code: "JP", name: "Nhật Bản", region: "Đông Á" },
    { code: "TH", name: "Thái Lan", region: "Đông Nam Á" },
    { code: "US", name: "Hoa Kỳ", region: "Bắc Mỹ" },
    { code: "GB", name: "Anh", region: "Châu Âu" },
    { code: "FR", name: "Pháp", region: "Châu Âu" },
    { code: "IN", name: "Ấn Độ", region: "Nam Á" },
    { code: "TW", name: "Đài Loan", region: "Đông Á" },
    { code: "HK", name: "Hồng Kông", region: "Đông Á" },
    { code: "CA", name: "Canada", region: "Bắc Mỹ" },
    { code: "ES", name: "Tây Ban Nha", region: "Châu Âu" },
    { code: "DE", name: "Đức", region: "Châu Âu" },
    { code: "IT", name: "Ý", region: "Châu Âu" },
    { code: "AU", name: "Úc", region: "Châu Đại Dương" },
    { code: "PH", name: "Philippines", region: "Đông Nam Á" },
    { code: "ID", name: "Indonesia", region: "Đông Nam Á" },
    { code: "BR", name: "Brazil", region: "Nam Mỹ" },
    { code: "MX", name: "Mexico", region: "Bắc Mỹ" },
  ];

  const countryList = document.querySelector("#countryList");
  const countrySearch = document.querySelector("#countrySearch");
  const countrySearchForm = document.querySelector("#countrySearchForm");
  const clearCountrySearch = document.querySelector("#clearCountrySearch");
  const countryResultCount = document.querySelector("#countryResultCount");
  const countryEmpty = document.querySelector("#countryEmpty");
  const movieSection = document.querySelector("#countryMovies");
  const movieSectionTitle = document.querySelector("#country-movies-title");
  const movieDescription = document.querySelector("#countryMoviesDescription");
  const movieStatus = document.querySelector("#countryMovieStatus");
  const movieList = document.querySelector("#countryMovieList");
  let latestMovieRequest = 0;

  if (
    !countryList ||
    !countrySearch ||
    !countrySearchForm ||
    !clearCountrySearch ||
    !countryResultCount ||
    !countryEmpty ||
    !movieSection ||
    !movieSectionTitle ||
    !movieDescription ||
    !movieStatus ||
    !movieList
  ) {
    return;
  }

  const normalizeText = (value) =>
    value
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .toLocaleLowerCase("vi");

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
  };

  const createCountryCard = (country) => {
    const item = document.createElement("div");
    item.className = "country-grid__item";
    item.setAttribute("role", "listitem");

    const button = document.createElement("button");
    button.className = "country-card";
    button.type = "button";
    button.dataset.countryCode = country.code;
    button.setAttribute("aria-pressed", "false");

    const code = document.createElement("span");
    code.className = "country-card__code";
    code.textContent = country.code;
    code.setAttribute("aria-hidden", "true");

    const copy = document.createElement("span");
    copy.className = "country-card__copy";

    const name = document.createElement("strong");
    name.textContent = country.name;

    const region = document.createElement("small");
    region.textContent = country.region;

    const arrow = document.createElement("i");
    arrow.className = "fa-solid fa-arrow-right country-card__arrow";
    arrow.setAttribute("aria-hidden", "true");

    copy.append(name, region);
    button.append(code, copy, arrow);
    button.addEventListener("click", () => selectCountry(country, true));
    item.append(button);

    return item;
  };

  const renderCountries = (query = "") => {
    const normalizedQuery = normalizeText(query.trim());
    const filteredCountries = countries.filter((country) =>
      normalizeText(`${country.name} ${country.region} ${country.code}`).includes(normalizedQuery),
    );

    const fragment = document.createDocumentFragment();
    filteredCountries.forEach((country) => fragment.append(createCountryCard(country)));

    countryList.replaceChildren(fragment);
    countryResultCount.textContent = `${filteredCountries.length} quốc gia`;
    countryEmpty.hidden = filteredCountries.length !== 0;
    countryList.hidden = filteredCountries.length === 0;

    const activeCode = new URLSearchParams(window.location.search).get("code");
    if (activeCode) updateActiveCountry(activeCode.toUpperCase());
  };

  const updateActiveCountry = (countryCode) => {
    countryList.querySelectorAll(".country-card").forEach((button) => {
      const isActive = button.dataset.countryCode === countryCode;
      button.classList.toggle("is-active", isActive);
      button.setAttribute("aria-pressed", String(isActive));
    });
  };

  const renderLoadingState = () => {
    movieStatus.textContent = "Đang tải phim...";
    movieStatus.className = "country-movie-status is-loading";
    movieList.replaceChildren();

    const fragment = document.createDocumentFragment();
    for (let index = 0; index < 10; index += 1) {
      const skeleton = document.createElement("div");
      skeleton.className = "country-movie-skeleton";
      skeleton.setAttribute("aria-hidden", "true");
      skeleton.innerHTML = '<span class="country-movie-skeleton__poster"></span><span></span><span></span>';
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

    const type = document.createElement("span");
    type.className = "country-movie-card__type";
    type.textContent = movie.type === "tv" ? "Phim bộ" : "Phim lẻ";
    poster.append(type);

    const content = document.createElement("div");
    content.className = "country-movie-card__content";

    const title = document.createElement("h3");
    title.textContent = movie.title;

    const metadata = document.createElement("p");
    const year = movie.releaseDate ? movie.releaseDate.slice(0, 4) : "Đang cập nhật";
    metadata.textContent = year;

    content.append(title, metadata);
    article.append(poster, content);
    return article;
  };

  const normalizeMovie = (movie, type) => ({
    id: `${type}-${movie.id}`,
    type,
    title: movie.title || movie.name || "Chưa có tên tiếng Việt",
    posterPath: movie.poster_path,
    releaseDate: movie.release_date || movie.first_air_date || "",
    popularity: Number(movie.popularity) || 0,
  });

  const loadMovies = async (country) => {
    const requestId = ++latestMovieRequest;
    renderLoadingState();

    const commonParams = new URLSearchParams({
      api_key: TMDB_API_KEY,
      language: "vi-VN",
      sort_by: "popularity.desc",
      include_adult: "false",
      page: "1",
      with_origin_country: country.code,
    });

    try {
      const [movieResponse, tvResponse] = await Promise.all([
        fetch(`https://api.themoviedb.org/3/discover/movie?${commonParams}`),
        fetch(`https://api.themoviedb.org/3/discover/tv?${commonParams}`),
      ]);

      if (!movieResponse.ok || !tvResponse.ok) {
        throw new Error("TMDB request failed");
      }

      const [movieData, tvData] = await Promise.all([movieResponse.json(), tvResponse.json()]);

      if (requestId !== latestMovieRequest) return;

      const movies = [
        ...(movieData.results || []).map((movie) => normalizeMovie(movie, "movie")),
        ...(tvData.results || []).map((movie) => normalizeMovie(movie, "tv")),
      ]
        .sort((first, second) => second.popularity - first.popularity)
        .slice(0, 12);

      movieList.replaceChildren();

      if (movies.length === 0) {
        movieStatus.className = "country-movie-status is-empty";
        movieStatus.innerHTML = '<i class="fa-solid fa-clapperboard" aria-hidden="true"></i><strong>Chưa có phim phù hợp</strong><span>Hãy thử chọn một quốc gia khác.</span>';
        return;
      }

      const fragment = document.createDocumentFragment();
      movies.forEach((movie) => fragment.append(createMovieCard(movie)));
      movieList.append(fragment);
      movieStatus.textContent = `Đã tìm thấy ${movies.length} phim nổi bật.`;
      movieStatus.className = "country-movie-status is-ready";
    } catch (error) {
      if (requestId !== latestMovieRequest) return;

      console.error("Không thể tải phim theo quốc gia:", error);
      movieList.replaceChildren();
      movieStatus.className = "country-movie-status is-error";
      movieStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i><strong>Không thể tải danh sách phim</strong><span>Vui lòng kiểm tra kết nối và thử lại.</span>';
    }
  };

  async function selectCountry(country, shouldScroll) {
    updateActiveCountry(country.code);
    movieSectionTitle.textContent = `Phim nổi bật tại ${country.name}`;
    movieDescription.textContent = `Phim lẻ và phim bộ phổ biến có nguồn gốc từ ${country.name}.`;

    const url = new URL(window.location.href);
    url.searchParams.set("code", country.code);
    window.history.replaceState({}, "", url);

    if (shouldScroll) {
      const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      movieSection.scrollIntoView({ behavior: reduceMotion ? "auto" : "smooth", block: "start" });
    }

    await loadMovies(country);
  }

  countrySearchForm.addEventListener("submit", (event) => event.preventDefault());
  countrySearch.addEventListener("input", () => renderCountries(countrySearch.value));
  clearCountrySearch.addEventListener("click", () => {
    countrySearch.value = "";
    renderCountries();
    countrySearch.focus();
  });

  configureSharedHeader();
  renderCountries();

  const requestedCode = new URLSearchParams(window.location.search).get("code");
  const initialCountry =
    countries.find((country) => country.code === requestedCode?.toUpperCase()) || countries[0];
  selectCountry(initialCountry, false);
});
