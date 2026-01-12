# Genes Framework Examples

These examples demonstrate proper usage of the Genes Framework, showcasing its core principles and capabilities.

## Core Principles

1. **Separation of Concerns** - HTML stays HTML, PHP stays PHP, JavaScript stays JavaScript
2. **Config-Driven** - Database, routing, and translations in config.json
3. **Auto-Initialization** - Database auto-connects, schema auto-creates, routes auto-load
4. **Standard Database Schema** - Universal 5-table structure for all content
5. **Progressive Enhancement** - HTML works without JavaScript, enhanced when available
6. **Multi-Language Support** - Built-in i18n via config.json translations
7. **Built-in REST API** - Automatic CRUD endpoints for all tables at `/api/*`

## Examples Overview

### [Example 1: Landing Page](1-landing-page/)
**No Database | Multi-Language | Template Engine**

Demonstrates:
- Multi-language routing (en/tr/de)
- data-g-* template directives
- Partial template loading
- Config.json translations
- Responsive CSS framework
- Progressive forms

**Key Files:**
- `index.php` - Clone functions for routing
- `data/config.json` - Routes, views, translations
- `ui/index.html` - Main template with data-g-*
- `ui/partials/` - Reusable components

---

### [Example 2: Blog System](2-blog-system/)
**Database | Multi-Language | CRUD**

Demonstrates:
- Using Genes standard 5-table schema
- Blog posts in `items` table (type="post")
- Categories/tags in `labels` table
- Multi-language content via JSON labels
- Database queries with db.select, db.insert
- Pagination
- Related content

**Key Files:**
- `index.php` - Database setup, clone functions
- `data/config.json` - Routes for list/single views
- `ui/index.html` - Post list template
- `ui/post.html` - Single post template

**Database Schema:**
```php
// Posts stored as items
array(
    "type" => "post",
    "state" => "published",
    "title" => "Post Title",
    "safe_url" => "post-slug",
    "blurb" => "Excerpt...",
    "text" => "<p>Full content...</p>",
    "labels" => json_encode(["en", "tutorial"]),
    "meta" => json_encode(["author" => "John Doe"])
)
```

---

### [Example 3: Custom REST API](3-rest-api/)
**Manual API Implementation | CRUD Operations | Learning Exercise**

Demonstrates:
- Building custom REST APIs from scratch
- Manual HTTP method routing (GET, POST, PUT, DELETE)
- Direct database operations (db.insert, db.select, db.update, db.delete)
- Custom JSON request/response handling
- Route parameter extraction
- When to build custom vs use built-in

**Key Files:**
- `index.php` - Custom API handlers (~300 lines)
- `data/config.json` - View routing configuration
- `ui/index.html` - Interactive demo UI
- `ui/assets/app.js` - JavaScript API client

**Custom Endpoints:**
```
GET    /todos      - List all todos
GET    /todos/:id  - Get single todo
POST   /todos      - Create todo
PUT    /todos/:id  - Update todo
DELETE /todos/:id  - Delete todo
```

**Use Case:** Learning exercise, custom business logic, special requirements

---

### [Example 4: Built-in REST API](4-builtin-api/)
**Zero-Code API | Automatic CRUD | Production-Ready**

Demonstrates:
- Using Genes' built-in REST API system
- Zero custom code needed
- Automatic CRUD on all 5 tables
- Built-in filtering, pagination, search
- Standard response format
- Production-ready immediately

**Key Files:**
- `index.php` - Minimal setup (~50 lines)
- `data/config.json` - Basic configuration
- Framework handles everything else automatically

**Built-in Endpoints:**
```
GET    /api/items?filters[type]=todo  - List filtered items
GET    /api/items/:hash               - Get single item
POST   /api/items                     - Create item
PUT    /api/items/:hash               - Update item
DELETE /api/items/:hash               - Delete item
```

