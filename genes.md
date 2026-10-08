# Genes

Genes is a specification for generating small, professional, dependency-free PHP
websites that run on ordinary shared hosting. Read this file completely before you
write any code, then follow it exactly.

## How to use this file

This file has two parts:

- **Part 1 — Core.** Apply it to every project.
- **Part 2 — Modules.** Apply a module only when the decision table says so.

Code blocks are marked in two ways:

- **COPY EXACTLY**: copy the code as it is. Do not rename, reorder, shorten, or
  "improve" it.
- **SHAPE**: keep the structure and names; change the content for the project.

### Decision table

| The user wants…                                                | Apply                        |
|----------------------------------------------------------------|------------------------------|
| Any website                                                    | Part 1 — Core                |
| News, blog, products, or any list edited in the browser        | Module A + Module B          |
| A searchable, filterable, or paginated list                    | Module A                     |
| An admin area or login                                         | Module A + Module B          |
| A contact form or any public form                              | Module A + Module B + Module C |
| Database changes after the site is live                        | Module D                     |

Module C needs Module B for sessions and CSRF and to show messages in the admin.

If a module is not in the table for this project, do not add it, its files, its
routes, or its database.

### Before generating

If the request does not answer these questions, ask them in one message and wait:

1. Site name, production URL, and what the organization does.
2. Languages (for example `en`, `tr`, `fi`) and the default language.
3. Pages.
4. Must someone edit content in the browser (news, blog, products)?
5. Is there a contact form? Which email address receives it?
6. Look and feel: light or dark, brand color, logo.

If the user says "just build it", use: English only, pages `home`, `about`,
`contact` (static text, no form), light theme, no modules.

---

# Part 1 — Core

## 1. Rules

- One public entry point: `index.php`. Every page request goes through it.
- No frameworks, Composer packages, Node.js, CDNs, or build tools.
- PHP 7.4 or newer. Do not use PHP 8-only syntax (`match`, `str_contains`, named
  arguments, nullsafe `?->`, constructor promotion).
- Apache with `mod_rewrite`, as on typical shared hosting. PHP's built-in server for
  local development.
- The same files must run unchanged at a domain root, in a subdirectory, locally, and
  in production. Never hardcode the base path or the host.
- All files are UTF-8 without BOM. See section 7.
- All CSS is in one file: `assets/css/site.css`. No `<style>` blocks, no `style`
  attributes.
- JavaScript is optional. When needed, put it in `assets/js/`. No inline scripts or
  inline event handlers, except the JSON-LD block in `layout.php`.
- Every user-visible text comes from `data/content.json` or the database. Never
  hardcode text in templates.
- Escape every plain-text value with `e()` when you output it.
- Write readable, indented code. Do not minify.
- Never change generated HTML with output buffering or string replacement. Change the
  template instead.
- Do not add features, pages, or components that the user did not ask for.

## 2. Project structure

Core project:

```text
index.php                 entry point and runtime (section 4)
.htaccess                 routing and protection (section 3)
robots.txt
llms.txt
data/
    config.json           site settings (section 5)
    content.json          all page text (section 6)
templates/
    layout.php            HTML document, head, header, footer (section 8)
    home.php              one template per page key
    about.php
    contact.php
    404.php
assets/
    css/site.css          all CSS (section 9)
    js/                   optional
    media/                images
    icons/
    fonts/
```

Modules add:

```text
app/                      module PHP code (not public)
    db.php                Module A
    schema.sql            Module A
    auth.php              Module B
    admin.php             Module B
    forms.php             Module C
templates/admin/          Module B
templates/<page>-item.php detail template for a collection page
data/content.sqlite       created automatically by Module A
migrations/               Module D
```

`sitemap.xml` is not a file. `index.php` generates it.

The default site has at least three pages: `home`, `about`, `contact`. A single-page
site uses only `home`.

## 3. .htaccess — COPY EXACTLY

```apache
Options -Indexes
DirectoryIndex index.php
RewriteEngine On

# Force HTTPS in production. Local hosts are skipped.
RewriteCond %{HTTPS} off
RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteCond %{HTTP_HOST} !^(localhost|127\.0\.0\.1|\[::1\])(:\d+)?$
RewriteCond %{HTTP_HOST} !\.(test|localhost)(:\d+)?$
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]

# Private folders, dotfiles, and data files.
RewriteRule ^(app|data|templates|migrations)(/|$) - [F,L]
RewriteRule (^|/)\. - [F,L]
RewriteRule \.(sqlite|sqlite3|db|sql|log)$ - [F,L]

# Public base path, for example /project when the site is in a subdirectory.
RewriteCond %{REQUEST_URI}::$0 ^(/.+)/(.*)::\2$
RewriteRule .* - [E=BASE:%1]

# Everything that is not a real file goes to index.php.
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ %{ENV:BASE}/index.php [L]

<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/plain application/javascript application/json application/xml image/svg+xml
</IfModule>
```

The `BASE` rule makes the same file work at the domain root, in a subdirectory, and
behind an Apache `Alias` (Laragon, XAMPP, MAMP). Do not add `RewriteBase`. Do not
edit this file per environment.

Every folder that receives uploads gets its own `.htaccess` — COPY EXACTLY:

```apache
<FilesMatch "\.(php|phtml|phar|php\d|cgi|pl|py|sh)$">
    Require all denied
</FilesMatch>
```

Do not use `php_flag engine off`; it causes HTTP 500 on many shared hosts.

## 4. Runtime: index.php — COPY EXACTLY

The runtime always follows this order:

```text
request → route → locale → page → data → view → template
```

