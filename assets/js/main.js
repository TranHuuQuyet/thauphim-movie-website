document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu
  const header = document.querySelector(".site-header");
  const toggleButton = document.querySelector(".menu-toggle");
  const menu = document.getElementById("primary-menu");

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
  // Header scroll
  if (header) {
    window.addEventListener("scroll", () => {
      header.classList.toggle("scrolled", window.scrollY > 80);
    });
  }
  // Dark / light mode
  const themeToggle = document.getElementById("themeToggle");

  if (localStorage.getItem("theme") === "light") {
    document.body.classList.add("light-mode");
  }

  if (themeToggle) {
    themeToggle.addEventListener("click", () => {
      document.body.classList.toggle("light-mode");

      if (document.body.classList.contains("light-mode")) {
        localStorage.setItem("theme", "light");
      } else {
        localStorage.setItem("theme", "dark");
      }
    });
  }
});

// Slide phim nổi bật

const trendingmovieList = document.querySelector("#trendingMovie");
const trendingUrl = `https://api.themoviedb.org/3/trending/movie/day?api_key=${TMDB_API_KEY}&language=vi-VN`;
const loadtrendingMovie = async () => {
  if (!trendingmovieList) return;
  try {
    const trendingresponse = await fetch(trendingUrl);
    const trendingdata = await trendingresponse.json();
    trendingmovieList.innerHTML = trendingdata.results
      .slice(0, 30)
      .map(
        (movie) => `
   <div class="swiper-slide">
  <div class="movie-card">
    <img
      src="https://image.tmdb.org/t/p/w500${movie.poster_path}"
      alt="${movie.title}"
      loading="lazy"
    >
    

    <span class="movie-status">P.Đề</span>
  </div>

  <div class="movie-card-info">
    <h4 class="movie-title">${movie.title}</h4>
    <p class="movie-original">“${movie.original_title}”</p>
  </div>
</div>
  `,
      )
      .join("");

    new Swiper(".movieSwiper", {
      slidesPerView: 3,
      spaceBetween: 24,
      loop: true,
      speed: 800,
      autoplay: {
        delay: 2500,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: ".movie-next",
        prevEl: ".movie-prev",
      },
      breakpoints: {
        0: { slidesPerView: 1.2, spaceBetween: 14 },
        768: { slidesPerView: 2, spaceBetween: 18 },
        1024: { slidesPerView: 3, spaceBetween: 24 },
      },
    });
  } catch (error) {
    console.log(error);
  }
};

loadtrendingMovie();

//phim moi gan day
const heroMovies = document.querySelector("#heroMovies");
const thumbMovies = document.querySelector("#thumbMovies");
const url = `https://api.themoviedb.org/3/movie/now_playing?api_key=${TMDB_API_KEY}&language=vi-VN&page=1`;

const loadheroMovies = async () => {
  if (!heroMovies || !thumbMovies) return;
  try {
    const response = await fetch(url);
    const data = await response.json();
    const movies = data.results.slice(0, 11);
    heroMovies.innerHTML = movies
      .map((movie) => {
        const backdrop = movie.backdrop_path
          ? `https://image.tmdb.org/t/p/original${movie.backdrop_path}`
          : "assets/images/pic1.webp";
        const year = movie.release_date
          ? movie.release_date.slice(0, 4)
          : "Đang cập nhật";
        return `
          <div class="swiper-slide movie-hero-slide">
            <img class="movie-hero-bg" src="${backdrop}" alt="${movie.title}">

            <div class="movie-hero-content">
              <h2>${movie.title}</h2>
              <p class="movie-original">${movie.original_title}</p>

              <div class="movie-meta">
                <span>IMDb ${movie.vote_average.toFixed(1)}</span>
                <span>T18</span>
                <span>${year}</span>
                <span>Phim lẻ</span>
                <span>HD</span>
              </div>

              <p class="movie-summary">
                ${movie.overview || "Nội dung phim đang được cập nhật."}
              </p>

              <div class="movie-actions">
                <a class="watch-button" href="#">
                  <i class="fa-solid fa-play"></i>
                </a>
                <button type="button">
                  <i class="fa-solid fa-heart"></i>
                </button>
                <button type="button">
                  <i class="fa-solid fa-circle-info"></i>
                </button>
              </div>
            </div>
          </div>
        `;
      })
      .join("");
    thumbMovies.innerHTML = data.results
      .slice(0, 11)
      .map((movie) => {
        const poster = movie.poster_path
          ? `https://image.tmdb.org/t/p/w300${movie.poster_path}`
          : "assets/images/pic1.webp";
        return `
       <div class="swiper-slide"><img src="${poster}" alt="${movie.title}"></div>
`;
      })
      .join("");
    const movieThumbs = new Swiper(".movieThumbSwiper", {
      slidesPerView: "auto",
      spaceBetween: 16,
      watchSlidesProgress: true,
      slideToClickedSlide: true,
    });
    new Swiper(".movieHeroSwiper", {
      effect: "fade",
      speed: 700,
      loop: false,
      rewind: true,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: ".hero-next",
        prevEl: ".hero-prev",
      },
      thumbs: { swiper: movieThumbs },
    });
  } catch (error) {
    console.log(error);
  }
};
loadheroMovies();
//phim top tuan
const topMovies = document.querySelector("#topMovies");
const topUrl = `https://api.themoviedb.org/3/movie/top_rated?api_key=${TMDB_API_KEY}&language=en-vi-VN&page=1`;

const loadtopMovies = async () => {
  if (!topMovies) return;
  try {
    const response = await fetch(topUrl);
    const data = await response.json();
    const movies = data.results;
    topMovies.innerHTML = data.results
      .slice(0, 10)
      .map((movie, index) => {
        return ` <div class="swiper-slide">
                    <img src="https://image.tmdb.org/t/p/w500${movie.backdrop_path}" alt="">
                    <div class="slide-content">
                        <span class="movie-rank">${index + 1}</span>
                         <h3>${movie.title}</h3>
                         <div class="movie-vote">
                        ⭐ ${movie.vote_average.toFixed(1)}
                         </div>
                         <button class="play-btn">XEM NGAY</button>
                        <p>${movie.overview}</p>
                    </div>
                </div>`;
      })
      .join("");
    new Swiper(".bannerSwiper", {
      loop: true,
      centeredSlides: true,
      slidesPerView: "auto",
      spaceBetween: 10,
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
        delay: 2500,
        disableOnInteraction: false,
      },

      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },

      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
    });
  } catch (error) {
    console.log(error);
  }
};
loadtopMovies();