**Advanced Features:**
- Pagination: `?page=1&limit=10`
- Sorting: `?order=created_at DESC`
- Search: `?search=keyword`
- Works on all tables: `/api/items`, `/api/persons`, `/api/labels`, `/api/clones`, `/api/events`

**Use Case:** Standard CRUD, rapid prototyping, admin panels, mobile backends

---

## Genes Standard Database Schema

All examples use the same 5-table structure:

### 1. **clones** - Projects/Instances
Multi-tenant isolation with clone_id

### 2. **persons** - Users/Authors
User accounts, authentication, profiles

### 3. **items** - All Content
Universal content table for posts, pages, products, todos, etc.
- Use `type` field to categorize (post, page, product, todo)
- Use `state` field for status (published, draft, pending, completed)
- Use `labels` JSON for categories, tags, language
- Use `meta` JSON for flexible metadata
- Use `safe_url` for URL slugs

### 4. **labels** - Categories/Tags/Taxonomy
Reusable labels and categorization

### 5. **events** - Activity Log
Track user actions, API requests, changes

**Why This Matters:**
- No custom tables needed for different content types
- Consistent API across all content
- Easy to add new content types
- Multi-tenant by default
- Flexible extensibility via JSON fields

## Key Framework Features

### Auto-Initialization
Genes automatically handles setup during initialization:

**`data/config.json`:**
```json
{
    "database": {
        "enabled": true,
        "type": "sqlite",
        "database": "app.db"
    }
}
```

**`index.php`:**
```php
<?php
require_once 'genes.php';

// That's it! Framework auto-connects database,
// creates schema, and loads configuration
```

What happens automatically:
1. ✅ Database connection from config
2. ✅ Schema creation if needed
3. ✅ Routes loaded from views
4. ✅ Built-in `/api/*` endpoints activated
5. ✅ Template engine initialized

### Built-in REST API
Access all 5 tables via automatic endpoints:

```bash
# Works on all tables without custom code
GET    /api/items?filters[type]=todo
GET    /api/persons?filters[type]=user
GET    /api/labels?filters[type]=tag
POST   /api/items
PUT    /api/items/:hash
DELETE /api/items/:hash
```

Features:
- Filtering: `?filters[key]=value`
- Pagination: `?page=1&limit=10`
- Search: `?search=keyword`
- Sorting: `?order=created_at DESC`
- No custom code needed

## Template Engine Directives

All examples use data-g-* attributes:

```html
<!-- Conditional rendering -->
<div data-g-if="user_logged_in">Welcome back!</div>

<!-- Loops -->
<article data-g-for="post in posts">
    <h2 data-g-bind="post.title">Title</h2>
</article>

<!-- Partials -->
<div data-g-load="partials/header.html"></div>

<!-- Text binding -->
<span data-g-bind="user.name">Guest</span>

<!-- HTML binding -->
<div data-g-html="post.content"></div>

<!-- Attribute binding -->
<a data-g-attr="href:/blog/{{post.safe_url}}">Read</a>

<!-- Variable replacement -->
<p>Welcome, {{user.name}}!</p>
```

## Routing Patterns

### View-Based Routing (Recommended)
In `data/config.json`:
```json
{
    "views": {
        "Index": {
            "function": "clone.Index",
            "urls": { "en": "index" }
        },
        "About": {
            "function": "clone.About",
            "urls": { 
                "en": "about",
                "tr": "hakkinda",
                "de": "uber"
            }
        }
    }
}
```

In `index.php`:
```php
g::def("clone", array(
    "Index" => function ($bits, $lang, $path) {
        // Your logic here
    },
    "About" => function ($bits, $lang, $path) {
        // Your logic here
    }
));
```

### Dynamic Routes with Segments
URLs automatically split into segments:

```
/blog/my-first-post
```

Access in view function:
```php
"Blog" => function ($bits, $lang, $path) {
    $request = g::get("request");
    $segments = $request['route_segments']; // ["my-first-post"]
    $slug = $segments[0];
}
```

