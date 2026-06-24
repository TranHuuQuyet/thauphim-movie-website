document.addEventListener("DOMContentLoaded", () => {
    const API_KEY = typeof TMDB_API_KEY !== "undefined" ? TMDB_API_KEY : "";
    const IMG_URL = "https://image.tmdb.org/t/p/w500";

    const params = new URLSearchParams(window.location.search);
    const tmdbId = params.get("tmdb_id");
    const movieId = tmdbId || params.get("id") || "1";

    const favoriteBtn = document.querySelector("#favoriteBtn");
    const addListBtn = document.querySelector("#addListBtn");
    const shareBtn = document.querySelector("#shareBtn");
    const commentBtn = document.querySelector("#commentBtn");
    const commentSection = document.querySelector(".comment-section");
    const relatedMovies = document.querySelector("#relatedMovies");
    const episodeList = document.querySelector("#episodeList");
    const watchNowBtn = document.querySelector("#watchNowBtn");
    const detailTabs = document.querySelectorAll(".detail-tab");
    const detailPanels = document.querySelectorAll(".detail-tab-panel");

    const commentInput = document.querySelector("#commentInput");
    const commentCount = document.querySelector("#commentCount");
    const sendCommentBtn = document.querySelector("#sendCommentBtn");
    const commentEmpty = document.querySelector(".comment-empty");

    const ratingStars = document.querySelector("#ratingStars");
    const ratingAverage = document.querySelector("#ratingAverage");
    const ratingTotal = document.querySelector("#ratingTotal");
    const ratingMessage = document.querySelector("#ratingMessage");

    const commentKey = `comments_${movieId}`;
    const ratingKey = `ratings_${movieId}`;

    const currentUser = window.currentUser || {};
    const currentUserId = currentUser.id ? String(currentUser.id) : null;
    const currentUserName = currentUser.name || "Bạn";

    const escapeHTML = (text) => {
        return String(text)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    };



    // FAVORITE / WATCHLIST / SHARE
    const toggleStorage = (key, id, activeText, normalText, button, activeClass) => {
        const list = JSON.parse(localStorage.getItem(key)) || [];

        if (list.includes(id)) {
            const newList = list.filter((item) => item !== id);
            localStorage.setItem(key, JSON.stringify(newList));

            button.textContent = normalText;
            button.classList.remove(activeClass);
        } else {
            list.push(id);
            localStorage.setItem(key, JSON.stringify(list));

            button.textContent = activeText;
            button.classList.add(activeClass);
        }
    };

    const setInitialButtonState = () => {
        const favoriteList = JSON.parse(localStorage.getItem("favoriteMovies")) || [];
        const watchList = JSON.parse(localStorage.getItem("watchListMovies")) || [];

        if (favoriteList.includes(movieId) && favoriteBtn) {
            favoriteBtn.textContent = "♥ Đã yêu thích";
            favoriteBtn.classList.add("is-favorite");
        }

        if (watchList.includes(movieId) && addListBtn) {
            addListBtn.textContent = "✓ Đã thêm";
            addListBtn.classList.add("is-added");
        }
    };

    if (favoriteBtn) {
        favoriteBtn.addEventListener("click", () => {
            toggleStorage("favoriteMovies", movieId, "♥ Đã yêu thích", "♡ Yêu thích", favoriteBtn, "is-favorite");
        });
    }

    if (addListBtn) {
        addListBtn.addEventListener("click", () => {
            toggleStorage("watchListMovies", movieId, "✓ Đã thêm", "＋ Thêm vào", addListBtn, "is-added");
        });
    }

    if (shareBtn) {
        shareBtn.addEventListener("click", async () => {
            try {
                await navigator.clipboard.writeText(window.location.href);
                shareBtn.textContent = "✓ Đã copy link";

                setTimeout(() => {
                    shareBtn.textContent = "↗ Chia sẻ";
                }, 1800);
            } catch (error) {
                alert("Không copy được link. Bạn hãy copy trên thanh địa chỉ.");
            }
        });
    }

    if (commentBtn && commentSection) {
        commentBtn.addEventListener("click", () => {
            commentSection.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        });
    }

    if (detailTabs.length > 0) {
        detailTabs.forEach((tab) => {
            tab.addEventListener("click", () => {
                const targetSelector = tab.dataset.target;

                detailTabs.forEach((item) => item.classList.remove("active"));
                tab.classList.add("active");

                detailPanels.forEach((panel) => {
                    const isTargetPanel = `#${panel.id}` === targetSelector;
                    panel.classList.toggle("active", isTargetPanel);
                });
            });
        });
    }

    async function loadMovieDetail(id) {
        try {
            const response = await fetch(
                `https://api.themoviedb.org/3/movie/${id}?api_key=${API_KEY}&language=vi-VN`
            );

            const movie = await response.json();
            const primaryGenreId = movie.genres?.[0]?.id || null;
            const primaryCountryCode = movie.production_countries?.[0]?.iso_3166_1 || "";

            loadRelatedMovies(primaryGenreId, primaryCountryCode, movie.id);

            document.querySelector(".detail-poster img").src = movie.poster_path
                ? `${IMG_URL}${movie.poster_path}`
                : "../assets/images/poster_movie.jpg";

            document.querySelector(".detail-poster img").alt = movie.title || "Movie";
            document.querySelector(".detail-info-text h1").textContent = movie.title || "Đang cập nhật";
            document.querySelector(".movie-name").textContent = movie.original_title || "";

            document.querySelector(".detail-desc-block p").textContent =
                movie.overview || "Nội dung phim đang được cập nhật.";

            const releaseDate = movie.release_date || "";
            const year = releaseDate ? releaseDate.slice(0, 4) : "N/A";

            document.querySelector(".detail-badges").innerHTML = `
                <span class="detail-badge age">T13</span>
                <span class="detail-badge">${releaseDate || "N/A"}</span>
                <span class="detail-badge">${year}</span>
                <span class="detail-badge">4K</span>
            `;

            document.querySelector(".detail-tags").innerHTML = movie.genres
                .slice(0, 4)
                .map((genre) => `<span>#${genre.name}</span>`)
                .join("");

            const today = new Date();
            const movieDate = releaseDate ? new Date(releaseDate) : null;
            const statusText = movieDate && movieDate > today ? "Chưa ra mắt" : "Đã phát hành";

            const statusBox = document.querySelector(".detail-status");
            const statusSpan = document.querySelector(".detail-status span");

            if (statusBox && statusSpan) {
                statusSpan.textContent = statusText;
                statusBox.classList.remove("status-warning", "status-success");

                if (statusText === "Chưa ra mắt") {
                    statusBox.classList.add("status-warning");
                } else statusBox.classList.add("status-success");
            }


            const countries = movie.production_countries?.map((item) => item.name).join(", ") || "Đang cập nhật";
            const companies = movie.production_companies?.slice(0, 3).map((item) => item.name).join(", ") || "Đang cập nhật";
            const actors = await getMovieActors(id);
            const actorsPanel = document.querySelector("#actorsPanel");

            if (actorsPanel) {
                actorsPanel.textContent = actors;
            }

            document.querySelector(".detail-info-list").innerHTML = `
                <p><strong>Thời lượng:</strong> ${movie.runtime ? movie.runtime + " phút" : "Đang cập nhật"}</p>
                <p><strong>Quốc gia:</strong> ${countries}</p>
                <p><strong>Sản xuất:</strong> ${companies}</p>
                <p><strong>Diễn viên:</strong> ${actors}</p>
            `;
        } catch (error) {
            console.log("Không tải được chi tiết phim:", error);
        }
    }

    async function getMovieActors(id) {
        try {
            const response = await fetch(
                `https://api.themoviedb.org/3/movie/${id}/credits?api_key=${API_KEY}&language=vi-VN`
            );

            const data = await response.json();

            const actors = data.cast
                ?.slice(0, 5)
                .map((actor) => actor.name)
                .join(", ");

            return actors || "Đang cập nhật";
        } catch (error) {
            console.log("Không tải được diễn viên:", error);
            return "Đang cập nhật";
        }
    }

    async function loadMovieTrailer(id) {
        try {
            const response = await fetch(
                `https://api.themoviedb.org/3/movie/${id}/videos?api_key=${API_KEY}&language=en-US`
            );

            const data = await response.json();

            const trailer = data.results.find((video) =>
                video.site === "YouTube" &&
                (video.type === "Trailer" || video.type === "Teaser")
            );

            if (!trailer) return;

            document.querySelector(".video-bg iframe").src =
                `https://www.youtube.com/embed/${trailer.key}?autoplay=1&mute=1&loop=1&playlist=${trailer.key}&controls=0&rel=0`;

            if (watchNowBtn) {
                watchNowBtn.href = `watch.php?tmdb_id=${id}&video_key=${trailer.key}&ep=1`;
            }
        } catch (error) {
            console.log("Không tải được trailer:", error);
        }
    }

    async function loadMovieVideos(id) {
        if (!episodeList) return;

        try {
            const response = await fetch(
                `https://api.themoviedb.org/3/movie/${id}/videos?api_key=${API_KEY}&language=en-US`
            );

            const data = await response.json();

            const videos = data.results.filter((video) =>
                video.site === "YouTube" &&
                (
                    video.type === "Trailer" ||
                    video.type === "Teaser" ||
                    video.type === "Clip" ||
                    video.type === "Behind the Scenes"
                )
            );

            if (videos.length === 0) {
                episodeList.innerHTML = `
                    <a class="episode-btn" href="watch.php?tmdb_id=${id}&ep=1">▶ Tập 1</a>
                `;
                return;
            }

            episodeList.innerHTML = videos.slice(0, 6).map((video, index) => {
                return `
                    <a class="episode-btn" href="watch.php?tmdb_id=${id}&video_key=${video.key}&ep=${index + 1}">
                        ▶ Tập ${index + 1}
                    </a>
                `;
            }).join("");
        } catch (error) {
            console.log("Không tải được tập phim:", error);
        }
    }

    async function loadRelatedMovies(genreId = null, countryCode = "", currentMovieId = null) {
        if (!relatedMovies) return;

        try {
            let apiUrl = `https://api.themoviedb.org/3/movie/top_rated?api_key=${API_KEY}&language=vi-VN&page=1`;

            if (genreId) {
                apiUrl = `https://api.themoviedb.org/3/discover/movie?api_key=${API_KEY}&language=vi-VN&page=1&sort_by=popularity.desc&with_genres=${genreId}`;

                if (countryCode) {
                    apiUrl += `&with_origin_country=${countryCode}`;
                }
            }

            const response = await fetch(apiUrl);
            const data = await response.json();

            const movies = (data.results || [])
                .filter((movie) => String(movie.id) !== String(currentMovieId))
                .slice(0, 6);

            if (movies.length === 0) {
                relatedMovies.innerHTML = `<p class="detail-empty">Chưa có phim đề xuất phù hợp.</p>`;
                return;
            }

            relatedMovies.innerHTML = movies.map((movie) => {
                const poster = movie.poster_path
                    ? `${IMG_URL}${movie.poster_path}`
                    : "../assets/images/poster_movie.jpg";

                return `
                    <a class="related-movie-card" href="movie-detail.php?tmdb_id=${movie.id}">
                        <img src="${poster}" alt="${escapeHTML(movie.title || "Movie")}">
                        <h3>${escapeHTML(movie.title || "Đang cập nhật")}</h3>
                    </a>
                `;
            }).join("");
        } catch (error) {
            relatedMovies.innerHTML = `<p class="detail-empty">Không tải được phim đề xuất.</p>`;
            console.log("Không tải được phim đề xuất:", error);
        }
    }


    // Comments
    const renderComments = () => {
        if (!commentEmpty) return;

        const comments = JSON.parse(localStorage.getItem(commentKey)) || [];

        if (comments.length === 0) {
            commentEmpty.classList.remove("has-comments");
            commentEmpty.innerHTML = "Chưa có bình luận nào";
            return;
        }

        commentEmpty.classList.add("has-comments");

        commentEmpty.innerHTML = comments.map((comment, index) => {
            const commentId = comment.id || index.toString();
            const authorId = comment.authorId ? String(comment.authorId) : null;
            const author = comment.author || "Bạn";

            const isMyComment = currentUserId && authorId === currentUserId;
            const isOldComment = currentUserId && !authorId;

            const canDelete = isMyComment || isOldComment;
            const deleteButton = canDelete
                ? `<button class="delete-comment-btn" type="button" data-comment-id="${commentId}">Xóa</button>`: "";

            return `
                <div class="comment-item">
                    <div class="comment-item-head">
                        <div class="comment-author">
                            <strong>${escapeHTML(author)}</strong>
                            <span>${escapeHTML(comment.time || "")}</span>
                        </div>

                        ${deleteButton}
                    </div>

                    <p>${escapeHTML(comment.content || "")}</p>
                </div>
            `;
        }).join("");
    };

    if (commentEmpty) {
        commentEmpty.addEventListener("click", (event) => {
            const deleteBtn = event.target.closest(".delete-comment-btn");

            if (!deleteBtn) return;

            const commentId = deleteBtn.dataset.commentId;
            const comments = JSON.parse(localStorage.getItem(commentKey)) || [];

            const newComments = comments.filter((comment, index) => {
                const currentId = comment.id || index.toString();
                return currentId !== commentId;
            });

            localStorage.setItem(commentKey, JSON.stringify(newComments));
            renderComments();
        });
    }

    const getRatings = () => {
        return JSON.parse(localStorage.getItem(ratingKey)) || {};
    };

    const renderRating = () => {
        const ratings = getRatings();
        const ratingValues = Object.values(ratings).map(Number);

        if (!ratingAverage || !ratingTotal) return;

        if (ratingValues.length === 0) {
            ratingAverage.textContent = "Chưa có đánh giá";
            ratingTotal.textContent = "0 lượt đánh giá";
        } else {
            const totalScore = ratingValues.reduce((sum, rating) => sum + rating, 0);
            const averageScore = totalScore / ratingValues.length;

            ratingAverage.textContent = `${averageScore.toFixed(1)} / 5`;
            ratingTotal.textContent = `${ratingValues.length} lượt đánh giá`;
        }

        if (!ratingStars) return;

        const myRating = currentUserId ? Number(ratings[currentUserId] || 0) : 0;
        const buttons = ratingStars.querySelectorAll("[data-rating]");

        buttons.forEach((button) => {
            const value = Number(button.dataset.rating);

            if (value <= myRating) {
                button.textContent = "★";
                button.classList.add("active");
            } else {
                button.textContent = "☆";
                button.classList.remove("active");
            }
        });

        if (ratingMessage) {
            ratingMessage.textContent = myRating > 0
                ? `Bạn đã đánh giá ${myRating} sao.`
                : "Chọn số sao để đánh giá phim.";
        }
    };

    if (ratingStars) {
        ratingStars.addEventListener("click", (event) => {
            const button = event.target.closest("[data-rating]");

            if (!button) return;

            if (!currentUserId) {
                alert("Bạn cần đăng nhập để đánh giá.");
                return;
            }

            const ratingValue = Number(button.dataset.rating);
            const ratings = getRatings();

            ratings[currentUserId] = ratingValue;

            localStorage.setItem(ratingKey, JSON.stringify(ratings));
            renderRating();
        });
    }

    if (commentInput && commentCount) {
        commentInput.addEventListener("input", () => {
            if (commentInput.value.length > 1000) {
                commentInput.value = commentInput.value.slice(0, 1000);
            }

            commentCount.textContent = `${commentInput.value.length} / 1000`;
        });
    }

    if (sendCommentBtn && commentInput) {
        sendCommentBtn.addEventListener("click", () => {
            if (!currentUserId) {
                alert("Bạn cần đăng nhập để bình luận.");
                return;
            }

            const content = commentInput.value.trim();

            if (content === "") {
                alert("Bạn chưa nhập bình luận.");
                return;
            }

            const comments = JSON.parse(localStorage.getItem(commentKey)) || [];

            comments.unshift({
                id: Date.now().toString(),
                authorId: currentUserId,
                author: currentUserName,
                content: content,
                time: new Date().toLocaleString("vi-VN")
            });

            localStorage.setItem(commentKey, JSON.stringify(comments));

            commentInput.value = "";
            commentCount.textContent = "0 / 1000";

            renderComments();
        });
    }


    if (tmdbId) {
        loadMovieDetail(tmdbId);
        loadMovieTrailer(tmdbId);
        loadMovieVideos(tmdbId); 
    } else loadRelatedMovies();

    setInitialButtonState();
    renderComments();
    renderRating();
});