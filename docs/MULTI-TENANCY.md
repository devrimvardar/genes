# Multi-Tenancy Guide

Building multi-tenant applications with Genes Framework's clone-based architecture.

## What is Multi-Tenancy?

**Multi-tenancy** allows a single application instance to serve multiple isolated customers (tenants).

### Traditional Approach
- Separate database per customer (expensive, hard to maintain)
- Separate application instances (resource intensive)

### Genes Approach
- **Single database** with clone-based isolation
- **Automatic filtering** via `clone_id`
- **Per-clone customization** via settings

## The Clone Pattern

In Genes, a **clone** represents an isolated workspace:

```php
// Clone = Project, Customer, Site, Workspace
clone {
    hash: "abc123...",
    name: "Acme Corp Blog",
    domain: "acme.myblogging.com",
    type: "blog",
    state: "active"
}
```

### Key Concept

Every data table (except `clones`) has a `clone_id` field:

```
clones (master table)
├── persons (clone_id → clones.hash)
├── items (clone_id → clones.hash)
├── labels (clone_id → clones.hash)
└── events (clone_id → clones.hash)
```

## Setting Clone Context

### Basic Pattern

```php
// 1. Detect which clone to use (from domain, subdomain, or path)
$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

// 2. Find the clone
$clones = g::run("db.select", "clones", array(
    "domain" => $domain,
    "state" => "active"
));

if (!empty($clones)) {
    $currentClone = $clones[0];
    
    // 3. Set clone context
    g::run("db.setClone", $currentClone['hash']);
    
    // Now all queries are automatically scoped to this clone!
}
```

### What Happens Automatically

Once clone context is set:

```php
// SELECT queries auto-add: WHERE clone_id = 'abc123...'
$posts = g::run("db.select", "items", array("type" => "post"));
// SQL: SELECT * FROM items WHERE type='post' AND clone_id='abc123...'

// INSERT queries auto-add: clone_id = 'abc123...'
$hash = g::run("db.insert", "items", array("title" => "New Post"));
// Automatically includes clone_id in the insert
```

## Multi-Tenant Patterns

### Pattern 1: Subdomain-Based

Each customer gets a subdomain: `acme.myapp.com`, `widgetco.myapp.com`

```php
<?php
require_once 'genes.php';

// Extract subdomain
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$parts = explode('.', $host);
$subdomain = $parts[0]; // 'acme' or 'widgetco'

// Full domain for lookup
$domain = $host; // 'acme.myapp.com'

// Connect to database
g::run("db.connect", array(
    "driver" => "mysql",
    "name" => "main",
    "host" => "localhost",
    "database" => "multitenantapp",
    "username" => "root",
    "password" => ""
));

// Find clone by domain
$clones = g::run("db.select", "clones", array(
    "domain" => $domain,
    "state" => "active"
));

if (empty($clones)) {
    die("Site not found");
}

$currentClone = $clones[0];

// Set clone context - everything now isolated
g::run("db.setClone", $currentClone['hash']);

// All queries scoped to this subdomain's clone
$posts = g::run("db.select", "items", array(
    "type" => "post",
    "state" => "published"
));

foreach ($posts as $post) {
    echo "<h2>" . htmlspecialchars($post['title']) . "</h2>";
}
?>
```

### Pattern 2: Path-Based

Single domain with path segments: `myapp.com/acme`, `myapp.com/widgetco`

