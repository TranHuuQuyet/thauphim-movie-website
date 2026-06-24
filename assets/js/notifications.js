document.addEventListener("DOMContentLoaded", () => {
  const root = document.querySelector("[data-notification-root]");

  if (!root) {
    return;
  }

  const toggle = root.querySelector("[data-notification-toggle]");
  const panel = root.querySelector("[data-notification-panel]");

  if (!toggle || !panel) {
    return;
  }

  const closePanel = () => {
    root.classList.remove("is-open");
    panel.hidden = true;
    toggle.setAttribute("aria-expanded", "false");
  };

  const openPanel = () => {
    document.querySelectorAll("[data-account-root].is-open").forEach((accountRoot) => {
      const accountToggle = accountRoot.querySelector("[data-account-toggle]");
      const accountPanel = accountRoot.querySelector("[data-account-panel]");

      accountRoot.classList.remove("is-open");

      if (accountPanel) {
        accountPanel.hidden = true;
      }

      if (accountToggle) {
        accountToggle.setAttribute("aria-expanded", "false");
      }
    });

    root.classList.add("is-open");
    panel.hidden = false;
    toggle.setAttribute("aria-expanded", "true");
  };

  const togglePanel = () => {
    if (panel.hidden) {
      openPanel();
      return;
    }

    closePanel();
  };

  toggle.addEventListener("click", (event) => {
    event.stopPropagation();
    togglePanel();
  });

  panel.addEventListener("click", (event) => {
    event.stopPropagation();
  });

  root.querySelectorAll("[data-open-login]").forEach((loginTrigger) => {
    loginTrigger.addEventListener("click", () => {
      closePanel();
    });
  });

  document.addEventListener("click", (event) => {
    if (!root.contains(event.target)) {
      closePanel();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape" || panel.hidden) {
      return;
    }

    closePanel();
    toggle.focus();
  });
});
