document.addEventListener("DOMContentLoaded", () => {
  const data = window.movieInteractionData || {};
  const movieId = Number(data.movieId || 0);
  const isLoggedIn = Boolean(data.isLoggedIn);
  const endpointsBase = data.endpointsBase || "/api/";

  if (!movieId) {
    return;
  }

  const favoriteBtn = document.querySelector("[data-favorite-toggle]");
  const addListBtn = document.querySelector("#addListBtn");
  const shareBtn = document.querySelector("#shareBtn");
  const commentBtn = document.querySelector("#commentBtn");
  const commentInput = document.querySelector("#commentInput");
  const commentCount = document.querySelector("#commentCount");
  const sendCommentBtn = document.querySelector("#sendCommentBtn");
  const commentList = document.querySelector("#commentList");
  const ratingAverage = document.querySelector("#ratingAverage");
  const ratingTotal = document.querySelector("#ratingTotal");
  const ratingMessage = document.querySelector("#ratingMessage");
  const ratingButtons = [...document.querySelectorAll("[data-rating]")];

  const endpoints = {
    favorites: `${endpointsBase}favorites.php`,
    comments: `${endpointsBase}comments.php`,
    ratings: `${endpointsBase}ratings.php`,
  };

  let currentFavoriteState = Boolean(favoriteBtn?.classList.contains("is-favorite"));

  const escapeHtml = (value) =>
    String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const openLogin = () => {
    const trigger = document.querySelector("[data-open-login]");
    if (trigger) {
      trigger.click();
      return;
    }

    window.location.href = data.loginUrl || "/index.php#authModal";
  };

const showToast = (message, type = "info") => {
  if (typeof Toastify === "function") {
    document.querySelectorAll(".thau-toast").forEach((toast) => toast.remove());

    Toastify({
      text: message,
      duration: 2200,
      gravity: "bottom",
      position: "right",
      close: false,
      stopOnFocus: true,
      offset: {
        x: 20,
        y: 20,
      },
      className: `thau-toast thau-toast-${type}`,
    }).showToast();

    return;
  }

  alert(message);
};

const requireLogin = (message) => {
    if (isLoggedIn) {
        return true;
    }

    showToast(message || "Vui lòng đăng nhập để sử dụng chức năng này.", "warning");
    return false;
};

  const requestJson = async (url, options = {}) => {
    const response = await fetch(url, {
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        ...(options.headers || {}),
      },
      ...options,
    });
    const payload = await response.json().catch(() => null);

    if (!response.ok || !payload || payload.success === false) {
      throw new Error(payload?.message || "Khong the thuc hien yeu cau");
    }

    return payload.data;
  };

  const updateFavoriteButton = (isFavorite, count = null) => {
    currentFavoriteState = Boolean(isFavorite);

    if (favoriteBtn) {
      favoriteBtn.classList.toggle("is-favorite", currentFavoriteState);
      favoriteBtn.setAttribute("aria-pressed", currentFavoriteState ? "true" : "false");
      favoriteBtn.textContent = `${currentFavoriteState ? "♥" : "♡"} Yêu thích`;

      if (count !== null) {
        favoriteBtn.dataset.favoriteCount = String(count);
      }
    }

    if (addListBtn) {
      addListBtn.classList.toggle("is-added", currentFavoriteState);
      addListBtn.setAttribute("aria-pressed", currentFavoriteState ? "true" : "false");
      addListBtn.textContent = currentFavoriteState
        ? "✓ Đã có trong danh sách"
        : "＋ Danh sách";
    }
  };

  const loadFavorite = async () => {
    if (!favoriteBtn || !isLoggedIn) return;

    try {
      const favorite = await requestJson(`${endpoints.favorites}?movie_id=${movieId}`);
      updateFavoriteButton(favorite.is_favorite, favorite.favorite_count);
    } catch (error) {
      console.warn(error.message);
    }
  };

  if (favoriteBtn) {
    favoriteBtn.addEventListener("click", async () => {
      if (!requireLogin("Vui lòng đăng nhập để thêm phim yêu thích.")) {
          return;
      }

      favoriteBtn.disabled = true;
      try {
        const favorite = await requestJson(endpoints.favorites, {
          method: "POST",
          body: JSON.stringify({ movie_id: movieId }),
        });
        updateFavoriteButton(favorite.is_favorite, favorite.favorite_count);

        showToast(
            favorite.is_favorite ? "Đã thêm vào yêu thích." : "Đã bỏ khỏi yêu thích.",
            "success"
        );

      } catch (error) {
        showToast(error.message || "Không thêm được vào yêu thích.", "error");
      } finally {
        favoriteBtn.disabled = false;
      }
    });
  }

  if (addListBtn) {
    addListBtn.addEventListener("click", async (event) => {
      event.preventDefault();

      if (!requireLogin("Vui lòng đăng nhập để thêm phim vào danh sách.")) {
        return;
      }

      if (currentFavoriteState) {
        showToast("Phim đã có trong danh sách.", "info");
        return;
      }

      addListBtn.disabled = true;

      try {
        const favorite = await requestJson(endpoints.favorites, {
          method: "POST",
          body: JSON.stringify({ movie_id: movieId }),
        });

        updateFavoriteButton(favorite.is_favorite, favorite.favorite_count);
        showToast("Đã thêm phim vào danh sách.", "success");
      } catch (error) {
        showToast(error.message || "Không thể thêm phim vào danh sách.", "error");
      } finally {
        addListBtn.disabled = false;
      }
    });
  }

  if (commentBtn) {
      commentBtn.addEventListener("click", () => {
          if (!requireLogin("Vui lòng đăng nhập để bình luận.")) {
              return;
          }

          const commentSection = document.querySelector(".comment-section, .watch-comment-box");
          const input = document.querySelector("#commentInput");

          commentSection?.scrollIntoView({ behavior: "smooth", block: "start" });
          input?.focus();
      });
  }

  if (shareBtn && !window.watchHistoryData) {
      shareBtn.addEventListener("click", async () => {
          try {
              await navigator.clipboard.writeText(window.location.href);
              showToast("Đã copy link phim.", "success");
          } catch (error) {
              showToast("Không copy được link. ", "error");
          }
      });
  }

  const renderComments = (comments) => {
    if (!commentList) return;

    if (!comments.length) {
      commentList.classList.remove("has-comments");
      commentList.innerHTML = "Chưa có bình luận nào";
      return;
    }

    commentList.classList.add("has-comments");
    commentList.innerHTML = comments
      .map(
        (comment) => `
          <article class="comment-item" data-comment-id="${comment.id}">
            <div class="comment-item-head">
              <div class="comment-author">
                <strong>${escapeHtml(comment.username)}</strong>
                <span>${escapeHtml(comment.created_at_label)}</span>
              </div>
              ${
                comment.can_delete
                  ? `<button class="delete-comment-btn" type="button" data-delete-comment="${comment.id}">Xóa</button>`
                  : ""
              }
            </div>
            <p>${escapeHtml(comment.content)}</p>
          </article>
        `,
      )
      .join("");
  };

  const loadComments = async () => {
    if (!commentList) return;

    try {
      const result = await requestJson(`${endpoints.comments}?movie_id=${movieId}`);
      renderComments(result.comments || []);
    } catch (error) {
      commentList.textContent = error.message;
    }
  };

  if (commentInput && commentCount) {
    commentInput.addEventListener("input", () => {
      if (commentInput.value.length > 1000) {
        commentInput.value = commentInput.value.slice(0, 1000);
      }

      commentCount.textContent = `${commentInput.value.length} / 1000`;
    });
  }

  if (sendCommentBtn && commentInput) {
    sendCommentBtn.addEventListener("click", async () => {
      if (!requireLogin("Vui lòng đăng nhập để bình luận.")) {
          return;
      }

      const content = commentInput.value.trim();
      if (!content) {
        showToast("Bạn chưa nhập nội dung bình luận.", "warning");
        return;
      }

      sendCommentBtn.disabled = true;
      try {
        await requestJson(endpoints.comments, {
          method: "POST",
          body: JSON.stringify({ movie_id: movieId, content }),
        });
        commentInput.value = "";
        if (commentCount) {
          commentCount.textContent = "0 / 1000";
        }
        await loadComments();
        showToast("Đã gửi bình luận.", "success");
      } catch (error) {
        showToast(error.message || "Không thể gửi bình luận.", "error");
      } finally {
        sendCommentBtn.disabled = false;
      }
    });
  }

  if (commentList) {
    commentList.addEventListener("click", async (event) => {
      const button = event.target.closest("[data-delete-comment]");
      if (!button) return;

      const commentId = Number(button.dataset.deleteComment || 0);
      if (!commentId) return;

      button.disabled = true;
      try {
        await requestJson(endpoints.comments, {
          method: "DELETE",
          body: JSON.stringify({ comment_id: commentId }),
        });
        await loadComments();
        showToast("Đã xóa bình luận.", "success");
      } catch (error) {
        showToast(error.message || "Không thể xóa bình luận.", "error");
        button.disabled = false;
      }
    });
  }

  const updateRating = (rating) => {
    const average = Number(rating.rating_average || 0);
    const count = Number(rating.rating_count || 0);
    const userRating = Number(rating.user_rating || 0);

    if (ratingAverage) {
      ratingAverage.textContent = count > 0 ? `${average.toFixed(1)} / 5` : "Chưa có đánh giá";
    }

    if (ratingTotal) {
      ratingTotal.textContent = `${count} lượt đánh giá`;
    }

    ratingButtons.forEach((button) => {
      const value = Number(button.dataset.rating || 0);
      button.classList.toggle("active", value <= userRating);
      button.textContent = value <= userRating ? "★" : "☆";
    });

    if (ratingMessage && userRating > 0) {
      ratingMessage.textContent = `Bạn đã đánh giá ${userRating} sao.`;
    }
  };

  const loadRating = async () => {
    if (!ratingAverage && !ratingButtons.length) return;

    try {
      const rating = await requestJson(`${endpoints.ratings}?movie_id=${movieId}`);
      updateRating(rating);
    } catch (error) {
      if (ratingMessage) {
        ratingMessage.textContent = error.message;
      }
    }
  };

  ratingButtons.forEach((button) => {
    button.addEventListener("click", async () => {
      if (!requireLogin("Vui lòng đăng nhập để đánh giá phim.")) {
          return;
      }

      const rating = Number(button.dataset.rating || 0);
      if (!rating) return;

      try {
        const result = await requestJson(endpoints.ratings, {
          method: "POST",
          body: JSON.stringify({ movie_id: movieId, rating }),
        });
        updateRating(result);
        showToast(`Đã đánh giá ${rating} sao.`, "success");
      } catch (error) {
        showToast(error.message || "Không thể gửi đánh giá.", "error");

        if (ratingMessage) {
          ratingMessage.textContent = error.message;
        }
      }
    });
  });

  loadFavorite();
  loadComments();
  loadRating();
});

