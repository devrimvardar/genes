<article class="article">
    <a class="back" href="<?= e(url(page_path($locale, $page))) ?>">← <?= e($common['back_to_articles']) ?></a>
    <p class="eyebrow"><?= e($content['eyebrow']) ?></p>
    <h1><?= e($content['title']) ?></h1>
    <p class="lead"><?= e($content['text']) ?></p>
    <h2><?= e($content['section_title']) ?></h2>
    <p><?= e($content['section_text']) ?></p>
<?php foreach ($content['cards'] as $card): ?>
    <h3><?= e($card['title']) ?></h3>
    <p><?= e($card['text']) ?></p>
<?php endforeach; ?>
    <a class="button" href="<?= e($content['button_url']) ?>"><?= e($content['button']) ?></a>
</article>
