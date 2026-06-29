document.addEventListener("DOMContentLoaded", () => {
  const data = window.movieInteractionData || {};
  const movieId = Number(data.movieId || 0);
  const isLoggedIn = Boolean(data.isLoggedIn);
  const endpointsBase = data.endpointsBase || "/api/";

  if (!movieId) {
    return;
  }

  const favoriteBtn = document.querySelector("[data-favorite-toggle]");
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
    if (!favoriteBtn) return;

    favoriteBtn.classList.toggle("is-favorite", Boolean(isFavorite));
    favoriteBtn.setAttribute("aria-pressed", isFavorite ? "true" : "false");
    favoriteBtn.textContent = `${isFavorite ? "♥" : "♡"} Yêu thích`;

    if (count !== null) {
      favoriteBtn.dataset.favoriteCount = String(count);
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
      if (!isLoggedIn) {
        openLogin();
        return;
      }

      favoriteBtn.disabled = true;
      try {
        const favorite = await requestJson(endpoints.favorites, {
          method: "POST",
          body: JSON.stringify({ movie_id: movieId }),
        });
        updateFavoriteButton(favorite.is_favorite, favorite.favorite_count);
      } catch (error) {
        alert(error.message);
      } finally {
        favoriteBtn.disabled = false;
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
      if (!isLoggedIn) {
        openLogin();
        return;
      }

      const content = commentInput.value.trim();
      if (!content) {
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
      } catch (error) {
        alert(error.message);
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
      } catch (error) {
        alert(error.message);
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
      if (!isLoggedIn) {
        openLogin();
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
      } catch (error) {
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

