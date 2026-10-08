<div class="admin-head">
    <h1><?= e($text['types'][$type] ?? $type) ?></h1>
</div>
<form class="admin-filters" method="get" action="<?= e(url($base)) ?>">
    <select name="status" aria-label="<?= e($text['status']['label']) ?>">
        <option value=""><?= e($text['all']) ?></option>
<?php foreach (['new', 'read'] as $option): ?>
        <option value="<?= e($option) ?>"<?= $list['filters']['status'] === $option ? ' selected' : '' ?>><?= e($text['status'][$option]) ?></option>
<?php endforeach; ?>
    </select>
    <button class="button button-small button-ghost" type="submit"><?= e($text['search']) ?></button>
</form>
<?php if (!$list['rows']): ?>
<p class="muted"><?= e($text['empty']) ?></p>
<?php endif; ?>
<ul class="admin-list">
<?php foreach ($list['rows'] as $row): ?>
    <li>
        <a href="<?= e(url($base . '/' . $row['hash'])) ?>"<?= ($event['hash'] ?? '') === $row['hash'] ? ' aria-current="true"' : '' ?>>
            <span class="admin-list-title"><?= e($row['data']['name'] ?? $row['hash']) ?></span>
            <span class="admin-list-meta">
                <span class="badge badge-<?= e($row['status']) ?>"><?= e($text['status'][$row['status']] ?? $row['status']) ?></span>
                <?= e(substr($row['created_at'], 0, 10)) ?>
            </span>
        </a>
    </li>
<?php endforeach; ?>
</ul>
<?php require ROOT . '/templates/admin/pager.php'; ?>
