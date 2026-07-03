document.addEventListener("DOMContentLoaded", () => {
    const watchHistoryData = window.watchHistoryData || {};
    const shareBtn = document.querySelector("#shareBtn");
    const reportErrorBtn = document.querySelector("#reportErrorBtn");

    let guestHistoryToastShown = false;
    let watchPlayer = null;
    let saveProgressTimer = null;
    let viewRecordRequested = false;

    const showWatchToast = (message, type = "info") => {
        if (typeof Toastify === "function") {
            Toastify({
                text: message,
                duration: 2500,
                gravity: "bottom",
                position: "right",
                close: true,
                stopOnFocus: true,
                className: `thau-toast thau-toast-${type}`,
            }).showToast();
            return;
        }
        alert(message);
    };

    const notifyGuestHistory = () => {
        if (guestHistoryToastShown) {
            return;
        }

        guestHistoryToastShown = true;
        showWatchToast("Đăng nhập để lưu lịch sử xem.", "warning");
    };

    if (shareBtn) {
        shareBtn.addEventListener("click", async () => {
            try {
                await navigator.clipboard.writeText(window.location.href);
                showWatchToast("Đã copy link phim.", "success");
            } catch (error) {
                showWatchToast("Không copy được link chia sẻ.", "error");
            }
        });
    }

    if (reportErrorBtn) {
        reportErrorBtn.addEventListener("click", () => {
            const commentInput = document.querySelector("#commentInput");
            const commentSection = document.querySelector(".watch-comment-box");

            if (!watchHistoryData.isLoggedIn || !commentInput) {
                showWatchToast("Vui lòng đăng nhập để báo lỗi.", "warning");
                return;
            }

            if (!commentInput.value.trim()) {
                commentInput.value = `[Error] This episode has a playback issue.
                Movie ID: ${watchHistoryData.movieId || "N/A"}
                Episode ID: ${watchHistoryData.episodeId || "N/A"}
                Link: ${window.location.href}
                Description: Video cannot be played`;
                commentInput.dispatchEvent(new Event("input"));
            }

            commentSection?.scrollIntoView({ behavior: "smooth", block: "start" });
            commentInput.focus();

            showWatchToast("Mô tả và bấm Gửi để báo lỗi.", "info");
        });
    }

    const saveWatchProgress = (isLeavingPage = false) => {
        if (!watchHistoryData.isLoggedIn) {
            notifyGuestHistory();
            return;
        }
        if (!watchPlayer || typeof watchPlayer.getCurrentTime !== "function") return;

        const progressSeconds = Math.floor(watchPlayer.getCurrentTime());

        if (progressSeconds < 0) return;

        const payload = {
            movie_id: watchHistoryData.movieId,
            episode_id: watchHistoryData.episodeId,
            progress_seconds: progressSeconds
        };

        if (isLeavingPage && navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(payload)], {
                type: "application/json"
            });

            navigator.sendBeacon("/api/update-watch-history.php", blob);
            return;
        }

        fetch("/api/update-watch-history.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload),
            keepalive: isLeavingPage
        }).catch((error) => {
            console.log("Không lưu được tiến độ xem:", error);
        });
    };

    const recordMovieView = async () => {
        if (
            viewRecordRequested ||
            !watchHistoryData.movieId ||
            !watchHistoryData.episodeId
        ) {
            return;
        }

        viewRecordRequested = true;

        try {
            const response = await fetch("/api/record-view.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    movie_id: watchHistoryData.movieId,
                    episode_id: watchHistoryData.episodeId
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
        } catch (error) {
            viewRecordRequested = false;
            console.log("Khong ghi nhan duoc luot xem:", error);
        }
    };

    const initYoutubePlayer = () => {
        const iframe = document.querySelector("#watchPlayer");

        if (!iframe || !window.YT || !YT.Player) return;

        watchPlayer = new YT.Player("watchPlayer", {
            events: {
                onReady: () => {
                    saveProgressTimer = setInterval(() => {
                        saveWatchProgress(false);
                    }, 10000);
                },
                onStateChange: (event) => {
                    if (event.data === YT.PlayerState.PLAYING) {
                        recordMovieView();
                    }

                    if (
                        event.data === YT.PlayerState.PAUSED ||
                        event.data === YT.PlayerState.ENDED
                    ) {
                        saveWatchProgress(true);
                    }
                }
            }
        });
    };

    window.onYouTubeIframeAPIReady = initYoutubePlayer;

    const youtubeScript = document.createElement("script");
    youtubeScript.src = "https://www.youtube.com/iframe_api";
    document.head.appendChild(youtubeScript);

    window.addEventListener("beforeunload", () => {
        saveWatchProgress(true);

        if (saveProgressTimer) {
            clearInterval(saveProgressTimer);
        }
    });
});