```php
<?php
declare(strict_types=1);

define('ROOT', __DIR__);

// Errors are shown only on the developer's machine and are always logged.
$local = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
ini_set('display_errors', $local ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', ROOT . '/data/error.log');
mb_internal_encoding('UTF-8');

// PHP built-in server: serve public files directly, never private folders.
if (PHP_SAPI === 'cli-server') {
    $file = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file(ROOT . $file) && !preg_match('#^/(app|data|templates|migrations)/|/\.#', $file)) {
        return false;
    }
}

define('BASE', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));
const RESERVED_PAGES = ['common', '404', 'admin'];

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string {
    return BASE . '/' . ltrim($path, '/');
}

function abs_url(string $siteUrl, string $path = ''): string {
    return rtrim($siteUrl, '/') . '/' . ltrim($path, '/');
}

function redirect(string $location): void {
    header('Location: ' . $location, true, 303);
    exit;
}

function page_path(string $locale, string $page, ?string $slug = null): string {
    if ($page === 'home' || $page === '404') return $locale . '/';
    return $locale . '/' . $page . ($slug !== null ? '/' . $slug : '');
}

function paragraphs(string $text): string {
    $html = '';
    foreach (preg_split('/\R{2,}/u', trim($text)) as $block) {
        if ($block !== '') $html .= '<p>' . nl2br(e($block), false) . "</p>\n";
    }
    return $html;
}

function format_date(?string $iso, array $common): string {
    if (!$iso) return '';
    $time = strtotime($iso);
    return strtr($common['date_format'], [
        '{d}' => date('j', $time), '{month}' => $common['months'][(int) date('n', $time) - 1], '{Y}' => date('Y', $time),
    ]);
}

function localize($value, string $locale, string $default, array $locales) {
    if (!is_array($value)) return $value;
    if ($value && !array_diff(array_keys($value), $locales)) {
        return $value[$locale] ?? $value[$default] ?? '';
    }
    foreach ($value as $key => $item) $value[$key] = localize($item, $locale, $default, $locales);
    return $value;
}

function route(string $uri, array $locales, string $default): array {
    $path = rawurldecode((string) parse_url($uri, PHP_URL_PATH));
    if (BASE !== '' && ($path === BASE || strpos($path, BASE . '/') === 0)) {
        $path = substr($path, strlen(BASE));
    }
    $parts = array_values(array_filter(explode('/', trim($path, '/')), 'strlen'));
    $hasLocale = in_array($parts[0] ?? '', $locales, true);
    $locale = $hasLocale ? array_shift($parts) : $default;
    return [
        'locale' => $locale,
        'has_locale' => $hasLocale,
        'parts' => $parts,
        'page' => $parts[0] ?? 'home',
        'slug' => $parts[1] ?? null,
        'valid' => count($parts) <= 2,
    ];
}

function load_page(array $content, string $page, ?string $slug): ?array {
    if (!preg_match('/^[a-z0-9-]+$/', $page) || in_array($page, RESERVED_PAGES, true) || !isset($content[$page])) {
        return null;
    }
    $data = $content[$page];
    // Module A adds one line here.
    if ($slug === null) return $data;
    if (!preg_match('/^[a-z0-9-]+$/', $slug) || !isset($data['items'][$slug])) return null;
    $parent = $data;
    unset($parent['items']);
    return $data['items'][$slug] + ['parent' => $parent];
}

function make_view(array $config, array $content, array $route, ?array $pageData): array {
    $locales = $config['site']['locales'];
    $default = $config['site']['default_locale'];
    $page = $route['page'];
    $slug = $route['slug'];
    if ($pageData === null) {
        http_response_code(404);
        $page = '404';
        $slug = null;
        $pageData = $content['404'] ?? [];
    }
    $view = [
        'site' => $config['site'],
        'theme' => $config['theme'],
        'locale' => $route['locale'],
        'locales' => $locales,
        'dir' => in_array($route['locale'], ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr',
        'page' => $page,
        'slug' => $slug,
        'template' => $slug !== null ? $page . '-item' : $page,
        'common' => localize($content['common'] ?? [], $route['locale'], $default, $locales),
        'content' => localize($pageData, $route['locale'], $default, $locales),
        'form' => [],
    ];
    // Module C adds one line here.
    $view['meta'] = make_meta($view);
    return $view;
}

function make_meta(array $view): array {
    $site = $view['site'];
    $content = $view['content'];
    $is404 = $view['page'] === '404';
    $title = (string) ($content['title'] ?? $view['theme']['title']);
    $description = (string) ($content['description'] ?? $content['summary'] ?? $content['text'] ?? $view['theme']['description']);
    $description = mb_substr(trim((string) preg_replace('/\s+/u', ' ', $description)), 0, 160);

    $alternates = [];
    foreach ($view['locales'] as $locale) {
        $alternates[$locale] = abs_url($site['url'], page_path($locale, $view['page'], $view['slug']));
    }
    $canonical = $is404 ? null : $alternates[$view['locale']];

    if ($view['page'] === 'home') {
        $schema = ['@type' => 'Organization', 'name' => $site['name'], 'url' => abs_url($site['url']), 'description' => $description];
    } elseif ($view['slug'] !== null) {
        $schema = ['@type' => 'Article', 'headline' => $title, 'description' => $description, 'url' => $canonical,
            'inLanguage' => $view['locale'], 'datePublished' => $content['published_at'] ?? null,
            'publisher' => ['@type' => 'Organization', 'name' => $site['name']]];
    } else {
        $schema = ['@type' => 'WebPage', 'name' => $title, 'description' => $description, 'url' => $canonical, 'inLanguage' => $view['locale']];
    }

    return [
        'title' => $view['page'] === 'home' ? $site['name'] . ' — ' . $title : $title . ' | ' . $site['name'],
        'description' => $description,
        'canonical' => $canonical,
        'alternates' => $is404 ? [] : $alternates,
        'x_default' => $alternates[$site['default_locale']],
        'robots' => $is404 ? 'noindex' : null,
        'image' => !empty($content['image']) ? abs_url($site['url'], $content['image']) : null,
        'schema' => $is404 ? null : ['@context' => 'https://schema.org'] + array_filter($schema),
    ];
}

function render(array $view): void {
    extract($view, EXTR_SKIP);
    require ROOT . '/templates/layout.php';
}

function render_sitemap(array $config, array $content): void {
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($config['site']['locales'] as $locale) {
        foreach ($content as $page => $data) {
            $page = (string) $page;
            if (in_array($page, RESERVED_PAGES, true)) continue;
            echo '  <url><loc>' . e(abs_url($config['site']['url'], page_path($locale, $page))) . '</loc></url>' . "\n";
            $slugs = array_fill_keys(array_keys($data['items'] ?? []), null);
            // Module A adds one line here.
            foreach ($slugs as $slug => $lastmod) {
                echo '  <url><loc>' . e(abs_url($config['site']['url'], page_path($locale, $page, (string) $slug))) . '</loc>'
                    . ($lastmod ? '<lastmod>' . e(substr($lastmod, 0, 10)) . '</lastmod>' : '') . '</url>' . "\n";
            }
        }
    }
    echo '</urlset>' . "\n";
}

function send_headers(): void {
    header('Content-Type: text/html; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; form-action 'self'; base-uri 'self'; frame-ancestors 'self'");
}

$config = json_decode(file_get_contents(ROOT . '/data/config.json'), true, 512, JSON_THROW_ON_ERROR);
$content = json_decode(file_get_contents(ROOT . '/data/content.json'), true, 512, JSON_THROW_ON_ERROR);

send_headers();
$route = route($_SERVER['REQUEST_URI'] ?? '/', $config['site']['locales'], $config['site']['default_locale']);

// Modules add their requires and routes here.

if (!$route['has_locale'] && $route['page'] === 'sitemap.xml' && $route['slug'] === null) {
    render_sitemap($config, $content);
    exit;
}

$pageData = $route['valid'] ? load_page($content, $route['page'], $route['slug']) : null;
render(make_view($config, $content, $route, $pageData));
```

What the runtime guarantees:

- `/` and `/about` use the default locale. `/en/` and `/en/about` use `en`.
- `/en/news/first-post` is page `news`, slug `first-post`.
- Unknown pages, unknown slugs, and extra segments render `404.php` with HTTP 404.
- Internal links have no trailing slash, except a locale home such as `/en/`.
- `?page=2` is allowed only for pagination. Never route pages with query strings.
- PHP turns numeric array keys such as `"404"` into integers. Cast keys with
  `(string)` before you pass them to typed functions.

Run locally without Apache from the project folder:

```text
php -S localhost:8000 index.php
```

## 5. Configuration: data/config.json — SHAPE

```json
{
    "site": {
        "name": "Site name",
        "url": "https://example.com",
        "default_locale": "en",
        "locales": ["en", "tr"]
    },
    "theme": {
        "title": "Site name — short promise",
        "description": "One sentence about the site."
    }
}
```

`theme.title` and `theme.description` are fallbacks only. Page titles and
descriptions come from `content.json` in the visitor's language.

- `site.url` is the production URL without a trailing slash. Canonical links,
  sitemap, and Open Graph always use it, so a local copy still points to production.
- `config.json` holds settings only: URLs, locales, email addresses, feature flags.
- Never put page text, translations, passwords, API keys, or other secrets in
  `config.json`.

