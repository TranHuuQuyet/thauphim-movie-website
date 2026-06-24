document.addEventListener("DOMContentLoaded", () => {
    const favoriteBtn = document.querySelector("#favoriteBtn");
    const addListBtn = document.querySelector("#addListBtn");
    const shareBtn = document.querySelector("#shareBtn");
    const commentBtn = document.querySelector("#commentBtn");
    const commentSection = document.querySelector(".comment-section");

    const detailTabs = document.querySelectorAll(".detail-tab");
    const detailPanels = document.querySelectorAll(".detail-tab-panel");

    const commentInput = document.querySelector("#commentInput");
    const commentCount = document.querySelector("#commentCount");
    const sendCommentBtn = document.querySelector("#sendCommentBtn");

    if (shareBtn) {
        shareBtn.addEventListener("click", async () => {
            try {
                await navigator.clipboard.writeText(window.location.href);
                shareBtn.textContent = "✓ Đã copy link";

                setTimeout(() => {
                    shareBtn.textContent = "↗ Chia sẻ";
                }, 1800);
            } catch (error) {
                alert("Lỗi! Không copy được link chia sẻ.");
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

    if (commentInput && commentCount) {
        commentInput.addEventListener("input", () => {
            if (commentInput.value.length > 1000) {
                commentInput.value = commentInput.value.slice(0, 1000);
            }

            commentCount.textContent = `${commentInput.value.length} / 1000`;
        });
    }

    [favoriteBtn, addListBtn, sendCommentBtn].forEach((button) => {
        if (!button) return;

        button.addEventListener("click", () => {
            alert("Main Task 4");
        });
    });

    document.querySelectorAll("[data-rating]").forEach((button) => {
        button.addEventListener("click", () => {
            alert("Main Task 4");
        });
    });
});