```php
<?php
require_once 'genes.php';

// Parse URL
$request = g::run("route.parseUrl");
$path = $request['path'];

// Extract tenant from path: /acme/posts → 'acme'
$segments = explode('/', trim($path, '/'));
$tenantKey = isset($segments[0]) ? $segments[0] : '';

if (empty($tenantKey)) {
    die("Tenant not specified");
}

// Connect to database
g::run("db.connect", array(
    "driver" => "sqlite",
    "name" => "main",
    "database" => "data/multitenant.db"
));

// Find clone by custom identifier (stored in settings)
$allClones = g::run("db.select", "clones", array("state" => "active"));

$currentClone = null;
foreach ($allClones as $clone) {
    $settings = json_decode($clone['settings'], true);
    if (isset($settings['url_key']) && $settings['url_key'] === $tenantKey) {
        $currentClone = $clone;
        break;
    }
}

if (!$currentClone) {
    die("Tenant not found");
}

// Set clone context
g::run("db.setClone", $currentClone['hash']);

// Remaining path segments for routing
$remainingPath = '/' . implode('/', array_slice($segments, 1));
// Now route to /posts, /about, etc.
?>
```

### Pattern 3: Database Detection

Determine clone from authenticated user:

```php
<?php
require_once 'genes.php';

session_start();

g::run("db.connect", array(
    "driver" => "mysql",
    "name" => "main",
    "host" => "localhost",
    "database" => "myapp",
    "username" => "root",
    "password" => ""
));

// Authenticate user (doesn't require clone context)
if (!g::run("auth.check")) {
    // Redirect to login
    header("Location: /login");
    exit;
}

$user = g::get("auth.user");

// Set clone from user's clone_id
g::run("db.setClone", $user['clone_id']);

// Now all queries scoped to user's clone
$posts = g::run("db.select", "items", array("type" => "post"));
?>
```

## Creating Clones

### Programmatic Creation

```php
// Create new clone for a customer
$cloneHash = g::run("db.insert", "clones", array(
    "name" => "Acme Corporation Blog",
    "domain" => "acme.myblogging.com",
    "type" => "blog",
    "state" => "active",
    "settings" => json_encode(array(
        "timezone" => "America/New_York",
        "theme" => "professional",
        "language" => "en",
        "plan" => "premium"
    ))
));

// Set context to new clone
g::run("db.setClone", $cloneHash);

// Create default admin for this clone
$adminHash = g::run("db.insert", "persons", array(
    "email" => "admin@acme.com",
    "password" => g::run("crypt.hashPassword", "temppassword"),
    "name" => "Acme Admin",
    "type" => "admin",
    "state" => "active"
));

// Create default categories for this clone
$categories = array("News", "Updates", "Announcements");
foreach ($categories as $catName) {
    g::run("db.insert", "labels", array(
        "type" => "category",
        "key" => strtolower($catName),
        "name" => $catName,
        "state" => "active"
    ));
}

// Log clone creation event
g::run("db.insert", "events", array(
    "type" => "clone.created",
    "ref1" => $cloneHash,
    "data" => json_encode(array(
        "domain" => "acme.myblogging.com",
        "created_by" => "system"
    ))
));
```

### Clone Provisioning Service

```php
g::def("multitenant", array(
    "createClone" => function($data) {
        // Validate required fields
        if (empty($data['name']) || empty($data['domain'])) {
            return array("success" => false, "error" => "Name and domain required");
        }
        
        // Check domain availability
        $existing = g::run("db.select", "clones", array(
            "domain" => $data['domain']
        ));
        
        if (!empty($existing)) {
            return array("success" => false, "error" => "Domain already taken");
        }
        
        // Create clone
        $cloneHash = g::run("db.insert", "clones", array(
            "name" => $data['name'],
            "domain" => $data['domain'],
            "type" => isset($data['type']) ? $data['type'] : 'platform',
            "state" => "active",
            "settings" => json_encode(isset($data['settings']) ? $data['settings'] : array())
        ));
        
        if (!$cloneHash) {
            return array("success" => false, "error" => "Failed to create clone");
        }
        
        // Set context to new clone
        g::run("db.setClone", $cloneHash);
        
        // Create admin user
        if (!empty($data['admin_email'])) {
            $password = isset($data['admin_password']) ? $data['admin_password'] : g::run("crypt.token", 16);
            
            g::run("db.insert", "persons", array(
                "email" => $data['admin_email'],
                "password" => g::run("crypt.hashPassword", $password),
                "name" => isset($data['admin_name']) ? $data['admin_name'] : 'Admin',
                "type" => "admin",
                "state" => "active"
            ));
        }
        
        // Create default labels
        $defaults = array(
            array("type" => "category", "key" => "general", "name" => "General"),
            array("type" => "category", "key" => "news", "name" => "News")
        );
        
        foreach ($defaults as $label) {
            g::run("db.insert", "labels", $label);
        }
        
        // Log creation
        g::run("db.insert", "events", array(
            "type" => "clone.provisioned",
            "ref1" => $cloneHash,
            "data" => json_encode($data)
        ));
        
        return array(
            "success" => true,
            "clone_hash" => $cloneHash,
            "domain" => $data['domain']
        );
    }
));

// Use it
$result = g::run("multitenant.createClone", array(
    "name" => "Acme Corp",
    "domain" => "acme.myapp.com",
    "type" => "blog",
    "admin_email" => "admin@acme.com",
    "admin_password" => "changeme",
    "settings" => array(
        "theme" => "default",
        "language" => "en"
    )
));
```

