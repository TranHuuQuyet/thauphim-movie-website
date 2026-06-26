document.addEventListener("DOMContentLoaded", () => {
  const actorList = document.querySelector("#actorList");
  const actorStatus = document.querySelector("#actorStatus");

  if (!actorList) return;

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
      image.addEventListener(
        "error",
        () => {
          image.replaceWith(createActorFallback());
        },
        { once: true },
      );
      card.append(image);
    } else {
      card.append(createActorFallback());
    }

    const name = document.createElement("h3");
    name.textContent = actor.name || "Đang cập nhật";
    card.append(name);

    return card;
  };

  const loadActors = async () => {
    actorList.replaceChildren();
    setStatus("Đang tải diễn viên...", "loading");

    try {
      const response = await fetch("/thauphim-movie-website/api/actors.php?page=1&limit=20", {
        headers: {
          Accept: "application/json",
        },
      });
      const payload = await response.json().catch(() => null);

      if (!response.ok || !payload?.success) {
        throw new Error(payload?.message || "Internal actor API request failed");
      }

      const actors = Array.isArray(payload.data) ? payload.data : [];
      if (actors.length === 0) {
        setStatus("Chưa có diễn viên.", "empty");
        return;
      }

      const fragment = document.createDocumentFragment();
      actors.forEach((actor) => fragment.append(createActorCard(actor)));
      actorList.append(fragment);
      setStatus(`Đã tải ${actors.length} diễn viên.`, "ready");
    } catch (error) {
      console.error("Không thể tải danh sách diễn viên:", error);
      actorList.replaceChildren();
      setStatus("Không thể tải danh sách diễn viên.", "error");
    }
  };

  loadActors();
});
