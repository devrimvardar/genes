# Architecture Overview

Understanding Genes Framework's design, philosophy, and implementation.

## Design Philosophy

### 1. AI-First Development
Genes is designed to be **readable by both humans and AI assistants**:
- Clear, descriptive function names
- Comprehensive inline documentation
- Logical namespace organization
- Self-documenting code structure

### 2. Zero Dependencies
No external packages or build tools:
- **No Composer** - Pure PHP, no autoloader needed
- **No npm** - Vanilla JavaScript (ES5+ compatible)
- **No Build Step** - Copy files and start coding

### 3. Multi-Tenant by Design
Built-in isolation via the **clone pattern**:
- Each `clone` is an independent workspace
- All data tables have `clone_id` for automatic filtering
- Single database, multiple isolated projects

### 4. Progressive Enhancement
Start simple, add complexity as needed:
- Basic CRUD → Full blog → Multi-tenant SaaS
- SQLite for prototyping → MySQL for production
- File-based auth → Database auth → OAuth

## Core Architecture

### The `g` Class

Everything in Genes is accessed through the global `g` class:

```php
// Define a namespace
g::def("myapp", array(
    "hello" => function($name) {
        return "Hello, $name!";
    }
));

// Run a function
$greeting = g::run("myapp.hello", "World");

// Get/set values
g::set("user.name", "John");
$name = g::get("user.name");
```

### Framework Phases

Genes is organized into **8 logical phases**:

```
Phase 1: Core          - Base utilities, path management
Phase 2: Config        - Configuration loading/saving
Phase 3: Logging       - Error handling, debugging
Phase 4: Routing       - URL parsing, dispatching
Phase 5: Auth          - Session, authentication
Phase 6: Cryptography  - Hashing, encryption, tokens
Phase 7: Database      - Multi-tenant CRUD operations
Phase 8: API           - RESTful API handlers
```

Each phase builds on the previous, creating a logical dependency chain.

## Database Schema

### The 5-Table Model

Genes uses **5 core tables** designed for multi-tenant applications:

#### 1. clones (Master Table)
**Purpose**: Projects, instances, or workspaces  
**Multi-Tenant**: No (this IS the tenant table)

```sql
CREATE TABLE clones (
    hash VARCHAR(32) PRIMARY KEY,
    type VARCHAR(50),              -- 'blog', 'ecommerce', 'platform'
    state VARCHAR(20),             -- 'active', 'suspended', 'deleted'
    name VARCHAR(255),
    domain VARCHAR(255),
    settings JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    created_by VARCHAR(32),
    updated_by VARCHAR(32)
);
```

**Use Cases**: 
- SaaS platforms with multiple customers
- Multi-site blog networks
- White-label applications

#### 2. persons (Users)
**Purpose**: User accounts  
**Multi-Tenant**: Yes (`clone_id`)

