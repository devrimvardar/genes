<!doctype html>
<html lang="<?= e($default) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e($text['title']) ?> | <?= e($site['name']) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/site.css')) ?>?v=<?= filemtime(ROOT . '/assets/css/site.css') ?>">
</head>
<body class="admin">
<div class="g-dashboard<?= $open ? ' is-open' : '' ?>">
    <nav class="g-nav admin-nav">
        <p class="admin-brand"><?= e($site['name']) ?> <span><?= e($text['title']) ?></span></p>
        <p class="admin-nav-title"><?= e($text['items']) ?></p>
<?php foreach (array_keys($config['admin']['types']) as $navType): ?>
        <a href="<?= e(url('admin/items/' . $navType)) ?>"<?= ($type ?? '') === $navType && $main === 'items-list' ? ' aria-current="page"' : '' ?>><?= e($text['types'][$navType] ?? $navType) ?></a>
<?php endforeach; ?>
        <p class="admin-nav-title"><?= e($text['events']) ?></p>
<?php foreach ($config['admin']['events'] as $navType): ?>
        <a href="<?= e(url('admin/events/' . $navType)) ?>"<?= ($type ?? '') === $navType && $main === 'events-list' ? ' aria-current="page"' : '' ?>><?= e($text['types'][$navType] ?? $navType) ?></a>
<?php endforeach; ?>
<?php if ($user['role'] === 'admin'): ?>
        <p class="admin-nav-title"><?= e($text['persons']) ?></p>
        <a href="<?= e(url('admin/persons')) ?>"<?= $main === 'persons-list' ? ' aria-current="page"' : '' ?>><?= e($text['persons']) ?></a>
<?php endif; ?>
        <div class="admin-user">
            <p><?= e($user['name']) ?></p>
            <a href="<?= e(url($default . '/')) ?>"><?= e($text['view_site']) ?></a>
            <form method="post" action="<?= e(url('admin/logout')) ?>">
                <?= csrf_field() ?>
                <button class="link-button" type="submit"><?= e($text['logout']) ?></button>
            </form>
        </div>
    </nav>
    <main class="g-main">
<?php if ($flash): ?>
        <p class="notice notice-sent" role="status"><?= e($text['flash'][$flash['type']] ?? '') ?></p>
<?php endif; ?>
<?php require ROOT . '/templates/admin/' . $main . '.php'; ?>
    </main>
    <aside class="g-side">
<?php if ($side): ?>
<?php require ROOT . '/templates/admin/' . $side . '.php'; ?>
<?php else: ?>
        <p class="muted"><?= e($text['select']) ?></p>
<?php endif; ?>
    </aside>
</div>
</body>
</html>
