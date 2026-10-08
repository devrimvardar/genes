<section class="hero">
    <p class="eyebrow">404</p>
    <h1><?= e($content['title']) ?></h1>
    <p class="lead"><?= e($content['text']) ?></p>
    <a class="button" href="<?= e(url($locale . '/')) ?>"><?= e($content['button']) ?></a>
</section>
