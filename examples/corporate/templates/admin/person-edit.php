<a class="back admin-back" href="<?= e(url($base)) ?>">← <?= e($text['back']) ?></a>
<form class="form editor" method="post" action="<?= e(url($base . '/' . $editing['hash'])) ?>">
    <?= csrf_field() ?>
    <div class="editor-head">
        <h2><?= e($editing['hash'] === 'new' ? $text['persons_create'] : $editing['name']) ?></h2>
        <button class="button" type="submit"><?= e($text['save']) ?></button>
    </div>
    <label class="field">
        <span><?= e($text['fields']['name']) ?></span>
        <input name="name" maxlength="100" required value="<?= e($editing['name']) ?>">
<?php if (isset($errors['name'])): ?>
        <small class="error"><?= e($text['errors']['name']) ?></small>
<?php endif; ?>
    </label>
    <label class="field">
        <span><?= e($text['fields']['email']) ?></span>
        <input name="email" type="email" maxlength="254" required value="<?= e($editing['email']) ?>">
<?php if (isset($errors['email'])): ?>
        <small class="error"><?= e($text['errors']['email']) ?></small>
<?php endif; ?>
    </label>
    <label class="field">
        <span><?= e($text['fields']['password']) ?></span>
        <input name="password" type="password" autocomplete="new-password" minlength="12"<?= $editing['hash'] === 'new' ? ' required' : '' ?>>
        <small class="help"><?= e($text['fields']['password_help']) ?></small>
<?php if (isset($errors['password'])): ?>
        <small class="error"><?= e($text['errors']['password']) ?></small>
<?php endif; ?>
    </label>
<?php if ($editing['hash'] !== $user['hash']): ?>
    <div class="editor-row">
        <label class="field">
            <span><?= e($text['fields']['role']) ?></span>
            <select name="role">
<?php foreach (['editor', 'admin'] as $option): ?>
                <option value="<?= e($option) ?>"<?= $editing['role'] === $option ? ' selected' : '' ?>><?= e($text['roles'][$option]) ?></option>
<?php endforeach; ?>
            </select>
        </label>
        <label class="field">
            <span><?= e($text['status']['label']) ?></span>
            <select name="status">
<?php foreach (['active', 'disabled'] as $option): ?>
                <option value="<?= e($option) ?>"<?= $editing['status'] === $option ? ' selected' : '' ?>><?= e($text[$option]) ?></option>
<?php endforeach; ?>
            </select>
        </label>
    </div>
<?php endif; ?>
</form>
