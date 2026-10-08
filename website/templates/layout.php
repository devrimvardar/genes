<!doctype html>
<html lang="<?= e($locale) ?>">
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
<?php foreach ($meta['alternates'] as $alternate => $href): ?>
    <link rel="alternate" hreflang="<?= e($alternate) ?>" href="<?= e($href) ?>">
<?php endforeach; ?>
    <link rel="alternate" hreflang="x-default" href="<?= e($meta['default_url']) ?>">
    <meta property="og:type" content="<?= $slug !== null ? 'article' : 'website' ?>">
    <meta property="og:site_name" content="<?= e($site['name']) ?>">
    <meta property="og:title" content="<?= e($content['title']) ?>">
    <meta property="og:description" content="<?= e($meta['description']) ?>">
    <meta property="og:url" content="<?= e($meta['canonical']) ?>">
    <meta name="twitter:card" content="summary">
<?php endif; ?>
    <link rel="stylesheet" href="<?= e(url('assets/css/site.css')) ?>?v=<?= filemtime(__DIR__ . '/../assets/css/site.css') ?>">
<?php if ($meta['schema']): ?>
    <script type="application/ld+json"><?= json_encode($meta['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endif; ?>
<?php if (!empty($analytics['ga4'])): ?>
    <script src="<?= e(url('assets/js/consent.js')) ?>?v=<?= filemtime(__DIR__ . '/../assets/js/consent.js') ?>" data-ga4="<?= e($analytics['ga4']) ?>" defer></script>
<?php endif; ?>
</head>
<body>
<div class="g-landing">
    <?php require __DIR__ . '/partials/header.php'; ?>
    <main>
        <?php require __DIR__ . '/' . ($slug !== null ? $page . '-item' : $page) . '.php'; ?>
    </main>
    <?php require __DIR__ . '/partials/footer.php'; ?>
</div>
<?php if (!empty($analytics['ga4'])): ?>
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
