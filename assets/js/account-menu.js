document.addEventListener("DOMContentLoaded", () => {
  const root = document.querySelector("[data-account-root]");

  if (!root) {
    return;
  }

  const toggle = root.querySelector("[data-account-toggle]");
  const panel = root.querySelector("[data-account-panel]");

  if (!toggle || !panel) {
    return;
  }

  const closeNotificationPanels = () => {
    document.querySelectorAll("[data-notification-root].is-open").forEach((notificationRoot) => {
      const notificationToggle = notificationRoot.querySelector("[data-notification-toggle]");
      const notificationPanel = notificationRoot.querySelector("[data-notification-panel]");

      notificationRoot.classList.remove("is-open");

      if (notificationPanel) {
        notificationPanel.hidden = true;
      }

      if (notificationToggle) {
        notificationToggle.setAttribute("aria-expanded", "false");
      }
    });
  };

  const closePanel = () => {
    root.classList.remove("is-open");
    panel.hidden = true;
    toggle.setAttribute("aria-expanded", "false");
  };

  const openPanel = () => {
    closeNotificationPanels();
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
