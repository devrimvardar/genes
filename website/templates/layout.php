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
<?php if (!empty($site['analytics'])): ?>
    <script src="<?= e(url('assets/js/consent.js')) ?>?v=<?= filemtime(ROOT . '/assets/js/consent.js') ?>" data-ga4="<?= e($site['analytics']) ?>" defer></script>
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
            <a href="https://github.com/devrimvardar/genes"><?= e($common['github']) ?></a>
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
        <ul class="footer-links">
<?php foreach ($common['footer_links'] as $link): ?>
            <li><a href="<?= e($link['url']) ?>"><?= e($link['label']) ?></a></li>
<?php endforeach; ?>
        </ul>
    </footer>
</div>
<?php if (!empty($site['analytics'])): ?>
<div class="consent" id="consent" hidden>
    <p><?= e($common['consent']['text']) ?></p>
    <div class="consent-actions">
        <button class="button" type="button" data-consent="yes"><?= e($common['consent']['accept']) ?></button>
        <button class="button button-ghost" type="button" data-consent="no"><?= e($common['consent']['decline']) ?></button>
    </div>
</div>
<?php endif; ?>
</body>
</html>