## 6. Content and localization: data/content.json — SHAPE

The root keys of `content.json` are page keys plus the reserved keys `common`,
`404`, and `admin` (Module B). Each page key has a template with the same name in
`templates/`.

```json
{
    "common": {
        "nav": {
            "home": { "en": "Home", "tr": "Ana sayfa" },
            "about": { "en": "About", "tr": "Hakkımızda" },
            "contact": { "en": "Contact", "tr": "İletişim" }
        },
        "language": { "en": "Language", "tr": "Dil" },
        "footer": { "en": "© Example Ltd", "tr": "© Example Ltd" },
        "months": {
            "en": ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            "tr": ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"]
        },
        "date_format": { "en": "{month} {d}, {Y}", "tr": "{d} {month} {Y}" }
    },
    "home": {
        "title": { "en": "We build bridges", "tr": "Köprüler inşa ediyoruz" },
        "description": { "en": "Search snippet text.", "tr": "Arama sonucu metni." },
        "intro": {
            "text": { "en": "Intro text", "tr": "Giriş metni" }
        }
    },
    "about": {
        "title": { "en": "About us", "tr": "Hakkımızda" },
        "description": { "en": "Who we are.", "tr": "Biz kimiz." }
    },
    "404": {
        "title": { "en": "Page not found", "tr": "Sayfa bulunamadı" },
        "text": { "en": "This page does not exist.", "tr": "Bu sayfa yok." }
    }
}
```

Rules:

- A **locale object** is an object whose keys are all configured locale codes:
  `{ "en": "...", "tr": "..." }`. Every translatable text is a locale object. A
  locale object may also hold a list, as in `months`.
- Never use a locale code as an ordinary key.
- Text that is the same in every language may be a plain string, for example a brand
  name or an email address.
- Pages may nest sections as deep as needed. Pages do not need the same structure.
- `common.nav` lists the menu in order. Its keys are page keys.
- Every page has `title` and `description` in every locale.
- A missing translation falls back to the default locale, then to an empty string.
- Templates are shared by all languages. Never copy a template per language.
- Text is plain text. HTML inside content is not allowed unless the user asks for it.

A small collection that does not need an admin stores records under `items`, keyed
by slug. The detail template is `<page>-item.php`:

```json
{
    "services": {
        "title": { "en": "Services", "tr": "Hizmetler" },
        "description": { "en": "What we do.", "tr": "Ne yapıyoruz." },
        "items": {
            "consulting": {
                "title": { "en": "Consulting", "tr": "Danışmanlık" },
                "text": { "en": "Body text", "tr": "Metin" }
            }
        }
    }
}
```

On a detail page, `$content` is the item, and `$content['parent']` holds the strings
of the list page, for example a "back" label.

Use Module A instead when the collection is edited in the browser, searched,
filtered, or paginated.

## 7. UTF-8 and languages

Everything is UTF-8, so adding a language means adding its code to `site.locales`
and its translations to the content. Follow these rules so that Turkish, Finnish,
German, and other languages work without special cases:

- Save every file as UTF-8 without BOM. `<meta charset="utf-8">` is the first
  element in `<head>`. The runtime sends `Content-Type: text/html; charset=utf-8`.
- For user text, use `mb_strlen`, `mb_substr`, `mb_strtolower`, and `mb_strtoupper`.
  Never use `strlen`, `substr`, `strtolower`, or `ucfirst` on user text; they count
  bytes and break characters such as `İ`, `ş`, `ä`.
- Write JSON with `json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)`.
- Regular expressions on user text use the `u` flag.
- Format dates only with `format_date()`, which uses `common.months` and
  `common.date_format`. Store dates in UTC as `YYYY-MM-DDTHH:MM:SSZ`. Do not depend
  on the `intl` extension; many shared hosts do not have it.
- Create slugs only with `slugify()` — COPY EXACTLY into the file that needs it:

```php
function slugify(string $text): string {
    $map = [
        'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i', 'ö' => 'o', 'Ö' => 'o',
        'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u', 'ä' => 'a', 'Ä' => 'a', 'å' => 'a', 'Å' => 'a',
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'í' => 'i',
        'î' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'û' => 'u', 'ñ' => 'n', 'ß' => 'ss',
        'æ' => 'ae', 'Æ' => 'ae', 'ø' => 'o', 'Ø' => 'o', 'œ' => 'oe', 'Œ' => 'oe',
    ];
    $text = strtolower(strtr($text, $map));
    $text = (string) preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim(substr($text, 0, 80), '-');
}
```

  If `slugify()` returns an empty string (for example for Cyrillic or Arabic text),
  use the record hash as the slug.
- Right-to-left languages (`ar`, `fa`, `he`, `ur`) get `dir="rtl"` from the runtime.
- Mail subjects use `mb_encode_mimeheader()`. Mail bodies are sent as
  `text/plain; charset=UTF-8`.
- SQLite stores UTF-8 natively. Its `LIKE`, `lower()`, and `upper()` only handle
  ASCII letters case-insensitively. For search, use FTS5 with
  `tokenize = "unicode61 remove_diacritics 2"`.

## 8. Templates

- Templates only output values from the view. They never read files, query the
  database, parse the URL, or choose the locale.
- Templates may call only `e()`, `url()`, `page_path()`, `paragraphs()`,
  `format_date()`, and `csrf_field()` (Modules B and C).
- Variables available in templates: `$site`, `$theme`, `$locale`, `$locales`, `$dir`,
  `$page`, `$slug`, `$template`, `$common`, `$content`, `$form`, `$meta`.
- Long text is output with `paragraphs()`, which splits on empty lines and escapes.

`templates/layout.php` — COPY EXACTLY. Add only extra header or footer markup that
the project needs:

```php
<!doctype html>
<html lang="<?= e($locale) ?>" dir="<?= e($dir) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($meta['title']) ?></title>
    <meta name="description" content="<?= e($meta['description']) ?>">
<?php if ($meta['robots']): ?>
    <meta name="robots" content="<?= e($meta['robots']) ?>">
<?php endif; ?>
<?php if ($meta['canonical']): ?>
    <link rel="canonical" href="<?= e($meta['canonical']) ?>">
<?php foreach ($meta['alternates'] as $hreflang => $href): ?>
    <link rel="alternate" hreflang="<?= e($hreflang) ?>" href="<?= e($href) ?>">
<?php endforeach; ?>
    <link rel="alternate" hreflang="x-default" href="<?= e($meta['x_default']) ?>">
    <meta property="og:type" content="<?= $slug !== null ? 'article' : 'website' ?>">
    <meta property="og:site_name" content="<?= e($site['name']) ?>">
    <meta property="og:title" content="<?= e($content['title'] ?? $theme['title']) ?>">
    <meta property="og:description" content="<?= e($meta['description']) ?>">
    <meta property="og:url" content="<?= e($meta['canonical']) ?>">
<?php if ($meta['image']): ?>
    <meta property="og:image" content="<?= e($meta['image']) ?>">
<?php endif; ?>
    <meta name="twitter:card" content="<?= $meta['image'] ? 'summary_large_image' : 'summary' ?>">
<?php endif; ?>
    <link rel="stylesheet" href="<?= e(url('assets/css/site.css')) ?>?v=<?= filemtime(ROOT . '/assets/css/site.css') ?>">
<?php if ($meta['schema']): ?>
    <script type="application/ld+json"><?= json_encode($meta['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endif; ?>
</head>
<body>
<div class="g-landing">
    <header class="header">
        <a class="brand" href="<?= e(url($locale . '/')) ?>"><?= e($site['name']) ?></a>
        <nav class="nav">
<?php foreach ($common['nav'] as $navPage => $label): ?>
            <a href="<?= e(url(page_path($locale, (string) $navPage))) ?>"<?= $page === (string) $navPage ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
<?php endforeach; ?>
        </nav>
<?php if (count($locales) > 1): ?>
        <nav class="languages" aria-label="<?= e($common['language']) ?>">
<?php foreach ($locales as $language): ?>
            <a href="<?= e(url(page_path($language, $page, $slug))) ?>" hreflang="<?= e($language) ?>"<?= $language === $locale ? ' aria-current="true"' : '' ?>><?= e(strtoupper($language)) ?></a>
<?php endforeach; ?>
        </nav>
<?php endif; ?>
    </header>
    <main>
<?php require ROOT . '/templates/' . $template . '.php'; ?>
    </main>
    <footer class="footer">
        <p><?= e($common['footer']) ?></p>
    </footer>
</div>
</body>
</html>
```

