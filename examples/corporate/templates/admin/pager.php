<?php if ($list['page'] > 1 || $list['more']): ?>
<nav class="pagination">
<?php if ($list['page'] > 1): ?>
    <a href="?<?= e(http_build_query(['page' => $list['page'] - 1] + array_filter($list['filters']))) ?>"><?= e($text['previous']) ?></a>
<?php endif; ?>
<?php if ($list['more']): ?>
    <a href="?<?= e(http_build_query(['page' => $list['page'] + 1] + array_filter($list['filters']))) ?>"><?= e($text['next']) ?></a>
<?php endif; ?>
</nav>
<?php endif; ?>
