document.addEventListener("DOMContentLoaded", () => {
  const flowRoot = document.querySelector("[data-reset-flow]");

  if (!flowRoot) {
    return;
  }

  const toastRegion = flowRoot.querySelector("[data-reset-toast-region]");
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const providers = {
    "gmail.com": "https://mail.google.com/",
    "googlemail.com": "https://mail.google.com/",
    "outlook.com": "https://outlook.live.com/mail/0/",
    "hotmail.com": "https://outlook.live.com/mail/0/",
    "live.com": "https://outlook.live.com/mail/0/",
    "msn.com": "https://outlook.live.com/mail/0/",
    "yahoo.com": "https://mail.yahoo.com/",
    "ymail.com": "https://mail.yahoo.com/",
    "icloud.com": "https://www.icloud.com/mail/",
    "me.com": "https://www.icloud.com/mail/",
    "mac.com": "https://www.icloud.com/mail/",
    "proton.me": "https://mail.proton.me/",
    "protonmail.com": "https://mail.proton.me/",
    "aol.com": "https://mail.aol.com/",
    "zoho.com": "https://mail.zoho.com/",
  };

  const showToast = (message, type = "success") => {
    if (!toastRegion || !message) {
      return;
    }

    const toast = document.createElement("div");
    toast.className = `reset-auth-toast reset-auth-toast--${type}`;
    toast.textContent = message;
    toastRegion.appendChild(toast);

    window.setTimeout(() => {
      toast.remove();
    }, 3600);
  };

  const setFieldError = (field, message) => {
    if (!field) {
      return;
    }

    const error = field.querySelector("[data-field-error]");
    const input = field.querySelector("input");

    if (error) {
      error.textContent = message || "";
    }

    if (input) {
      input.setAttribute("aria-invalid", message ? "true" : "false");
    }
  };

  const setLoading = (form, loading, text) => {
    const button = form.querySelector("[data-submit-button]");
    const buttonText = form.querySelector("[data-button-text]");

    form.classList.toggle("is-loading", loading);

    if (button) {
      button.disabled = loading;
      button.setAttribute("aria-busy", String(loading));
    }

    if (buttonText && text) {
      buttonText.textContent = text;
    }
  };

  const firstServerError = flowRoot.querySelector("[data-server-errors] p");
  if (firstServerError) {
    showToast(firstServerError.textContent.trim(), "error");
  }

  const autofocusTarget = flowRoot.querySelector("[data-autofocus]");
  if (autofocusTarget) {
    window.setTimeout(() => autofocusTarget.focus(), 80);
  }

  flowRoot.querySelectorAll("[data-webmail-link]").forEach((link) => {
    const email = (link.dataset.email || "").trim().toLowerCase();
    const domain = email.includes("@") ? email.split("@").pop() : "";
    const inboxUrl = providers[domain];

    if (!inboxUrl) {
      link.hidden = true;
      return;
    }

    link.href = inboxUrl;
  });

  flowRoot.querySelectorAll("[data-toggle-password]").forEach((toggle) => {
    const input = toggle.parentElement?.querySelector("input");
    const icon = toggle.querySelector("i");

    if (!input) {
      return;
    }

    toggle.addEventListener("click", () => {
      const showPassword = input.type === "password";
      input.type = showPassword ? "text" : "password";
      toggle.setAttribute("aria-label", showPassword ? "Hide password" : "Show password");

      if (icon) {
        icon.classList.toggle("fa-eye", !showPassword);
        icon.classList.toggle("fa-eye-slash", showPassword);
      }
    });
  });

  const validateEmailForm = (form) => {
    const input = form.querySelector("[data-email-input]");
    const field = form.querySelector('[data-field="email"]');
    const value = (input?.value || "").trim();

    if (!value) {
      setFieldError(field, "Email is required");
      input?.focus();
      return false;
    }

    if (!emailPattern.test(value)) {
      setFieldError(field, "Enter a valid email address");
      input?.focus();
      return false;
    }

    setFieldError(field, "");
    return true;
  };

  flowRoot.querySelectorAll('[data-reset-form="forgot"]').forEach((form) => {
    const input = form.querySelector("[data-email-input]");

    input?.addEventListener("input", () => {
      if ((input.value || "").trim() === "" || emailPattern.test(input.value.trim())) {
        setFieldError(form.querySelector('[data-field="email"]'), "");
      }
    });

    form.addEventListener("submit", (event) => {
      if (!validateEmailForm(form)) {
        event.preventDefault();
        showToast("Check the email field before continuing.", "error");
        return;
      }

      setLoading(form, true, "Sending...");
    });
  });

  flowRoot.querySelectorAll('[data-reset-form="resend"]').forEach((form) => {
    const button = form.querySelector("[data-submit-button]");
    const buttonText = form.querySelector("[data-button-text]");
    const email = form.querySelector('input[name="email"]')?.value || "unknown";
    const seconds = Math.max(1, Number.parseInt(form.dataset.resendCooldown || "30", 10));
    const storageKey = `thau-reset-resend:${email}`;
    const now = Date.now();
    const storedExpiry = Number.parseInt(sessionStorage.getItem(storageKey) || "0", 10);
    let expiresAt = storedExpiry > now ? storedExpiry : now + seconds * 1000;

    sessionStorage.setItem(storageKey, String(expiresAt));

    const updateCooldown = () => {
      const remaining = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));

      if (!button || !buttonText) {
        return;
      }

      if (remaining > 0) {
        button.disabled = true;
        buttonText.textContent = `Resend Email (${remaining}s)`;
        return;
      }

      button.disabled = false;
      buttonText.textContent = "Resend Email";
    };

    updateCooldown();
    const timer = window.setInterval(() => {
      updateCooldown();

      if (Date.now() >= expiresAt) {
        window.clearInterval(timer);
      }
    }, 500);

    form.addEventListener("submit", (event) => {
      if (Date.now() < expiresAt) {
        event.preventDefault();
        return;
      }

      expiresAt = Date.now() + seconds * 1000;
      sessionStorage.setItem(storageKey, String(expiresAt));
      setLoading(form, true, "Resending...");
    });
  });

  const evaluatePassword = (password) => {
    const rules = {
      length: password.length >= 8,
      uppercase: /[A-Z]/.test(password),
      number: /\d/.test(password),
      special: /[^A-Za-z0-9]/.test(password),
      long: password.length >= 12,
    };
    const score = Object.values(rules).filter(Boolean).length;
    const level = password.length === 0 ? 1 : Math.max(1, Math.min(5, score));
    const labels = ["Weak", "Fair", "Good", "Strong", "Excellent"];

    return {
      rules,
      level,
      label: labels[level - 1],
    };
  };

  const updatePasswordStrength = (form) => {
    const password = form.querySelector("[data-password-input]")?.value || "";
    const result = evaluatePassword(password);
    const meter = form.querySelector("[data-strength-meter]");
    const label = form.querySelector("[data-strength-label]");

    if (meter) {
      meter.dataset.level = String(result.level);
    }

    if (label) {
      label.textContent = result.label;
    }

    Object.entries(result.rules).forEach(([rule, met]) => {
      if (rule === "long") {
        return;
      }

      form.querySelector(`[data-password-rule="${rule}"]`)?.classList.toggle("is-met", met);
    });
  };

  const validatePasswordForm = (form) => {
    const passwordInput = form.querySelector("[data-password-input]");
    const confirmInput = form.querySelector("[data-confirm-input]");
    const password = passwordInput?.value || "";
    const confirm = confirmInput?.value || "";
    const passwordField = form.querySelector('[data-field="password"]');
    const confirmField = form.querySelector('[data-field="confirm"]');
    let valid = true;

    if (!password) {
      setFieldError(passwordField, "New password is required");
      valid = false;
    } else if (password.length < 6) {
      setFieldError(passwordField, "Use at least 6 characters.");
      valid = false;
    } else {
      setFieldError(passwordField, "");
    }

    if (!confirm) {
      setFieldError(confirmField, "Confirm your new password");
      valid = false;
    } else if (password !== confirm) {
      setFieldError(confirmField, "Passwords do not match");
      valid = false;
    } else {
      setFieldError(confirmField, "");
    }

    if (!valid) {
      (passwordInput && !password ? passwordInput : confirmInput)?.focus();
    }

    return valid;
  };

  const showPasswordChangedState = (form) => {
    const targetSelector = form.dataset.successState;
    const state = targetSelector ? document.querySelector(targetSelector) : null;

    if (!state) {
      return false;
    }

    form.hidden = true;
    state.hidden = false;
    showToast("Password changed successfully.", "success");
    state.querySelector("a, button")?.focus();
    return true;
  };

  const submitPasswordWithFetch = async (form) => {
    setLoading(form, true, "Resetting...");

    try {
      const response = await fetch(form.action, {
        method: form.method || "POST",
        body: new FormData(form),
        credentials: "same-origin",
      });

      if (response.redirected) {
        showPasswordChangedState(form);
        return;
      }

      const html = await response.text();
      const documentFragment = new DOMParser().parseFromString(html, "text/html");
      const errors = Array.from(documentFragment.querySelectorAll("[data-server-errors] p"))
        .map((item) => item.textContent.trim())
        .filter(Boolean);

      showToast(errors[0] || "Unable to reset password. Please refresh and try again.", "error");
      setLoading(form, false, "Reset Password");
    } catch (error) {
      showToast("Network error. Please try again.", "error");
      setLoading(form, false, "Reset Password");
    }
  };

  flowRoot.querySelectorAll('[data-reset-form="password"]').forEach((form) => {
    const passwordInput = form.querySelector("[data-password-input]");
    const confirmInput = form.querySelector("[data-confirm-input]");

    updatePasswordStrength(form);

    passwordInput?.addEventListener("input", () => {
      updatePasswordStrength(form);
      validatePasswordForm(form);
    });

    confirmInput?.addEventListener("input", () => {
      validatePasswordForm(form);
    });

    form.addEventListener("submit", (event) => {
      event.preventDefault();

      if (!validatePasswordForm(form)) {
        showToast("Check the password fields before continuing.", "error");
        return;
      }

      submitPasswordWithFetch(form);
    });
  });
});
