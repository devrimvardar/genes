# Quickstart Guide

Get up and running with Genes Framework in 5 minutes.

## What is Genes?

Genes is a **single-file framework** consisting of just 3 files:
- **genes.php** (~6,400 lines) - Complete backend framework
- **genes.js** (~1,300 lines) - Frontend library  
- **genes.css** (~1,700 lines) - Responsive CSS framework

**Zero dependencies. No build tools. Just copy and code.**

## Installation

### Option 1: Download Files
```bash
# Download the 3 framework files
curl -O https://raw.githubusercontent.com/devrimvardar/genes/main/genes.php
curl -O https://raw.githubusercontent.com/devrimvardar/genes/main/genes.js
curl -O https://raw.githubusercontent.com/devrimvardar/genes/main/genes.css
```

### Option 2: Clone Repository
```bash
git clone https://github.com/devrimvardar/genes.git
cd genes
```

That's it! No `composer install`, no `npm install`, no build step.

## Hello World

### Create index.php
```php
<?php
require_once 'genes.php';
echo "Hello, Genes!";
```

### Run It
```bash
php -S localhost:8000
```

Visit `http://localhost:8000` - Done!

## Database Setup

### Step 1: Connect to Database

```php
<?php
require_once 'genes.php';

// SQLite (development)
g::run("db.connect", array(
    "driver" => "sqlite",
    "name" => "main",
    "database" => "data/app.db"
));

// MySQL (production)
g::run("db.connect", array(
    "driver" => "mysql",
    "name" => "main",
    "host" => "localhost",
    "database" => "myapp",
    "user" => "root",
    "pass" => ""
));
```

### Step 2: Create Schema

```php
// Creates 5 standard tables: clones, persons, items, labels, events
g::run("db.createSchema", "main");
```

### Step 3: Create a Clone (Project)

```php
// Create a project/site
$cloneHash = g::run("db.insert", "clones", array(
    "name" => "My App",
    "domain" => "myapp.local",
    "type" => "app",
    "state" => "active"
));

// Set as current clone (for multi-tenant isolation)
g::run("db.setClone", $cloneHash);
```

Now you're ready to insert content!

## The 5-Table Schema

Genes uses a **universal 5-table structure** for all content:

### 1. clones - Projects/Instances
The "master" table for multi-tenant isolation.

```php
// Each clone is a separate project
$blogClone = g::run("db.insert", "clones", array(
    "name" => "My Blog",
    "type" => "blog"
));
```

### 2. persons - Users/Authors
User accounts with `clone_id`.

```php
g::run("db.setClone", $blogClone);

$user = g::run("db.insert", "persons", array(
    "email" => "john@example.com",
    "name" => "John Doe",
    "alias" => "johndoe",
    "type" => "author"
));
```

### 3. items - All Content
Posts, pages, products, comments - **everything goes here**.

```php
// Blog post
$post = g::run("db.insert", "items", array(
    "type" => "post",           // Categorize by type
    "state" => "published",     // Draft, published, archived
    "title" => "My First Post",
    "safe_url" => "my-first-post",  // URL slug
    "blurb" => "Short excerpt...",
    "text" => "<p>Full content...</p>",
    "labels" => json_encode(array("tutorial", "php")),
    "meta" => json_encode(array("views" => 0)),
    "created_by" => $user
));

// Product (same table!)
$product = g::run("db.insert", "items", array(
    "type" => "product",
    "state" => "available",
    "title" => "Blue Widget",
    "blurb" => "A great widget",
    "data" => json_encode(array("price" => 29.99, "sku" => "BW001"))
));

// Comment (same table!)
$comment = g::run("db.insert", "items", array(
    "type" => "comment",
    "state" => "approved",
    "text" => "Great post!",
    "meta" => json_encode(array("post_id" => $post))
));
```

### 4. labels - Categories/Tags
Reusable taxonomy.

```php
$category = g::run("db.insert", "labels", array(
    "type" => "category",
    "key" => "tutorials",
    "name" => "Tutorials"
));
```

### 5. events - Activity Log
Track everything.

```php
g::run("db.insert", "events", array(
    "type" => "post_view",
    "item_id" => $post,
    "person_id" => $user,
    "ref1" => $_SERVER['REMOTE_ADDR']
));
```

## Key Concepts

### Multi-Tenant Isolation
```php
// Set current clone
g::run("db.setClone", $cloneHash);

// All queries are automatically filtered by clone_id
$posts = g::run("db.select", "items", array("type" => "post"));
// Only returns posts for current clone!
```

