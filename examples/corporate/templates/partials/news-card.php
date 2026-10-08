<article class="card news-card">
<?php if ($item['image']): ?>
    <img src="<?= e(url($item['image'])) ?>" alt="" width="368" height="207" loading="lazy">
<?php endif; ?>
    <p class="date"><?= e(format_date($item['published_at'], $common)) ?></p>
    <h3><a href="<?= e(url(page_path($locale, 'news', (string) $itemSlug))) ?>"><?= e($item['title']) ?></a></h3>
    <p><?= e($item['summary']) ?></p>
</article>
