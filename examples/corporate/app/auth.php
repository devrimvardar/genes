<?php
declare(strict_types=1);

function start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('genes');
    session_set_cookie_params(['lifetime' => 0, 'path' => BASE . '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function csrf_token(): string {
    start_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool {
    start_session();
    $token = $_POST['csrf'] ?? '';
    return is_string($token) && $token !== '' && hash_equals($_SESSION['csrf'] ?? '', $token);
}

function flash(?array $message = null): ?array {
    start_session();
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }
    $stored = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $stored;
}

function current_user(): ?array {
    start_session();
    if (empty($_SESSION['user'])) return null;
    $stmt = db()->prepare("SELECT hash, name, email, role FROM persons WHERE hash = :hash AND status = 'active'");
    $stmt->execute([':hash' => $_SESSION['user']]);
    return $stmt->fetch() ?: null;
}

function require_user(string $role = 'editor'): array {
    $user = current_user();
    if (!$user) redirect(url('admin/login'));
    if ($role === 'admin' && $user['role'] !== 'admin') {
        http_response_code(403);
        exit;
    }
    return $user;
}

function attempt_login(string $email, string $password): bool {
    if (too_many('login_failed', 5, 900)) return false;
    $stmt = db()->prepare("SELECT hash, password_hash FROM persons WHERE email = :email AND status = 'active'");
    $stmt->execute([':email' => mb_strtolower(trim($email))]);
    $person = $stmt->fetch();
    $hash = $person['password_hash'] ?? password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    if (!$person || !password_verify($password, $hash)) {
        log_event('login_failed');
        return false;
    }
    start_session();
    session_regenerate_id(true);
    $_SESSION['user'] = $person['hash'];
    log_event('login', [], 'done', $person['hash'], $person['hash']);
    return true;
}

function logout(): void {
    start_session();
    $_SESSION = [];
    session_destroy();
}

function admin_exists(): bool {
    return (bool) db()->query("SELECT 1 FROM persons WHERE role = 'admin' LIMIT 1")->fetchColumn();
}

function setup_token(): string {
    $file = ROOT . '/data/setup-token.txt';
    if (!is_file($file)) file_put_contents($file, bin2hex(random_bytes(16)));
    return trim((string) file_get_contents($file));
}
