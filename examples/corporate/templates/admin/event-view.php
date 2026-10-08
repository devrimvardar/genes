<a class="back admin-back" href="<?= e(url($base)) ?>">← <?= e($text['back']) ?></a>
<article class="event">
    <h2><?= e($event['data']['name'] ?? '') ?></h2>
    <dl class="event-meta">
        <dt><?= e($text['fields']['email']) ?></dt>
        <dd><a href="mailto:<?= e($event['data']['email'] ?? '') ?>"><?= e($event['data']['email'] ?? '') ?></a></dd>
        <dt><?= e($text['fields']['date']) ?></dt>
        <dd><?= e(str_replace(['T', 'Z'], [' ', ' UTC'], $event['created_at'])) ?></dd>
        <dt><?= e($text['fields']['locale']) ?></dt>
        <dd><?= e(strtoupper($event['data']['locale'] ?? '')) ?></dd>
    </dl>
    <h3><?= e($text['fields']['message']) ?></h3>
    <div class="prose"><?= paragraphs((string) ($event['data']['message'] ?? '')) ?></div>
</article>
<details class="danger">
    <summary><?= e($text['delete']) ?></summary>
    <p><?= e($text['delete_question']) ?></p>
    <form method="post" action="<?= e(url($base . '/' . $event['hash'] . '/delete')) ?>">
        <?= csrf_field() ?>
        <button class="button button-danger" type="submit"><?= e($text['confirm_delete']) ?></button>
    </form>
</details>
