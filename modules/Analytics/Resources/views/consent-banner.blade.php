@if(setting('cookie_bar_enabled'))
{{-- GDPR/KVKK Cookie Consent Banner --}}
<div id="cookie-consent-banner" style="display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #fff; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); z-index: 2147483647; padding: 20px; border-top: 3px solid #0071e3;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px;">
            <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 600; color: #1d1d1f;">
                🍪 {{ setting('cookie_consent_title', trans('analytics::consent.title')) }}
            </h3>
            <p style="margin: 0; font-size: 14px; color: #6e6e73; line-height: 1.5;">
                {!! setting('cookie_consent_message', trans('analytics::consent.message')) !!}
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button id="cookie-consent-accept" style="background: #0071e3; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s;">
                {{ trans('analytics::consent.accept') }}
            </button>
            <button id="cookie-consent-reject" style="background: #f5f5f7; color: #1d1d1f; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s;">
                {{ trans('analytics::consent.reject') }}
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    var consentBanner = document.getElementById('cookie-consent-banner');
    var acceptBtn = document.getElementById('cookie-consent-accept');
    var rejectBtn = document.getElementById('cookie-consent-reject');
    
    // Check if user has already made a choice
    var consent = localStorage.getItem('cookie_consent');
    
    if (!consent) {
        // Show banner if no consent decision has been made
        consentBanner.style.display = 'block';
    } else if (consent === 'accepted') {
        // Initialize tracking if consent was given
        initializeTracking();
    }
    
    // Accept button handler
    acceptBtn.addEventListener('click', function() {
        localStorage.setItem('cookie_consent', 'accepted');
        consentBanner.style.display = 'none';
        initializeTracking();
        
        // Send consent event
        if (typeof gtag === 'function') {
            gtag('consent', 'update', {
                'analytics_storage': 'granted',
                'ad_storage': 'granted',
                'ad_user_data': 'granted',
                'ad_personalization': 'granted'
            });
        }
    });
    
    // Reject button handler
    rejectBtn.addEventListener('click', function() {
        localStorage.setItem('cookie_consent', 'rejected');
        consentBanner.style.display = 'none';
        
        // Send consent denial
        if (typeof gtag === 'function') {
            gtag('consent', 'update', {
                'analytics_storage': 'denied',
                'ad_storage': 'denied',
                'ad_user_data': 'denied',
                'ad_personalization': 'denied'
            });
        }
    });
    
    function initializeTracking() {
        // Tracking scripts are already loaded, just enable them
        if (typeof gtag === 'function') {
            gtag('consent', 'update', {
                'analytics_storage': 'granted',
                'ad_storage': 'granted',
                'ad_user_data': 'granted',
                'ad_personalization': 'granted'
            });
        }
    }
    
    // Hover effects
    acceptBtn.addEventListener('mouseenter', function() {
        this.style.background = '#0077ed';
    });
    acceptBtn.addEventListener('mouseleave', function() {
        this.style.background = '#0071e3';
    });
    
    rejectBtn.addEventListener('mouseenter', function() {
        this.style.background = '#e8e8ed';
    });
    rejectBtn.addEventListener('mouseleave', function() {
        this.style.background = '#f5f5f7';
    });
})();
</script>

<style>
@media (max-width: 768px) {
    #cookie-consent-banner > div {
        flex-direction: column;
        text-align: center;
    }
    #cookie-consent-banner > div > div:last-child {
        width: 100%;
    }
    #cookie-consent-banner button {
        width: 100%;
    }
}
</style>
@endif
