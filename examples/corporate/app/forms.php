<?php
declare(strict_types=1);

function clean_text(string $value): string {
    $value = str_replace("\r", '', $value);
    return trim((string) preg_replace('/[^\P{C}\n\t]/u', '', $value));
}

function form_state(): array {
    start_session();
    $_SESSION['form_time'] = time();
    return (flash() ?? []) + ['status' => null, 'errors' => [], 'old' => []];
}

function handle_contact(array $config, string $locale): void {
    $back = url(page_path($locale, 'contact'));
    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 20000 || !csrf_valid()) {
        http_response_code(400);
        exit;
    }
    $tooFast = time() - (int) ($_SESSION['form_time'] ?? 0) < 3;
    if (($_POST['website'] ?? '') !== '' || $tooFast) {
        flash(['status' => 'sent']);
        redirect($back);
    }
    if (too_many('contact', 3, 600) || too_many('contact_total', 100, 86400, 'all')) {
        flash(['status' => 'limited']);
        redirect($back);
    }

    $old = [
        'name' => clean_text((string) ($_POST['name'] ?? '')),
        'email' => clean_text((string) ($_POST['email'] ?? '')),
        'message' => clean_text((string) ($_POST['message'] ?? '')),
    ];
    $errors = [];
    if ($old['name'] === '' || mb_strlen($old['name']) > 100) $errors['name'] = true;
    if (mb_strlen($old['email']) > 254 || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = true;
    if (mb_strlen($old['message']) < 10 || mb_strlen($old['message']) > 5000) $errors['message'] = true;
    if ($errors) {
        flash(['status' => 'invalid', 'errors' => $errors, 'old' => $old]);
        redirect($back);
    }

    log_event('contact', $old + ['locale' => $locale]);
    log_event('contact_total', [], 'done', null, null, 'all');

    $subject = mb_encode_mimeheader('[' . $config['site']['name'] . '] ' . mb_substr($old['name'], 0, 60), 'UTF-8');
    $body = "Name: {$old['name']}\nEmail: {$old['email']}\nLanguage: {$locale}\n\n{$old['message']}\n";
    $headers = [
        'From' => $config['contact']['from'],
        'Reply-To' => $old['email'],
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8',
        'Content-Transfer-Encoding' => '8bit',
    ];
    if (!@mail($config['contact']['to'], $subject, $body, $headers)) {
        error_log('Contact mail could not be sent.');
    }

    flash(['status' => 'sent']);
    redirect($back);
}