## Cross-Clone Operations

### Accessing Data Across Clones

Sometimes you need to query across clones (admin dashboards, reporting):

```php
// Temporarily disable clone filtering
g::set("db.current_clone", null);

// Now queries return data from ALL clones
$allPosts = g::run("db.select", "items", array(
    "type" => "post",
    "state" => "published"
));

// Group by clone
$postsByClone = array();
foreach ($allPosts as $post) {
    $cloneId = $post['clone_id'];
    if (!isset($postsByClone[$cloneId])) {
        $postsByClone[$cloneId] = array();
    }
    $postsByClone[$cloneId][] = $post;
}

// Restore clone context
g::run("db.setClone", $originalCloneHash);
```

### Clone-Specific Queries

Explicitly query a specific clone:

```php
// Current clone context
$currentClone = g::get("db.current_clone");

// Query different clone
$otherPosts = g::run("db.select", "items", array(
    "clone_id" => $otherCloneHash,
    "type" => "post"
), "main");

// Context unchanged - still set to currentClone
```

## Clone Settings & Customization

### Storing Clone-Specific Settings

```php
// Update clone settings
$clone = g::run("db.get", "clones", $cloneHash);
$settings = json_decode($clone['settings'], true);

$settings['theme'] = 'dark-mode';
$settings['features'] = array(
    'comments' => true,
    'social_share' => true,
    'analytics' => false
);

g::run("db.update", "clones",
    array("settings" => json_encode($settings)),
    array("hash" => $cloneHash)
);
```

### Feature Flags Per Clone

```php
g::def("features", array(
    "isEnabled" => function($feature) {
        $cloneHash = g::get("db.current_clone");
        if (!$cloneHash) return false;
        
        $clone = g::run("db.get", "clones", $cloneHash);
        if (!$clone) return false;
        
        $settings = json_decode($clone['settings'], true);
        return isset($settings['features'][$feature]) ? $settings['features'][$feature] : false;
    }
));

// Use feature flags
if (g::run("features.isEnabled", "comments")) {
    // Show comment form
}
```

## Isolation Best Practices

### 1. Always Verify Clone Context

```php
function ensureCloneContext() {
    $cloneHash = g::get("db.current_clone");
    if (empty($cloneHash)) {
        die("Clone context not set");
    }
    return $cloneHash;
}

// Use in routes
g::run("route.define", "/posts", function() {
    ensureCloneContext();
    
    $posts = g::run("db.select", "items", array("type" => "post"));
    // ... render posts
});
```

### 2. Clone-Aware URLs

```php
function getPostUrl($post) {
    $clone = g::run("db.get", "clones", $post['clone_id']);
    $domain = $clone['domain'];
    
    return "https://" . $domain . "/posts/" . $post['safe_url'];
}
```

