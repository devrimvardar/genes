<section class="hero">
    <p class="eyebrow"><?= e($content['hero']['eyebrow']) ?></p>
    <h1><?= e($content['hero']['title']) ?></h1>
    <p class="lead"><?= e($content['hero']['text']) ?></p>
    <div class="actions">
        <a class="button" href="<?= e(url(page_path($locale, 'contact'))) ?>"><?= e($content['hero']['primary']) ?></a>
        <a class="button button-ghost" href="<?= e(url(page_path($locale, 'services'))) ?>"><?= e($content['hero']['secondary']) ?></a>
    </div>
</section>

<section class="stats">
<?php foreach ($content['stats'] as $stat): ?>
    <div class="stat">
        <p class="stat-value"><?= e($stat['value']) ?></p>
        <p class="stat-label"><?= e($stat['label']) ?></p>
    </div>
<?php endforeach; ?>
</section>

<section class="section">
    <div class="section-head">
        <h2><?= e($content['approach']['title']) ?></h2>
        <p><?= e($content['approach']['text']) ?></p>
    </div>
    <div class="cards">
<?php foreach ($content['approach']['cards'] as $card): ?>
        <article class="card">
            <h3><?= e($card['title']) ?></h3>
            <p><?= e($card['text']) ?></p>
        </article>
<?php endforeach; ?>
    </div>
</section>

<?php if ($content['latest']['items']): ?>
<section class="section">
    <div class="section-head section-head-row">
        <h2><?= e($content['latest']['title']) ?></h2>
        <a class="link" href="<?= e(url(page_path($locale, 'news'))) ?>"><?= e($content['latest']['more']) ?> →</a>
    </div>
    <div class="cards">
<?php foreach ($content['latest']['items'] as $itemSlug => $item): ?>
<?php require ROOT . '/templates/partials/news-card.php'; ?>
<?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
