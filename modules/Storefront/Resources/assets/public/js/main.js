// stores
import "./app";
import "./stores/cartStore";
import "./stores/wishlistStore";
import "./stores/compareStore";
import "./stores/layoutStore";

// layouts
import "./layouts/Header";
import "./layouts/PrimaryMenu";
import "./layouts/SidebarCart";
import "./layouts/CookieBar";
import "./layouts/NewsletterSubscription";
import "./layouts/NewsletterPopup";
import "./layouts/Popup";
import "./layouts/ScrollToTop";

function disableBrowserZoom() {
    try {
        // Ctrl + wheel (desktop browsers)
        window.addEventListener(
            "wheel",
            (e) => {
                try {
                    if (e.ctrlKey) {
                        e.preventDefault();
                    }
                } catch (_) {}
            },
            { passive: false }
        );

        // Ctrl + +/-/0
        window.addEventListener(
            "keydown",
            (e) => {
                try {
                    if (!e.ctrlKey) return;

                    const k = (e.key || "").toLowerCase();
                    if (k === "+" || k === "=" || k === "-" || k === "_" || k === "0") {
                        e.preventDefault();
                    }
                } catch (_) {}
            },
            { passive: false }
        );

        // iOS Safari pinch zoom gestures
        document.addEventListener(
            "gesturestart",
            (e) => {
                try {
                    e.preventDefault();
                } catch (_) {}
            },
            { passive: false }
        );
        document.addEventListener(
            "gesturechange",
            (e) => {
                try {
                    e.preventDefault();
                } catch (_) {}
            },
            { passive: false }
        );
        document.addEventListener(
            "gestureend",
            (e) => {
                try {
                    e.preventDefault();
                } catch (_) {}
            },
            { passive: false }
        );
    } catch (_) {}
}

document.addEventListener("DOMContentLoaded", () => {
    disableBrowserZoom();
    if (window.Alpine && typeof window.Alpine.start === "function") {
        window.Alpine.start();
    }
});
