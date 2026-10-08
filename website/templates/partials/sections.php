<section class="hero">
    <p class="eyebrow"><?= e($content['eyebrow']) ?></p>
    <h1><?= e($content['title']) ?></h1>
    <p class="lead"><?= e($content['text']) ?></p>
    <a class="button" href="<?= e(preg_match('#^https?://#', $content['button_url']) ? $content['button_url'] : url($content['button_url'])) ?>"><?= e($content['button']) ?></a>
</section>
<section class="section">
    <h2><?= e($content['section_title']) ?></h2>
    <div class="section-body">
        <p><?= e($content['section_text']) ?></p>
<?php if ($content['cards']): ?>
        <div class="cards">
<?php foreach ($content['cards'] as $card): ?>
            <article class="card">
                <h3><?= e($card['title']) ?></h3>
                <p><?= e($card['text']) ?></p>
            </article>
<?php endforeach; ?>
        </div>
<?php endif; ?>
    </div>
</section>
