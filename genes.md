# Genes

Generate a dependency-free PHP project with one public `index.php` entry point from
this specification.

## Rules

- Always include the BASE CSS below exactly in the generated `index.php`.
- Select one layout preset according to the requested page.
- Include only the selected layout preset CSS.
- The layout preset defines structure, not visual identity.
- Always add a compact visual design for the requested site: typography, colors,
  spacing, navigation, buttons, forms, cards, and content sections.
- Keep fixed `rem` canvas and fixed-width columns.
- Do not use `minmax()`, fluid columns, frameworks, CDNs, or build tools.
- The layout presets are examples, not mandatory page types.
- Choose the number, meaning, and width of columns according to the page.
- CSS may be inline in `<head>` or stored in an external project stylesheet.
- Keep PHP, HTML, CSS, JSON, SQL, Markdown, and `.htaccess` files UTF-8 without BOM.

## BASE CSS — copy exactly

```css
* { box-sizing: border-box; padding: 0; margin: 0; line-height: 1em }
html { -webkit-text-size-adjust: 100% }
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

Canvas sizes:

```text
Mobile: 32rem
Tablet: 64rem
Desktop: 128rem
```

## Layout presets

### Dashboard

Desktop columns: `20rem 36rem 72rem`.
Tablet columns: `20rem 44rem`.
Mobile: one `32rem` column.

```html
<div class="g-dashboard">
    <nav class="g-nav"></nav>
    <main class="g-main"></main>
    <aside class="g-side"></aside>
