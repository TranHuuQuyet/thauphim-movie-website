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

  // Slide phim nổi bật
  if (document.querySelector(".movieSwiper")) {
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
        0: {
          slidesPerView: 1.2,
          spaceBetween: 14,
        },
        768: {
          slidesPerView: 2,
          spaceBetween: 18,
        },
        1024: {
          slidesPerView: 3,
          spaceBetween: 24,
        },
      },
    });
  }

  // Slide phim mới gần đây
  const heroSlider = document.querySelector(".movieHeroSwiper");
  const thumbSlider = document.querySelector(".movieThumbSwiper");

  if (heroSlider && thumbSlider) {
    const movieThumbs = new Swiper(".movieThumbSwiper", {
      slidesPerView: "auto",
      spaceBetween: 16,
      watchSlidesProgress: true,
      slideToClickedSlide: true,
    });

    const movieHero = new Swiper(".movieHeroSwiper", {
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
      thumbs: {
        swiper: movieThumbs,
      },
    });

    document
      .querySelectorAll(".movieThumbSwiper .swiper-slide")
      .forEach((thumb, index) => {
        thumb.addEventListener("click", () => {
          movieHero.slideTo(index);
          movieThumbs.slideTo(index);
        });
      });

    movieHero.on("slideChange", () => {
      movieThumbs.slideTo(movieHero.activeIndex);
    });
  }
});