### JSON Fields for Flexibility
```php
// labels - Array of tags/categories
"labels" => json_encode(array("php", "tutorial", "beginner"))

// meta - Custom metadata
"meta" => json_encode(array(
    "views" => 1250,
    "reading_time" => "5 min",
    "featured" => true
))

// media - Images/files
"media" => json_encode(array(
    "thumbnail" => "thumb.jpg",
    "gallery" => array("img1.jpg", "img2.jpg")
))

// data - Structured data (products, forms, etc.)
"data" => json_encode(array(
    "price" => 29.99,
    "stock" => 50,
    "variants" => array(...)
))
```

### Auto-Generated Fields
When you insert, Genes automatically adds:
- `hash` - Unique 32-char ID (primary key)
- `clone_id` - Current clone (if `db.setClone` was called)
- `created_at` - Current timestamp
- `updated_at` - Current timestamp
- `created_by` - Current user (if set)

## CRUD Operations

### Create
```php
$hash = g::run("db.insert", "items", array(
    "type" => "post",
    "title" => "Hello World"
));
```

### Read
```php
// Select all posts
$posts = g::run("db.select", "items", array(
    "type" => "post",
    "state" => "published"
));

// Select single post
$post = g::run("db.select", "items", array(
    "hash" => $hash
));
```

### Update
```php
g::run("db.update", "items", $hash, array(
    "title" => "Updated Title",
    "state" => "archived"
));
```

### Delete
```php
g::run("db.delete", "items", $hash);
```

## Routing & Templates

### Create config.json
```json
{
    "views": {
        "Index": "ui/index.html",
        "About": "ui/about.html"
    },
    "routes": {
        "en": {
            "/": "Index",
            "/about": "About"
        }
    },
    "translations": {
        "en": {
            "welcome": "Welcome!",
            "title": "My Site"
        }
    }
}
```

### Create Clone Functions
```php
<?php
require_once 'genes.php';

g::def("clone", array(
    "Index" => function($bits, $lang, $path) {
        $data = array(
            "bits" => $bits,  // Contains translations, config
            "lang" => $lang,  // Current language
            "posts" => g::run("db.select", "items", array("type" => "post"))
        );
        echo g::run("tpl.renderView", "Index", $data);
    },
    
    "About" => function($bits, $lang, $path) {
        echo g::run("tpl.renderView", "About", array("bits" => $bits));
    }
));

g::run("route.handle");
```

### Create Template (ui/index.html)
```html
<!DOCTYPE html>
<html>
<head>
    <title data-g-bind="bits.translations.title">Site</title>
    <link rel="stylesheet" href="genes.css">
</head>
<body>
    <h1 data-g-bind="bits.translations.welcome">Welcome</h1>
    
    <!-- Loop through posts -->
    <article data-g-for="post in posts">
        <h2 data-g-bind="post.title">Post Title</h2>
        <p data-g-bind="post.blurb">Excerpt...</p>
        <a data-g-attr="href:/post/{{post.safe_url}}">Read More</a>
    </article>
</body>
</html>
```

## Template Directives

### data-g-if - Conditional Rendering
```html
<div data-g-if="logged_in">Welcome back!</div>
<div data-g-if="!logged_in">Please login</div>
<div data-g-if="status:active">Account active</div>
```

### data-g-for - Loops
```html
<div data-g-for="item in items">
    <h3>{{item.title}}</h3>
    <p>Index: {{_index}}, First: {{_first}}, Last: {{_last}}</p>
</div>
```

### data-g-load - Partial Templates
```html
<div data-g-load="partials/header.html"></div>
<div data-g-load="partials/footer.html"></div>
```

### data-g-bind - Text Content (Escaped)
```html
<h1 data-g-bind="post.title">Default</h1>
```

### data-g-html - Raw HTML (Not Escaped)
```html
<div data-g-html="post.content"></div>
```

### data-g-attr - Dynamic Attributes
```html
<a data-g-attr="href:/post/{{post.safe_url}}">Link</a>
<img data-g-attr="src:{{product.image}};alt:{{product.name}}">
```

### {{variable}} - Variable Replacement
```html
<p>Hello, {{user.name}}!</p>
```

## Next Steps

1. **Check the Examples**
   - [Example 1: Landing Page](../examples/1-landing-page/) - Template engine basics
   - [Example 2: Blog System](../examples/2-blog-system/) - Database integration
   - [Example 3: REST API](../examples/3-rest-api/) - Building APIs

2. **Read the Documentation**
   - [Architecture](ARCHITECTURE.md) - Framework design
   - [Database Schema](../DATABASE-SCHEMA.md) - Complete schema reference
   - [Multi-Tenancy](MULTI-TENANCY.md) - Multi-tenant patterns

3. **Start Building!**
   - Copy the framework files
   - Follow an example pattern
   - Customize for your needs

---

**Questions?** Check the [examples/](../examples/) folder for complete working applications.
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
