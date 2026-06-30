document.addEventListener("DOMContentLoaded", () => {
  const root = document.querySelector("[data-notification-root]");

  if (!root) {
    return;
  }

  const toggle = root.querySelector("[data-notification-toggle]");
  const panel = root.querySelector("[data-notification-panel]");
  const badge = root.querySelector("[data-notification-badge]");
  const countLabel = root.querySelector("[data-notification-count-label]");
  const markReadUrl = root.dataset.notificationMarkReadUrl || "/api/notifications.php";
  let hasMarkedVisibleNotifications = false;

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
    markVisibleNotificationsRead();
  };

  const markVisibleNotificationsRead = async () => {
    if (hasMarkedVisibleNotifications) {
      return;
    }

    const scheduleIds = Array.from(root.querySelectorAll("[data-notification-item][data-schedule-id]"))
      .map((item) => Number.parseInt(item.dataset.scheduleId || "", 10))
      .filter((id) => Number.isInteger(id) && id > 0);

    if (scheduleIds.length === 0) {
      return;
    }

    try {
      const response = await fetch(markReadUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ schedule_ids: scheduleIds }),
      });

      if (!response.ok) {
        return;
      }

      const payload = await response.json();

      if (!payload.success) {
        return;
      }

      hasMarkedVisibleNotifications = true;

      if (badge) {
        badge.hidden = true;
        badge.textContent = "";
      }

      if (countLabel) {
        countLabel.textContent = "0 chưa đọc";
      }

      root.querySelectorAll("[data-notification-item]").forEach((item) => {
        item.classList.remove("notification-item--unread");
        item.querySelectorAll(".notification-item__dot").forEach((dot) => dot.remove());
      });
    } catch (error) {
      console.error("Unable to mark notifications as read:", error);
    }
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
