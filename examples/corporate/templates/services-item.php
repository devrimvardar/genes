<article class="g-blog article">
    <div class="g-article">
        <a class="back" href="<?= e(url(page_path($locale, $page))) ?>">← <?= e($content['parent']['back']) ?></a>
        <h1><?= e($content['title']) ?></h1>
        <p class="lead"><?= e($content['summary']) ?></p>
        <div class="prose"><?= paragraphs($content['text']) ?></div>
    </div>
    <aside class="g-related">
        <div class="card">
            <h2><?= e($common['footer']['contact']) ?></h2>
            <p><a href="mailto:<?= e($common['email']) ?>"><?= e($common['email']) ?></a><br><?= e($common['phone']) ?></p>
        </div>
    </aside>
</article>