```sql
CREATE TABLE persons (
    hash VARCHAR(32) PRIMARY KEY,
    clone_id VARCHAR(32),          -- Links to clones table
    type VARCHAR(50),              -- 'user', 'admin', 'persona'
    state VARCHAR(20),             -- 'active', 'suspended', 'deleted'
    email VARCHAR(255) UNIQUE,
    alias VARCHAR(100),
    password VARCHAR(255),
    name VARCHAR(255),
    labels JSON,                   -- e.g., ['moderator', 'premium']
    meta JSON,                     -- Additional data
    media JSON,                    -- Profile images, etc.
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Use Cases**:
- User authentication
- Author profiles
- Customer accounts

#### 3. items (Content)
**Purpose**: Flexible content storage  
**Multi-Tenant**: Yes (`clone_id`)

```sql
CREATE TABLE items (
    hash VARCHAR(32) PRIMARY KEY,
    clone_id VARCHAR(32),
    type VARCHAR(50),              -- 'post', 'page', 'product', 'comment'
    state VARCHAR(20),             -- 'draft', 'published', 'deleted'
    title VARCHAR(500),
    link VARCHAR(1000),            -- External URL
    safe_url VARCHAR(500),         -- Internal slug
    blurb TEXT,                    -- Short description
    text LONGTEXT,                 -- Full content
    labels JSON,                   -- Categories, tags
    meta JSON,                     -- SEO, settings
    media JSON,                    -- Images, files
    data JSON,                     -- Type-specific data
    start_at TIMESTAMP,            -- Publish date, event start
    end_at TIMESTAMP,              -- Expiry, event end
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    created_by VARCHAR(32),
    updated_by VARCHAR(32)
);
```

**Use Cases**:
- Blog posts (`type='post'`)
- Static pages (`type='page'`)
- Products (`type='product'`)
- Comments (`type='comment'`)
- Events (`type='event'`)

#### 4. labels (Taxonomy)
**Purpose**: Categories, tags, and statuses  
**Multi-Tenant**: Yes (`clone_id`)

```sql
CREATE TABLE labels (
    hash VARCHAR(32) PRIMARY KEY,
    clone_id VARCHAR(32),
    type VARCHAR(50),              -- 'category', 'tag', 'item.state'
    state VARCHAR(20),             -- 'active', 'deleted'
    key VARCHAR(255),              -- Machine name
    name VARCHAR(255),             -- Display name
    labels JSON,                   -- Parent categories, hierarchy
    meta JSON,                     -- Color, icon, description
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Use Cases**:
- Blog categories (`type='category'`)
- Tags (`type='tag'`)
- Custom taxonomies
- Workflow statuses

#### 5. events (Audit Log)
**Purpose**: Activity tracking and analytics  
**Multi-Tenant**: Yes (`clone_id`)

```sql
CREATE TABLE events (
    hash VARCHAR(32) PRIMARY KEY,
    clone_id VARCHAR(32) NOT NULL,
    person_id VARCHAR(32),         -- Who did it
    item_id VARCHAR(32),           -- What was affected
    type VARCHAR(50) NOT NULL,     -- 'post.created', 'user.login'
    state VARCHAR(20),             -- 'active', 'processed'
    ref1 VARCHAR(255),             -- Custom reference 1
    ref2 VARCHAR(255),             -- Custom reference 2
    ref3 VARCHAR(255),             -- Custom reference 3
    ref4 VARCHAR(255),             -- Custom reference 4
    labels JSON,                   -- Event tags
    data JSON,                     -- Event payload
    created_at TIMESTAMP
);
```

**Use Cases**:
- Audit trails
- Analytics tracking
- Activity feeds
- Notification triggers

### Schema Flexibility

The `items` table is intentionally **generic and flexible**:

```php
// Blog post
g::run("db.insert", "items", array(
    "type" => "post",
    "title" => "My Blog Post",
    "text" => "Full content...",
    "safe_url" => "my-blog-post"
));

// Product
g::run("db.insert", "items", array(
    "type" => "product",
    "title" => "Widget Pro",
    "blurb" => "Amazing widget",
    "data" => json_encode(array(
        "price" => 29.99,
        "sku" => "WGT-001",
        "inventory" => 50
    ))
));

// Comment
g::run("db.insert", "items", array(
    "type" => "comment",
    "text" => "Great article!",
    "meta" => json_encode(array(
        "parent_id" => $postHash,
        "author_name" => "John"
    ))
));
```

## Multi-Tenant Isolation

### How Clone Context Works

1. **Set the clone context**:
   ```php
   g::run("db.setClone", $cloneHash);
   ```

2. **All queries auto-filter**:
   ```php
   // Automatically adds WHERE clone_id = $cloneHash
   $posts = g::run("db.select", "items", array(
       "type" => "post"
   ));
   ```

3. **Inserts auto-add clone_id**:
   ```php
   // clone_id automatically added
   g::run("db.insert", "items", array(
       "title" => "New Post"
   ));
   ```

### Multi-Clone Application Pattern

```php
// Detect clone from domain
$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

// Get clone by domain
$clones = g::run("db.select", "clones", array(
    "domain" => $domain,
    "state" => "active"
));

if (!empty($clones)) {
    $currentClone = $clones[0];
    
    // Set clone context - ALL queries now isolated
    g::run("db.setClone", $currentClone['hash']);
    
    // Now everything is scoped to this clone
    $users = g::run("db.select", "persons", array());
    $posts = g::run("db.select", "items", array("type" => "post"));
}
```

## Database Operations

### Auto-Generated Fields

The framework **automatically manages** these fields:

```php
// When inserting
$hash = g::run("db.insert", "items", array(
    "title" => "Test"
));

// Framework adds:
// - hash (auto-generated unique ID)
// - clone_id (from current clone context)
// - created_at (current timestamp)
// - updated_at (current timestamp)
```

### Soft Delete Pattern

By default, deletes are **soft** (sets `state = 'deleted'`):

```php
// Soft delete (recommended)
g::run("db.delete", "items", array("hash" => $hash));
// Sets state = 'deleted', keeps record

// Hard delete (permanent)
g::run("db.delete", "items", array("hash" => $hash), true);
// Removes record from database
```

### JSON Columns

Several columns store **JSON data** for flexibility:

```php
// Storing structured data
g::run("db.insert", "items", array(
    "type" => "product",
    "title" => "Widget",
    "data" => json_encode(array(
        "price" => 29.99,
        "currency" => "USD",
        "dimensions" => array(
            "length" => 10,
            "width" => 5,
            "height" => 2
        )
    ))
));

// Framework auto-decodes JSON on select
$item = g::run("db.get", "items", $hash);
print_r($item['data']); // Already decoded to array
```

## API Layer

### RESTful API Pattern

```php
// Define API endpoint
g::run("route.define", "/api/posts", function($params) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // List posts
        $posts = g::run("db.select", "items", array(
            "type" => "post",
            "state" => "published"
        ));
        
        header('Content-Type: application/json');
        echo json_encode(array(
            "success" => true,
            "data" => $posts
        ));
    }
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Create post
        $data = json_decode(file_get_contents('php://input'), true);
        
        $hash = g::run("db.insert", "items", array(
            "type" => "post",
            "title" => $data['title'],
            "text" => $data['text']
        ));
        
        header('Content-Type: application/json');
        echo json_encode(array(
            "success" => true,
            "hash" => $hash
        ));
    }
});
```

### Automatic API Handling

Routes starting with `/api/` are **automatically handled**:

```php
// This is called automatically for /api/* routes
g::run("route.handle");
```

## Authentication System

### Dual-Mode Authentication

Genes supports **both file-based and database-based** authentication:

```php
// Database authentication (recommended)
$user = g::run("auth.login", "user@example.com", "password");

if ($user) {
    // User authenticated
    $currentUser = g::get("auth.user");
    echo "Welcome " . $currentUser['name'];
}

// Check if authenticated
if (g::run("auth.check")) {
    // User is logged in
}

// Logout
g::run("auth.logout");
```

## Configuration System

### config.json Structure

```json
{
  "clone": {
    "name": "My Application",
    "domain": "myapp.local",
    "type": "platform",
    "state": "active",
    "default_language": "en",
    "settings": {
      "timezone": "UTC",
      "date_format": "Y-m-d"
    }
  },
  "admin": {
    "email": "admin@example.com",
    "password": "changeme",
    "name": "Administrator",
    "type": "admin",
    "state": "active"
  },
  "database": {
    "driver": "sqlite",
    "database": "data/app.db"
  },
  "security": {
    "salt": "random-salt-here",
    "secret": "random-secret-here"
  }
}
```

### Auto-Seeding

When you run `g::run("db.createSchema")`, the framework **automatically**:

1. Creates clone record from `config.clone`
2. Creates admin user from `config.admin`
3. Creates generic labels (post.state.published, etc.)
4. Marks setup as complete

**This only runs once** - safe to call repeatedly.

## Performance Considerations

### Indexes

The schema includes **strategic indexes** for common queries:

- `clone_id, type, state` - Main filtering
- `clone_id, safe_url` - URL lookups
- `created_at` - Chronological sorting
- `email` - User lookups
- Fulltext indexes on `items` (title, blurb, text)

### Connection Pooling

Database connections are **reused automatically**:

```php
// First call creates connection
g::run("db.connect", array("name" => "main", ...));

// Subsequent calls reuse connection
$pdo = g::run("db.connection", "main");
```

### JSON Column Performance

JSON columns provide flexibility but consider:
- Use for **variable data** (settings, metadata)
- Use regular columns for **frequently queried** fields
- MySQL 5.7+ has native JSON functions
- SQLite stores as TEXT but works fine

## Extending Genes

### Custom Namespaces

Add your own functionality:

```php
g::def("blog", array(
    "getRecentPosts" => function($limit = 10) {
        return g::run("db.select", "items",
            array("type" => "post", "state" => "published"),
            "main",
            array("limit" => $limit, "order" => "created_at DESC")
        );
    },
    
    "getPostByUrl" => function($url) {
        $results = g::run("db.select", "items", array(
            "type" => "post",
            "safe_url" => $url,
            "state" => "published"
        ));
        
        return !empty($results) ? $results[0] : false;
    }
));

// Use your functions
$posts = g::run("blog.getRecentPosts", 5);
$post = g::run("blog.getPostByUrl", "hello-world");
```

### Custom Item Types

Create specialized content types:

```php
// Product type
g::run("db.insert", "items", array(
    "type" => "product",
    "title" => "Widget Pro",
    "blurb" => "Professional widget",
    "data" => json_encode(array(
        "price" => 99.99,
        "sku" => "WGT-PRO-001",
        "stock" => 25,
        "weight" => 1.5,
        "dimensions" => array("l" => 10, "w" => 5, "h" => 3)
    ))
));

// Event type
g::run("db.insert", "items", array(
    "type" => "event",
    "title" => "Tech Conference 2026",
    "blurb" => "Annual technology conference",
    "start_at" => "2026-06-15 09:00:00",
    "end_at" => "2026-06-17 17:00:00",
    "data" => json_encode(array(
        "venue" => "Convention Center",
        "capacity" => 500,
        "tickets_sold" => 327
    ))
));
```

## Best Practices

### 1. Always Set Clone Context

```php
// At application bootstrap
$clone = g::run("db.get", "clones", $cloneHash);
g::run("db.setClone", $clone['hash']);
```

### 2. Use Soft Deletes

```php
// Recommended
g::run("db.delete", "items", array("hash" => $hash));

// Only if truly needed
g::run("db.delete", "items", array("hash" => $hash), true);
```

### 3. Leverage Events for Tracking

```php
// Log important actions
g::run("db.insert", "events", array(
    "type" => "post.published",
    "item_id" => $postHash,
    "person_id" => $authorHash,
    "data" => json_encode(array(
        "title" => $post['title'],
        "published_at" => date('Y-m-d H:i:s')
    ))
));
```

### 4. Use Labels for Taxonomy

```php
// Create hierarchical categories
$parentCat = g::run("db.insert", "labels", array(
    "type" => "category",
    "key" => "technology",
    "name" => "Technology"
));

$childCat = g::run("db.insert", "labels", array(
    "type" => "category",
    "key" => "php",
    "name" => "PHP",
    "labels" => json_encode(array($parentCat))
));
```

## Next Steps

- **See Working Code** → [Examples](EXAMPLES.md)
- **Function Reference** → [API Reference](API-REFERENCE.md)
- **Quick Start** → [Quickstart Guide](QUICKSTART.md)
- **Multi-Tenancy** → [Multi-Tenancy Guide](MULTI-TENANCY.md)
