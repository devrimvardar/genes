# Genes Framework Examples

Working examples demonstrating the **correct 5-table schema** and **multi-tenant patterns**.

## Overview

All examples use the **correct schema**:
- `clones` - Projects/instances (master multi-tenancy table)
- `persons` - Users with `clone_id`
- `items` - Content with `clone_id`
- `labels` - Taxonomy with `clone_id`
- `events` - Audit log with `clone_id`

## Examples

### 1. Database CRUD Operations
**Path**: `examples/1-database-crud/`  
**Run**: `php -S localhost:8000 -t examples/1-database-crud`

**Demonstrates**:
- ✅ Correct 5-table schema
- ✅ Both MySQL and SQLite drivers
- ✅ Complete CRUD operations (Create, Read, Update, Delete)
- ✅ Auto-generated hashes and timestamps
- ✅ Multi-tenant clone isolation
- ✅ Soft delete pattern

**Perfect for**: Understanding the basic database operations and schema structure.

---

### 2. Multi-Tenant Blog System
**Path**: `examples/2-blog-system/`  
**Run**: `php -S localhost:8001 -t examples/2-blog-system`

**Demonstrates**:
- ✅ Multi-clone architecture
- ✅ Items table for blog posts
- ✅ Labels for categories
- ✅ Person (author) relationships
- ✅ Events for view tracking
- ✅ URL routing with `safe_url`
- ✅ Clone context management

**Perfect for**: Building content-driven multi-tenant applications.

**URLs**:
- `http://localhost:8001` - Blog home (list of posts)
- `http://localhost:8001/?post=slug` - Single post view

---

### 3. REST API with Clone Context
**Path**: `examples/3-rest-api/`  
**Run**: `php -S localhost:8002 -t examples/3-rest-api`

**Demonstrates**:
- ✅ RESTful API endpoints (GET, POST, PUT, DELETE)
- ✅ Multi-tenant clone isolation
- ✅ JSON request/response handling
- ✅ CORS support
- ✅ Error handling
- ✅ Event logging

**Perfect for**: Building APIs for single-page applications or mobile apps.

**Endpoints**:
```bash
GET    /api/posts          # List posts
GET    /api/posts/:hash    # Get single post
POST   /api/posts          # Create post
PUT    /api/posts/:hash    # Update post
DELETE /api/posts/:hash    # Delete post
GET    /api/persons        # List users
GET    /api/labels         # List labels
GET    /api/events         # List events
```

**Test**:
```bash
curl http://localhost:8002/api/posts
curl -X POST http://localhost:8002/api/posts \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","text":"Content"}'
```

---

## Key Concepts Demonstrated

### 1. Clone-Based Multi-Tenancy

All examples use **clone context** for automatic data isolation:

```php
// Set clone context
g::run("db.setClone", $cloneHash);

// All queries now auto-filter by clone_id
$posts = g::run("db.select", "items", array("type" => "post"));
// SQL: SELECT * FROM items WHERE type='post' AND clone_id='...'
```

### 2. Correct Schema Usage

**Items Table** (not "nodes" or "clones"):
```php
g::run("db.insert", "items", array(
    "type" => "post",
    "title" => "My Post",
    "text" => "Content..."
    // clone_id auto-added!
));
```

**Labels Table** (not "links"):
```php
g::run("db.insert", "labels", array(
    "type" => "category",
    "key" => "tech",
    "name" => "Technology"
    // clone_id auto-added!
));
```

**Events Table** (audit log):
```php
g::run("db.insert", "events", array(
    "type" => "post.viewed",
    "item_id" => $postHash
    // clone_id auto-added!
));
```

### 3. Auto-Generated Fields

The framework **automatically** adds:
- `hash` - Unique identifier
- `clone_id` - Current clone context (if set)
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

### 4. Database Flexibility

All examples work with both **MySQL** and **SQLite**:

```php
// SQLite (default)
g::run("db.connect", array(
    "driver" => "sqlite",
    "name" => "main",
    "database" => "data/app.db"
));

// MySQL
g::run("db.connect", array(
    "driver" => "mysql",
    "name" => "main",
    "host" => "localhost",
    "database" => "myapp",
    "username" => "root",
    "password" => ""
));
```

## Common Patterns

### Create Clone

```php
$cloneHash = g::run("db.insert", "clones", array(
    "name" => "My Blog",
    "domain" => "myblog.local",
    "type" => "blog",
    "state" => "active"
));
g::run("db.setClone", $cloneHash);
```

### Create User

```php
$userHash = g::run("db.insert", "persons", array(
    "email" => "user@example.com",
    "name" => "John Doe",
    "type" => "user",
    "state" => "active"
    // clone_id auto-added from context!
));
```

### Create Blog Post

```php
$postHash = g::run("db.insert", "items", array(
    "type" => "post",
    "state" => "published",
    "title" => "My Post",
    "safe_url" => "my-post",
    "text" => "Post content...",
    "created_by" => $userHash
    // clone_id auto-added!
));
```

### Create Category

```php
$categoryHash = g::run("db.insert", "labels", array(
    "type" => "category",
    "key" => "tech",
    "name" => "Technology"
    // clone_id auto-added!
));
```

### Log Event

```php
g::run("db.insert", "events", array(
    "type" => "post.viewed",
    "item_id" => $postHash,
    "person_id" => $userHash,
    "data" => json_encode(array("ip" => $_SERVER['REMOTE_ADDR']))
    // clone_id auto-added!
));
```

## Requirements

- PHP 5.6+
- SQLite or MySQL/MariaDB
- No additional dependencies

## Next Steps

1. **Run an example** - Start with Example 1
2. **Read the docs** - Check `/docs` folder
3. **Build your own** - Use examples as templates

## Documentation

- [Quickstart Guide](../docs/QUICKSTART.md)
- [Architecture Overview](../docs/ARCHITECTURE.md)
- [Multi-Tenancy Guide](../docs/MULTI-TENANCY.md)
- [Database Schema](../DATABASE-SCHEMA.md)

## Support

- GitHub: https://github.com/devrimvardar/genes
- Issues: https://github.com/devrimvardar/genes/issues
