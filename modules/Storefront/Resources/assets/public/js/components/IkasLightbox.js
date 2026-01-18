// IKAS-style custom lightbox
class IkasLightbox {
    constructor() {
        this.currentIndex = 0;
        this.images = [];
        this.container = null;
        this.touchStartX = 0;
        this.touchEndX = 0;
    }

    init(images, startIndex = 0) {
        this.images = images;
        this.currentIndex = startIndex;
        this.createLightbox();
        this.show();
    }

    createLightbox() {
        // Remove existing lightbox if any
        const existing = document.querySelector('.ikas-lightbox');
        if (existing) existing.remove();

        // Create lightbox container
        this.container = document.createElement('div');
        this.container.className = 'ikas-lightbox';
        this.container.innerHTML = `
            <div class="ikas-lightbox-overlay"></div>
            <div class="ikas-lightbox-content">
                <div class="ikas-lightbox-image-container">
                    <img src="" alt="" class="ikas-lightbox-image">
                </div>
                <div class="ikas-lightbox-counter"></div>
                <div class="ikas-lightbox-bottom-nav">
                    <button class="ikas-lightbox-prev" aria-label="Previous">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>
                    <button class="ikas-lightbox-close" aria-label="Close">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                    <button class="ikas-lightbox-next" aria-label="Next">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(this.container);
        this.bindEvents();
    }

    bindEvents() {
        // Close button
        this.container.querySelector('.ikas-lightbox-close').addEventListener('click', () => this.close());

        // Navigation buttons
        this.container.querySelector('.ikas-lightbox-prev').addEventListener('click', () => this.prev());
        this.container.querySelector('.ikas-lightbox-next').addEventListener('click', () => this.next());

        // Keyboard navigation
        this._boundHandleKeyboard = this.handleKeyboard.bind(this);
        document.addEventListener('keydown', this._boundHandleKeyboard);

        // Touch events for swipe
        const imageContainer = this.container.querySelector('.ikas-lightbox-image-container');
        imageContainer.addEventListener('touchstart', (e) => {
            this.touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        imageContainer.addEventListener('touchend', (e) => {
            this.touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe();
        }, { passive: true });
    }

    handleKeyboard(e) {
        if (!this.container || !this.container.classList.contains('active')) return;

        switch (e.key) {
            case 'Escape':
                this.close();
                break;
            case 'ArrowLeft':
                this.prev();
                break;
            case 'ArrowRight':
                this.next();
                break;
        }
    }

    handleSwipe() {
        const diff = this.touchStartX - this.touchEndX;
        const threshold = 100;

        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.next();
            } else {
                this.prev();
            }
        }
    }

    show() {
        this.updateImage();
        this.updateCounter();
        this.updateButtons();

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Show lightbox with animation
        requestAnimationFrame(() => {
            this.container.classList.add('active');
        });
    }

    close() {
        if (this.container) {
            this.container.classList.remove('active');
        }
        document.body.style.overflow = '';

        if (this._boundHandleKeyboard) {
            document.removeEventListener('keydown', this._boundHandleKeyboard);
        }

        // Release video resources immediately on close
        const video = this.container ? this.container.querySelector('video') : null;
        if (video) {
            try {
                video.pause();
                video.src = '';
                video.load();
            } catch (_) { }
        }

        setTimeout(() => {
            if (this.container) {
                this.container.remove();
                this.container = null;
            }
        }, 300);
    }

    next() {
        this.currentIndex = (this.currentIndex + 1) % this.images.length;
        this.updateImage();
        this.updateCounter();
        this.updateButtons();
    }

    prev() {
        this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.updateImage();
        this.updateCounter();
        this.updateButtons();
    }

    updateImage() {
        const container = this.container.querySelector('.ikas-lightbox-image-container');
        const currentMedia = this.images[this.currentIndex];

        // Fade out
        container.style.opacity = '0';

        // Explicitly clean up existing video resources before removing
        const existingVideo = container.querySelector('video');
        if (existingVideo) {
            try {
                existingVideo.pause();
                existingVideo.src = '';
                existingVideo.load();
                existingVideo.remove();
            } catch (_) { }
        }

        setTimeout(() => {
            // Clear container
            container.innerHTML = '';

            if (currentMedia.type === 'video') {
                // Create video wrapper for play overlay
                const videoWrapper = document.createElement('div');
                videoWrapper.className = 'ikas-lightbox-video-wrapper';
                videoWrapper.setAttribute('data-playing', 'false');

                // Create video element
                const video = document.createElement('video');
                video.className = 'ikas-lightbox-video';
                video.controls = true;
                video.setAttribute('controlslist', 'nodownload');
                video.setAttribute('playsinline', '');
                video.setAttribute('webkit-playsinline', '');
                video.setAttribute('disablePictureInPicture', 'true');
                video.setAttribute('disableRemotePlayback', 'true');
                video.setAttribute('preload', 'metadata');

                const source = document.createElement('source');
                source.src = currentMedia.src;
                source.type = 'video/mp4';

                video.appendChild(source);
                videoWrapper.appendChild(video);
                container.appendChild(videoWrapper);

                console.log('Video created:', video);
                console.log('Video controls:', video.controls);

                const setVideoControls = (enabled) => {
                    try {
                        // Always keep controls enabled so user can see duration/progress
                        // Our custom overlay will handle the big play button visibility
                        video.controls = true;
                    } catch (_) { }
                };

                // Initial controls state
                setVideoControls(false);

                // Track playing state
                video.addEventListener('play', () => {
                    videoWrapper.setAttribute('data-playing', 'true');
                    setVideoControls(true);
                });

                video.addEventListener('pause', () => {
                    videoWrapper.setAttribute('data-playing', 'false');
                    setVideoControls(false);
                });

                video.addEventListener('ended', () => {
                    videoWrapper.setAttribute('data-playing', 'false');
                    setVideoControls(false);
                });

                let touchMoved = false;
                let touchStartPos = { x: 0, y: 0 };
                let lastToggleTime = 0;
                const toggleCooldown = 400; // ms

                const handleToggle = (e) => {
                    const now = Date.now();
                    if (now - lastToggleTime < toggleCooldown) return;

                    // Only handle clicks on the video element itself or the wrapper background
                    if (e.target !== video && e.target !== videoWrapper) return;

                    // Check if click is in the native controls area (approx bottom 60px)
                    const rect = video.getBoundingClientRect();
                    const clientY = e.type.startsWith('touch') ? e.changedTouches[0].clientY : e.clientY;
                    const isNearBottom = clientY > (rect.bottom - 60);

                    // If it's playing and we clicked near the bottom, let native controls handle it
                    if (!video.paused && isNearBottom) return;

                    // If touch event and we moved, ignore
                    if (e.type === 'touchend' && touchMoved) return;

                    e.preventDefault();
                    e.stopPropagation();
                    lastToggleTime = now;

                    if (video.paused) {
                        video.play().catch(err => console.error('Lightbox Play Error:', err));
                    } else {
                        video.pause();
                    }
                };

                videoWrapper.addEventListener('touchstart', (e) => {
                    touchMoved = false;
                    touchStartPos = { x: e.touches[0].clientX, y: e.touches[0].clientY };
                }, { passive: true });

                videoWrapper.addEventListener('touchmove', (e) => {
                    const dx = Math.abs(e.touches[0].clientX - touchStartPos.x);
                    const dy = Math.abs(e.touches[0].clientY - touchStartPos.y);
                    if (dx > 10 || dy > 10) touchMoved = true;
                }, { passive: true });

                // Use touchend for mobile and click for desktop to be safe
                const isMobile = window.innerWidth <= 990;
                if (isMobile) {
                    videoWrapper.addEventListener('touchend', handleToggle);
                } else {
                    videoWrapper.addEventListener('click', handleToggle);
                }
            } else {
                // Create image element
                const img = document.createElement('img');
                img.className = 'ikas-lightbox-image';
                img.src = currentMedia.src;
                img.alt = currentMedia.alt || '';
                container.appendChild(img);
            }

            // Fade in
            container.style.opacity = '1';
        }, 150);
    }


    updateCounter() {
        const counter = this.container.querySelector('.ikas-lightbox-counter');
        counter.textContent = `${this.currentIndex + 1} / ${this.images.length}`;
    }

    updateButtons() {
        const prevBtn = this.container.querySelector('.ikas-lightbox-prev');
        const nextBtn = this.container.querySelector('.ikas-lightbox-next');

        // Show/hide buttons based on image count
        if (this.images.length <= 1) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'flex';
            nextBtn.style.display = 'flex';
        }
    }

    reload() {
        // This method is called when gallery slides are updated (e.g., after variant change)
        // We need to reinitialize the lightbox handlers
        if (typeof window.initIkasLightbox === 'function') {
            window.initIkasLightbox();
        }
    }
}

// Initialize lightbox for product gallery
window.initIkasLightbox = function () {
    const gallerySlides = document.querySelectorAll('.gallery-preview-slide');

    if (!gallerySlides.length) return;

    // Collect all media (images and videos)
    const allMedia = [];

    gallerySlides.forEach((slide) => {
        const isVideo = slide.querySelector('.gallery-preview-item--video');

        if (isVideo) {
            // Video slide - no poster needed
            const video = slide.querySelector('video');
            const videoSrc = video?.querySelector('source')?.getAttribute('src') || video?.src || '';

            allMedia.push({
                type: 'video',
                src: videoSrc,
                alt: 'Product Video'
            });
        } else {
            // Image slide
            const img = slide.querySelector('.gallery-preview-item img');
            if (img) {
                const zoomSrc = img.getAttribute('data-zoom') || img.src;
                allMedia.push({
                    type: 'image',
                    src: zoomSrc,
                    alt: img.alt || ''
                });
            }
        }
    });

    // Remove old event listeners by cloning and replacing elements
    gallerySlides.forEach((slide, index) => {
        const clickTarget = slide.querySelector('.gallery-preview-item');
        if (!clickTarget) return;

        // Remove old data attribute if exists
        if (clickTarget.hasAttribute('data-lightbox-initialized')) {
            // Clone and replace to remove all event listeners
            const newClickTarget = clickTarget.cloneNode(true);
            clickTarget.parentNode.replaceChild(newClickTarget, clickTarget);
        }
    });

    // Re-query after potential replacements
    const updatedSlides = document.querySelectorAll('.gallery-preview-slide');

    // Add click handlers
    updatedSlides.forEach((slide, index) => {
        const clickTarget = slide.querySelector('.gallery-preview-item');
        if (!clickTarget) return;

        // Mark as initialized
        clickTarget.setAttribute('data-lightbox-initialized', 'true');

        // Handle click on the image/video container
        clickTarget.addEventListener('click', (e) => {
            // If this is a video item, don't open lightbox on click
            // The video should play/pause inline instead
            if (clickTarget.classList.contains('gallery-preview-item--video')) {
                return;
            }

            e.preventDefault();
            openLightboxAtIndex(index);
        });

        // Handle click on the magnifying glass icon
        const icon = slide.querySelector('.gallery-view-icon');
        if (icon) {
            // Remove old listener by cloning
            if (icon.hasAttribute('data-lightbox-initialized')) {
                const newIcon = icon.cloneNode(true);
                icon.parentNode.replaceChild(newIcon, icon);
            }

            const currentIcon = slide.querySelector('.gallery-view-icon');
            if (currentIcon) {
                currentIcon.setAttribute('data-lightbox-initialized', 'true');
                currentIcon.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    openLightboxAtIndex(index);
                });
            }
        }
    });

    function openLightboxAtIndex(index) {
        // Open lightbox with all media
        const lightbox = new IkasLightbox();
        lightbox.init(allMedia, index);
    }
};

// Auto-init on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(window.initIkasLightbox, 300);
    });
} else {
    setTimeout(window.initIkasLightbox, 300);
}

// Make IkasLightbox globally accessible
window.IkasLightbox = IkasLightbox;

export default IkasLightbox;

