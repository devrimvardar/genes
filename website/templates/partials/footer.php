<footer class="footer">
    <p><?= e($common['footer']) ?></p>
    <ul class="footer-links">
<?php foreach ($common['footer_links'] as $link): ?>
        <li><a href="<?= e($link['url']) ?>"><?= e($link['label']) ?></a></li>
<?php endforeach; ?>
    </ul>
</footer>
