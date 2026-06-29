document.addEventListener("DOMContentLoaded", () => {
    const shareBtn = document.querySelector("#shareBtn");

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

    const watchHistoryData = window.watchHistoryData || {};
    let watchPlayer = null;
    let saveProgressTimer = null;

    const saveWatchProgress = (isLeavingPage = false) => {
        if (!watchHistoryData.isLoggedIn) return;
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