A page template — SHAPE:

```php
<section class="hero">
    <h1><?= e($content['title']) ?></h1>
    <?= paragraphs($content['intro']['text']) ?>
</section>
```

## 9. CSS

### 9.1 BASE CSS — COPY EXACTLY at the top of assets/css/site.css

```css
* { box-sizing: border-box; padding: 0; margin: 0; line-height: 1em }
html { -webkit-text-size-adjust: 100% }
html, body { overflow-x: clip }
@media (max-width: 639px) { html { font-size: 3.125vw } }
@media (min-width: 640px) and (max-width: 1279px) { html { font-size: 1.5625vw } }
@media (min-width: 1280px) { html { font-size: .78125vw } }
img, svg, video, canvas { display: block; max-width: 100% }
button, input, textarea, select { font: inherit; border: 0 }
ul, ol { list-style: none }
table { border-collapse: collapse }
* { scrollbar-width: thin; scrollbar-color: #999 transparent }
*::-webkit-scrollbar { width: .6rem; height: .6rem }
*::-webkit-scrollbar-track { background: transparent }
*::-webkit-scrollbar-thumb { background: #999; border-radius: 1rem }
*::-webkit-scrollbar-thumb:hover { background: #777 }
```

How it works: the page canvas always equals the screen width, so `1rem` is 1/32 of
the screen on mobile, 1/64 on tablet, and 1/128 on desktop. Every size scales with
the screen inside its breakpoint. Only the three breakpoints change the layout.

```text
Mobile   < 640px     canvas 32rem
Tablet   640–1279px  canvas 64rem
Desktop  ≥ 1280px    canvas 128rem
```

- Use `rem` for every size, including fonts, gaps, and icons. Use `px` only for 1px
  lines.
- Columns have fixed `rem` widths. Do not use `%` or `fr` for columns, `minmax()`,
  `auto-fit`, `auto-fill`, or `clamp()`.
- Column widths plus gaps plus side gutters must add up exactly to the canvas.
  Check the sum for each breakpoint.
- Write desktop styles first, then one tablet block and one mobile block. Use only
  the three media queries from BASE CSS.

### 9.2 Layout presets — COPY, then replace the placeholder colors

The presets are structure, not design.

**Landing** — every public page:

```css
.g-landing { width: 128rem; min-height: 100vh; padding: 0 6.4rem }
@media (min-width: 640px) and (max-width: 1279px) {
    .g-landing { width: 64rem; padding: 0 3.2rem }
}
@media (max-width: 639px) {
    .g-landing { width: 32rem; padding: 0 1.6rem }
}
```

Content width inside the landing canvas: desktop `115.2rem`, tablet `57.6rem`,
mobile `28.8rem`.

**Blog** — an article with a sidebar, inside `.g-landing`:

```html
<div class="g-blog">
    <article class="g-article"></article>
    <aside class="g-related"></aside>
</div>
```

```css
.g-blog { display: grid; grid-template-columns: 76.8rem 32rem; gap: 6.4rem; width: 115.2rem }
@media (min-width: 640px) and (max-width: 1279px) {
    .g-blog { grid-template-columns: 57.6rem; gap: 4.8rem; width: 57.6rem }
}
@media (max-width: 639px) {
    .g-blog { grid-template-columns: 28.8rem; gap: 3.2rem; width: 28.8rem }
}
```

On tablet and mobile the sidebar moves below the article.

**Dashboard** — the admin (Module B), not inside `.g-landing`:

```html
<div class="g-dashboard<?= $open ? ' is-open' : '' ?>">
    <nav class="g-nav"></nav>
    <main class="g-main"></main>
    <aside class="g-side"></aside>
</div>
```

```css
.g-dashboard { display: grid; grid-template-columns: 20rem 36rem 72rem; width: 128rem; height: 100vh }
.g-nav, .g-main, .g-side { overflow-y: auto; padding: 2.4rem }
.g-nav { background: #ddd }
.g-main { background: #eee }
.g-side { background: #fff }
@media (min-width: 640px) and (max-width: 1279px) {
    .g-dashboard { grid-template-columns: 20rem 44rem; width: 64rem }
    .g-side { display: none }
    .g-dashboard.is-open .g-main { display: none }
    .g-dashboard.is-open .g-side { display: block }
}
@media (max-width: 639px) {
    .g-dashboard { grid-template-columns: 32rem; grid-template-rows: auto 1fr; width: 32rem }
    .g-nav, .g-main, .g-side { padding: 1.6rem }
    .g-side { display: none }
    .g-dashboard.is-open .g-main { display: none }
    .g-dashboard.is-open .g-side { display: block }
}
```

The server adds `is-open` when a record is open in the editor. On tablet and mobile
the editor then replaces the list. On mobile the navigation is a top bar.

### 9.3 Visual design system

The visual identity is free; this quality floor is not.

**Color roles** — define once, then use only these variables:

```css
:root {
    --bg: #ffffff;        /* page background */
    --surface: #f4f4f2;   /* cards, panels, inputs */
    --line: #e2e2de;      /* borders and dividers */
    --text: #161616;      /* body text */
    --muted: #5c5c58;     /* secondary text */
    --accent: #1f5eff;    /* links, primary buttons, focus */
    --on-accent: #ffffff; /* text on accent */
    --radius: .8rem;      /* one radius for the whole site, or 0 */
}
```

- One accent color unless the brand needs more.
- Body text contrast at least 4.5:1; large headings at least 3:1.
- For a dark site, change the values, not the roles.

**Typography** — BASE CSS sets `line-height: 1em` everywhere, so always set it on
text: `1.5` for paragraphs and lists, `1.1`–`1.2` for headings.

```text
size      desktop  tablet  mobile
display   7.2      5.6     3.6     hero headline only
h1        4.8      4.0     2.8
h2        3.2      2.8     2.2
h3        2.0      1.8     1.7
body      1.6      1.4     1.4
small     1.3      1.2     1.2
```

- Running text is at most `64rem` wide on desktop.
- At most two font families. Default: `system-ui, -apple-system, "Segoe UI", Roboto, sans-serif`.
- Custom fonts are self-hosted `woff2` files in `assets/fonts/` with
  `font-display: swap`.

**Spacing** — use only these steps in `rem`: `0.4 0.8 1.2 1.6 2.4 3.2 4.8 6.4 9.6`.

