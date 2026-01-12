# Example 1: Multi-Language Landing Page

A complete demonstration of building a professional landing page with Genes Framework v2.0.

## What This Example Teaches

### 1. **View-Based Routing** (`config.json`)
- Define routes for multiple languages
- Map URLs to view functions
- Automatic language detection from URL

### 2. **Modern Template Engine** (`data-g-*` attributes)
- **`data-g-if`** - Conditional rendering based on page/state
- **`data-g-for`** - Loop through arrays (features, stats, plans)
- **`data-g-load`** - Load partial templates (contact form, product content)
- **`data-g-bind`** - Safe text content binding
- **`data-g-html`** - Raw HTML binding
- **`{{variable}}`** - Simple variable replacement

### 3. **Multi-Language Support**
- Three languages: English, Turkish, German
- URL-based language detection
- Translated bits in config.json
- Language switcher in navigation

### 4. **Genes.css Framework**
- Responsive rem-based layouts
- Utility classes (`.p-4`, `.flex-center`, `.grid`, `.gap-2`)
- Dark/light theme support
- Custom styles extending genes.css

### 5. **Separation of Concerns**
- **HTML** in `ui/index.html` (template)
- **PHP** in `index.php` (logic)
- **Config** in `data/config.json` (routing, translations)
- **CSS** in `ui/assets/app.css` (custom styles)
- **JavaScript** in `ui/assets/app.js` (interactivity)

### 6. **Partial Templates**
- `ui/partials/contact-form.html` - Reusable contact form
- `ui/partials/product-content.html` - Product information
- Loaded with `data-g-load` directive

## File Structure

```
1-landing-page/
├── index.php                       # Main entry point with clone functions
├── data/
│   └── config.json                # Views, routes, translations
├── ui/
│   ├── index.html                 # Main template with data-g-* attributes
│   ├── partials/
│   │   ├── contact-form.html     # Contact form partial
│   │   └── product-content.html  # Product content partial
│   └── assets/
│       ├── app.css               # Custom styles (extends genes.css)
│       └── app.js                # Custom JavaScript
├── cache/                         # Auto-created for caching
└── uploads/                       # Auto-created for uploads
```

## How It Works

### 1. Routing Flow

```
User visits: /pricing
          ↓
route.handle() parses URL
          ↓
Matches "en": "pricing" in config.json
          ↓
Language detected: en
          ↓
Calls: clone.Pricing($bits, "en", "/pricing")
          ↓
Function prepares data
          ↓
tpl.renderView("Pricing", $data)
          ↓
Loads ui/index.html
          ↓
Renders with data using data-g-* attributes
          ↓
Returns HTML to browser
```

### 2. Template Rendering

```html
<!-- Before rendering -->
<div data-g-for="feature in features" data-a>
    <h3>{{feature.title}}</h3>
</div><!--a-->

<!-- After rendering -->
<div>
    <h3>Zero Dependencies</h3>
</div>
<div>
    <h3>Simple & Powerful</h3>
</div>
<div>
    <h3>Multi-Language</h3>
</div>
```

### 3. Multi-Language

```
/index → English home page
/anasayfa → Turkish home page
/startseite → German home page

/pricing → English pricing
/fiyatlandirma → Turkish pricing
/preise → German pricing
```

## Running The Example

### Option 1: PHP Built-in Server

```bash
cd examples/1-landing-page
php -S localhost:8000
```

Visit: `http://localhost:8000/index`

### Option 2: Apache/Nginx

Place in web root and configure virtual host to point to the directory.

## Testing Different Pages

- **Home (English):** `/index`
- **Home (Turkish):** `/anasayfa`
- **Home (German):** `/startseite`
- **Product:** `/product`, `/urun`, `/produkt`
- **Pricing:** `/pricing`, `/fiyatlandirma`, `/preise`
- **About:** `/about`, `/hakkinda`, `/uber-uns`
- **Contact:** `/contact`, `/iletisim`, `/kontakt`

## Key Concepts Demonstrated

### Progressive Enhancement

HTML is valid and shows defaults, enhanced server-side with data:

```html
<!-- Progressive enhancement in action -->
<h1 data-g-bind="bits.title" data-a>Welcome</h1><!--a-->
```

If JavaScript fails, users still see "Welcome".  
When rendered server-side, they see the actual translated title.

### Marker System

Use single-letter markers (`data-a`, `data-b`) with matching comments:

```html
<div data-g-if="user" data-a>
    Content here
</div><!--a-->
```

This helps the template engine find matching closing tags.

### Conditional Content

Show different content based on data:

```html
<!-- Show only on home page -->
<section data-g-if="current_page:home" data-a>
    Hero section
</section><!--a-->

<!-- Show only on pricing page -->
<section data-g-if="show_pricing:true" data-b>
    Pricing content
</section><!--b-->
```

### Loops with Special Variables

```html
<div data-g-for="item in items" data-a>
    Item #{{_index}} (First: {{_first}}, Last: {{_last}})
    {{item.name}}
</div><!--a-->
```

Special variables available in loops:
- `{{_index}}` - Current index (0-based)
- `{{_first}}` - true if first item
- `{{_last}}` - true if last item

## Customization

### Adding a New Language

1. **Add to `config.json`:**
```json
{
  "views": {
    "Index": {
      "urls": {
        "en": "index",
        "tr": "anasayfa",
        "de": "startseite",
        "fr": "accueil"  // Add French
      },
      "bits": {
        "title": {
          "en": "Welcome",
          "tr": "Hoşgeldiniz",
          "de": "Willkommen",
          "fr": "Bienvenue"  // Add translation
        }
      }
    }
  }
}
```

2. **Add language switcher button:**
```html
<a href="accueil" class="lang-btn" title="Français">FR</a>
```

### Adding a New Page

1. **Add view to `config.json`:**
```json
{
  "views": {
    "Services": {
      "function": "clone.Services",
      "urls": {
        "en": "services"
      },
      "bits": {
        "title": {
          "en": "Our Services"
        }
      }
    }
  }
}
```

2. **Add function to `index.php`:**
```php
"Services" => function ($bits, $lang, $path) {
    $data = array(
        "bits" => $bits,
        "lang" => $lang,
        "current_page" => "services",
        "show_services" => true
    );
    
    $html = g::run("tpl.renderView", "Services", $data);
    echo $html;
}
```

3. **Add content section to `ui/index.html`:**
```html
<section data-g-if="show_services:true" class="content-section p-4" data-newid>
    <h1 data-g-bind="bits.title" data-newid2>Services</h1><!--newid2-->
    <!-- Your content here -->
</section><!--newid-->
```

## Next Steps

After mastering this example, check out:
- **Example 2: Blog System** - Database queries, pagination
- **Example 3: REST API/Todo** - Interactive app with authentication

## Learn More

- [Genes Framework Documentation](../../docs/)
- [GENES-V2-CAPABILITIES.md](../../GENES-V2-CAPABILITIES.md)
- [GitHub Repository](https://github.com/devrimvardar/genes)
