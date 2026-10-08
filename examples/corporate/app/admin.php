<?php
declare(strict_types=1);

require ROOT . '/app/slugify.php';

const ADMIN_PAGE_SIZE = 50;
const HASH_PATTERN = '/^[a-z]{3}_[a-z0-9]{6,32}$/';
const IMAGE_TYPES = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];

function run_admin(array $config, array $content, array $parts): void {
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex');
    $default = $config['site']['default_locale'];
    $ctx = [
        'config' => $config,
        'site' => $config['site'],
        'locales' => $config['site']['locales'],
        'default' => $default,
        'text' => localize($content['admin'], $default, $default, $config['site']['locales']),
        'user' => null,
    ];

    $post = $_SERVER['REQUEST_METHOD'] === 'POST';
    if ($post) {
        if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 1200000) admin_stop(413);
        if (!csrf_valid()) admin_stop(400);
    }

    $section = $parts[0] ?? '';
    if ($section === 'setup' && count($parts) === 1) {
        admin_setup($ctx, $post);
        return;
    }
    if ($section === 'login' && count($parts) === 1) {
        admin_login($ctx, $post);
        return;
    }
    if ($section === 'logout' && count($parts) === 1 && $post) {
        logout();
        redirect(url('admin/login'));
    }

    $ctx['user'] = require_user();
    $types = array_keys($config['admin']['types']);
    if ($section === '' && !$parts) redirect(url('admin/items/' . $types[0]));
    if ($section === 'items' && in_array($parts[1] ?? '', $types, true) && count($parts) <= 4) {
        admin_items($ctx, $parts[1], $parts[2] ?? null, $parts[3] ?? null, $post);
        return;
    }
    if ($section === 'events' && in_array($parts[1] ?? '', $config['admin']['events'], true) && count($parts) <= 4) {
        admin_events($ctx, $parts[1], $parts[2] ?? null, $parts[3] ?? null, $post);
        return;
    }
    if ($section === 'persons' && count($parts) <= 2) {
        require_user('admin');
        admin_persons($ctx, $parts[1] ?? null, $post);
        return;
    }
    admin_stop(404);
}

function admin_stop(int $code): void {
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $code;
    exit;
}

function admin_render(array $ctx, string $main, ?string $side, array $vars = []): void {
    $vars += ['open' => $side !== null, 'errors' => [], 'flash' => flash()];
    extract($ctx + $vars, EXTR_SKIP);
    require ROOT . '/templates/admin/layout.php';
}

function admin_render_auth(array $ctx, string $form, array $vars = []): void {
    $vars += ['errors' => [], 'old' => []];
    extract($ctx + $vars, EXTR_SKIP);
    require ROOT . '/templates/admin/auth.php';
}

function admin_clean(string $value): string {
    $value = str_replace("\r", '', $value);
    return trim((string) preg_replace('/[^\P{C}\n\t]/u', '', $value));
}

function admin_post(string $name): string {
    $value = $_POST[$name] ?? '';
    return is_string($value) ? admin_clean($value) : '';
}

function admin_post_locales(string $name, array $locales): array {
    $values = $_POST[$name] ?? [];
    $result = [];
    foreach ($locales as $locale) {
        $value = is_array($values) ? ($values[$locale] ?? '') : '';
        $result[$locale] = is_string($value) ? admin_clean($value) : '';
    }
    return $result;
}

function admin_page_number(): int {
    return min(1000, max(1, (int) ($_GET['page'] ?? 1)));
}

function admin_like(string $query): string {
    return '%' . addcslashes($query, '%_\\') . '%';
}

// ---------------------------------------------------------------- setup and login

