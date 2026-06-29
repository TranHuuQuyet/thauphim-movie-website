// Cho DOM tai xong truoc khi thao tac
document.addEventListener("DOMContentLoaded", () => {
    // Kiem tra file da duoc load
    console.log("browse.js đã kích hoạt thành công!");
  
    // Lay cac phan tu filter tren giao dien
    const filterForm = document.querySelector(".filter-form");
    const keywordInput = document.querySelector('input[name="q"]');
    const genreSelect = document.querySelector('select[name="genre"]');
    const countrySelect = document.querySelector('select[name="country"]');
    const yearSelect = document.querySelector('select[name="year"]');
    const sortSelect = document.querySelector('select[name="sort"]');
    const btnClear = document.querySelector(".btn-clear-filter");
  
    // Doc cac tham so tren url
    const urlParams = new URLSearchParams(window.location.search);
    
    if (keywordInput && urlParams.has("q")) keywordInput.value = urlParams.get("q");
    if (genreSelect && urlParams.has("genre")) genreSelect.value = urlParams.get("genre");
    if (countrySelect && urlParams.has("country")) countrySelect.value = urlParams.get("country");
    if (yearSelect && urlParams.has("year")) yearSelect.value = urlParams.get("year");
    if (sortSelect && urlParams.has("sort")) sortSelect.value = urlParams.get("sort");
  
    // Nut xoa bo loc
    if (btnClear) {
      btnClear.addEventListener("click", (e) => {
        e.preventDefault();
        if (keywordInput) keywordInput.value = "";
        if (genreSelect) genreSelect.value = "";
        if (countrySelect) countrySelect.value = "";
        if (yearSelect) yearSelect.value = "";
        if (sortSelect) sortSelect.value = "newest"; 
  
        window.location.href = "browse.php";
      });
    }
  
    // Tu dong submit khi doi bo loc
    const autoSubmitFields = [genreSelect, countrySelect, yearSelect, sortSelect];
    autoSubmitFields.forEach(field => {
      if (field) {
        field.addEventListener("change", () => {
          if (filterForm) filterForm.submit();
        });
      }
    });
  
    // Giu nguyen cac tham so filter khi chuyen trang
    const pageLinks = document.querySelectorAll(".page-link");
    pageLinks.forEach(link => {
      link.addEventListener("click", (e) => {
        const href = link.getAttribute("href");
        if (href && href.includes("page=")) {
          e.preventDefault();
          const targetPage = new URLSearchParams(href.split("?")[1]).get("page");
          
          //Cap nhat so trang moi vao url roi chuyen huong
          urlParams.set("page", targetPage);
          
          window.location.href = `browse.php?${urlParams.toString()}`;
        }
      });
    });
  });