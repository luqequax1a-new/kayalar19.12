function initInsightsHub() {
    const tabsContainer = document.querySelector('[data-insights-tabs]');
    if (!tabsContainer) return;

    const tabs = tabsContainer.querySelectorAll('.tab');
    const panels = document.querySelectorAll('.insights-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const targetPanel = tab.getAttribute('data-tab');

            // Remove active class from all tabs and panels
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));

            // Add active class to clicked tab and corresponding panel
            tab.classList.add('active');
            const panel = document.querySelector(`[data-panel="${targetPanel}"]`);
            if (panel) {
                panel.classList.add('active');
            }
        });
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initInsightsHub);
} else {
    initInsightsHub();
}