### HTTP Method Detection
Check method within view function:
```php
"Todos" => function ($bits, $lang, $path) {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // List todos
    } elseif ($method === 'POST') {
        // Create todo
    }
}
```

## CSS Framework

All examples use genes.css responsive system:

### Layout
```html
<div class="container">     <!-- Centered container -->
<div class="flex">          <!-- Flexbox layout -->
<div class="grid">          <!-- Grid layout -->
```

### Spacing
```html
<div class="p-1">   <!-- Padding: 0.5rem -->
<div class="p-2">   <!-- Padding: 1rem -->
<div class="p-3">   <!-- Padding: 1.5rem -->
<div class="p-4">   <!-- Padding: 2rem -->

<div class="m-1">   <!-- Margin: 0.5rem -->
<div class="mt-2">  <!-- Margin-top: 1rem -->
<div class="mb-3">  <!-- Margin-bottom: 1.5rem -->

<div class="py-2">  <!-- Vertical padding -->
<div class="px-3">  <!-- Horizontal padding -->
<div class="gap-2"> <!-- Grid/flex gap -->
```

### Components
```html
<button class="btn">Button</button>
<button class="btn btn-sm">Small Button</button>
<input class="input" type="text">
<div class="border">Bordered</div>
<div class="rounded">Rounded corners</div>
```

### Utilities
```html
<div class="text-center">Centered text</div>
<div class="text-muted">Muted text</div>
<p class="text-sm">Small text</p>
<div class="bg-light">Light background</div>
```

### Spacing (rem-based)
```html
<div class="pad-2">      <!-- padding: 2rem -->
<div class="mar-b-3">    <!-- margin-bottom: 3rem -->
<div class="gap-1">      <!-- gap: 1rem -->
```

### Layout
```html
<div class="flex flex-between flex-middle">
<div class="grid grid-3">  <!-- 3-column grid -->
```

## Running the Examples

1. **Start local server:**
   ```bash
   php -S localhost:8000
   ```

2. **Access examples:**
   - Example 1: http://localhost:8000/examples/1-landing-page/
   - Example 2: http://localhost:8000/examples/2-blog-system/
   - Example 3: http://localhost:8000/examples/3-rest-api/

3. **Try different languages:**
   - English: `/`
   - Turkish: `/tr`
   - German: `/de`

## Learning Path

1. **Start with Example 1** - Learn template engine and routing basics
2. **Move to Example 2** - Understand database integration
3. **Finish with Example 3** - Build APIs with Genes

## Common Patterns

### Initialize Database
```php
g::run("db.connect", array(
    "driver" => "sqlite",
    "database" => DATA_FOLDER . "app.db"
));
g::run("db.createSchema", "main");
```

### Insert Content
```php
g::run("db.insert", "items", array(
    "type" => "post",
    "state" => "published",
    "title" => "My Post",
    "safe_url" => "my-post",
    "text" => "<p>Content...</p>"
));
```

### Query Content
```php
$posts = g::run("db.select", "items", array(
    "type" => "post",
    "state" => "published"
));
```

### Update Content
```php
g::run("db.update", "items", $hash, array(
    "title" => "Updated Title"
));
```

### Delete Content
```php
g::run("db.delete", "items", $hash);
```

## Documentation

- [GENES-V2-CAPABILITIES.md](../GENES-V2-CAPABILITIES.md) - Complete framework reference
- [DATABASE-SCHEMA.md](../DATABASE-SCHEMA.md) - Schema documentation
- [ARCHITECTURE.md](../docs/ARCHITECTURE.md) - Framework architecture
- [QUICKSTART.md](../docs/QUICKSTART.md) - Quick start guide

## Need Help?

Each example has its own detailed README with:
- What it teaches
- How it works
- Database schema usage
- Key takeaways
- How to adapt for your project

Start with the example that matches your use case!

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
