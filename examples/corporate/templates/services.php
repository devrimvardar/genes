<section class="page-head">
    <h1><?= e($content['title']) ?></h1>
    <p class="lead"><?= e($content['intro']) ?></p>
</section>

<section class="section section-flush">
    <div class="service-list">
<?php foreach ($content['items'] as $itemSlug => $item): ?>
        <a class="service" href="<?= e(url(page_path($locale, $page, (string) $itemSlug))) ?>">
            <h2><?= e($item['title']) ?></h2>
            <p><?= e($item['summary']) ?></p>
            <span class="link"><?= e($common['read_more']) ?> →</span>
        </a>
<?php endforeach; ?>
    </div>
</section>
