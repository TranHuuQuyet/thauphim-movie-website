document.addEventListener("DOMContentLoaded", () => {
  const authModal = document.querySelector("#authModal");
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  const setAuthMode = (root, mode) => {
    if (!root || !["login", "register"].includes(mode)) {
      return;
    }

    root.dataset.authMode = mode;

    root.querySelectorAll("[data-auth-tab]").forEach((tab) => {
      const isActive = tab.dataset.authTab === mode;
      tab.classList.toggle("is-active", isActive);
      tab.setAttribute("aria-selected", String(isActive));
    });

    root.querySelectorAll("[data-auth-panel]").forEach((panel) => {
      panel.hidden = panel.dataset.authPanel !== mode;
    });
  };

  const openAuthModal = (mode = "login") => {
    if (!authModal) {
      return;
    }

    const root = authModal.querySelector("[data-auth-root]");
    setAuthMode(root, mode);
    authModal.classList.add("active");
  };

  const closeAuthModal = () => {
    if (!authModal) {
      return;
    }

    authModal.classList.remove("active");
  };

  document.querySelectorAll("[data-auth-root]").forEach((root) => {
    const initialMode = root.dataset.authMode || "login";
    setAuthMode(root, initialMode);

    root.querySelectorAll("[data-auth-tab]").forEach((tab) => {
      tab.addEventListener("click", () => {
        setAuthMode(root, tab.dataset.authTab || "login");
      });
    });

    root.querySelectorAll("[data-auth-switch]").forEach((switcher) => {
      switcher.addEventListener("click", (event) => {
        event.preventDefault();
        setAuthMode(root, switcher.dataset.authSwitch || "login");
      });
    });

    const slides = Array.from(root.querySelectorAll("[data-auth-slide]"));
    const dots = Array.from(root.querySelectorAll("[data-auth-dot]"));

    if (slides.length > 1) {
      let activeIndex = Math.max(0, slides.findIndex((slide) => slide.classList.contains("is-active")));

      const showSlide = (nextIndex) => {
        activeIndex = (nextIndex + slides.length) % slides.length;

        slides.forEach((slide, index) => {
          slide.classList.toggle("is-active", index === activeIndex);
        });

        dots.forEach((dot, index) => {
          dot.classList.toggle("is-active", index === activeIndex);
        });
      };

      dots.forEach((dot, index) => {
        dot.addEventListener("click", () => showSlide(index));
      });

      if (!reduceMotion) {
        window.setInterval(() => showSlide(activeIndex + 1), 4800);
      }
    }
  });

  document.querySelectorAll("#openLogin, [data-open-login]").forEach((trigger) => {
    trigger.addEventListener("click", (event) => {
      event.preventDefault();
      openAuthModal("login");
    });
  });

  document.querySelectorAll("[data-open-register]").forEach((trigger) => {
    trigger.addEventListener("click", (event) => {
      event.preventDefault();
      openAuthModal("register");
    });
  });

  authModal?.querySelectorAll("[data-auth-close]").forEach((closeButton) => {
    closeButton.addEventListener("click", (event) => {
      event.preventDefault();
      closeAuthModal();
    });
  });

  authModal?.addEventListener("click", (event) => {
    if (event.target === authModal) {
      closeAuthModal();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && authModal?.classList.contains("active")) {
      closeAuthModal();
    }
  });

  if (window.location.hash === "#authModal") {
    openAuthModal("login");
  }
});
