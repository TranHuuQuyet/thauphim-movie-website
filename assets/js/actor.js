document.addEventListener("DOMContentLoaded", () => {
  const API_BASE = "/api";
  const actorList = document.querySelector("#actorList");
  const actorStatus = document.querySelector("#actorStatus");
  const actorPagination = document.querySelector("#actorPagination");
  const actorSearchInput = document.querySelector("#actorSearchInput");

  if (!actorList) return;

  let currentPage = 1;
  const limit = 18; 
  let searchQuery = "";
  let searchTimeout = null;

  const setStatus = (message, state = "") => {
    if (!actorStatus) return;
    actorStatus.textContent = message;
    actorStatus.className = `actor-status${state ? ` is-${state}` : ""}`;
  };

  const createActorFallback = () => {
    const fallback = document.createElement("div");
    fallback.className = "actor-card__fallback";
    const icon = document.createElement("i");
    icon.className = "fa-solid fa-user";
    icon.setAttribute("aria-hidden", "true");
    fallback.append(icon);
    return fallback;
  };

  const createActorCard = (actor) => {
    const card = document.createElement("article");
    card.className = "actor-card";

    if (actor.profile_url) {
      const image = document.createElement("img");
      image.src = actor.profile_url;
      image.alt = actor.name || "Actor";
      image.loading = "lazy";
      image.width = 500;
      image.height = 750;
      image.addEventListener("error", () => image.replaceWith(createActorFallback()), { once: true });
      card.append(image);
    } else {
      card.append(createActorFallback());
    }

    const name = document.createElement("h3");
    name.textContent = actor.name || "Đang cập nhật";
    card.append(name);

    if (actor.movie_count > 0) {
        const count = document.createElement("span");
        count.className = "actor-movie-count";
        count.textContent = `${actor.movie_count} phim`;
        count.style.cssText = "display:block; font-size:12px; color:#aaa; margin-top:5px;";
        card.append(count);
    }

    return card;
  };

  const renderPagination = (totalItems) => {
    if (!actorPagination) return;
    actorPagination.innerHTML = "";
    
    const totalPages = Math.ceil(totalItems / limit);
    if (totalPages <= 1) return;

    const ul = document.createElement("ul");
    ul.style.cssText = "display: flex; list-style: none; padding: 0; margin: 0; gap: 8px;";

    for (let i = 1; i <= totalPages; i++) {
      const li = document.createElement("li");
      const btn = document.createElement("button");
      btn.textContent = i;
      btn.className = `page-link ${i === currentPage ? "active-page" : ""}`;
      
      btn.style.cssText = `padding: 8px 16px; border: 1px solid #333; background: ${i === currentPage ? '#e50914' : '#111'}; color: #fff; cursor: pointer; border-radius: 4px;`;
      
      btn.addEventListener("click", () => {
        currentPage = i;
        loadActors();
        window.scrollTo({ top: 0, behavior: "smooth" });
      });
      li.append(btn);
      ul.append(li);
    }
    actorPagination.append(ul);
  };

  const loadActors = async () => {
    actorList.replaceChildren();
    setStatus("Đang tải danh sách diễn viên...", "loading");

    try {
      const url = `${API_BASE}/actors.php?page=${currentPage}&limit=${limit}&q=${encodeURIComponent(searchQuery)}`;
      const response = await fetch(url, { headers: { Accept: "application/json" } });
      const payload = await response.json().catch(() => null);

      if (!response.ok || !payload?.success) {
        throw new Error(payload?.message || "Internal actor API request failed");
      }

      const actors = Array.isArray(payload.data) ? payload.data : [];
      if (actors.length === 0) {
        setStatus("Không tìm thấy diễn viên nào phù hợp.", "empty");
        if (actorPagination) actorPagination.innerHTML = "";
        return;
      }

      const fragment = document.createDocumentFragment();
      actors.forEach((actor) => fragment.append(createActorCard(actor)));
      actorList.append(fragment);
      
      setStatus(searchQuery ? `Tìm thấy ${payload.meta?.total || actors.length} kết quả.` : "", "ready");
      
      if (payload.meta && payload.meta.total) {
        renderPagination(payload.meta.total);
      } else {
        renderPagination(actors.length);
      }
    } catch (error) {
      console.error("Không thể tải danh sách diễn viên:", error);
      actorList.replaceChildren();
      setStatus("Không thể kết nối danh sách dữ liệu. Vui lòng thử lại.", "error");
    }
  };

  if (actorSearchInput) {
    actorSearchInput.addEventListener("input", (e) => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        searchQuery = e.target.value.trim();
        currentPage = 1; 
        loadActors();
      }, 500);
    });
  }

  loadActors();
});