function admin_setup(array $ctx, bool $post): void {
    if (admin_exists()) admin_stop(404);
    $token = setup_token();
    $errors = [];
    $old = ['name' => '', 'email' => ''];

    if ($post) {
        if (too_many('setup_failed', 5, 900)) admin_stop(429);
        $old = ['name' => admin_post('name'), 'email' => mb_strtolower(admin_post('email'))];
        $password = (string) ($_POST['password'] ?? '');
        if (!hash_equals($token, admin_post('token'))) $errors['token'] = true;
        if ($old['name'] === '' || mb_strlen($old['name']) > 100) $errors['name'] = true;
        if (mb_strlen($old['email']) > 254 || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = true;
        if (mb_strlen($password) < 12 || mb_strlen($password) > 200) $errors['password'] = true;

        if ($errors) {
            log_event('setup_failed');
        } else {
            $hash = new_hash('prs');
            $stmt = db()->prepare("INSERT INTO persons (hash, role, name, email, password_hash, created_by, updated_by)
                VALUES (:hash, 'admin', :name, :email, :password, :hash, :hash)");
            $stmt->execute([':hash' => $hash, ':name' => $old['name'], ':email' => $old['email'],
                ':password' => password_hash($password, PASSWORD_DEFAULT)]);
            @unlink(ROOT . '/data/setup-token.txt');
            start_session();
            session_regenerate_id(true);
            $_SESSION['user'] = $hash;
            log_event('person_created', [], 'done', $hash, $hash);
            redirect(url('admin'));
        }
    }
    admin_render_auth($ctx, 'setup', ['errors' => $errors, 'old' => $old]);
}

function admin_login(array $ctx, bool $post): void {
    if (!admin_exists()) redirect(url('admin/setup'));
    if (current_user()) redirect(url('admin'));
    $errors = [];
    $old = ['email' => ''];
    if ($post) {
        $old['email'] = admin_post('email');
        if (too_many('login_failed', 5, 900)) http_response_code(429);
        if (attempt_login($old['email'], (string) ($_POST['password'] ?? ''))) redirect(url('admin'));
        $errors['login'] = true;
    }
    admin_render_auth($ctx, 'login', ['errors' => $errors, 'old' => $old]);
}

// ---------------------------------------------------------------- items

function admin_items(array $ctx, string $type, ?string $hash, ?string $action, bool $post): void {
    $user = $ctx['user'];
    $base = 'admin/items/' . $type;
    $record = null;

    if ($hash !== null && $hash !== 'new') {
        if (!preg_match(HASH_PATTERN, $hash)) admin_stop(404);
        $stmt = db()->prepare('SELECT * FROM items WHERE type = :type AND hash = :hash');
        $stmt->execute([':type' => $type, ':hash' => $hash]);
        $record = $stmt->fetch();
        if (!$record) admin_stop(404);
        $record = decode_row($record);
    }

    if ($action !== null) {
        if ($action !== 'delete' || !$post || !$record) admin_stop(404);
        if (!admin_can_edit($user, $record)) admin_stop(403);
        db()->prepare('DELETE FROM items WHERE id = :id')->execute([':id' => $record['id']]);
        admin_remove_image($record['image']);
        log_event('item_deleted', ['type' => $type, 'slug' => $record['slug']], 'done', $record['hash'], $user['hash']);
        flash(['type' => 'deleted']);
        redirect(url($base));
    }

    $errors = [];
    $editing = null;
    if ($hash !== null) {
        $editing = $record ?? ['hash' => 'new', 'status' => 'draft', 'slug' => '', 'title' => [], 'summary' => [],
            'text' => [], 'image' => '', 'published_at' => null, 'owner' => $user['hash']];
        if ($post) {
            if ($record && !admin_can_edit($user, $record)) admin_stop(403);
            [$saved, $errors, $editing] = admin_save_item($ctx, $type, $record, $editing);
            if ($saved) {
                flash(['type' => 'saved']);
                redirect(url($base . '/' . $saved));
            }
        }
    }

    admin_render($ctx, 'items-list', $editing ? 'item-edit' : null, [
        'type' => $type,
        'base' => $base,
        'fields' => $ctx['config']['admin']['types'][$type]['fields'],
        'list' => admin_item_list($type),
        'editing' => $editing,
        'can_edit' => $editing ? admin_can_edit($user, $editing) : false,
        'errors' => $errors,
    ]);
}

function admin_can_edit(array $user, array $record): bool {
    return $user['role'] === 'admin' || ($record['owner'] ?? null) === $user['hash'];
}

function admin_item_list(string $type): array {
    $status = (string) ($_GET['status'] ?? '');
    $query = mb_substr(admin_clean((string) ($_GET['q'] ?? '')), 0, 100);
    $page = admin_page_number();
    $where = 'type = :type';
    $params = [':type' => $type];
    if (in_array($status, ['draft', 'published'], true)) {
        $where .= ' AND status = :status';
        $params[':status'] = $status;
    } else {
        $status = '';
    }
    if ($query !== '') {
        $where .= " AND (slug LIKE :q ESCAPE '\\' OR title LIKE :q ESCAPE '\\')";
        $params[':q'] = admin_like($query);
    }
    $stmt = db()->prepare("SELECT hash, slug, title, status, published_at, updated_at FROM items WHERE $where
        ORDER BY COALESCE(published_at, created_at) DESC, id DESC LIMIT :limit OFFSET :offset");
    foreach ($params as $key => $value) $stmt->bindValue($key, $value);
    $stmt->bindValue(':limit', ADMIN_PAGE_SIZE + 1, PDO::PARAM_INT);
    $stmt->bindValue(':offset', ($page - 1) * ADMIN_PAGE_SIZE, PDO::PARAM_INT);
    $stmt->execute();
    $rows = array_map('decode_row', $stmt->fetchAll());
    return [
        'rows' => array_slice($rows, 0, ADMIN_PAGE_SIZE),
        'page' => $page,
        'more' => count($rows) > ADMIN_PAGE_SIZE,
        'filters' => ['status' => $status, 'q' => $query],
    ];
}

function admin_save_item(array $ctx, string $type, ?array $record, array $editing): array {
    $locales = $ctx['locales'];
    $default = $ctx['default'];
    $user = $ctx['user'];
    $fields = $ctx['config']['admin']['types'][$type]['fields'];
    $errors = [];

    $values = [
        'title' => admin_post_locales('title', $locales),
        'summary' => in_array('summary', $fields, true) ? admin_post_locales('summary', $locales) : ($editing['summary'] ?: []),
        'text' => in_array('text', $fields, true) ? admin_post_locales('text', $locales) : ($editing['text'] ?: []),
        'slug' => admin_post('slug'),
        'status' => admin_post('status'),
        'published_at' => admin_post('published_at'),
    ];

    if ($values['title'][$default] === '') $errors['title'] = true;
    foreach ($locales as $locale) {
        if (mb_strlen($values['title'][$locale]) > 200) $errors['title'] = true;
        if (mb_strlen($values['summary'][$locale] ?? '') > 500) $errors['summary'] = true;
        if (mb_strlen($values['text'][$locale] ?? '') > 50000) $errors['text'] = true;
    }
    if (!in_array($values['status'], ['draft', 'published'], true)) $values['status'] = 'draft';

    if ($values['slug'] === '') $values['slug'] = slugify($values['title'][$default]);
    if ($values['slug'] === '' && $record) $values['slug'] = str_replace('_', '-', $record['hash']);
    if (!preg_match('/^[a-z0-9-]{1,80}$/', $values['slug'])) {
        $errors['slug'] = true;
    } else {
        $stmt = db()->prepare('SELECT 1 FROM items WHERE type = :type AND slug = :slug AND hash != :hash');
        $stmt->execute([':type' => $type, ':slug' => $values['slug'], ':hash' => $record['hash'] ?? '']);
        if ($stmt->fetchColumn()) $errors['slug'] = true;
    }

    $publishedAt = $record['published_at'] ?? null;
    if ($values['published_at'] !== '') {
        $date = DateTime::createFromFormat('!Y-m-d', $values['published_at'], new DateTimeZone('UTC'));
        if (!$date || $date->format('Y-m-d') !== $values['published_at']) {
            $errors['published_at'] = true;
        } else {
            $publishedAt = $date->format('Y-m-d') . 'T08:00:00Z';
        }
    }
    if ($values['status'] === 'published' && !$publishedAt) $publishedAt = now();

    $image = $editing['image'];
    if (!$errors && in_array('image', $fields, true)) {
        if (!empty($_POST['remove_image'])) $image = '';
        $upload = admin_upload_image($type);
        if ($upload === false) {
            $errors['image'] = true;
        } elseif ($upload !== null) {
            $image = $upload;
        }
    }

    $editing = array_merge($editing, $values, ['published_at' => $publishedAt, 'image' => $image]);
    if ($errors) return [null, $errors, $editing];

    if ($record && $record['image'] !== $image) admin_remove_image($record['image']);

    $params = [
        ':status' => $values['status'], ':slug' => $values['slug'], ':title' => encode_json($values['title']),
        ':summary' => encode_json($values['summary']), ':text' => encode_json($values['text']), ':image' => $image,
        ':published_at' => $publishedAt, ':by' => $user['hash'], ':now' => now(),
    ];
    if ($record) {
        $stmt = db()->prepare('UPDATE items SET status = :status, slug = :slug, title = :title, summary = :summary,
            text = :text, image = :image, published_at = :published_at, updated_by = :by, updated_at = :now WHERE id = :id');
        $stmt->execute($params + [':id' => $record['id']]);
        log_event('item_updated', ['type' => $type, 'slug' => $values['slug']], 'done', $record['hash'], $user['hash']);
        return [$record['hash'], [], $editing];
    }

    $hash = new_hash('itm');
    $stmt = db()->prepare('INSERT INTO items (hash, type, status, slug, title, summary, text, image, published_at,
        owner, created_by, updated_by, created_at, updated_at)
        VALUES (:hash, :type, :status, :slug, :title, :summary, :text, :image, :published_at, :by, :by, :by, :now, :now)');
    $stmt->execute($params + [':hash' => $hash, ':type' => $type]);
    log_event('item_created', ['type' => $type, 'slug' => $values['slug']], 'done', $hash, $user['hash']);
    return [$hash, [], $editing];
}

/** Returns the new relative path, null when no file was sent, or false when the file is invalid. */
function admin_upload_image(string $type) {
    $file = $_FILES['image_file'] ?? null;
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 1048576 || !is_uploaded_file($file['tmp_name'])) return false;
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !isset(IMAGE_TYPES[$info[2]]) || $info[0] < 1 || $info[0] > 1920 || $info[1] > 1920) return false;

    $folder = 'assets/items/' . $type;
    if (!is_dir(ROOT . '/' . $folder) && !mkdir(ROOT . '/' . $folder, 0755, true)) return false;
    $path = $folder . '/' . bin2hex(random_bytes(8)) . '.' . IMAGE_TYPES[$info[2]];
    return move_uploaded_file($file['tmp_name'], ROOT . '/' . $path) ? $path : false;
}

function admin_remove_image(string $path): void {
    if (preg_match('#^assets/items/[a-z0-9-]+/[a-f0-9]{16}\.(jpg|png|gif|webp)$#', $path)) {
        @unlink(ROOT . '/' . $path);
    }
}

// ---------------------------------------------------------------- events

function admin_events(array $ctx, string $type, ?string $hash, ?string $action, bool $post): void {
    $base = 'admin/events/' . $type;
    $record = null;
    if ($hash !== null) {
        if (!preg_match(HASH_PATTERN, $hash)) admin_stop(404);
        $stmt = db()->prepare("SELECT * FROM events WHERE type = :type AND hash = :hash AND ip != 'all'");
        $stmt->execute([':type' => $type, ':hash' => $hash]);
        $record = $stmt->fetch();
        if (!$record) admin_stop(404);
        $record = decode_row($record);
    }

    if ($action !== null) {
        if ($action !== 'delete' || !$post || !$record) admin_stop(404);
        db()->prepare('DELETE FROM events WHERE id = :id')->execute([':id' => $record['id']]);
        flash(['type' => 'deleted']);
        redirect(url($base));
    }
    if ($post) admin_stop(405);

    if ($record && $record['status'] === 'new') {
        db()->prepare("UPDATE events SET status = 'read' WHERE id = :id")->execute([':id' => $record['id']]);
        $record['status'] = 'read';
    }

    $status = (string) ($_GET['status'] ?? '');
    $page = admin_page_number();
    $where = "type = :type AND ip != 'all'";
    $params = [':type' => $type];
    if (in_array($status, ['new', 'read'], true)) {
        $where .= ' AND status = :status';
        $params[':status'] = $status;
    } else {
        $status = '';
    }
    $stmt = db()->prepare("SELECT hash, status, data, created_at FROM events WHERE $where
        ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset");
    foreach ($params as $key => $value) $stmt->bindValue($key, $value);
    $stmt->bindValue(':limit', ADMIN_PAGE_SIZE + 1, PDO::PARAM_INT);
    $stmt->bindValue(':offset', ($page - 1) * ADMIN_PAGE_SIZE, PDO::PARAM_INT);
    $stmt->execute();
    $rows = array_map('decode_row', $stmt->fetchAll());

    admin_render($ctx, 'events-list', $record ? 'event-view' : null, [
        'type' => $type,
        'base' => $base,
        'list' => ['rows' => array_slice($rows, 0, ADMIN_PAGE_SIZE), 'page' => $page,
            'more' => count($rows) > ADMIN_PAGE_SIZE, 'filters' => ['status' => $status]],
        'event' => $record,
    ]);
}

// ---------------------------------------------------------------- persons

function admin_persons(array $ctx, ?string $hash, bool $post): void {
    $user = $ctx['user'];
    $record = null;
    if ($hash !== null && $hash !== 'new') {
        if (!preg_match(HASH_PATTERN, $hash)) admin_stop(404);
        $stmt = db()->prepare('SELECT hash, name, email, role, status FROM persons WHERE hash = :hash');
        $stmt->execute([':hash' => $hash]);
        $record = $stmt->fetch();
        if (!$record) admin_stop(404);
    }

    $errors = [];
    $editing = $hash !== null ? ($record ?? ['hash' => 'new', 'name' => '', 'email' => '', 'role' => 'editor', 'status' => 'active']) : null;
    if ($post && $editing) {
        $values = [
            'name' => admin_post('name'),
            'email' => mb_strtolower(admin_post('email')),
            'role' => admin_post('role') === 'admin' ? 'admin' : 'editor',
            'status' => admin_post('status') === 'disabled' ? 'disabled' : 'active',
        ];
        if ($record && $record['hash'] === $user['hash']) {
            $values['role'] = 'admin';
            $values['status'] = 'active';
        }
        $password = (string) ($_POST['password'] ?? '');
        if ($values['name'] === '' || mb_strlen($values['name']) > 100) $errors['name'] = true;
        if (mb_strlen($values['email']) > 254 || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = true;
        } else {
            $stmt = db()->prepare('SELECT 1 FROM persons WHERE email = :email AND hash != :hash');
            $stmt->execute([':email' => $values['email'], ':hash' => $record['hash'] ?? '']);
            if ($stmt->fetchColumn()) $errors['email'] = true;
        }
        if ((!$record || $password !== '') && (mb_strlen($password) < 12 || mb_strlen($password) > 200)) $errors['password'] = true;
        $editing = array_merge($editing, $values);

        if (!$errors) {
            $params = [':name' => $values['name'], ':email' => $values['email'], ':role' => $values['role'],
                ':status' => $values['status'], ':by' => $user['hash'], ':now' => now()];
            if ($record) {
                $stmt = db()->prepare('UPDATE persons SET name = :name, email = :email, role = :role, status = :status,
                    updated_by = :by, updated_at = :now WHERE hash = :hash');
                $stmt->execute($params + [':hash' => $record['hash']]);
                if ($password !== '') {
                    db()->prepare('UPDATE persons SET password_hash = :password WHERE hash = :hash')
                        ->execute([':password' => password_hash($password, PASSWORD_DEFAULT), ':hash' => $record['hash']]);
                }
                $saved = $record['hash'];
                flash(['type' => 'updated']);
            } else {
                $saved = new_hash('prs');
                $stmt = db()->prepare('INSERT INTO persons (hash, name, email, role, status, password_hash, created_by, updated_by, created_at, updated_at)
                    VALUES (:hash, :name, :email, :role, :status, :password, :by, :by, :now, :now)');
                $stmt->execute($params + [':hash' => $saved, ':password' => password_hash($password, PASSWORD_DEFAULT)]);
                flash(['type' => 'created']);
            }
            log_event('person_saved', [], 'done', $saved, $user['hash']);
            redirect(url('admin/persons/' . $saved));
        }
    } elseif ($post) {
        admin_stop(405);
    }

    $rows = db()->query('SELECT hash, name, email, role, status FROM persons ORDER BY name LIMIT 500')->fetchAll();
    admin_render($ctx, 'persons-list', $editing ? 'person-edit' : null, [
        'base' => 'admin/persons',
        'rows' => $rows,
        'editing' => $editing,
        'errors' => $errors,
    ]);
}
