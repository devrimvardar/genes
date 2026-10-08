<section class="hero">
    <p class="eyebrow"><?= e($content['eyebrow']) ?></p>
    <h1><?= e($content['title']) ?></h1>
    <p class="lead"><?= e($content['text']) ?></p>
</section>
<section class="section">
    <h2><?= e($content['section_title']) ?></h2>
    <div class="section-body">
        <p><?= e($content['section_text']) ?></p>
        <ul class="article-list">
<?php foreach ($content['items'] as $itemSlug => $item): ?>
            <li>
                <a class="article-link" href="<?= e(url(page_path($locale, $page, (string) $itemSlug))) ?>">
                    <span class="eyebrow"><?= e($item['eyebrow']) ?></span>
                    <span class="article-title"><?= e($item['title']) ?></span>
                    <span class="article-more"><?= e($common['read_more']) ?> →</span>
                </a>
            </li>
<?php endforeach; ?>
        </ul>
    </div>
</section>
