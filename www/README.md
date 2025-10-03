# Trackveil Marketing Website

Static HTML marketing site with Tailwind CSS.

## Structure

```
www/
├── public/              # Document root (deploy this)
│   ├── index.html      # Landing page
│   ├── img/
│   │   └── logo_icon.png
│   └── .htaccess       # Security & caching
│
├── design_manual.md     # Design guidelines
├── landing_page_wire_frame.md  # Page structure
└── example.html         # Original design (reference)
```

## Deployment

### Deploy to trackveil.net

```bash
# From local machine
cd /Users/lgforsberg/Projects/trackveil/www

rsync -avz public/ lg@markedo.com:/var/www/trackveil.net/
```

### Nginx Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name trackveil.net www.trackveil.net;
    
    root /var/www/trackveil.net;
    index index.html;
    
    # SSL
    ssl_certificate /etc/letsencrypt/live/trackveil.net/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/trackveil.net/privkey.pem;
    
    location / {
        try_files $uri $uri/ =404;
    }
    
    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|svg|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

## Design

Based on:
- Radar/sonar icon (turquoise/blue gradient)
- Clean, minimal aesthetic
- Navy/turquoise color scheme
- Modern, trustworthy feel

See `design_manual.md` for complete guidelines.

## Local Development

```bash
cd public
python3 -m http.server 8000

# Visit: http://localhost:8000
```

## Pages

- ✅ `index.html` - Landing page with hero, features, pricing, FAQ
- Phase 2: Privacy policy, Terms of service, Documentation

## Updates

To update the live site:

```bash
# Edit public/index.html
# Then deploy:
rsync -avz public/ lg@markedo.com:/var/www/trackveil.net/
```

No restart needed - static files!

