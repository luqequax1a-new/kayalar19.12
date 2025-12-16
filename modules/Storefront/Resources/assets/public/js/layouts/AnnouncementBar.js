function parseFloatOr(value, fallback) {
    const v = Number.parseFloat(value);

    return Number.isFinite(v) ? v : fallback;
}

function whenFontsReady(callback) {
    if (document.fonts && typeof document.fonts.ready?.then === "function") {
        document.fonts.ready.then(() => {
            callback();
        });

        return;
    }

    callback();
}

function getTranslateX(element) {
    const transform = window.getComputedStyle(element).transform;

    if (!transform || transform === "none") {
        return 0;
    }

    const match = transform.match(/matrix\(([^)]+)\)/);

    if (!match) {
        return 0;
    }

    const parts = match[1].split(",").map((p) => Number.parseFloat(p.trim()));

    // matrix(a, b, c, d, tx, ty)
    if (parts.length < 6) {
        return 0;
    }

    return parts[4] || 0;
}

function runAnnouncementBar(bar) {
    const items = (() => {
        try {
            return JSON.parse(bar.dataset.items || "[]");
        } catch (e) {
            return [];
        }
    })();

    const viewport = bar.querySelector(".announcement-bar__viewport");
    const track = bar.querySelector(".announcement-bar__track");

    if (!viewport || !track) {
        return;
    }

    if (!Array.isArray(items) || items.length === 0) {
        return;
    }

    // Reset any previous run state
    track.replaceWith(track.cloneNode(true));
    const freshTrack = bar.querySelector(".announcement-bar__track");
    if (!freshTrack) {
        return;
    }

    // eslint-disable-next-line no-param-reassign
    // (we want to keep code below simple by reusing the variable name)
    //
    // Note: this only updates local reference, does not mutate the original const.
    // We intentionally use a new variable to avoid confusion.
    const trackEl = freshTrack;

    const speedSeconds = parseFloatOr(bar.dataset.speedSeconds, 10);

    let index = 0;
    let paused = false;
    let pendingTimeout = null;

    function clearTimers() {
        if (pendingTimeout) {
            clearTimeout(pendingTimeout);
            pendingTimeout = null;
        }
    }

    function scheduleNext(delayMs) {
        clearTimers();

        pendingTimeout = setTimeout(() => {
            pendingTimeout = null;

            if (!paused) {
                animateOnce();
            }
        }, delayMs);
    }

    function getViewportWidth() {
        const rect = viewport.getBoundingClientRect();

        return Math.round(rect.width);
    }

    function startWhenReady() {
        const vw = getViewportWidth();

        if (vw <= 0) {
            scheduleNext(100);
            return;
        }

        animateOnce();
    }

    function animateOnce() {
        const text = items[index % items.length];
        index += 1;

        trackEl.textContent = text;

        // Reset transition
        trackEl.style.transition = "none";

        const viewportWidth = getViewportWidth();

        if (viewportWidth <= 0) {
            scheduleNext(100);
            return;
        }

        // Start just outside right edge.
        // We apply transform in one frame, then start transition in the next frame
        // to avoid the first-run "jump" where the browser batches styles.
        trackEl.style.transform = `translateX(${viewportWidth}px)`;

        window.requestAnimationFrame(() => {
            const rectWidth = Math.ceil(trackEl.getBoundingClientRect().width);
            const scrollWidth = Math.ceil(trackEl.scrollWidth);
            const textWidth = Math.max(rectWidth, scrollWidth);

            const distance = viewportWidth + textWidth;

            const durationMs = Math.max(
                1500,
                (speedSeconds * 1000 * distance) / Math.max(1, viewportWidth)
            );

            window.requestAnimationFrame(() => {
                if (paused) {
                    return;
                }

                trackEl.style.transition = `transform ${durationMs}ms linear`;
                trackEl.style.transform = `translateX(${-textWidth}px)`;
            });
        });

        const onEnd = () => {
            trackEl.removeEventListener("transitionend", onEnd);

            if (paused) {
                return;
            }

            scheduleNext(450);
        };

        trackEl.addEventListener("transitionend", onEnd);
    }

    function pause() {
        if (paused) {
            return;
        }

        paused = true;

        // Freeze at current position
        const x = getTranslateX(trackEl);
        trackEl.style.transition = "none";
        trackEl.style.transform = `translateX(${x}px)`;

        clearTimers();
    }

    function resume() {
        if (!paused) {
            return;
        }

        paused = false;
        scheduleNext(0);
    }

    viewport.addEventListener("mouseenter", pause);
    viewport.addEventListener("mouseleave", resume);

    whenFontsReady(startWhenReady);
}

export default function initAnnouncementBars() {
    document.querySelectorAll(".announcement-bar[data-items]").forEach((bar) => {
        runAnnouncementBar(bar);
    });
}
