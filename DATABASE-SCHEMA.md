# Genes Framework - Database Schema

> **Complete reference for the multi-tenant 5-table schema**

---

## 🎯 Schema Overview

Genes uses a **multi-tenant architecture** where multiple projects ("clones") share a single database. Each clone is isolated via `clone_id` foreign keys.

### Supported Databases

- ✅ **MySQL** / MariaDB (recommended)
- ✅ **SQLite** (great for development/small apps)
- 🔜 **PostgreSQL** (planned)

---

## 📊 The 5 Tables

### 1. **clones** - Projects/Instances

Represents individual projects or applications sharing the database.

| Column | Type | Description |
|--------|------|-------------|
| `hash` | VARCHAR(32) | Primary key |
| `type` | VARCHAR(50) | Clone type (platform, app, site) |
| `state` | VARCHAR(20) | Status (active, suspended, deleted) |
| `name` | VARCHAR(255) | Display name |
| `domain` | VARCHAR(255) | Primary domain |
| `settings` | JSON | Configuration data |
| `created_at` | TIMESTAMP | Creation time |
| `updated_at` | TIMESTAMP | Last update |
| `created_by` | VARCHAR(32) | Creator hash |
| `updated_by` | VARCHAR(32) | Last updater hash |

**Example:**
```php
// Create a clone (project)
$cloneHash = g::run("db.insert", "clones", array(
    "name" => "My Blog",
    "domain" => "myblog.com",
    "type" => "blog",
    "state" => "active",
    "settings" => json_encode(array(
        "theme" => "dark",
        "language" => "en"
    ))
));

// Set as active clone context
g::run("db.setClone", $cloneHash);
```

---

### 2. **persons** - Users/Accounts

Users, administrators, authors - anyone with an account.

| Column | Type | Description |
|--------|------|-------------|
| `hash` | VARCHAR(32) | Primary key |
| `clone_id` | VARCHAR(32) | Parent clone |
| `type` | VARCHAR(50) | User type (user, admin, author) |
| `state` | VARCHAR(20) | Status (active, suspended, deleted) |
| `email` | VARCHAR(255) | Email (unique) |
| `alias` | VARCHAR(100) | Username/handle |
| `password` | VARCHAR(255) | Hashed password |
| `name` | VARCHAR(255) | Display name |
| `labels` | JSON | Tag array |
| `meta` | JSON | Custom metadata |
| `media` | JSON | Avatar, images |
| `created_at` | TIMESTAMP | Registration time |
| `updated_at` | TIMESTAMP | Last update |
| `created_by` | VARCHAR(32) | Creator hash |
| `updated_by` | VARCHAR(32) | Last updater hash |

**Example:**
```php
// Create a user
$userHash = g::run("db.insert", "persons", array(
    "email" => "user@example.com",
    "alias" => "johndoe",
    "name" => "John Doe",
    "password" => g::run("auth.hash", "password123"),
    "type" => "user",
    "state" => "active",
    "meta" => json_encode(array("role" => "member"))
));
```

---

### 3. **items** - Content

Posts, pages, comments, products - any content.

| Column | Type | Description |
|--------|------|-------------|
| `hash` | VARCHAR(32) | Primary key |
| `clone_id` | VARCHAR(32) | Parent clone |
| `type` | VARCHAR(50) | Content type (post, page, comment, product) |
| `state` | VARCHAR(20) | Status (draft, published, deleted) |
| `title` | VARCHAR(500) | Title/headline |
| `link` | VARCHAR(1000) | External URL |
| `safe_url` | VARCHAR(500) | Slug/permalink (unique per clone) |
| `blurb` | TEXT | Excerpt/summary |
| `text` | LONGTEXT | Main content |
| `labels` | JSON | Tags/categories |
| `meta` | JSON | Custom metadata (views, likes, etc.) |
| `media` | JSON | Images, attachments |
| `data` | JSON | Structured data |
| `start_at` | TIMESTAMP | Publish/event start time |
| `end_at` | TIMESTAMP | Event end time |
| `created_at` | TIMESTAMP | Creation time |
| `updated_at` | TIMESTAMP | Last update |
| `created_by` | VARCHAR(32) | Author hash |
| `updated_by` | VARCHAR(32) | Last updater hash |

**Example:**
```php
// Create a blog post
$postHash = g::run("db.insert", "items", array(
    "type" => "post",
    "state" => "published",
    "title" => "Getting Started with Genes",
    "safe_url" => "getting-started",
    "blurb" => "Learn the basics of Genes Framework",
    "text" => "Full article content here...",
    "labels" => json_encode(array("tutorial", "beginner")),
    "meta" => json_encode(array("views" => 0, "reading_time" => 5)),
    "created_by" => $userHash
));
```

---

### 4. **labels** - Taxonomy/Categories

Reusable labels, tags, categories, types.

| Column | Type | Description |
|--------|------|-------------|
| `hash` | VARCHAR(32) | Primary key |
| `clone_id` | VARCHAR(32) | Parent clone |
| `type` | VARCHAR(50) | Label type (tag, category, status) |
| `state` | VARCHAR(20) | Status (active, archived) |
| `key` | VARCHAR(255) | Unique identifier |
| `name` | VARCHAR(255) | Display name (can use dot notation) |
| `labels` | JSON | Parent labels |
| `meta` | JSON | Custom metadata |
| `created_at` | TIMESTAMP | Creation time |
| `updated_at` | TIMESTAMP | Last update |
| `created_by` | VARCHAR(32) | Creator hash |
| `updated_by` | VARCHAR(32) | Last updater hash |