```text
                      desktop  tablet  mobile
page side gutter      6.4      3.2     1.6
section padding (y)   9.6      6.4     4.8
card padding          3.2      2.4     1.6
grid gap              2.4      1.6     1.6
```

**Components**

- Buttons and inputs are at least `4.4rem` tall on desktop and tablet and `3.6rem` on
  mobile.
- Every link, button, and input has visible `:hover` and `:focus-visible` states.
- Every form field has a visible `<label>`. Errors appear next to the field.
- Mobile navigation must fit in `28.8rem`: let the links wrap. No JavaScript needed.
- Cards in one row have the same height and structure.
- Images have `width`, `height`, and `alt`. Images below the first screen have
  `loading="lazy"`.

## 10. Search and AI discoverability

The runtime and `layout.php` already output title, description, canonical,
`hreflang`, Open Graph, JSON-LD, and `noindex` for 404. You must also:

- Give every page and record its own `title` and `description` (120–160 characters)
  in every locale.
- Use semantic HTML: exactly one `h1` per page, then `h2` and `h3` in order;
  `header`, `nav`, `main`, `article`, `footer`.
- Make every page readable without JavaScript.
- Use descriptive link text, never "click here". Put facts in text, not only in
  images.
- Write `robots.txt` — SHAPE:

```text
User-agent: *
Allow: /
Disallow: /admin

User-agent: OAI-SearchBot
Allow: /

User-agent: GPTBot
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: PerplexityBot
Allow: /

Sitemap: https://example.com/sitemap.xml
```

- Write `llms.txt` in the project root — SHAPE:

```markdown
# Site name

> One-sentence summary of the organization.

One paragraph that explains what the organization does, for whom, and where.

## Pages

- [About](https://example.com/en/about): who we are
- [Services](https://example.com/en/services): what we offer
- [Contact](https://example.com/en/contact): how to reach us
```

- Keep pages fast on shared hosting: no unused CSS or JavaScript, compressed images,
  no third-party scripts unless the user asks for them.
- Analytics is optional. In the EU, load analytics only after the visitor consents.

## 11. Security

Apply every rule in every project, even small ones.

**Injection**

- SQL: PDO prepared statements with bound values for every value. Never put request
  data into an SQL string. Column names and sort orders come from a fixed list.
- HTML: escape every plain-text value with `e()` at output, including attributes.
  Never output `$_GET`, `$_POST`, `$_SERVER`, or `REQUEST_URI` without `e()`.
- Links from data must start with `/`, `https://`, `http://`, `mailto:`, or `tel:`.
  Reject `javascript:` and `data:`.
- JSON inside `<script>`: `json_encode()` with `JSON_HEX_TAG | JSON_HEX_AMP`.
- Files: never build a file path from request data. Page keys and slugs must match
  `^[a-z0-9-]+$`. Uploaded files get a generated name.
- Mail: never put request data in mail headers, except a validated email address in
  `Reply-To`.
- Never use `eval`, `exec`, `system`, `shell_exec`, `passthru`, `unserialize` on
  input, or `extract` on request data.

**Abuse and denial of service** — shared hosting has little CPU and few PHP
workers, so every request must be cheap and bounded:

- Read `content.json` once per request.
- Every database list has `LIMIT`; the maximum page size is 50. Clamp `?page=` to an
  integer between 1 and 1000.
- Never call remote URLs, send many mails, or resize large images during a page
  request.
- Login and public forms use rate limits, a honeypot, and size limits (Modules B
  and C).
- Do not trust `X-Forwarded-For`; use `REMOTE_ADDR`.

**Production**

- `display_errors` is off in production; the runtime does this.
- After the first upload, never overwrite `data/content.sqlite` from your computer;
  it contains the live content. Download it regularly as a backup.

## 12. Assets

- Store CSS, JavaScript, images, icons, and fonts under `assets/`.
- Output every asset path with `url()`.
- Store only relative paths such as `assets/media/team.webp` in content and the
  database. Never store files as base64.
- File names are lowercase and URL-safe.
- Page images: `jpg`, `jpeg`, `png`, `webp`, or `gif`; at most `1920×1920` pixels;
  at most 1 MB.
- `data/` is private. `assets/` is public.

## 13. Final checklist

Before you finish, check every item:

- [ ] Every page in `common.nav` opens, in every locale.
- [ ] `/xx/unknown` returns HTTP 404 with the 404 template.
- [ ] `/data/config.json` and `/templates/layout.php` return HTTP 403.
- [ ] `/sitemap.xml` lists every page in every locale.
- [ ] No text is hardcoded in templates; switching the language changes every text.
- [ ] No horizontal scrolling at 375px, 768px, and 1280px wide.
- [ ] Column widths, gaps, and gutters add up to the canvas at every breakpoint.
- [ ] Spacing uses only the spacing steps; contrast passes.
- [ ] No placeholder colors, lorem ipsum, or empty sections remain.
- [ ] One `h1` per page; every page has its own title and description.
- [ ] Every output uses `e()` or `paragraphs()`.
- [ ] The site works at `http://localhost/project/` and at a domain root without
      changing any file.

---

# Part 2 — Modules

## Module A — SQLite content

Use SQLite for content that is edited in the browser, listed, searched, filtered, or
paginated. JSON stays for fixed page text.

The database is `data/content.sqlite`. `db()` creates it automatically from
`app/schema.sql` on the first request. `data/` must be writable by PHP.

### A.1 Tables

There are four tables. Every project uses the same four tables; do not create other
tables for content.

```text
persons  people: admins, editors, authors, team members
items    content records: news, pages, products, projects, jobs …
labels   definitions: types, statuses, roles, categories, tags
events   things that happened: contact messages, logins, changes
```

- `type` says what a record is (`news`, `project`). `status` says its state
  (`draft`, `published`). Both are plain lowercase text.
- Text in more than one language is a JSON locale object in a TEXT column.
- Fields that only one type needs go into the `data` JSON column, for example
  `{"location": "Helsinki"}`.
- Selected tags are a JSON array of label values in the `labels` column, for example
  `["design", "steel"]`. The tag definitions are rows in `labels` with
  `type = 'tag'`.
- `hash` is the public ID used in URLs and forms. `id` is internal only.
- `owner`, `created_by`, and `updated_by` hold `persons.hash`.

### A.2 app/schema.sql — COPY EXACTLY

