<section class="page-head">
    <h1><?= e($content['title']) ?></h1>
    <p class="lead"><?= e($content['intro']) ?></p>
</section>

<section class="section section-flush">
<?php if (!$content['items']): ?>
    <p><?= e($content['empty']) ?></p>
<?php endif; ?>
    <div class="cards">
<?php foreach ($content['items'] as $itemSlug => $item): ?>
<?php require ROOT . '/templates/partials/news-card.php'; ?>
<?php endforeach; ?>
    </div>
<?php if ($content['pagination']['pages'] > 1): ?>
    <nav class="pagination" aria-label="<?= e($content['pages']) ?>">
<?php for ($number = 1; $number <= $content['pagination']['pages']; $number++): ?>
        <a href="<?= e(url(page_path($locale, $page))) ?>?page=<?= $number ?>"<?= $number === $content['pagination']['page'] ? ' aria-current="page"' : '' ?>><?= $number ?></a>
<?php endfor; ?>
    </nav>
<?php endif; ?>
</section>
