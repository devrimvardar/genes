<section class="page-head">
    <h1><?= e($content['title']) ?></h1>
    <p class="lead"><?= e($content['intro']) ?></p>
</section>

<section class="contact">
    <div class="contact-form">
<?php if ($form['status']): ?>
        <p class="notice notice-<?= e($form['status']) ?>" role="status"><?= e($content['form']['messages'][$form['status']]) ?></p>
<?php endif; ?>
        <form class="form" method="post" action="<?= e(url(page_path($locale, 'contact'))) ?>">
            <?= csrf_field() ?>
            <label class="field">
                <span><?= e($content['form']['name']) ?></span>
                <input name="name" maxlength="100" required autocomplete="name" value="<?= e($form['old']['name'] ?? '') ?>">
<?php if (isset($form['errors']['name'])): ?>
                <small class="error"><?= e($content['form']['errors']['name']) ?></small>
<?php endif; ?>
            </label>
            <label class="field">
                <span><?= e($content['form']['email']) ?></span>
                <input name="email" type="email" maxlength="254" required autocomplete="email" value="<?= e($form['old']['email'] ?? '') ?>">
<?php if (isset($form['errors']['email'])): ?>
                <small class="error"><?= e($content['form']['errors']['email']) ?></small>
<?php endif; ?>
            </label>
            <label class="field">
                <span><?= e($content['form']['message']) ?></span>
                <textarea name="message" rows="8" minlength="10" maxlength="5000" required><?= e($form['old']['message'] ?? '') ?></textarea>
<?php if (isset($form['errors']['message'])): ?>
                <small class="error"><?= e($content['form']['errors']['message']) ?></small>
<?php endif; ?>
            </label>
            <div class="hp" aria-hidden="true">
                <label>Website <input name="website" tabindex="-1" autocomplete="off"></label>
            </div>
            <button class="button" type="submit"><?= e($content['form']['submit']) ?></button>
        </form>
    </div>
    <aside class="contact-details">
        <div class="card">
            <h2><?= e($content['details']) ?></h2>
            <p><a href="mailto:<?= e($common['email']) ?>"><?= e($common['email']) ?></a><br><?= e($common['phone']) ?></p>
        </div>
<?php foreach ($common['offices'] as $office): ?>
        <div class="card">
            <h2><?= e($office['city']) ?></h2>
            <p><?= e($office['address']) ?></p>
        </div>
<?php endforeach; ?>
    </aside>
</section>