```sql
CREATE TABLE IF NOT EXISTS persons (
    id INTEGER PRIMARY KEY,
    hash TEXT NOT NULL UNIQUE,
    type TEXT NOT NULL DEFAULT 'user',
    status TEXT NOT NULL DEFAULT 'active',
    role TEXT NOT NULL DEFAULT 'editor',
    name TEXT NOT NULL DEFAULT '',
    email TEXT UNIQUE,
    password_hash TEXT,
    labels TEXT NOT NULL DEFAULT '[]',
    data TEXT NOT NULL DEFAULT '{}',
    created_by TEXT,
    updated_by TEXT,
    created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')),
    updated_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now'))
);

CREATE TABLE IF NOT EXISTS items (
    id INTEGER PRIMARY KEY,
    hash TEXT NOT NULL UNIQUE,
    type TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'draft',
    slug TEXT NOT NULL,
    title TEXT NOT NULL DEFAULT '{}',
    summary TEXT NOT NULL DEFAULT '{}',
    text TEXT NOT NULL DEFAULT '{}',
    image TEXT NOT NULL DEFAULT '',
    labels TEXT NOT NULL DEFAULT '[]',
    data TEXT NOT NULL DEFAULT '{}',
    owner TEXT,
    created_by TEXT,
    updated_by TEXT,
    published_at TEXT,
    created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')),
    updated_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')),
    UNIQUE (type, slug)
);
CREATE INDEX IF NOT EXISTS items_list ON items (type, status, published_at);
CREATE INDEX IF NOT EXISTS items_created ON items (created_at);

CREATE TABLE IF NOT EXISTS labels (
    id INTEGER PRIMARY KEY,
    hash TEXT NOT NULL UNIQUE,
    key TEXT NOT NULL,
    value TEXT NOT NULL,
    type TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    text TEXT NOT NULL DEFAULT '{}',
    data TEXT NOT NULL DEFAULT '{}',
    UNIQUE (key, value)
);

CREATE TABLE IF NOT EXISTS events (
    id INTEGER PRIMARY KEY,
    hash TEXT NOT NULL UNIQUE,
    type TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'new',
    subject TEXT,
    ip TEXT NOT NULL DEFAULT '',
    data TEXT NOT NULL DEFAULT '{}',
    created_by TEXT,
    created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now'))
);
CREATE INDEX IF NOT EXISTS events_limit ON events (type, ip, created_at);
CREATE INDEX IF NOT EXISTS events_list ON events (type, status, created_at);
```

`events.ip` stores `client_key()`, a SHA-256 hash of the IP address, never the plain
IP. The value `all` marks rows that count toward a site-wide limit.

### A.3 app/db.php — COPY EXACTLY

```php
<?php
declare(strict_types=1);

const JSON_FIELDS = ['title', 'summary', 'text', 'labels', 'data'];

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $file = ROOT . '/data/content.sqlite';
    $new = !is_file($file);
    $pdo = new PDO('sqlite:' . $file, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    if ($new) {
        $pdo->exec((string) file_get_contents(ROOT . '/app/schema.sql'));
        if (is_file(ROOT . '/app/seed.sql')) $pdo->exec((string) file_get_contents(ROOT . '/app/seed.sql'));
    }
    return $pdo;
}

function new_hash(string $prefix): string {
    return $prefix . '_' . bin2hex(random_bytes(6));
}

function now(): string {
    return gmdate('Y-m-d\TH:i:s\Z');
}

function client_key(): string {
    return hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
}

function encode_json($value): string {
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function decode_row(array $row): array {
    foreach (JSON_FIELDS as $field) {
        if (!array_key_exists($field, $row)) continue;
        $value = json_decode((string) $row[$field], true);
        if (in_array($field, ['labels', 'data'], true)) {
            $row[$field] = is_array($value) ? $value : [];
        } else {
            $row[$field] = $value ?: '';
        }
    }
    return $row;
}

function log_event(string $type, array $data = [], string $status = 'new', ?string $subject = null, ?string $by = null, ?string $ip = null): void {
    $stmt = db()->prepare('INSERT INTO events (hash, type, status, subject, ip, data, created_by)
        VALUES (:hash, :type, :status, :subject, :ip, :data, :by)');
    $stmt->execute([':hash' => new_hash('evt'), ':type' => $type, ':status' => $status, ':subject' => $subject,
        ':ip' => $ip ?? client_key(), ':data' => encode_json($data), ':by' => $by]);
}

function too_many(string $type, int $max, int $seconds, ?string $ip = null): bool {
    $stmt = db()->prepare('SELECT COUNT(*) FROM events WHERE type = :type AND ip = :ip AND created_at >= :since');
    $stmt->execute([':type' => $type, ':ip' => $ip ?? client_key(), ':since' => gmdate('Y-m-d\TH:i:s\Z', time() - $seconds)]);
    return (int) $stmt->fetchColumn() >= $max;
}

function load_collection(array $data, ?string $slug): ?array {
    $type = (string) $data['collection'];
    if ($slug !== null) {
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) return null;
        $stmt = db()->prepare("SELECT hash, slug, title, summary, text, image, labels, data, published_at, updated_at
            FROM items WHERE type = :type AND slug = :slug AND status = 'published'");
        $stmt->execute([':type' => $type, ':slug' => $slug]);
        $row = $stmt->fetch();
        if (!$row) return null;
        unset($data['collection'], $data['per_page']);
        return decode_row($row) + ['parent' => $data];
    }

    $perPage = min(50, max(1, (int) ($data['per_page'] ?? 10)));
    $current = min(1000, max(1, (int) ($_GET['page'] ?? 1)));
    $count = db()->prepare("SELECT COUNT(*) FROM items WHERE type = :type AND status = 'published'");
    $count->execute([':type' => $type]);
    $pages = max(1, (int) ceil((int) $count->fetchColumn() / $perPage));

    $stmt = db()->prepare("SELECT hash, slug, title, summary, image, published_at FROM items
        WHERE type = :type AND status = 'published'
        ORDER BY published_at DESC, id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':type', $type);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', ($current - 1) * $perPage, PDO::PARAM_INT);
    $stmt->execute();

    $data['items'] = [];
    foreach ($stmt->fetchAll() as $row) $data['items'][$row['slug']] = decode_row($row);
    $data['pagination'] = ['page' => $current, 'pages' => $pages];
    return $data;
}

function latest_items(string $type, int $limit): array {
    $stmt = db()->prepare("SELECT hash, slug, title, summary, image, published_at FROM items
        WHERE type = :type AND status = 'published' ORDER BY published_at DESC, id DESC LIMIT :limit");
    $stmt->bindValue(':type', $type);
    $stmt->bindValue(':limit', min(12, max(1, $limit)), PDO::PARAM_INT);
    $stmt->execute();
    $items = [];
    foreach ($stmt->fetchAll() as $row) $items[$row['slug']] = decode_row($row);
    return $items;
}

function with_latest(array $data): array {
    foreach ($data as $key => $section) {
        if (is_array($section) && isset($section['collection'], $section['limit'])) {
            $data[$key]['items'] = latest_items((string) $section['collection'], (int) $section['limit']);
        }
    }
    return $data;
}

function collection_slugs(string $type): array {
    $stmt = db()->prepare("SELECT slug, updated_at FROM items WHERE type = :type AND status = 'published'
        ORDER BY published_at DESC LIMIT 5000");
    $stmt->execute([':type' => $type]);
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}
```

### A.4 Connect Module A to the runtime

1. In `index.php`, below `// Modules add their requires and routes here.`, add:

```php
require ROOT . '/app/db.php';
```

2. In `load_page()`, replace `// Module A adds one line here.` with these two lines:

```php
    if (isset($data['collection'])) return load_collection($data, $slug);
    $data = with_latest($data);
```

3. In `render_sitemap()`, replace `// Module A adds one line here.` with:

```php
            if (isset($data['collection'])) $slugs = collection_slugs((string) $data['collection']);
```

4. Mark a page as a database collection in `content.json` — SHAPE:

```json
{
    "news": {
        "collection": "news",
        "per_page": 10,
        "title": { "en": "News", "tr": "Haberler" },
        "description": { "en": "Latest news.", "tr": "Son haberler." },
        "back": { "en": "All news", "tr": "Tüm haberler" },
        "empty": { "en": "No news yet.", "tr": "Henüz haber yok." }
    }
}
```

`collection` is the `items.type` value. The list template `news.php` loops over
`$content['items']` (keyed by slug) and shows `$content['pagination']`. The detail
template `news-item.php` shows one item and uses `$content['parent']` for page
strings. JSON collections and database collections use the same templates.

