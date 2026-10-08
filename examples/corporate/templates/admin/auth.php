<!doctype html>
<html lang="<?= e($default) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e($text[$form]['title']) ?> | <?= e($site['name']) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/site.css')) ?>?v=<?= filemtime(ROOT . '/assets/css/site.css') ?>">
</head>
<body class="admin admin-auth">
<main class="auth-card">
    <p class="admin-brand"><?= e($site['name']) ?> <span><?= e($text['title']) ?></span></p>
    <h1><?= e($text[$form]['title']) ?></h1>
<?php if ($form === 'setup'): ?>
    <p class="muted"><?= e($text['setup']['text']) ?></p>
<?php endif; ?>
<?php if (isset($errors['login'])): ?>
    <p class="notice notice-invalid" role="alert"><?= e($text['login']['failed']) ?></p>
<?php endif; ?>
    <form class="form" method="post" action="<?= e(url('admin/' . $form)) ?>">
        <?= csrf_field() ?>
<?php if ($form === 'setup'): ?>
        <label class="field">
            <span><?= e($text['fields']['token']) ?></span>
            <input name="token" required autocomplete="off">
<?php if (isset($errors['token'])): ?>
            <small class="error"><?= e($text['errors']['token']) ?></small>
<?php endif; ?>
        </label>
        <label class="field">
            <span><?= e($text['fields']['name']) ?></span>
            <input name="name" maxlength="100" required value="<?= e($old['name'] ?? '') ?>">
<?php if (isset($errors['name'])): ?>
            <small class="error"><?= e($text['errors']['name']) ?></small>
<?php endif; ?>
        </label>
<?php endif; ?>
        <label class="field">
            <span><?= e($text['fields']['email']) ?></span>
            <input name="email" type="email" maxlength="254" required autocomplete="username" value="<?= e($old['email'] ?? '') ?>">
<?php if (isset($errors['email'])): ?>
            <small class="error"><?= e($text['errors']['email']) ?></small>
<?php endif; ?>
        </label>
        <label class="field">
            <span><?= e($text['fields']['password']) ?></span>
            <input name="password" type="password" required autocomplete="<?= $form === 'setup' ? 'new-password' : 'current-password' ?>"<?= $form === 'setup' ? ' minlength="12"' : '' ?>>
<?php if ($form === 'setup'): ?>
            <small class="help"><?= e($text['fields']['password_help']) ?></small>
<?php endif; ?>
<?php if (isset($errors['password'])): ?>
            <small class="error"><?= e($text['errors']['password']) ?></small>
<?php endif; ?>
        </label>
        <button class="button" type="submit"><?= e($text[$form]['submit']) ?></button>
    </form>
</main>
</body>
</html>
