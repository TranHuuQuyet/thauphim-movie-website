(() => {
  const STORAGE_KEY = "theme";

  const getStoredTheme = () => {
    try {
      return localStorage.getItem(STORAGE_KEY) === "light" ? "light" : "dark";
    } catch (error) {
      return "dark";
    }
  };

  const updateToggle = (theme) => {
    const toggle = document.getElementById("themeToggle");

    if (!toggle) {
      return;
    }

    const isLight = theme === "light";
    toggle.setAttribute("aria-pressed", String(isLight));
    toggle.setAttribute(
      "aria-label",
      isLight ? "Chuyển sang giao diện tối" : "Chuyển sang giao diện sáng",
    );
    toggle.title = isLight ? "Chuyển sang giao diện tối" : "Chuyển sang giao diện sáng";
  };

  const applyTheme = (theme) => {
    const normalizedTheme = theme === "light" ? "light" : "dark";
    const isLight = normalizedTheme === "light";

    document.documentElement.dataset.theme = normalizedTheme;

    if (document.body) {
      document.body.classList.toggle("light-mode", isLight);
      document.body.classList.toggle("light-theme", isLight);
    }

    updateToggle(normalizedTheme);
  };

  const storeTheme = (theme) => {
    try {
      localStorage.setItem(STORAGE_KEY, theme);
    } catch (error) {
      // The selected theme still applies for the current page when storage is blocked.
    }
  };

  const initializeTheme = () => {
    applyTheme(getStoredTheme());

    const toggle = document.getElementById("themeToggle");
    if (toggle && toggle.dataset.themeReady !== "true") {
      toggle.dataset.themeReady = "true";
      toggle.addEventListener("click", () => {
        const nextTheme =
          document.documentElement.dataset.theme === "light" ? "dark" : "light";
        applyTheme(nextTheme);
        storeTheme(nextTheme);
      });
    }

    const scrollTopBtn = document.getElementById("scrollTopBtn");
    if (!scrollTopBtn) {
      return;
    }

    const updateScrollButton = () => {
      scrollTopBtn.classList.toggle("show", window.scrollY > 400);
    };

    window.addEventListener("scroll", updateScrollButton, { passive: true });
    scrollTopBtn.addEventListener("click", () => {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
    updateScrollButton();
  };

  applyTheme(getStoredTheme());

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initializeTheme, { once: true });
  } else {
    initializeTheme();
  }
})();