### 3. Prevent Clone Leakage

```php
// DON'T: Hardcode clone_id
$posts = g::run("db.select", "items", array(
    "clone_id" => "abc123",  // ❌ BAD
    "type" => "post"
));

// DO: Use clone context
g::run("db.setClone", $cloneHash);  // ✅ GOOD
$posts = g::run("db.select", "items", array(
    "type" => "post"
));
```

## Real-World Example: Blog Platform

Complete multi-tenant blog platform:

```php
<?php
require_once 'genes.php';

// Connect to database
g::run("db.connect", array(
    "driver" => "mysql",
    "name" => "main",
    "host" => "localhost",
    "database" => "blogplatform",
    "username" => "root",
    "password" => ""
));

// Detect clone from subdomain
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$clones = g::run("db.select", "clones", array(
    "domain" => $host,
    "state" => "active"
));

if (empty($clones)) {
    die("Blog not found. Are you sure this domain is configured?");
}

$currentClone = $clones[0];
g::run("db.setClone", $currentClone['hash']);

// Load clone settings
$settings = json_decode($currentClone['settings'], true);
$theme = isset($settings['theme']) ? $settings['theme'] : 'default';

// Parse URL
$request = g::run("route.parseUrl");
$path = $request['path'];

// Home page
if ($path === '/' || $path === '/index') {
    $posts = g::run("db.select", "items",
        array("type" => "post", "state" => "published"),
        "main",
        array("limit" => 10, "order" => "created_at DESC")
    );
    
    echo "<h1>" . htmlspecialchars($currentClone['name']) . "</h1>";
    foreach ($posts as $post) {
        echo "<article>";
        echo "<h2>" . htmlspecialchars($post['title']) . "</h2>";
        echo "<p>" . htmlspecialchars($post['blurb']) . "</p>";
        echo "<a href='/posts/" . htmlspecialchars($post['safe_url']) . "'>Read more</a>";
        echo "</article>";
    }
}
// Single post
elseif (strpos($path, '/posts/') === 0) {
    $slug = str_replace('/posts/', '', $path);
    
    $posts = g::run("db.select", "items", array(
        "type" => "post",
        "safe_url" => $slug,
        "state" => "published"
    ));
    
    if (empty($posts)) {
        echo "Post not found";
    } else {
        $post = $posts[0];
        
        // Log view event
        g::run("db.insert", "events", array(
            "type" => "post.viewed",
            "item_id" => $post['hash'],
            "data" => json_encode(array(
                "ip" => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown'
            ))
        ));
        
        echo "<h1>" . htmlspecialchars($post['title']) . "</h1>";
        echo "<div>" . nl2br(htmlspecialchars($post['text'])) . "</div>";
    }
}
?>
```

## Migration Strategy

### From Single-Tenant to Multi-Tenant

1. **Add clone_id to existing tables**:
   ```sql
   ALTER TABLE persons ADD COLUMN clone_id VARCHAR(32);
   ALTER TABLE items ADD COLUMN clone_id VARCHAR(32);
   ```

2. **Create initial clone**:
   ```php
   $mainClone = g::run("db.insert", "clones", array(
       "name" => "Main Site",
       "domain" => "mysite.com",
       "state" => "active"
   ));
   ```

3. **Migrate existing data**:
   ```php
   $pdo = g::run("db.connection", "main");
   $pdo->exec("UPDATE persons SET clone_id = '$mainClone'");
   $pdo->exec("UPDATE items SET clone_id = '$mainClone'");
   ```

4. **Deploy clone-aware code**:
   ```php
   g::run("db.setClone", $mainClone);
   // Now all queries are scoped
   ```

## Next Steps

- **Working Examples** → [Examples](EXAMPLES.md)
- **Architecture Deep Dive** → [Architecture](ARCHITECTURE.md)
- **Quick Start** → [Quickstart](QUICKSTART.md)
