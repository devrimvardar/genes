<?php
declare(strict_types=1);

$local = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
ini_set('display_errors', $local ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/data/error.log');

// PHP built-in server: serve existing files directly.
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

define('BASE', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string {
    return BASE . '/' . ltrim($path, '/');
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
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    if (BASE !== '' && ($path === BASE || strpos($path, BASE . '/') === 0)) {
        $path = substr($path, strlen(BASE));
    }
    $parts = array_values(array_filter(explode('/', trim($path, '/')), 'strlen'));
    $hasLocale = in_array($parts[0] ?? '', $locales, true);
    $locale = $hasLocale ? array_shift($parts) : $default;
    return [$locale, $parts[0] ?? 'home', $parts[1] ?? null, count($parts) > 2, $hasLocale];
}

function page_path(string $locale, string $page, ?string $slug = null): string {
    if ($page === 'home' || $page === '404') return $locale . '/';
    return $locale . '/' . $page . ($slug !== null ? '/' . $slug : '');
}

function make_view(array $config, array $content, string $locale, string $page, ?string $slug): array {
    $locales = $config['site']['locales'];
    $default = $config['site']['default_locale'];
    $valid = preg_match('/^[a-z0-9-]+$/', $page) && $page !== 'common' && $page !== '404' && isset($content[$page]);
    $pageData = $valid ? $content[$page] : null;
    if ($pageData && $slug !== null) {
        $pageData = preg_match('/^[a-z0-9-]+$/', $slug) ? ($pageData['items'][$slug] ?? null) : null;
    }
    if (!$pageData) {
        http_response_code(404);
        $page = '404';
        $slug = null;
        $pageData = $content['404'];
    }
    $view = [
        'site' => $config['site'], 'theme' => $config['theme'],
        'analytics' => $config['analytics'] ?? [],
        'locale' => $locale, 'locales' => $locales, 'page' => $page, 'slug' => $slug,
        'common' => localize($content['common'], $locale, $default, $locales),
        'content' => localize($pageData, $locale, $default, $locales),
    ];
    $view['meta'] = make_meta($view);
    return $view;
}

function make_meta(array $view): array {
    $site = rtrim($view['site']['url'], '/') . '/';
    $page = $view['page'];
    $content = $view['content'];
    $title = $content['title'] ?? $view['theme']['title'];
    $description = $content['description'] ?? $content['text'] ?? $view['theme']['description'];
    $alternates = [];
    foreach ($view['locales'] as $locale) {
        $alternates[$locale] = $site . page_path($locale, $page, $view['slug']);
    }
    $canonical = $page === '404' ? null : $alternates[$view['locale']];

    $schema = ['@context' => 'https://schema.org'];
    if ($page === 'home') {
        $schema += [
            '@type' => 'SoftwareSourceCode', 'name' => $view['site']['name'], 'description' => $description,
            'url' => $canonical, 'codeRepository' => 'https://github.com/devrimvardar/genes',
            'programmingLanguage' => 'PHP', 'license' => 'https://opensource.org/licenses/MIT',
            'author' => ['@type' => 'Person', 'name' => 'Devrim Vardar', 'url' => 'https://devrimvardar.com/'],
        ];
    } elseif ($view['slug'] !== null) {
        $schema += [
            '@type' => 'Article', 'headline' => $title, 'description' => $description,
            'url' => $canonical, 'inLanguage' => $view['locale'],
            'author' => ['@type' => 'Person', 'name' => 'Devrim Vardar', 'url' => 'https://devrimvardar.com/'],
        ];
    } else {
        $schema += ['@type' => 'WebPage', 'name' => $title, 'description' => $description, 'url' => $canonical, 'inLanguage' => $view['locale']];
    }

    return [
        'title' => $page === 'home' ? $view['theme']['title'] : $title . ' | ' . $view['site']['name'],
        'description' => $description,
        'canonical' => $canonical,
        'alternates' => $canonical ? $alternates : [],
        'default_url' => $alternates[$view['site']['default_locale']],
        'robots' => $page === '404' ? 'noindex' : null,
        'schema' => $page === '404' ? null : $schema,
    ];
}

function render(array $view): void {
    extract($view, EXTR_SKIP);
    require __DIR__ . '/templates/layout.php';
}

function render_sitemap(array $config, array $content): void {
    $site = rtrim($config['site']['url'], '/') . '/';
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($config['site']['locales'] as $locale) {
        foreach ($content as $page => $data) {
            $page = (string) $page; // PHP turns the "404" key into an integer
            if ($page === 'common' || $page === '404') continue;
            echo '  <url><loc>' . e($site . page_path($locale, $page)) . '</loc></url>' . "\n";
            foreach (array_keys($data['items'] ?? []) as $slug) {
                echo '  <url><loc>' . e($site . page_path($locale, $page, $slug)) . '</loc></url>' . "\n";
            }
        }
    }
    echo '</urlset>' . "\n";
}

$config = json_decode(file_get_contents(__DIR__ . '/data/config.json'), true, 512, JSON_THROW_ON_ERROR);
$content = json_decode(file_get_contents(__DIR__ . '/data/content.json'), true, 512, JSON_THROW_ON_ERROR);

$csp = "default-src 'self'; img-src 'self' data: https://*.google-analytics.com https://*.googletagmanager.com; "
     . "script-src 'self' https://www.googletagmanager.com; "
     . "connect-src 'self' https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com; "
     . "frame-ancestors 'self'";
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');
header('Content-Security-Policy: ' . $csp);

[$locale, $page, $slug, $extra, $hasLocale] = route($_SERVER['REQUEST_URI'] ?? '/', $config['site']['locales'], $config['site']['default_locale']);

if (!$hasLocale && $page === 'sitemap.xml' && $slug === null) {
    render_sitemap($config, $content);
    exit;
}

render(make_view($config, $content, $locale, $extra ? '404' : $page, $slug));
