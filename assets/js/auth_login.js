document.addEventListener("DOMContentLoaded", () => {
  const openLogin = document.querySelector("#openLogin");
  const authModal = document.querySelector("#authModal");
  const closeAuth = document.querySelector("#closeAuth");

  if (!openLogin || !authModal || !closeAuth) {
    return;
  }

  openLogin.addEventListener("click", (event) => {
    event.preventDefault();
    authModal.classList.add("active");
  });

  closeAuth.addEventListener("click", (event) => {
    event.preventDefault();
    authModal.classList.remove("active");
  });
});
