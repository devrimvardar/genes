# Genes Framework Examples

Working examples demonstrating the **correct 5-table schema**, **multi-tenant patterns**, and **no-database usage**.

## Overview

All database examples use the **correct schema**:
- `clones` - Projects/instances (master multi-tenancy table)
- `persons` - Users with `clone_id`
- `items` - Content with `clone_id`
- `labels` - Taxonomy with `clone_id`
- `events` - Audit log with `clone_id`

Non-database examples demonstrate using **config.json** and **HTML partials** for static sites.

## Examples

### Database Examples (1-3)

#### 1. Database CRUD Operations
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

#### 2. Multi-Tenant Blog System
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

#### 3. REST API with Clone Context
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

### No-Database Examples (4-5)

#### 4. Config-Based Data
**Path**: `examples/4-config-data/`  
**Run**: `php -S localhost:8003 -t examples/4-config-data`

**Demonstrates**:
- ✅ Using config.json as data source
- ✅ No database required
- ✅ Perfect for static content sites
- ✅ Simple configuration management
- ✅ JSON data handling

**Perfect for**: Small sites, settings pages, or when you don't need a database.

**Features**:
- Loads site data from `config.json`
- Displays features, stats, and metadata
- No database overhead
- Easy to deploy and maintain

---

#### 5. HTML Partials & Templating
**Path**: `examples/5-html-partials/`  
**Run**: `php -S localhost:8004 -t examples/5-html-partials`

**Demonstrates**:
- ✅ HTML partial template system
- ✅ Variable substitution (`{{variable}}`)
- ✅ Template composition
- ✅ Static site generation
- ✅ No database, no build tools

**Perfect for**: Static websites, landing pages, or template-driven sites.

**Features**:
- `loadPartial()` function for template loading
- Simple `{{variable}}` syntax
- Reusable header, nav, footer components
- Clean separation of content and layout

---

### Complete Website Example

#### 6. genes.one Website
**Path**: `website/`  
**Run**: `php -S localhost:8005 -t website`

**Demonstrates**:
- ✅ Complete, production-ready website
- ✅ Combines config.json + HTML partials
- ✅ Clean routing system
- ✅ Modern, responsive design
- ✅ Multiple pages (home, docs, examples, download, about)
- ✅ Professional layout components

**Perfect for**: Real-world reference implementation and learning by example.

**Features**:
- Full routing with `renderPage()` function
- Layout components (header, nav, footer)
- Page templates with variable substitution
- CSS Grid and Flexbox layouts
- Mobile-responsive design
- Interactive JavaScript features

**This is the actual genes.one website!** View it as both:
1. A complete working example
2. The official framework documentation site

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
