# Genes Framework Website

Official website for Genes Framework - **genes.one**

## Overview

This is the complete source code for the Genes Framework website. It serves dual purposes:

1. **Official Website**: Deployed at genes.one
2. **Complete Example**: Full-featured site built with Genes Framework

## Features

- ✅ No database required (uses HTML partials + config.json)
- ✅ Responsive design
- ✅ Clean, modern UI
- ✅ Interactive examples
- ✅ Documentation integration
- ✅ Download section
- ✅ PHP 5.6+ compatible

## Running Locally

```bash
php -S localhost:8080 -t website
```

Visit: `http://localhost:8080`

## Structure

```
website/
├── index.php              # Main router
├── assets/
│   ├── css/
│   │   └── style.css     # Main stylesheet
│   └── js/
│       └── main.js       # Interactive features
├── templates/
│   ├── layout/
│   │   ├── header.html   # Site header
│   │   ├── nav.html      # Navigation
│   │   └── footer.html   # Site footer
│   └── pages/
│       ├── home.html     # Homepage
│       ├── docs.html     # Documentation
│       ├── examples.html # Examples showcase
│       ├── download.html # Download/Getting Started
│       └── about.html    # About Genes
└── data/
    └── config.json       # Site configuration & content
```

## Deployment

### Option 1: Traditional Hosting

1. Upload `website/` folder to your web host
2. Point domain to the folder
3. Ensure PHP 5.6+ is available
4. Done!

### Option 2: GitHub Pages (with PHP workaround)

Since GitHub Pages doesn't support PHP, you'd need to:
1. Pre-render pages to static HTML
2. Or use Netlify/Vercel with PHP support

### Option 3: Netlify/Vercel

1. Connect repository
2. Set build directory to `website/`
3. Deploy

## Customization

All content is in `data/config.json` and `templates/pages/*.html`:

- **Edit content**: Modify HTML files in `templates/pages/`
- **Change styling**: Edit `assets/css/style.css`
- **Update config**: Modify `data/config.json`

## Technologies

- **Framework**: Genes (of course!)
- **PHP**: 5.6+
- **CSS**: Vanilla CSS with CSS Grid & Flexbox
- **JavaScript**: Vanilla ES5 (no frameworks)
- **Database**: None (uses static files)

## Learn From This

This website demonstrates:
- HTML partial templating
- Config-based data management
- Responsive design
- No-build-tool workflow
- SEO-friendly structure
- Fast page loads

Perfect example for building marketing sites, portfolios, or documentation with Genes Framework!