</div>
```

```css
.g-dashboard { display: grid; grid-template-columns: 20rem 36rem 72rem; width: 128rem; height: 100vh }
.g-nav { background: #ddd; padding: 1rem }
.g-main { background: #eee; padding: 1rem }
.g-side { background: #ccc; padding: 1rem }
@media (min-width: 640px) and (max-width: 1279px) {
    .g-dashboard { grid-template-columns: 20rem 44rem; width: 64rem }
    .g-side { display: none }
}
@media (max-width: 639px) {
    .g-dashboard { grid-template-columns: 32rem; width: 32rem }
    .g-nav, .g-side { display: none }
}
```

### Blog

Desktop columns: `80rem 48rem`.
Tablet: one `64rem` column.
Mobile: one `32rem` column.

```html
<div class="g-blog">
    <main class="g-article"></main>
    <aside class="g-related"></aside>
</div>
```

```css
.g-blog { display: grid; grid-template-columns: 80rem 48rem; width: 128rem; min-height: 100vh }
.g-article { background: #eee; padding: 1rem }
.g-related { background: #ccc; padding: 1rem }
@media (min-width: 640px) and (max-width: 1279px) {
    .g-blog { grid-template-columns: 64rem; width: 64rem }
    .g-related { display: none }
}
@media (max-width: 639px) {
    .g-blog { grid-template-columns: 32rem; width: 32rem }
    .g-related { display: none }
}
```

### Landing

Desktop: one `128rem` column.
Tablet: one `64rem` column.
Mobile: one `32rem` column.

```html
<main class="g-landing"></main>
```

```css
.g-landing { width: 128rem; min-height: 100vh; background: #eee; padding: 2rem }
@media (min-width: 640px) and (max-width: 1279px) {
    .g-landing { width: 64rem }
}
@media (max-width: 639px) {
    .g-landing { width: 32rem }
}
```

## Generation

For `dashboard`, `blog`, or `landing`, copy the BASE CSS, add only the selected
preset CSS, and generate the matching HTML structure. Add the user's content inside
the regions. Do not add unused layouts or speculative components.

## Generated project structure

The default static site should be multi-page, even when all content is static.
If the user explicitly requests a single-page site, use `home` as the only page.

Minimum static site:

```text
index.php
templates/
    layout.php
    home.php
    404.php
data/config.json
data/content.json
.htaccess
```

The default multi-page static site must contain at least three pages: `/`, `/about`,
and `/contact`. Use one `index.php` entry point and route requests from `REQUEST_URI`.
Do not use query-string page routing such as `?page=about` unless explicitly requested.

### Example project recipe: multilingual static landing

For a three-language, three-page landing site, generate:

```text
index.php
templates/
    layout.php
    home.php
    about.php
    contact.php
    404.php
data/config.json
data/content.json
.htaccess
```

Use locale-aware paths:

```text
/en/          /en/about          /en/contact
/tr/          /tr/about          /tr/contact
/de/          /de/about          /de/contact
```

Store all page translations in `data/content.json`. Keep templates shared between
languages and use the landing preset for all three pages unless requested otherwise.

## URL rewriting

Generate this root `.htaccess` for Apache unless the user requests another server:

```apache
RewriteEngine On

RewriteRule ^data/ - [F,L]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

This routes extensionless paths to `index.php`, preserves existing files and
directories, and denies direct web access to `/data/`.

## Request routing

Parse the request path from `$_SERVER['REQUEST_URI']`:

```text
/              -> locale: default, page: home
/about         -> locale: default, page: about
/en/           -> locale: en, page: home
/en/about      -> locale: en, page: about
```

Remove the query string and leading/trailing slashes before parsing. If the first
segment matches a configured locale, use it as the locale; otherwise use the default
locale and treat the first segment as the page. Return a 404 response for unknown
pages or unsupported locales. Do not use query-string routing for pages.

## Configuration

Always generate `data/config.json` for site-level configuration. Keep configuration
separate from templates and content. `config.json` must never contain page content or
translations.

```json
{
    "site": {
        "name": "Site name",
        "url": "https://example.com",
        "default_locale": "en",
        "locales": ["en", "tr"]
    },
    "theme": {
        "title": "Site title",
        "description": "Site description"
    }
}
```

Use `config.json` only for system and site settings such as URLs, locale settings,
feature flags, environment-independent options, and theme metadata. Do not put page
content, posts, passwords, secrets, or translations in `config.json`.

Use `content.json`, `content.sqlite`, or both as required; `config.json` does not
select one provider.

## Localization

Store static and page content in `data/content.json`. Store queryable or editable
records in `data/content.sqlite` when needed; both may be used by the same page. The
JSON content root always uses page keys. A single-page site uses
`home` as its only page key. A multi-page site uses keys such as `home`, `about`, and
`contact`. Do not duplicate templates per language.

```json
{
    "home": {
        "title": {
            "en": "English Title",
            "tr": "Türkçe title"
        },
        "text": {
            "en": "English text",
            "tr": "Türkçe metin"
        }
    }
}
```

The `home` object may contain any sections required by the page; it is not limited to
`hero`. Use keys such as `header`, `intro`, `features`, `pricing`, `faq`, `article`,
`related`, and `footer` only when needed.

Every user-visible string must come from the content layer, including navigation
labels, buttons, links, headings, labels, placeholders, and messages. Use a `common`
object for shared strings such as site navigation; use page objects for page-specific
strings. Never hardcode user-visible text in the template.

The content tree may be shallow or deeply nested. Pages may use sections, subsections,
or direct values. Do not require every page to use the same structure. The only
required rule is that a translatable leaf is a locale object.

For multiple pages, add sibling page keys:

```json
{
    "home": {
        "header": {
            "title": {
                "en": "English title",
                "tr": "Turkish title",
                "de": "German title"
            }
        }
    },
    "about": {
        "title": {
            "en": "About us",
            "tr": "Hakkımızda",
            "de": "Über uns"
        }
    }
}
```

Select locale from the URL path, for example `/en/about` and `/tr/about`, unless the
user requests another method. Use `site.default_locale` as fallback. Resolve a missing
translation to the requested locale, then the default locale, then an empty string.
Escape translated plain text with `htmlspecialchars()`.

Resolve the page key before rendering its matching template; unknown page keys use the
404 template.

Language links must preserve the current page, for example `/en/about` links to
`/tr/about` and `/de/about`. Only locales listed in `config.json` are valid.

Plain text must always be escaped before output. Do not output content as raw HTML.
Rich HTML content is allowed only when explicitly requested and clearly marked as
trusted content in the data model.

## Data sources

- System settings: use `data/config.json`.
- Static, custom, shared, and localized page content: use `data/content.json`.
- Queryable, editable, filterable, paginated, or relational records: use `data/content.sqlite`.
- A page may use JSON, SQLite, or both.
- CRUD/CMS: add only when the user requests editable content or an admin interface.

Normalize all required data into one `$view` package. A page may use either source or
both; templates must not depend on where a field came from.

Allowed root-level content keys are page keys plus the reserved `common` object. Never
expose database files, configuration files, or raw JSON directly. Do not add SQLite,
CRUD, authentication, or an admin panel to a simple static site.

## Canonical runtime logic

Do not invent a new routing, localization, or rendering architecture. Use this
pipeline in every generated project:

```text
request → route → locale → page → data sources → view package → template
```

The runtime must:

1. Load `data/config.json`.
2. Load the required data sources: `content.json`, `content.sqlite`, or both.
3. Parse `REQUEST_URI` after removing the project base path and query string.
4. Resolve locale from the first URL segment or use `site.default_locale`.
5. Resolve page from the next URL segment or use `home`.
6. Reject unsupported locales and unknown pages with HTTP 404.
7. Resolve localized values using the requested locale, then the default locale.
8. Build one `$view` package containing `site`, `theme`, `locale`, `page`, `common`,
   and localized page `content`.
9. Escape plain text with `htmlspecialchars()` before output.
10. Render the shared template with the `$view` package.

Templates must not parse routes, load JSON/SQLite, resolve locales, or call a text
lookup function for every field. They only render `$site`, `$theme`, `$locale`,
`$common`, and `$content`. Use native PHP templates; do not invent a template DSL.

Use this compact runtime shape. Normalize JSON and SQLite results into the same `$view`
package so templates do not depend on where a field came from:

```php
function localize($value, $locale, $default) {
    if (!is_array($value)) return $value;
    if (array_key_exists($locale, $value)) return $value[$locale];
    if (array_key_exists($default, $value)) return $value[$default];
    foreach ($value as $key => $item) $value[$key] = localize($item, $locale, $default);
    return $value;
}

function make_view($config, $content, $locale, $page, $records = []) {
    $pageData = $content[$page] ?? null;
    if (!$pageData) { http_response_code(404); $page = '404'; $pageData = $content[$page] ?? []; }
    $default = $config['site']['default_locale'] ?? 'en';
    return [
        'site' => $config['site'] ?? [], 'theme' => $config['theme'] ?? [],
        'locale' => $locale, 'page' => $page,
        'common' => localize($content['common'] ?? [], $locale, $default),
        'content' => localize($pageData, $locale, $default), 'data' => $records
    ];
}

function render($view) {
    extract($view, EXTR_SKIP);
    require __DIR__ . "/templates/{$page}.php";
}
```

Keep routing, data loading, view preparation, and rendering as separate small steps.
Data may come from JSON, SQLite, or both, but the data source must not create a new
routing, localization, or template architecture.

## Assets

Assets are shared by all page types:

```text
assets/
├── media/
├── icons/
└── fonts/
```

- Store images, icons, and fonts under `assets/`.
- Store only relative asset paths in content data, never binary blobs or base64.
- Use stable, lowercase, URL-safe filenames.
- Store uploaded media under a content-type directory, for example
  `assets/items/<item-slug>/` or `assets/persons/<person-key>/`.
- A CMS may provide a small media manager for listing, uploading, selecting, and
  removing assets.
- Page images must use only `jpg`, `jpeg`, `png`, `webp`, or `gif`.
- Page images must be no larger than `1920x1920` pixels.
- Page images must not exceed `1 MB` per file.
- Validate the real MIME type and dimensions server-side; never trust the filename
  extension or client-provided MIME type.
- Reject images that fail validation before writing them to `assets/`.
- Do not overwrite an existing asset without an explicit request.
- Keep `data/` private and `assets/` public.
- Do not allow PHP execution inside upload/media directories.
- Generated `.htaccess` rules must protect `data/` without blocking public assets.
- Asset paths must work from every localized page URL.

## SQLite mode

Use SQLite for queryable, editable, filterable, paginated, or relational data. Use
JSON for static, custom, shared, or page-specific data. A page may use either or both;
data sources have no semantic role such as blog or landing.

Store the database at:

```text
data/content.sqlite
```

The main content tables are:

```text
persons
items
labels
events
```

Every main table has an internal integer `id` and a public stable `hash`. Fast filter
fields such as `type` and `status` are regular TEXT columns containing URL-safe values,
not hashes. They may be used on `persons`, `items`, and `events` when relevant. Their
definitions are stored in `labels` using `key` and `value`.

Content ownership fields use person hashes:

```text
created_by = persons.hash
updated_by = persons.hash
owner      = persons.hash
```

Use `created_by` and `updated_by` for audit history. Use `owner` when users must only
access or edit their own content. Admin users may access all records according to their
role. Editors may edit only records they own or are assigned. Apply ownership and role
checks in the admin layer.

Example only; use values required by the requested project:

```text
items.hash   = "itm_abc123"
items.type   = "post"
items.status = "published"
```

The `labels` table contains definitions and localized display text:

```text
labels.hash
labels.key
labels.value
labels.type
labels.status
labels.text
labels.data
```

Use a unique constraint on `(key, value)`. Every label also has a `type` and `status`
field. Examples of label types are `tag`, `category`, `role`, `type`, and `status`.
Label status may be `active` or `archived`.

Free tags are stored in the `labels` table as `key/value` definitions, and selected
values are stored in the entity `labels` JSON array. A tag cloud queries labels with
`type = 'tag'` and `status = 'active'`. Keep `type` and `status` on entities as indexed
columns. Use `data` JSON for type-specific fields.

Localized content fields use locale objects:

```json
{
    "en": "English title",
    "fi": "Finnish title",
    "tr": "Turkish title"
}
```

Use this format for fields such as `items.title`, `items.summary`, `items.text`, and
localized label display text. Use SQLite JSON functions for locale extraction and
fallback. Collection list queries must select only `hash`, `slug`, localized `title`, and
localized `summary`; do not load the full body for a list.

For `items`, index `type`, `status`, and `created_at`. Add equivalent indexes to
`persons` or `events` when those fields are used for filtering. Use SQLite FTS5 as an optional
search index for title, summary, and body when full-text search is needed. FTS5 is an
index over content, not a replacement for `items`.

## CMS admin mode

Add the admin only when the user requests manual editing. The admin writes directly to
the live SQLite database; it does not require a public write API.

Use the dashboard preset as the admin workspace:

```text
20rem left column   = table and navigation selector
36rem center column = filtered record list
72rem right column  = selected record editor
```

Example structure:

```html
<div class="g-dashboard g-admin">
    <nav class="g-nav"></nav>
    <main class="g-main"></main>
    <aside class="g-side"></aside>
</div>
```

The first admin version supports only:

```text
login
list
search
filter
create
edit
publish/unpublish
delete
locale selection
```

Admin scope:

```text
items   = primary content editor
persons = user and ownership management
labels  = type, status, category, role, and tag definitions
events  = read-only audit and submission viewer
```

Require sessions, password hashing, CSRF protection, prepared statements, input
validation, and login rate limiting. Do not add a visual page builder, drag-and-drop
editor, generic table editor, or large JavaScript admin framework.

## SQLite migrations

Migration support is a later optional layer. Do not add it to a simple SQLite site
unless requested. When enabled, add this directory:

```text
migrations/
```

Keep migration files in a non-public `migrations/` directory. If that
directory must be inside the web root, deny it in `.htaccess`. When enabled, ordered SQL
migration files are applied by a site-side consumer and recorded in `schema_migrations`.
Migrations may insert, update, or delete records, but must be incremental and must not
rebuild or replace content tables or overwrite manual admin changes.

