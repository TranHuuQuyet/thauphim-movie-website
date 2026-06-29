document.addEventListener("DOMContentLoaded", () => {
    document.title = "Thể loại phim - ThauPhim";
  
    const API_BASE = "/api";
    const genresStatus = document.querySelector("#genresStatus");
    const genresList = document.querySelector("#genresList");
  
    if (!genresStatus || !genresList) return;
  
    const setStatus = (message, state = "") => {
      genresStatus.textContent = message;
      genresStatus.className = `genres-status${state ? ` is-${state}` : ""}`;
      if (state === "ready") {
          genresStatus.style.display = "none";
      }
    };
  
    const createGenreCard = (genre) => {
      const card = document.createElement("a");
      card.href = `/pages/browse.php?genre=${genre.id}`; 
      card.className = "genre-card";
  
      const name = document.createElement("h3");
      name.textContent = `Phim ${genre.name}`;
      
      const count = document.createElement("span");
      count.className = "genre-count";
      count.textContent = `${genre.movie_count || 0} phim`;
  
      card.append(name, count);
      return card;
    };
  
    const loadGenres = async () => {
      genresList.replaceChildren();
      setStatus("Đang tải danh sách thể loại...", "loading");
  
      try {
        const response = await fetch(`${API_BASE}/genres.php`, {
          headers: { Accept: "application/json" },
        });
        const payload = await response.json().catch(() => null);
  
        if (!response.ok || !payload?.success) {
          throw new Error(payload?.message || "Lỗi tải dữ liệu API");
        }
  
        const genres = Array.isArray(payload.data) ? payload.data : [];
        if (genres.length === 0) {
          setStatus("Chưa có thể loại nào được cập nhật.", "empty");
          return;
        }
  
        const fragment = document.createDocumentFragment();
        genres.forEach((genre) => fragment.append(createGenreCard(genre)));
        genresList.append(fragment);
        setStatus("", "ready");
      } catch (error) {
        console.error("Lỗi:", error);
        genresList.replaceChildren();
        setStatus("Không thể tải danh sách. Vui lòng thử lại sau.", "error");
      }
    };
  
    loadGenres();
  });