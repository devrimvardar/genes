# Example 5: HTML Partials & Templating

Demonstrating Genes Framework with **static HTML partials** and template rendering.

## What This Demonstrates

- ✅ Static HTML partial files
- ✅ Template composition
- ✅ Header/Footer includes
- ✅ Component reusability
- ✅ No database required
- ✅ Fast static rendering

## How It Works

Pages are composed from HTML partial files:

```
templates/
├── header.html      - Site header
├── footer.html      - Site footer
├── nav.html         - Navigation menu
└── pages/
    ├── home.html    - Home page content
    ├── about.html   - About page content
    └── contact.html - Contact page content
```

The PHP script loads and assembles these partials.

## Running the Example

```bash
php -S localhost:8004 -t examples/5-html-partials
```

Visit:
- `http://localhost:8004` - Home
- `http://localhost:8004?page=about` - About
- `http://localhost:8004?page=contact` - Contact

## Template Variables

Partials can include placeholders:

```html
<!-- header.html -->
<h1>{{site_name}}</h1>
<p>{{tagline}}</p>
```

Replaced at runtime:

```php
g::set("template.site_name", "My Site");
g::set("template.tagline", "Welcome!");
```

## Use Cases

- Marketing sites
- Documentation
- Static blogs
- Landing pages
- Portfolio sites
- Company websites

## Benefits

- **Version Control**: HTML files in Git
- **Designer Friendly**: Pure HTML, no PHP needed
- **Fast**: No database queries
- **Cacheable**: Static output
- **Composable**: Reuse partials

## Advanced Features

### Includes
```html
{{include:components/button.html}}
```

### Conditionals (via PHP)
```php
if ($showNewsletter) {
    echo loadPartial('newsletter.html');
}
```

### Loops
```php
foreach ($items as $item) {
    echo renderTemplate('item-card.html', $item);
}
```

## Next Steps

- **Config Data** → See Example 4
- **Full Stack** → See Examples 1-3
- **Documentation** → Read `/docs`
