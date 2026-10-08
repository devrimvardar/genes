# Arkka — Genes corporate example

A complete multilingual corporate website generated from [genes.md](https://genes.one/).
Arkka is a fictional engineering studio; replace the content with your own.

- Three languages: English, Turkish, Finnish (`/en/`, `/tr/`, `/fi/`)
- Pages: home, about, services (JSON collection), news (SQLite), contact
- Admin at `/admin`: news editor with image upload, contact messages, users
- Contact form with PHP `mail()`, CSRF, honeypot, and rate limits
- SEO: canonical, hreflang, Open Graph, JSON-LD, generated `sitemap.xml`, `llms.txt`
- No dependencies, no build step. PHP 7.4+ with `pdo_sqlite`, Apache with `mod_rewrite`

## Run locally

```text
php -S localhost:8000 index.php
```

Or copy the folder into your Laragon, XAMPP, or MAMP web root and open
`http://localhost/<folder>/`.

## Install on shared hosting

1. Upload all files with FTP to the web root or to a subfolder.
2. Make sure PHP can write to `data/` and `assets/items/` (usually `755`).
3. Open `/en/` once. The database `data/content.sqlite` is created with four example
   news items.
4. Open `/admin`. You are sent to `/admin/setup`.
5. Download `data/setup-token.txt` with FTP, paste the token, and create the first
   admin. The token file is deleted afterwards.

## Make it yours

| What                         | Where                          |
|------------------------------|--------------------------------|
| Site name, URL, languages    | `data/config.json` → `site`    |
| Contact form recipient       | `data/config.json` → `contact` |
| All page texts               | `data/content.json`            |
| News                         | `/admin`                       |
| Colors, fonts, spacing       | `assets/css/site.css` → `:root` |
| Search engine and AI summary | `robots.txt`, `llms.txt`       |

`contact.from` must be an address on your own domain. Set up SPF and DKIM for the
domain at your host so the mails are delivered.

To add a language, add its code to `site.locales` and add the translations to
`content.json`. Missing translations fall back to the default language.

## Important

- After the first upload, never overwrite `data/content.sqlite`; it holds the live
  news, users, and messages. Download it regularly as a backup.
- `data/`, `app/`, and `templates/` are blocked from the web by `.htaccess`.

## Files

```text
index.php               runtime (genes.md section 4 + modules A, B, C)
.htaccess               routing and protection
app/schema.sql          database tables
app/seed.sql            example news
app/db.php              database helpers          (Module A)
app/auth.php            sessions, CSRF, login     (Module B)
app/admin.php           admin pages               (Module B)
app/forms.php           contact form              (Module C)
app/slugify.php         URL slugs
templates/              page templates
templates/admin/        admin templates
assets/css/site.css     all styles
data/config.json        settings
data/content.json       texts in all languages
```

MIT licensed. Made with [Genes](https://genes.one/) by [Devrim Vardar](https://devrimvardar.com/).
