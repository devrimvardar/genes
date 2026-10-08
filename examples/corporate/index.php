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
    if (isset($data['collection'])) return load_collection($data, $slug);
    $data = with_latest($data);
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
    if ($page === 'contact') $view['form'] = form_state();
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
            if (isset($data['collection'])) $slugs = collection_slugs((string) $data['collection']);
            foreach ($slugs as $slug => $lastmod) {
                echo '  <url><loc>' . e(abs_url($config['site']['url'], page_path($locale, $page, (string) $slug))) . '</loc>'
                    . ($lastmod ? '<lastmod>' . e(substr($lastmod, 0, 10)) . '</lastmod>' : '') . '</url>' . "\n";
            }
        }
    }
    echo '</urlset>' . "\n";
}

function send_headers(array $config): void {
    $policy = [
        'default-src' => ["'self'"], 'img-src' => ["'self'", 'data:'], 'form-action' => ["'self'"],
        'base-uri' => ["'self'"], 'frame-ancestors' => ["'self'"],
    ];
    foreach ($config['csp'] ?? [] as $directive => $sources) {
        $policy[$directive] = array_merge($policy[$directive] ?? ["'self'"], $sources);
    }
    $csp = [];
    foreach ($policy as $directive => $sources) $csp[] = $directive . ' ' . implode(' ', array_unique($sources));
    header('Content-Type: text/html; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Content-Security-Policy: ' . implode('; ', $csp));
}

$config = json_decode(file_get_contents(ROOT . '/data/config.json'), true, 512, JSON_THROW_ON_ERROR);
$content = json_decode(file_get_contents(ROOT . '/data/content.json'), true, 512, JSON_THROW_ON_ERROR);

send_headers($config);
$route = route($_SERVER['REQUEST_URI'] ?? '/', $config['site']['locales'], $config['site']['default_locale']);

// Modules add their requires and routes here.
require ROOT . '/app/db.php';
require ROOT . '/app/auth.php';
if (!$route['has_locale'] && $route['page'] === 'admin') {
    require ROOT . '/app/admin.php';
    run_admin($config, $content, array_slice($route['parts'], 1));
    exit;
}
require ROOT . '/app/forms.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route['valid'] && $route['page'] === 'contact' && $route['slug'] === null) {
    handle_contact($config, $route['locale']);
}

if (!$route['has_locale'] && $route['page'] === 'sitemap.xml' && $route['slug'] === null) {
    render_sitemap($config, $content);
    exit;
}

$pageData = $route['valid'] ? load_page($content, $route['page'], $route['slug']) : null;
render(make_view($config, $content, $route, $pageData));
