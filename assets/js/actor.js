document.addEventListener("DOMContentLoaded", () => {
  const actorPage = document.querySelector(".actor-page");
  const actorList = document.querySelector("#actorList");
  const actorStatus = document.querySelector("#actorStatus");
  const actorPagination = document.querySelector("#actorPagination");
  const actorSearchInput = document.querySelector("#actorSearchInput");

  if (!actorPage || !actorList) return;

  const actorsApiUrl = actorPage.dataset.actorsApi;

  let currentPage = 1;
  const limit = 18;
  let searchQuery = "";
  let searchTimeout = null;

  const setStatus = (message, state = "") => {
    if (!actorStatus) return;
    actorStatus.textContent = message;
    actorStatus.className = `actor-status${state ? ` is-${state}` : ""}`;
    actorStatus.hidden = message === "";
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
      card.append(count);
    }

    return card;
  };

  const getPaginationItems = (totalPages) => {
    const pages = new Set([1, totalPages]);
    const start = Math.max(2, currentPage - 1);
    const end = Math.min(totalPages - 1, currentPage + 1);

    for (let page = start; page <= end; page += 1) {
      pages.add(page);
    }

    if (currentPage <= 2) {
      pages.add(2);
      pages.add(3);
    }

    if (currentPage >= totalPages - 1) {
      pages.add(totalPages - 2);
      pages.add(totalPages - 1);
    }

    return [...pages]
      .filter((page) => page >= 1 && page <= totalPages)
      .sort((a, b) => a - b)
      .reduce((items, page, index, sortedPages) => {
        if (index > 0 && page - sortedPages[index - 1] > 1) {
          items.push("ellipsis");
        }

        items.push(page);
        return items;
      }, []);
  };

  const createPaginationButton = (label, page, options = {}) => {
    const li = document.createElement("li");
    const btn = document.createElement("button");
    const isCurrent = page === currentPage;

    btn.type = "button";
    btn.textContent = label;
    btn.className = `page-link${isCurrent ? " active-page" : ""}`;
    btn.disabled = Boolean(options.disabled) || isCurrent;

    if (isCurrent) {
      btn.setAttribute("aria-current", "page");
    }

    btn.addEventListener("click", () => {
      if (btn.disabled) return;

      currentPage = page;
      loadActors();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });

    li.append(btn);
    return li;
  };

  const createPaginationEllipsis = () => {
    const li = document.createElement("li");
    const ellipsis = document.createElement("span");

    ellipsis.className = "page-ellipsis";
    ellipsis.textContent = "...";
    ellipsis.setAttribute("aria-hidden", "true");
    li.append(ellipsis);
    return li;
  };

  const renderPagination = (totalItems) => {
    if (!actorPagination) return;
    actorPagination.innerHTML = "";

    const totalPages = Math.ceil(totalItems / limit);
    if (totalPages <= 1) return;

    const ul = document.createElement("ul");
    ul.className = "pagination-list";

    ul.append(createPaginationButton("Trước", Math.max(1, currentPage - 1), {
      disabled: currentPage === 1,
    }));

    getPaginationItems(totalPages).forEach((item) => {
      ul.append(
        item === "ellipsis"
          ? createPaginationEllipsis()
          : createPaginationButton(String(item), item),
      );
    });

    ul.append(createPaginationButton("Sau", Math.min(totalPages, currentPage + 1), {
      disabled: currentPage === totalPages,
    }));

    actorPagination.append(ul);
  };

  const loadActors = async () => {
    actorList.replaceChildren();
    setStatus("Đang tải danh sách diễn viên...", "loading");

    try {
      const url = `${actorsApiUrl}?page=${currentPage}&limit=${limit}&q=${encodeURIComponent(searchQuery)}`;
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