**Example:**
```php
// Create a category label
$labelHash = g::run("db.insert", "labels", array(
    "type" => "category",
    "key" => "tutorials",
    "name" => "Tutorials",
    "state" => "active",
    "meta" => json_encode(array("color" => "#3498db"))
));

// Create a status label with dot notation
g::run("db.insert", "labels", array(
    "type" => "item.state",
    "key" => "published",
    "name" => "item.state.published",
    "state" => "active"
));
```

---

### 5. **events** - Audit Log/Analytics

Track user actions, system events, analytics.

| Column | Type | Description |
|--------|------|-------------|
| `hash` | VARCHAR(32) | Primary key |
| `clone_id` | VARCHAR(32) | Parent clone (required) |
| `person_id` | VARCHAR(32) | Related user |
| `item_id` | VARCHAR(32) | Related item |
| `type` | VARCHAR(50) | Event type (login, view, purchase) |
| `state` | VARCHAR(20) | Status (active, processed) |
| `ref1` | VARCHAR(255) | Reference field 1 |
| `ref2` | VARCHAR(255) | Reference field 2 |
| `ref3` | VARCHAR(255) | Reference field 3 |
| `ref4` | VARCHAR(255) | Reference field 4 |
| `labels` | JSON | Event tags |
| `data` | JSON | Event payload |
| `created_at` | TIMESTAMP | Event time |
| `updated_at` | TIMESTAMP | Last update |

**Example:**
```php
// Log a page view
g::run("db.insert", "events", array(
    "type" => "page.view",
    "person_id" => $userHash,
    "item_id" => $postHash,
    "ref1" => $_SERVER['REMOTE_ADDR'],
    "ref2" => $_SERVER['HTTP_USER_AGENT'],
    "data" => json_encode(array(
        "referer" => $_SERVER['HTTP_REFERER'] ?? null,
        "duration" => 45
    ))
));
```

---

## 🏗️ Multi-Tenant Pattern

### How Multi-Tenancy Works

1. **Create clones** for different projects
2. **Set active clone** using `g::run("db.setClone", $hash)`
3. **All queries auto-filter** by clone_id

```php
// Setup Clone A (Blog)
$blogClone = g::run("db.insert", "clones", array(
    "name" => "Tech Blog",
    "domain" => "techblog.com"
));

g::run("db.setClone", $blogClone);

// These users belong to Blog clone
g::run("db.insert", "persons", array(...)); // auto-gets clone_id
g::run("db.insert", "items", array(...));   // auto-gets clone_id

// Setup Clone B (Shop)
$shopClone = g::run("db.insert", "clones", array(
    "name" => "My Shop",
    "domain" => "myshop.com"
));

g::run("db.setClone", $shopClone);

// These belong to Shop clone
g::run("db.insert", "persons", array(...)); // different clone_id
g::run("db.insert", "items", array(...));   // different clone_id

// Queries auto-filter by active clone
$users = g::run("db.select", "persons"); // Only Shop users
$products = g::run("db.select", "items"); // Only Shop items
```

---

## 💾 Database Setup Examples

### MySQL

```php
<?php
require_once './genes.php';

// Connect to MySQL
g::run("db.connect", array(
    "driver" => "mysql",
    "host" => "localhost",
    "database" => "genes_db",
    "username" => "root",
    "password" => ""
));

// Create schema
g::run("db.createSchema");
```

### SQLite

```php
<?php
require_once './genes.php';

// Connect to SQLite
g::run("db.connect", array(
    "driver" => "sqlite",
    "database" => "data/genes.db"
));

// Create schema
g::run("db.createSchema");
```

---

## 🔍 Common Queries

### Select by Clone

```php
// Auto-filtered by active clone
$posts = g::run("db.select", "items", array(
    "type" => "post",
    "state" => "published"
));
```

### Cross-Clone Query

```php
// Get specific clone's data
$users = g::run("db.select", "persons", array(
    "clone_id" => $specificCloneHash
));
```

### Join Pattern

```php
// Get items with author info
$items = g::run("db.select", "items", array("type" => "post"));

foreach ($items as &$item) {
    if ($item["created_by"]) {
        $authors = g::run("db.select", "persons", array(
            "hash" => $item["created_by"]
        ));
        $item["author"] = $authors ? $authors[0] : null;
    }
}
```

---

## 📝 Field Conventions

### Common Fields (All Tables)

- `hash` - 32-char unique ID (auto-generated)
- `type` - Subtype classifier
- `state` - Status/lifecycle
- `created_at` - ISO timestamp
- `updated_at` - ISO timestamp
- `created_by` - Creator person hash
- `updated_by` - Updater person hash

### Multi-Tenant Fields

- `clone_id` - Parent clone (all except clones table)

### JSON Fields

Store flexible data as JSON:
- `settings` - Configuration
- `labels` - Tag arrays
- `meta` - Custom metadata
- `media` - Files/images
- `data` - Structured data

---

## 🔒 Indexes

### MySQL Indexes

- Primary keys on `hash`
- Clone isolation: `(clone_id, state)`, `(clone_id, type, state)`
- Lookups: `email`, `alias`, `safe_url`, `domain`
- Time-based: `created_at`, `(person_id, created_at)`
- Full-text: `(title, blurb, text)` on items

### SQLite Indexes

Same coverage via explicit CREATE INDEX statements.

---

## 🚀 Best Practices

1. **Always set clone context** early in your application
2. **Use JSON fields** for flexible/evolving data
3. **Soft delete** via state field (not hard delete)
4. **Log to events** for audit trails
5. **Use labels** for taxonomy instead of separate tables
6. **Leverage indexes** for clone_id queries

---

## 📚 See Also

- [Quick Reference](docs/GENES-QUICKREF.md) - API cheat sheet
- [Examples](examples/) - Working code samples
- [AI Guide](docs/GENES-AI-FRAMEWORK.md) - Complete documentation
