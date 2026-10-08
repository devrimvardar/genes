<?php
declare(strict_types=1);

const JSON_FIELDS = ['title', 'summary', 'text', 'labels', 'data'];

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $file = ROOT . '/data/content.sqlite';
    $new = !is_file($file);
    $pdo = new PDO('sqlite:' . $file, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    if ($new) {
        $pdo->exec((string) file_get_contents(ROOT . '/app/schema.sql'));
        if (is_file(ROOT . '/app/seed.sql')) $pdo->exec((string) file_get_contents(ROOT . '/app/seed.sql'));
    }
    return $pdo;
}

function new_hash(string $prefix): string {
    return $prefix . '_' . bin2hex(random_bytes(6));
}

function now(): string {
    return gmdate('Y-m-d\TH:i:s\Z');
}

function client_key(): string {
    return hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
}

function encode_json($value): string {
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function decode_row(array $row): array {
    foreach (JSON_FIELDS as $field) {
        if (!array_key_exists($field, $row)) continue;
        $value = json_decode((string) $row[$field], true);
        if (in_array($field, ['labels', 'data'], true)) {
            $row[$field] = is_array($value) ? $value : [];
        } else {
            $row[$field] = $value ?: '';
        }
    }
    return $row;
}

function log_event(string $type, array $data = [], string $status = 'new', ?string $subject = null, ?string $by = null, ?string $ip = null): void {
    $stmt = db()->prepare('INSERT INTO events (hash, type, status, subject, ip, data, created_by)
        VALUES (:hash, :type, :status, :subject, :ip, :data, :by)');
    $stmt->execute([':hash' => new_hash('evt'), ':type' => $type, ':status' => $status, ':subject' => $subject,
        ':ip' => $ip ?? client_key(), ':data' => encode_json($data), ':by' => $by]);
}

function too_many(string $type, int $max, int $seconds, ?string $ip = null): bool {
    $stmt = db()->prepare('SELECT COUNT(*) FROM events WHERE type = :type AND ip = :ip AND created_at >= :since');
    $stmt->execute([':type' => $type, ':ip' => $ip ?? client_key(), ':since' => gmdate('Y-m-d\TH:i:s\Z', time() - $seconds)]);
    return (int) $stmt->fetchColumn() >= $max;
}

function load_collection(array $data, ?string $slug): ?array {
    $type = (string) $data['collection'];
    if ($slug !== null) {
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) return null;
        $stmt = db()->prepare("SELECT hash, slug, title, summary, text, image, labels, data, published_at, updated_at
            FROM items WHERE type = :type AND slug = :slug AND status = 'published'");
        $stmt->execute([':type' => $type, ':slug' => $slug]);
        $row = $stmt->fetch();
        if (!$row) return null;
        unset($data['collection'], $data['per_page']);
        return decode_row($row) + ['parent' => $data];
    }

    $perPage = min(50, max(1, (int) ($data['per_page'] ?? 10)));
    $current = min(1000, max(1, (int) ($_GET['page'] ?? 1)));
    $count = db()->prepare("SELECT COUNT(*) FROM items WHERE type = :type AND status = 'published'");
    $count->execute([':type' => $type]);
    $pages = max(1, (int) ceil((int) $count->fetchColumn() / $perPage));

    $stmt = db()->prepare("SELECT hash, slug, title, summary, image, published_at FROM items
        WHERE type = :type AND status = 'published'
        ORDER BY published_at DESC, id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':type', $type);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', ($current - 1) * $perPage, PDO::PARAM_INT);
    $stmt->execute();

    $data['items'] = [];
    foreach ($stmt->fetchAll() as $row) $data['items'][$row['slug']] = decode_row($row);
    $data['pagination'] = ['page' => $current, 'pages' => $pages];
    return $data;
}

function latest_items(string $type, int $limit): array {
    $stmt = db()->prepare("SELECT hash, slug, title, summary, image, published_at FROM items
        WHERE type = :type AND status = 'published' ORDER BY published_at DESC, id DESC LIMIT :limit");
    $stmt->bindValue(':type', $type);
    $stmt->bindValue(':limit', min(12, max(1, $limit)), PDO::PARAM_INT);
    $stmt->execute();
    $items = [];
    foreach ($stmt->fetchAll() as $row) $items[$row['slug']] = decode_row($row);
    return $items;
}

function with_latest(array $data): array {
    foreach ($data as $key => $section) {
        if (is_array($section) && isset($section['collection'], $section['limit'])) {
            $data[$key]['items'] = latest_items((string) $section['collection'], (int) $section['limit']);
        }
    }
    return $data;
}

function collection_slugs(string $type): array {
    $stmt = db()->prepare("SELECT slug, updated_at FROM items WHERE type = :type AND status = 'published'
        ORDER BY published_at DESC LIMIT 5000");
    $stmt->execute([':type' => $type]);
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}
