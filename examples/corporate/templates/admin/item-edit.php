<a class="back admin-back" href="<?= e(url($base)) ?>">← <?= e($text['back']) ?></a>
<form class="form editor" method="post" enctype="multipart/form-data" action="<?= e(url($base . '/' . $editing['hash'])) ?>">
    <?= csrf_field() ?>
    <div class="editor-head">
        <h2><?= e($editing['hash'] === 'new' ? $text['new'] : ($editing['title'][$default] ?? '')) ?></h2>
<?php if ($can_edit): ?>
        <button class="button" type="submit"><?= e($text['save']) ?></button>
<?php endif; ?>
    </div>
<?php if (!$can_edit): ?>
    <p class="notice notice-invalid"><?= e($text['errors']['forbidden']) ?></p>
<?php endif; ?>

    <div class="editor-row">
        <label class="field">
            <span><?= e($text['status']['label']) ?></span>
            <select name="status">
<?php foreach (['draft', 'published'] as $option): ?>
                <option value="<?= e($option) ?>"<?= $editing['status'] === $option ? ' selected' : '' ?>><?= e($text['status'][$option]) ?></option>
<?php endforeach; ?>
            </select>
        </label>
<?php if (in_array('published_at', $fields, true)): ?>
        <label class="field">
            <span><?= e($text['fields']['published_at']) ?></span>
            <input name="published_at" type="date" value="<?= e(substr((string) $editing['published_at'], 0, 10)) ?>">
<?php if (isset($errors['published_at'])): ?>
            <small class="error"><?= e($text['errors']['published_at']) ?></small>
<?php endif; ?>
        </label>
<?php endif; ?>
        <label class="field">
            <span><?= e($text['fields']['slug']) ?></span>
            <input name="slug" maxlength="80" pattern="[a-z0-9-]+" value="<?= e($editing['slug']) ?>">
            <small class="help"><?= e($text['fields']['slug_help']) ?></small>
<?php if (isset($errors['slug'])): ?>
            <small class="error"><?= e($text['errors']['slug']) ?></small>
<?php endif; ?>
        </label>
    </div>

<?php foreach ($locales as $fieldLocale): ?>
    <fieldset class="locale-group">
        <legend><?= e(strtoupper($fieldLocale)) ?></legend>
        <label class="field">
            <span><?= e($text['fields']['title']) ?></span>
            <input name="title[<?= e($fieldLocale) ?>]" maxlength="200"<?= $fieldLocale === $default ? ' required' : '' ?> value="<?= e($editing['title'][$fieldLocale] ?? '') ?>">
<?php if (isset($errors['title']) && $fieldLocale === $default): ?>
            <small class="error"><?= e($text['errors']['title']) ?></small>
<?php endif; ?>
        </label>
<?php if (in_array('summary', $fields, true)): ?>
        <label class="field">
            <span><?= e($text['fields']['summary']) ?></span>
            <textarea name="summary[<?= e($fieldLocale) ?>]" rows="3" maxlength="500"><?= e($editing['summary'][$fieldLocale] ?? '') ?></textarea>
<?php if (isset($errors['summary']) && $fieldLocale === $default): ?>
            <small class="error"><?= e($text['errors']['summary']) ?></small>
<?php endif; ?>
        </label>
<?php endif; ?>
<?php if (in_array('text', $fields, true)): ?>
        <label class="field">
            <span><?= e($text['fields']['text']) ?></span>
            <textarea name="text[<?= e($fieldLocale) ?>]" rows="12" maxlength="50000"><?= e($editing['text'][$fieldLocale] ?? '') ?></textarea>
<?php if (isset($errors['text']) && $fieldLocale === $default): ?>
            <small class="error"><?= e($text['errors']['text']) ?></small>
<?php endif; ?>
        </label>
<?php endif; ?>
    </fieldset>
<?php endforeach; ?>

<?php if (in_array('image', $fields, true)): ?>
    <div class="field">
        <span><?= e($text['fields']['image']) ?></span>
<?php if ($editing['image']): ?>
        <img class="editor-image" src="<?= e(url($editing['image'])) ?>" alt="" width="288" height="162">
        <label class="check"><input type="checkbox" name="remove_image" value="1"> <?= e($text['fields']['remove_image']) ?></label>
<?php endif; ?>
        <input name="image_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
        <small class="help"><?= e($text['fields']['image_help']) ?></small>
<?php if (isset($errors['image'])): ?>
        <small class="error"><?= e($text['errors']['image']) ?></small>
<?php endif; ?>
    </div>
<?php endif; ?>
</form>

<?php if ($editing['hash'] !== 'new' && $can_edit): ?>
<details class="danger">
    <summary><?= e($text['delete']) ?></summary>
    <p><?= e($text['delete_question']) ?></p>
    <form method="post" action="<?= e(url($base . '/' . $editing['hash'] . '/delete')) ?>">
        <?= csrf_field() ?>
        <button class="button button-danger" type="submit"><?= e($text['confirm_delete']) ?></button>
    </form>
</details>
<?php endif; ?>
