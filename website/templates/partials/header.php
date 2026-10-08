<header class="header">
    <a class="brand" href="<?= e(url($locale . '/')) ?>"><?= e($common['brand']) ?></a>
    <nav class="nav" aria-label="<?= e($common['nav_label']) ?>">
<?php foreach (['home', 'docs', 'examples', 'download', 'articles'] as $item): ?>
        <a href="<?= e(url(page_path($locale, $item))) ?>"<?= $page === $item ? ' aria-current="page"' : '' ?>><?= e($common['nav'][$item]) ?></a>
<?php endforeach; ?>
        <a href="https://github.com/devrimvardar/genes"><?= e($common['nav']['github']) ?></a>
    </nav>
    <nav class="languages" aria-label="<?= e($common['language_label']) ?>">
<?php foreach ($locales as $language): ?>
        <a href="<?= e(url(page_path($language, $page, $slug))) ?>" hreflang="<?= e($language) ?>"<?= $language === $locale ? ' aria-current="true"' : '' ?>><?= e(strtoupper($language)) ?></a>
<?php endforeach; ?>
    </nav>
</header>
