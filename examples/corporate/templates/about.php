<section class="page-head">
    <h1><?= e($content['title']) ?></h1>
    <div class="prose"><?= paragraphs($content['intro']) ?></div>
</section>

<section class="section">
    <div class="section-head">
        <h2><?= e($content['values']['title']) ?></h2>
    </div>
    <div class="cards">
<?php foreach ($content['values']['cards'] as $card): ?>
        <article class="card">
            <h3><?= e($card['title']) ?></h3>
            <p><?= e($card['text']) ?></p>
        </article>
<?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2><?= e($content['team']['title']) ?></h2>
    </div>
    <ul class="people">
<?php foreach ($content['team']['people'] as $person): ?>
        <li>
            <p class="person-name"><?= e($person['name']) ?></p>
            <p class="person-role"><?= e($person['role']) ?></p>
        </li>
<?php endforeach; ?>
    </ul>
</section>
