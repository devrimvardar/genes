<?php if ($page !== 'contact' && $page !== '404'): ?>
<section class="cta">
    <h2><?= e($common['cta']['title']) ?></h2>
    <p><?= e($common['cta']['text']) ?></p>
    <a class="button" href="<?= e(url(page_path($locale, 'contact'))) ?>"><?= e($common['cta']['button']) ?></a>
</section>
<?php endif; ?>
