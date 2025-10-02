# Trackveil Tracker

Lightweight JavaScript snippet for tracking website visitors.

## Installation

Add this single line to your website's HTML (before closing `</body>` tag):

```html
<script async src="https://cdn.trackveil.net/tracker.min.js" data-site-id="YOUR_SITE_ID"></script>
```

Replace `YOUR_SITE_ID` with your actual site ID from the Trackveil dashboard.

## What It Tracks (Phase 1)

### Automatic Collection
- **Page URL** - Current page location
- **Page Title** - Document title
- **Referrer** - Where the visitor came from
- **Screen Resolution** - Visitor's screen dimensions
- **User Agent** - Browser and device information (parsed server-side)
- **IP Address** - Collected server-side for geolocation
- **Page Load Time** - How long the page took to load
- **Unique Visitor ID** - Browser fingerprint for visitor identification

### How Visitor Identification Works
The tracker creates a semi-unique "fingerprint" using:
- User agent
- Screen resolution
- Language
- Timezone
- Canvas fingerprint
- Other browser properties

This fingerprint is stored in `localStorage` and used to identify returning visitors without cookies.

## Privacy Considerations

- **No Personal Data**: The tracker never collects names, emails, or other personal information
- **No Cookies**: Uses localStorage for fingerprinting instead of cookies
- **Fingerprint Only**: Visitor identification is based on browser characteristics, not personal data
- **IP Addresses**: Collected for geolocation but can be anonymized
- **GDPR Friendly**: As a data processor, we only collect infrastructure data

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11+ (with fetch polyfill)
- Mobile browsers (iOS Safari, Chrome Android)

## File Structure

```
tracker/
├── tracker.js        # Source code (readable)
├── tracker.min.js    # Minified version (production)
├── package.json      # Build configuration
└── README.md         # This file
```

## Development

### Building the Minified Version

```bash
npm install
npm run build
```

This will minify `tracker.js` and output `tracker.min.js`.

### Testing Locally

1. Start the API server (see `api/README.md`)
2. Update the `API_ENDPOINT` in `tracker.js` to `http://localhost:8080/track`
3. Create a test HTML file:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Trackveil Test</title>
</head>
<body>
    <h1>Testing Trackveil</h1>
    <p>Check your browser console and API logs.</p>
    
    <script src="tracker.js" data-site-id="00000000-0000-0000-0000-000000000003"></script>
</body>
</html>
```

4. Open in a browser and check:
   - Browser console for any errors
   - API server logs for incoming requests
   - Database for recorded page views

## Deployment

1. Build the minified version
2. Upload `tracker.min.js` to your CDN/web server
3. Point `cdn.trackveil.net` to your server
4. Ensure CORS is properly configured on the API

## Future Features (Phase 2+)

- Custom event tracking
- SPA (Single Page Application) support
- E-commerce tracking
- Form submission tracking
- Error tracking
- Session replay

