# Example 4: Config-Based Data (No Database)

Demonstrating Genes Framework **without a database** using config.json as data source.

## What This Demonstrates

- ✅ No database required
- ✅ Config.json as data source
- ✅ Static content management
- ✅ File-based routing
- ✅ Template rendering
- ✅ Perfect for simple sites

## Use Cases

- Documentation sites
- Landing pages
- Portfolios
- Small business sites
- Static blogs (no CMS needed)

## How It Works

All content is stored in `data/config.json`:

```json
{
  "site": {
    "name": "My Portfolio",
    "tagline": "Developer & Designer"
  },
  "pages": [
    {
      "slug": "home",
      "title": "Home",
      "content": "Welcome to my portfolio..."
    },
    {
      "slug": "about",
      "title": "About Me",
      "content": "I'm a developer..."
    }
  ],
  "projects": [
    {
      "title": "Project 1",
      "description": "A cool project",
      "url": "https://github.com/..."
    }
  ]
}
```

No database connection needed!

## Running the Example

```bash
php -S localhost:8003 -t examples/4-config-data
```

Visit:
- `http://localhost:8003` - Home page
- `http://localhost:8003?page=about` - About page

## Features

- Load data from config.json
- No SQL queries
- File-based content
- Easy to edit (just JSON)
- Version control friendly
- Fast and simple

## When to Use This Pattern

**Perfect for:**
- Static sites with <50 pages
- Personal portfolios
- Documentation sites
- Landing pages
- Prototyping

**Not suitable for:**
- User-generated content
- Comments/interactions
- Large datasets
- Search functionality
- Multi-user editing

## Next Steps

- **HTML Partials** → See Example 5
- **Full Stack** → See Examples 1-3
- **Documentation** → Read `/docs`
