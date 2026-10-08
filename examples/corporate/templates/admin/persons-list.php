<div class="admin-head">
    <h1><?= e($text['persons']) ?></h1>
    <a class="button button-small" href="<?= e(url($base . '/new')) ?>"><?= e($text['persons_create']) ?></a>
</div>
<ul class="admin-list">
<?php foreach ($rows as $row): ?>
    <li>
        <a href="<?= e(url($base . '/' . $row['hash'])) ?>"<?= ($editing['hash'] ?? '') === $row['hash'] ? ' aria-current="true"' : '' ?>>
            <span class="admin-list-title"><?= e($row['name']) ?></span>
            <span class="admin-list-meta">
                <span class="badge badge-<?= e($row['status'] === 'active' ? 'published' : 'draft') ?>"><?= e($text[$row['status']] ?? $row['status']) ?></span>
                <?= e($text['roles'][$row['role']] ?? $row['role']) ?> · <?= e($row['email']) ?>
            </span>
        </a>
    </li>
<?php endforeach; ?>
</ul>
