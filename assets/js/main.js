document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector(".site-header");
  const toggleButton = document.querySelector(".menu-toggle");
  const menu = document.getElementById("primary-menu");

  if (!header || !toggleButton || !menu) {
    return;
  }

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
});
// banel
const header = document.querySelector(".site-header");

window.addEventListener("scroll", () => {
  if (window.scrollY > 80) {
    header.classList.add("scrolled");
  } else {
    header.classList.remove("scrolled");
  }
});
// darklightmode
const themeToggle = document.getElementById("themeToggle");

if (localStorage.getItem("theme") === "light") {
  document.body.classList.add("light-mode");
}

themeToggle.addEventListener("click", () => {
  document.body.classList.toggle("light-mode");

  if (document.body.classList.contains("light-mode")) {
    localStorage.setItem("theme", "light");
  } else {
    localStorage.setItem("theme", "dark");
  }
});