A section inside any page can show the newest records of a collection. Give the
section `collection` and `limit`; the runtime adds `items` to it — SHAPE:

```json
{
    "home": {
        "latest": {
            "collection": "news",
            "limit": 3,
            "title": { "en": "Latest news", "tr": "Son haberler" }
        }
    }
}
```

The home template then loops over `$content['latest']['items']`.

Initial content: if `app/seed.sql` exists, `db()` runs it once, right after
`schema.sql`, when it creates the database. Use it for example records and labels.
Never put a person with a password in `seed.sql`.

List template — SHAPE:

```php
<section class="section">
    <h1><?= e($content['title']) ?></h1>
<?php if (!$content['items']): ?>
    <p><?= e($content['empty']) ?></p>
<?php endif; ?>
<?php foreach ($content['items'] as $itemSlug => $item): ?>
    <article class="card">
        <h2><a href="<?= e(url(page_path($locale, $page, (string) $itemSlug))) ?>"><?= e($item['title']) ?></a></h2>
        <p class="date"><?= e(format_date($item['published_at'], $common)) ?></p>
        <p><?= e($item['summary']) ?></p>
    </article>
<?php endforeach; ?>
<?php if ($content['pagination']['pages'] > 1): ?>
    <nav class="pagination">
<?php for ($number = 1; $number <= $content['pagination']['pages']; $number++): ?>
        <a href="<?= e(url(page_path($locale, $page))) ?>?page=<?= $number ?>"<?= $number === $content['pagination']['page'] ? ' aria-current="page"' : '' ?>><?= $number ?></a>
<?php endfor; ?>
    </nav>
<?php endif; ?>
</section>
```

### A.5 Query rules

- List queries never select `text`. They select `hash`, `slug`, `title`, `summary`,
  `image`, and dates only.
- Public queries always filter `status = 'published'`.
- Localize database rows in PHP: `decode_row()` turns JSON columns into arrays, and
  `make_view()` localizes them like `content.json`. Do not use SQLite JSON functions;
  some hosts do not have them.
- Optional full-text search: an FTS5 table over title, summary, and text with
  `tokenize = "unicode61 remove_diacritics 2"`. FTS5 is an index, not a replacement
  for `items`.

## Module B — Admin

Add the admin only when the user wants to edit content in the browser. It writes
directly to `data/content.sqlite`. Requires Module A.

### B.1 Routes

The admin is not localized and lives at `/admin`. Its interface text comes from the
`admin` key in `content.json`, shown in the default locale.

```text
GET  /admin                              redirect to the first item type
GET  /admin/setup                        create the first admin (only while none exists)
POST /admin/setup
GET  /admin/login
POST /admin/login
POST /admin/logout
GET  /admin/items/<type>                 list with filters ?status= and ?q=
GET  /admin/items/<type>/new             empty editor
GET  /admin/items/<type>/<hash>          editor for one record
POST /admin/items/<type>/<hash>          save; hash "new" creates a record
POST /admin/items/<type>/<hash>/delete   delete
GET  /admin/events/<type>                read-only list, for example contact messages
GET  /admin/events/<type>/<hash>         one event; opening it sets status "read"
GET  /admin/persons                      admin role only
```

The editable item types and their fields come from `config.json` — SHAPE:

```json
{
    "admin": {
        "types": {
            "news": { "fields": ["title", "summary", "text", "image", "published_at"] }
        },
        "events": ["contact"]
    }
}
```

Connect the admin in `index.php`, below the Module A require:

```php
require ROOT . '/app/auth.php';
if (!$route['has_locale'] && $route['page'] === 'admin') {
    require ROOT . '/app/admin.php';
    run_admin($config, $content, array_slice($route['parts'], 1));
    exit;
}
```

`run_admin()` reads the remaining URL parts, checks the method, and renders
templates from `templates/admin/` with the dashboard preset. Use `slugify()` from
section 7 in `app/admin.php`.

### B.2 app/auth.php — COPY EXACTLY

```php
<?php
declare(strict_types=1);

function start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('genes');
    session_set_cookie_params(['lifetime' => 0, 'path' => BASE . '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function csrf_token(): string {
    start_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool {
    start_session();
    $token = $_POST['csrf'] ?? '';
    return is_string($token) && $token !== '' && hash_equals($_SESSION['csrf'] ?? '', $token);
}

function flash(?array $message = null): ?array {
    start_session();
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $stored = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $stored;
}

function current_user(): ?array {
    start_session();
    if (empty($_SESSION['user'])) return null;
    $stmt = db()->prepare("SELECT hash, name, email, role FROM persons WHERE hash = :hash AND status = 'active'");
    $stmt->execute([':hash' => $_SESSION['user']]);
    return $stmt->fetch() ?: null;
}

function require_user(string $role = 'editor'): array {
    $user = current_user();
    if (!$user) redirect(url('admin/login'));
    if ($role === 'admin' && $user['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }
    return $user;
}

function attempt_login(string $email, string $password): bool {
    if (too_many('login_failed', 5, 900)) return false;
    $stmt = db()->prepare("SELECT hash, password_hash FROM persons WHERE email = :email AND status = 'active'");
    $stmt->execute([':email' => mb_strtolower(trim($email))]);
    $person = $stmt->fetch();
    $hash = $person['password_hash'] ?? password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    if (!$person || !password_verify($password, $hash)) {
        log_event('login_failed');
        return false;
    }
    start_session();
    session_regenerate_id(true);
    $_SESSION['user'] = $person['hash'];
    log_event('login', [], 'done', $person['hash'], $person['hash']);
    return true;
}

function logout(): void {
    start_session();
    $_SESSION = [];
    session_destroy();
}

function admin_exists(): bool {
    return (bool) db()->query("SELECT 1 FROM persons WHERE role = 'admin' LIMIT 1")->fetchColumn();
}

function setup_token(): string {
    $file = ROOT . '/data/setup-token.txt';
    if (!is_file($file)) file_put_contents($file, bin2hex(random_bytes(16)));
    return trim((string) file_get_contents($file));
}
```

### B.3 First admin account

Shared hosting has no command line, so the first admin is created in the browser:

1. While no admin exists, `/admin/login` redirects to `/admin/setup`.
2. `/admin/setup` calls `setup_token()`, which writes a random token to
   `data/setup-token.txt`. The page asks for that token, a name, an email, and a
   password of at least 12 characters.
3. The site owner downloads `data/setup-token.txt` with FTP (or opens it locally)
   and enters the token. Compare it with `hash_equals()`.
4. On success, create the person with `role = 'admin'` and `password_hash()`,
   delete `data/setup-token.txt`, log in, and redirect to the admin.
5. When an admin exists, `/admin/setup` returns HTTP 404.

### B.4 Admin rules

- Every admin page calls `require_user()` first, except setup and login.
- Every POST checks `csrf_valid()` first; if it fails, return HTTP 400.
- After every successful POST, redirect (303) to a GET page and show the result with
  `flash()`.
- Admin pages render `<meta name="robots" content="noindex">` and send
  `Cache-Control: no-store`.
- The admin uses the dashboard preset: left = item types and event types, center =
  list, right = editor. Add `is-open` when a record is open.
- The editor shows one input per field and per locale, for example `title[en]` and
  `title[tr]`, with the locale code in the label. Validate lengths with `mb_strlen`:
  title ≤ 200, summary ≤ 500, text ≤ 50 000 characters.
- Slug: if empty, use `slugify()` of the default-locale title. It must match
  `^[a-z0-9-]+$` and be unique per type; show an error instead of crashing.
