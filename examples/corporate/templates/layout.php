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
    <?php require ROOT . '/templates/partials/cta.php'; ?>
    <footer class="footer">
        <div class="footer-about">
            <a class="brand" href="<?= e(url($locale . '/')) ?>"><?= e($site['name']) ?></a>
            <p><?= e($common['footer']['about']) ?></p>
        </div>
        <div>
            <h2><?= e($common['footer']['offices']) ?></h2>
<?php foreach ($common['offices'] as $office): ?>
            <p><strong><?= e($office['city']) ?></strong><br><?= e($office['address']) ?></p>
<?php endforeach; ?>
        </div>
        <div>
            <h2><?= e($common['footer']['contact']) ?></h2>
            <p><a href="mailto:<?= e($common['email']) ?>"><?= e($common['email']) ?></a><br><?= e($common['phone']) ?></p>
        </div>
        <p class="footer-rights"><?= e($common['footer']['rights']) ?></p>
    </footer>
</div>
</body>
</html>
