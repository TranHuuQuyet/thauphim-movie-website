document.addEventListener("DOMContentLoaded", () => {
    const detailTabs = document.querySelectorAll(".detail-tab");
    const detailPanels = document.querySelectorAll(".detail-tab-panel");

    detailTabs.forEach((tab) => {
        tab.addEventListener("click", (event) => {
            event.preventDefault();

            const targetSelector = tab.dataset.target;
            const targetPanel = document.querySelector(targetSelector);

            if (!targetPanel) {
                return;
            }

            detailTabs.forEach((item) => item.classList.remove("active"));
            tab.classList.add("active");

            detailPanels.forEach((panel) => {
                panel.classList.toggle("active", panel === targetPanel);
            });
        });
    });
});