- Status is `draft` or `published`. Set `published_at` when a record is first
  published, unless the editor entered a date.
- Save `updated_by` and `updated_at`; on insert also `created_by` and `owner`. Log
  `item_created`, `item_updated`, and `item_deleted` events.
- Editors may change only records where `owner` is their hash. Admins may change all.
- Delete asks for confirmation and uses its own POST form.
- Lists are paginated (50 per page). Filters use bound parameters.
- Image upload: check the `$_FILES` error code, size ≤ 1 MB, real type with
  `getimagesize()` (JPEG, PNG, WebP, GIF only), dimensions ≤ 1920×1920. Save as
  `assets/items/<type>/<random>.<ext>`, put the upload `.htaccess` (section 3) in
  `assets/items/`, and store the relative path in `items.image`.
- Do not build a page builder, drag-and-drop editor, generic table editor, or
  JavaScript framework.

## Module C — Forms and contact

Requires Modules A and B. Sends mail with PHP `mail()`.

### C.1 Flow

1. `GET /en/contact` shows the form with `csrf_field()` and a honeypot field.
2. `POST /en/contact` checks size, CSRF, honeypot, timing, and rate limits, then
   validates, stores the message as an `events` row with `type = 'contact'`, and
   sends the mail.
3. The handler redirects (303) back to the same URL. The result is stored with
   `flash()` and shown once.
4. The message is stored even if `mail()` fails. The admin sees it under
   `/admin/events/contact`.

Configuration — SHAPE:

```json
{
    "contact": {
        "to": "info@example.com",
        "from": "noreply@example.com"
    }
}
```

`from` must be an address on the site's own domain, or many hosts will not deliver
the mail. Tell the user to set up SPF and DKIM for the domain at the host.

### C.2 Connect Module C

1. In `index.php`, below the Module B lines, add:

```php
require ROOT . '/app/forms.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route['valid'] && $route['page'] === 'contact' && $route['slug'] === null) {
    handle_contact($config, $route['locale']);
}
```

2. In `make_view()`, replace `// Module C adds one line here.` with:

```php
    if ($page === 'contact') $view['form'] = form_state();
```

### C.3 app/forms.php — COPY EXACTLY

```php
<?php
declare(strict_types=1);

function clean_text(string $value): string {
    $value = str_replace("\r", '', $value);
    return trim((string) preg_replace('/[^\P{C}\n\t]/u', '', $value));
}

function form_state(): array {
    start_session();
    $_SESSION['form_time'] = time();
    return (flash() ?? []) + ['status' => null, 'errors' => [], 'old' => []];
}

function handle_contact(array $config, string $locale): void {
    $back = url(page_path($locale, 'contact'));
    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 20000 || !csrf_valid()) {
        http_response_code(400);
        exit;
    }
    $tooFast = time() - (int) ($_SESSION['form_time'] ?? 0) < 3;
    if (($_POST['website'] ?? '') !== '' || $tooFast) {
        flash(['status' => 'sent']);
        redirect($back);
    }
    if (too_many('contact', 3, 600) || too_many('contact_total', 100, 86400, 'all')) {
        flash(['status' => 'limited']);
        redirect($back);
    }

    $old = [
        'name' => clean_text((string) ($_POST['name'] ?? '')),
        'email' => clean_text((string) ($_POST['email'] ?? '')),
        'message' => clean_text((string) ($_POST['message'] ?? '')),
    ];
    $errors = [];
    if ($old['name'] === '' || mb_strlen($old['name']) > 100) $errors['name'] = true;
    if (mb_strlen($old['email']) > 254 || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = true;
    if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 5000) $errors['message'] = true;
    if ($errors) {
        flash(['status' => 'invalid', 'errors' => $errors, 'old' => $old]);
        redirect($back);
    }

    log_event('contact', $old + ['locale' => $locale]);
    log_event('contact_total', [], 'done', null, null, 'all');

    $subject = mb_encode_mimeheader('[' . $config['site']['name'] . '] ' . mb_substr($old['name'], 0, 60), 'UTF-8');
    $body = "Name: {$old['name']}\nEmail: {$old['email']}\nLanguage: {$locale}\n\n{$old['message']}\n";
    $headers = [
        'From' => $config['contact']['from'],
        'Reply-To' => $old['email'],
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8',
        'Content-Transfer-Encoding' => '8bit',
    ];
    if (!@mail($config['contact']['to'], $subject, $body, $headers)) {
        error_log('Contact mail could not be sent.');
    }

    flash(['status' => 'sent']);
    redirect($back);
}
```

The second `log_event()` writes a `contact_total` row with `ip = 'all'` that counts
toward the site-wide limit of 100 messages per day. It is a counter, not a message,
so the admin never lists it. Bots that fill the honeypot or submit in
under three seconds see the success message, but nothing is stored or sent.

### C.4 Contact template — SHAPE

```php
<section class="section">
    <h1><?= e($content['title']) ?></h1>
<?php if ($form['status']): ?>
    <p class="notice notice-<?= e($form['status']) ?>" role="status"><?= e($content['form']['messages'][$form['status']]) ?></p>
<?php endif; ?>
    <form class="form" method="post" action="<?= e(url(page_path($locale, 'contact'))) ?>">
        <?= csrf_field() ?>
        <label class="field">
            <span><?= e($content['form']['name']) ?></span>
            <input name="name" maxlength="100" required value="<?= e($form['old']['name'] ?? '') ?>">
<?php if (isset($form['errors']['name'])): ?>
            <small class="error"><?= e($content['form']['errors']['name']) ?></small>
<?php endif; ?>
        </label>
        <label class="field">
            <span><?= e($content['form']['email']) ?></span>
            <input name="email" type="email" maxlength="254" required value="<?= e($form['old']['email'] ?? '') ?>">
<?php if (isset($form['errors']['email'])): ?>
            <small class="error"><?= e($content['form']['errors']['email']) ?></small>
<?php endif; ?>
        </label>
        <label class="field">
            <span><?= e($content['form']['message']) ?></span>
            <textarea name="message" rows="8" minlength="10" maxlength="5000" required><?= e($form['old']['message'] ?? '') ?></textarea>
<?php if (isset($form['errors']['message'])): ?>
            <small class="error"><?= e($content['form']['errors']['message']) ?></small>
<?php endif; ?>
        </label>
        <div class="hp" aria-hidden="true">
            <label>Website <input name="website" tabindex="-1" autocomplete="off"></label>
        </div>
        <button class="button" type="submit"><?= e($content['form']['submit']) ?></button>
    </form>
</section>
```

Hide the honeypot with CSS: `.hp { position: absolute; left: -100rem }`. Do not use
`display: none`; some bots skip hidden fields.

The `contact` page in `content.json` needs these locale objects: `form.name`,
`form.email`, `form.message`, `form.submit`, `form.messages.sent`,
`form.messages.invalid`, `form.messages.limited`, `form.errors.name`,
`form.errors.email`, and `form.errors.message`.

## Module D — Migrations

Add migrations only when the database structure must change after the site is live.

- Put ordered SQL files in `migrations/`, for example `001-add-team.sql`.
- Record applied files in a table
  `schema_migrations (name TEXT PRIMARY KEY, applied_at TEXT NOT NULL)`.
- An admin-only POST action applies new files in order, each inside a transaction.
- Migrations change things step by step. They never drop or rebuild content tables
  and never overwrite records edited in the admin.
