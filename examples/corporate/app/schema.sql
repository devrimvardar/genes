CREATE TABLE IF NOT EXISTS persons (
    id INTEGER PRIMARY KEY,
    hash TEXT NOT NULL UNIQUE,
    type TEXT NOT NULL DEFAULT 'user',
    status TEXT NOT NULL DEFAULT 'active',
    role TEXT NOT NULL DEFAULT 'editor',
    name TEXT NOT NULL DEFAULT '',
    email TEXT UNIQUE,
    password_hash TEXT,
    labels TEXT NOT NULL DEFAULT '[]',
    data TEXT NOT NULL DEFAULT '{}',
    created_by TEXT,
    updated_by TEXT,
    created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')),
    updated_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now'))
);

CREATE TABLE IF NOT EXISTS items (
    id INTEGER PRIMARY KEY,
    hash TEXT NOT NULL UNIQUE,
    type TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'draft',
    slug TEXT NOT NULL,
    title TEXT NOT NULL DEFAULT '{}',
    summary TEXT NOT NULL DEFAULT '{}',
    text TEXT NOT NULL DEFAULT '{}',
    image TEXT NOT NULL DEFAULT '',
    labels TEXT NOT NULL DEFAULT '[]',
    data TEXT NOT NULL DEFAULT '{}',
    owner TEXT,
    created_by TEXT,
    updated_by TEXT,
    published_at TEXT,
    created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')),
    updated_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now')),
    UNIQUE (type, slug)
);
CREATE INDEX IF NOT EXISTS items_list ON items (type, status, published_at);
CREATE INDEX IF NOT EXISTS items_created ON items (created_at);

CREATE TABLE IF NOT EXISTS labels (
    id INTEGER PRIMARY KEY,
    hash TEXT NOT NULL UNIQUE,
    key TEXT NOT NULL,
    value TEXT NOT NULL,
    type TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    text TEXT NOT NULL DEFAULT '{}',
    data TEXT NOT NULL DEFAULT '{}',
    UNIQUE (key, value)
);

CREATE TABLE IF NOT EXISTS events (
    id INTEGER PRIMARY KEY,
    hash TEXT NOT NULL UNIQUE,
    type TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'new',
    subject TEXT,
    ip TEXT NOT NULL DEFAULT '',
    data TEXT NOT NULL DEFAULT '{}',
    created_by TEXT,
    created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now'))
);
CREATE INDEX IF NOT EXISTS events_limit ON events (type, ip, created_at);
CREATE INDEX IF NOT EXISTS events_list ON events (type, status, created_at);
