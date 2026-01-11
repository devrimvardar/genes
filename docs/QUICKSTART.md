# Quickstart Guide

Get up and running with Genes Framework in 5 minutes.

## Installation

1. **Download Genes**
   ```bash
   git clone https://github.com/devrimvardar/genes.git
   cd genes
   ```

2. **Copy Framework Files**
   ```bash
   # Copy to your project
   cp genes.php your-project/
   cp genes.js your-project/
   cp genes.css your-project/
   ```

That's it! No `composer install`, no `npm install`, no build step.

## Basic Setup

### Step 1: Create index.php

```php
<?php
require_once 'genes.php';

// Connect to SQLite database
g::run("db.connect", array(
    "driver" => "sqlite",
    "name" => "main",
    "database" => "data/app.db"
));

// Create schema (first time only)
g::run("db.createSchema", "main");

echo "Genes Framework Ready!";
?>
```

### Step 2: Run the Application

```bash
php -S localhost:8000
```

Visit `http://localhost:8000` - you should see "Genes Framework Ready!"

## Understanding the Schema

Genes uses **5 core tables** for multi-tenant applications:

### 1. clones (Master Table)
Projects or instances. Each clone is an isolated workspace.

```php
// Get your clone
$clones = g::run("db.select", "clones", array("state" => "active"));
$myClone = $clones[0];
```

### 2. persons (Users)
User accounts with `clone_id` for isolation.

```php
// Set clone context
g::run("db.setClone", $myClone['hash']);

// Create a user (auto-assigned to current clone)
$userHash = g::run("db.insert", "persons", array(
    "email" => "user@example.com",
    "name" => "John Doe",
    "type" => "user",
    "state" => "active"
));
```

### 3. items (Content)
Posts, pages, products, comments - anything with `clone_id`.

```php
// Create a blog post
$postHash = g::run("db.insert", "items", array(
    "type" => "post",
    "state" => "published",
    "title" => "My First Post",
    "text" => "Hello from Genes Framework!",
    "safe_url" => "my-first-post"
));
```

### 4. labels (Taxonomy)
Categories, tags, statuses with `clone_id`.

```php
// Create a category
$categoryHash = g::run("db.insert", "labels", array(
    "type" => "category",
    "key" => "tech",
    "name" => "Technology",
    "state" => "active"
));
```

### 5. events (Audit Log)
Track actions, analytics with `clone_id`.

```php
// Log an event
g::run("db.insert", "events", array(
    "type" => "post.viewed",
    "item_id" => $postHash,
    "data" => json_encode(array("ip" => $_SERVER['REMOTE_ADDR']))
));
```

## Multi-Tenant Pattern

**Clone isolation is automatic** once you set the clone context:

```php
// Set clone context
g::run("db.setClone", $cloneHash);

// All queries now auto-filter by clone_id
$posts = g::run("db.select", "items", array(
    "type" => "post",
    "state" => "published"
));
// Returns ONLY posts from the current clone

// Insert also auto-adds clone_id
$newPost = g::run("db.insert", "items", array(
    "type" => "post",
    "title" => "New Post"
    // clone_id automatically added!
));
```

## Common Operations

### Create

```php
// Hash is auto-generated if not provided
$hash = g::run("db.insert", "items", array(
    "type" => "post",
    "state" => "draft",
    "title" => "Draft Post"
));
```

### Read

```php
// Get by hash
$item = g::run("db.get", "items", $hash);

// Select with filters
$published = g::run("db.select", "items", array(
    "type" => "post",
    "state" => "published"
));
```

### Update

```php
g::run("db.update", "items", 
    array("state" => "published"),  // data to update
    array("hash" => $hash)           // where condition
);
```

### Delete (Soft)

```php
// Soft delete (sets state = 'deleted')
g::run("db.delete", "items", array("hash" => $hash));

// Hard delete
g::run("db.delete", "items", array("hash" => $hash), true);
```

## Using Both MySQL and SQLite

Genes supports multiple database connections:

```php
// SQLite for local data
g::run("db.connect", array(
    "driver" => "sqlite",
    "name" => "local",
    "database" => "data/local.db"
));

// MySQL for production
g::run("db.connect", array(
    "driver" => "mysql",
    "name" => "production",
    "host" => "localhost",
    "database" => "myapp",
    "username" => "root",
    "password" => ""
));

// Use specific connection
$items = g::run("db.select", "items", array(), "production");
```

## Configuration with config.json

Create `data/config.json`:

```json
{
  "clone": {
    "name": "My Blog",
    "domain": "myblog.local",
    "type": "blog",
    "state": "active",
    "default_language": "en"
  },
  "admin": {
    "email": "admin@example.com",
    "password": "changeme",
    "name": "Admin User",
    "type": "admin"
  },
  "database": {
    "driver": "sqlite",
    "database": "data/blog.db"
  }
}
```

Load configuration:

```php
g::run("config.load", "data/config.json");
$config = g::get("config");
```

## Next Steps

- **See Real Examples** → [Examples Guide](EXAMPLES.md)
- **Build an API** → [API Reference](API-REFERENCE.md)
- **Understand Architecture** → [Architecture Guide](ARCHITECTURE.md)
- **Multi-Tenant Apps** → [Multi-Tenancy Guide](MULTI-TENANCY.md)
- **Live Examples** → Check `/examples` folder

## Common Patterns

### Blog System

```php
// Create post
$post = g::run("db.insert", "items", array(
    "type" => "post",
    "state" => "published",
    "title" => "Hello World",
    "blurb" => "A short intro...",
    "text" => "Full post content here...",
    "safe_url" => "hello-world"
));

// Add category label
$cat = g::run("db.insert", "labels", array(
    "type" => "category",
    "key" => "tech",
    "name" => "Technology"
));

// Get recent posts
$posts = g::run("db.select", "items", 
    array("type" => "post", "state" => "published"),
    "main",
    array("limit" => 10, "order" => "created_at DESC")
);
```

### User Authentication

```php
// Register user
$userHash = g::run("auth.register", array(
    "email" => "user@example.com",
    "password" => "secret123",
    "name" => "John Doe"
));

// Login
$user = g::run("auth.login", "user@example.com", "secret123");

// Check authentication
if (g::run("auth.check")) {
    $currentUser = g::get("auth.user");
    echo "Welcome " . $currentUser['name'];
}
```

## Troubleshooting

**Database not created?**
- Check `data/` folder exists and is writable
- Verify `g::run("db.createSchema")` was called

**Clone context not working?**
- Ensure `g::run("db.setClone", $hash)` is called before queries
- Check clone hash is valid

**Functions not found?**
- Make sure `require_once 'genes.php'` is at the top
- Verify function namespace: `g::run("db.insert", ...)` not `db.insert(...)`

## Need Help?

- Check [Examples](EXAMPLES.md) for working code
- See [API Reference](API-REFERENCE.md) for all functions
- Read [Architecture](ARCHITECTURE.md) to understand internals
