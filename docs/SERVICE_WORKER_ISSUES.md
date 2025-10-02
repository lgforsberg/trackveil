# Service Worker Compatibility

## The Problem

Some websites use Service Workers that intercept all fetch requests, including third-party API calls. This can cause Trackveil tracking to fail with errors like:

```
The FetchEvent for "https://api.trackveil.net/track" resulted in a network error response
POST https://api.trackveil.net/track net::ERR_FAILED
```

## The Solution

### Option 1: Update Trackveil Tracker (Done)

The latest tracker includes `cache: 'no-store'` and tries `sendBeacon` first, which often bypasses service workers.

### Option 2: Whitelist Trackveil in Your Service Worker (Recommended)

If you control the website's service worker, add this to your `sw.js`:

```javascript
// In your service worker fetch event listener
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  
  // Don't intercept Trackveil API calls
  if (url.hostname === 'api.trackveil.net') {
    return; // Let the request go through normally
  }
  
  // Your existing service worker logic here
  event.respondWith(
    // ... your cache logic
  );
});
```

Or more generally, don't intercept cross-origin POST requests:

```javascript
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  
  // Don't intercept cross-origin POST requests (analytics, APIs, etc.)
  if (url.origin !== location.origin && event.request.method === 'POST') {
    return;
  }
  
  // Your existing service worker logic
  event.respondWith(
    // ... your cache logic
  );
});
```

### Option 3: Unregister Service Worker (Temporary Test)

To test if the service worker is the issue:

```javascript
// Open browser console on the affected site and run:
navigator.serviceWorker.getRegistrations().then(function(registrations) {
  for(let registration of registrations) {
    registration.unregister();
  }
  location.reload();
});
```

If tracking works after this, the service worker was the culprit.

## For kea.gl Specifically

Your kea.gl site has a service worker at `sw.js` that's intercepting the Trackveil requests. 

**Quick fix:** Add this near the top of your `sw.js`:

```javascript
self.addEventListener('fetch', (event) => {
  // Let analytics and tracking requests pass through
  if (event.request.url.includes('api.trackveil.net')) {
    return; // Don't intercept
  }
  
  // ... rest of your existing code
});
```

## Testing

After uploading the new tracker, test with:

```javascript
// Open console on your site
console.log('Testing Trackveil...');

// The tracker should work without service worker errors
// Check Network tab for successful POST to api.trackveil.net
```

## Technical Details

**Why does this happen?**

Service workers can intercept all network requests from a page. If the service worker doesn't properly handle or pass through certain requests (like POST requests to third-party APIs), they can fail.

**What methods bypass service workers?**

1. `navigator.sendBeacon()` - Often bypasses service workers (used by Trackveil)
2. Requests with `cache: 'no-store'` - May bypass some service worker caching
3. Direct DOM operations (img.src, script.src) - Usually bypass service workers

**What Trackveil does:**

1. First tries `sendBeacon` (most reliable, bypasses SW)
2. Falls back to `fetch()` with `cache: 'no-store'` and `credentials: 'omit'`
3. Catches all errors silently to not disrupt the user experience

## Debugging

To see what's happening:

```javascript
// Check if service worker is active
navigator.serviceWorker.controller && console.log('Service worker active:', navigator.serviceWorker.controller);

// Check if sendBeacon is available
console.log('sendBeacon available:', !!navigator.sendBeacon);

// Monitor fetch requests in Network tab
// Filter by "track" to see Trackveil requests
```

## Support

If you're still having issues after trying these solutions, check:

1. Browser console for specific error messages
2. Network tab to see if requests are being made
3. Database to confirm if any tracking data is getting through

