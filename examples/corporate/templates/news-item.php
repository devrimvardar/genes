<article class="g-blog article">
    <div class="g-article">
        <a class="back" href="<?= e(url(page_path($locale, $page))) ?>">← <?= e($content['parent']['back']) ?></a>
        <p class="date"><?= e(format_date($content['published_at'], $common)) ?></p>
        <h1><?= e($content['title']) ?></h1>
        <p class="lead"><?= e($content['summary']) ?></p>
<?php if ($content['image']): ?>
        <img class="article-image" src="<?= e(url($content['image'])) ?>" alt="" width="768" height="432">
<?php endif; ?>
        <div class="prose"><?= paragraphs($content['text']) ?></div>
    </div>
    <aside class="g-related">
        <div class="card">
            <h2><?= e($common['footer']['contact']) ?></h2>
            <p><a href="mailto:<?= e($common['email']) ?>"><?= e($common['email']) ?></a><br><?= e($common['phone']) ?></p>
        </div>
    </aside>
</article>
