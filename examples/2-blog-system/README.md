# Example 2: Multi-Tenant Blog System

Complete blog system demonstrating **multi-clone architecture** with proper isolation.

## What This Demonstrates

- ✅ Multi-tenant clone-based isolation
- ✅ Items table for blog posts
- ✅ Labels for categories and tags
- ✅ Person (author) relationships
- ✅ Events for analytics
- ✅ URL routing and safe_url patterns
- ✅ Clone context management

## Features

- Multiple blogs on single database
- Automatic clone_id filtering
- Blog posts with categories
- Author attribution
- View tracking (events)
- Recent posts listing
- Single post view

## Running the Example

```bash
php -S localhost:8001 -t examples/2-blog-system
```

Visit: 
- `http://localhost:8001` - Blog home (list of posts)
- `http://localhost:8001/?post=my-first-post` - Single post view

## Clone Isolation

This example demonstrates how multiple blogs can coexist:

```php
// Blog A (clone_id = 'abc123')
Clone: "Tech Blog"
Posts:
  - "Understanding PHP 5.6" (clone_id = 'abc123')
  - "Database Design" (clone_id = 'abc123')

// Blog B (clone_id = 'def456')
Clone: "Food Blog"
Posts:
  - "Best Pasta Recipes" (clone_id = 'def456')
  - "Italian Cooking" (clone_id = 'def456')

// Setting context to Blog A ONLY shows Blog A posts
g::run("db.setClone", "abc123");
$posts = g::run("db.select", "items", array("type" => "post"));
// Returns ONLY Tech Blog posts!
```

## Schema Usage

### items Table (Blog Posts)

```php
g::run("db.insert", "items", array(
    "type" => "post",
    "state" => "published",
    "title" => "My Blog Post",
    "safe_url" => "my-blog-post",
    "blurb" => "Short intro...",
    "text" => "Full post content here...",
    "created_by" => $authorHash
    // clone_id auto-added!
));
```

### labels Table (Categories)

```php
g::run("db.insert", "labels", array(
    "type" => "category",
    "key" => "tech",
    "name" => "Technology"
    // clone_id auto-added!
));
```

### events Table (Analytics)

```php
g::run("db.insert", "events", array(
    "type" => "post.viewed",
    "item_id" => $postHash,
    "data" => json_encode(array("ip" => $_SERVER['REMOTE_ADDR']))
    // clone_id auto-added!
));
```

## Key Patterns

### 1. Clone Detection
```php
// In real app, detect from subdomain or domain
$clones = g::run("db.select", "clones", array(
    "domain" => $_SERVER['HTTP_HOST']
));
g::run("db.setClone", $clones[0]['hash']);
```

### 2. Author Relationship
```php
// Get post with author info
$post = g::run("db.get", "items", $postHash);
$author = g::run("db.get", "persons", $post['created_by']);
```

### 3. Category Filtering
```php
// Filter posts by category (using labels)
$posts = g::run("db.select", "items", array(
    "type" => "post",
    "state" => "published"
));

// Filter in PHP where labels contain category hash
$filtered = array();
foreach ($posts as $post) {
    $labels = json_decode($post['labels'], true);
    if (in_array($categoryHash, $labels)) {
        $filtered[] = $post;
    }
}
```

## Next Steps

- **REST API** → See Example 3
- **CRUD Basics** → See Example 1
- **Documentation** → Read `/docs`
