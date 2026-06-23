document.addEventListener("DOMContentLoaded", () => {
  const openLoginTriggers = document.querySelectorAll("#openLogin, [data-open-login]");
  const authModal = document.querySelector("#authModal");
  const closeAuth = document.querySelector("#closeAuth");

  if (!authModal || !closeAuth) {
    return;
  }

  const openAuthModal = (event) => {
    event.preventDefault();
    authModal.classList.add("active");
  };

  openLoginTriggers.forEach((trigger) => {
    trigger.addEventListener("click", openAuthModal);
  });

  closeAuth.addEventListener("click", (event) => {
    event.preventDefault();
    authModal.classList.remove("active");
  });
});
