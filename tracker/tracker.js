/**
 * Trackveil - Simple Website Visitor Tracker
 * Phase 1: Automatic page view tracking
 */

(function() {
  'use strict';

  // Configuration
  const API_ENDPOINT = 'https://api.trackveil.net/track';
  const FINGERPRINT_KEY = 'tv_fp';
  const SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes
  const DEBUG = false; // Set to true during development only

  /**
   * Debug logging (only when DEBUG is enabled)
   */
  function log(message, data) {
    if (DEBUG && console && console.log) {
      console.log('[Trackveil] ' + message, data || '');
    }
  }

  /**
   * Get the site ID from the script tag
   */
  function getSiteId() {
    const script = document.currentScript || 
                   document.querySelector('script[data-site-id]');
    
    if (!script) {
      log('Script tag not found - tracking disabled');
      return null;
    }

    const siteId = script.getAttribute('data-site-id');
    if (!siteId) {
      log('data-site-id attribute is required - tracking disabled');
      return null;
    }

    return siteId;
  }

  /**
   * Generate a browser fingerprint
   * Combines various browser properties to create a semi-unique identifier
   */
  function generateFingerprint() {
    const components = [
      navigator.userAgent,
      navigator.language,
      screen.width + 'x' + screen.height,
      screen.colorDepth,
      new Date().getTimezoneOffset(),
      !!window.sessionStorage,
      !!window.localStorage,
      navigator.platform,
      navigator.hardwareConcurrency || 'unknown',
      navigator.deviceMemory || 'unknown'
    ];

    // Add canvas fingerprint (if available)
    try {
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');
      if (ctx) {
        ctx.textBaseline = 'top';
        ctx.font = '14px Arial';
        ctx.fillText('Trackveil', 2, 2);
        components.push(canvas.toDataURL());
      }
    } catch (e) {
      // Canvas fingerprinting might be blocked
    }

    // Simple hash function
    const str = components.join('|');
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      const char = str.charCodeAt(i);
      hash = ((hash << 5) - hash) + char;
      hash = hash & hash; // Convert to 32-bit integer
    }

    return 'fp_' + Math.abs(hash).toString(36) + '_' + Date.now().toString(36);
  }

  /**
   * Get or create a persistent fingerprint
   */
  function getFingerprint() {
    try {
      let fingerprint = localStorage.getItem(FINGERPRINT_KEY);
      
      if (!fingerprint) {
        fingerprint = generateFingerprint();
        localStorage.setItem(FINGERPRINT_KEY, fingerprint);
      }

      return fingerprint;
    } catch (e) {
      // LocalStorage might be disabled
      return generateFingerprint();
    }
  }

  /**
   * Get page load time (if available)
   */
  function getPageLoadTime() {
    if (!window.performance || !window.performance.timing) {
      return null;
    }

    const timing = window.performance.timing;
    const loadTime = timing.loadEventEnd - timing.navigationStart;

    // Return only if load is complete and time is reasonable
    if (timing.loadEventEnd === 0 || loadTime < 0 || loadTime > 60000) {
      return null;
    }

    return loadTime;
  }

  /**
   * Collect tracking data
   */
  function collectData(siteId) {
    return {
      site_id: siteId,
      page_url: window.location.href,
      page_title: document.title,
      referrer: document.referrer,
      screen_width: screen.width,
      screen_height: screen.height,
      fingerprint: getFingerprint(),
      load_time: getPageLoadTime()
    };
  }

  /**
   * Send tracking data to the API
   * Uses multiple fallback methods to ensure delivery even with service workers
   */
  function sendTracking(data) {
    // Method 1: sendBeacon (best for most modern browsers, often bypasses service workers)
    if (navigator.sendBeacon) {
      try {
        const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
        const sent = navigator.sendBeacon(API_ENDPOINT, blob);
        if (sent) {
          return; // Successfully sent via sendBeacon
        }
      } catch (e) {
        // sendBeacon failed, continue to fallbacks
      }
    }

    // Method 2: Image pixel (universal fallback - ALWAYS works, bypasses ALL service workers)
    // This is the most reliable cross-browser, cross-service-worker method
    try {
      var img = new Image(1, 1);
      img.style.display = 'none';
      
      // Encode data as URL parameters for GET request
      var params = [];
      for (var key in data) {
        if (data.hasOwnProperty(key) && data[key] != null) {
          params.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
        }
      }
      
      // Use GET endpoint (we'll need to add this to the API)
      img.src = API_ENDPOINT + '?' + params.join('&');
      
      // Clean up after load
      img.onload = img.onerror = function() {
        img = null;
      };
      
      // Append to body briefly to ensure it loads
      document.body.appendChild(img);
      setTimeout(function() {
        if (img && img.parentNode) {
          img.parentNode.removeChild(img);
        }
      }, 1000);
      
      return; // Image request sent
    } catch (e) {
      // Image method failed, try fetch as last resort
    }

    // Method 3: fetch (last resort, may be blocked by service workers)
    try {
      fetch(API_ENDPOINT, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
        keepalive: true,
        credentials: 'omit',
        cache: 'no-store',
        mode: 'cors'
      }).catch(function(error) {
        // Completely silent fail - don't disrupt the user experience
        log('All tracking methods failed', error);
      });
    } catch (e) {
      // All methods failed - completely silent
      log('Tracking completely failed', e);
    }
  }

  /**
   * Initialize tracking
   */
  function init() {
    // Get site ID
    const siteId = getSiteId();
    if (!siteId) {
      return;
    }

    // Wait for page to be interactive/complete
    function track() {
      const data = collectData(siteId);
      sendTracking(data);
    }

    // Track page view
    if (document.readyState === 'complete') {
      // Page already loaded
      setTimeout(track, 100); // Small delay to get load time
    } else {
      // Wait for page load
      window.addEventListener('load', function() {
        setTimeout(track, 100);
      });
    }

    // Track page visibility changes (for better load time accuracy)
    // and potential future session tracking
    document.addEventListener('visibilitychange', function() {
      if (document.visibilityState === 'hidden') {
        // Page is being hidden, could track session end in Phase 2
      }
    });
  }

  // Start tracking
  init();

})();

