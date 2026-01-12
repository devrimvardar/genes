# Genes Framework API Reference

Complete reference for all Genes Framework functions.

## Core Functions

### g::run()
Execute a registered function.

```php
$result = g::run("namespace.function", $arg1, $arg2);
```

### g::def()
Define a namespace of functions.

```php
g::def("myapp", array(
    "hello" => function($name) {
        return "Hello, $name!";
    }
));
```

### g::set() / g::get()
Store and retrieve values.

```php
g::set("user.name", "John");
$name = g::get("user.name");
$default = g::get("missing.key", "default value");
```

## Database Functions

### db.connect
Connect to a database.

```php
// SQLite
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
    "database" => "mydb",
    "user" => "root",
    "pass" => "password"
));
```

### db.createSchema
Create the 5 standard tables.

```php
g::run("db.createSchema", "main");  // Connection name
```

Creates: `clones`, `persons`, `items`, `labels`, `events`

### db.setClone
Set the current clone for multi-tenant isolation.

```php
g::run("db.setClone", $cloneHash);
// All subsequent queries filtered by this clone_id
```

### db.insert
Insert a record.

```php
$hash = g::run("db.insert", "items", array(
    "type" => "post",
    "title" => "My Post",
    "state" => "published"
));
// Returns: 32-character hash (primary key)
```

**Auto-added fields:**
- `hash` - Unique ID
- `clone_id` - Current clone (if set)
- `created_at` - Current timestamp
- `updated_at` - Current timestamp
- `created_by` - Current user (if set)

**JSON encoding:**
Arrays/objects are automatically JSON-encoded for: `labels`, `meta`, `media`, `data`, `settings`

### db.select
Query records.

```php
// All posts
$posts = g::run("db.select", "items", array(
    "type" => "post"
));

// With multiple conditions
$posts = g::run("db.select", "items", array(
    "type" => "post",
    "state" => "published",
    "created_by" => $userHash
));

// Select by hash
$post = g::run("db.select", "items", array(
    "hash" => $postHash
));
```

Returns: Array of records (empty array if none found)

**Note:** Automatically filters by current `clone_id` if set.

### db.update
Update a record.

```php
g::run("db.update", "items", $hash, array(
    "title" => "Updated Title",
    "state" => "archived"
));
```

**Auto-updated fields:**
- `updated_at` - Current timestamp
- `updated_by` - Current user (if set)

### db.delete
Delete a record.

```php
g::run("db.delete", "items", $hash);
```

**Note:** This is a hard delete. For soft delete, update `state` to "deleted" instead.

## Template Functions

### tpl.renderView
Render a view template with data.

```php
$html = g::run("tpl.renderView", "Index", array(
    "title" => "Welcome",
    "posts" => $posts,
    "user" => $currentUser
));
```

View name matches key in `config.json` views.

### tpl.render
Render a template string with data.

```php
$template = "<h1>{{title}}</h1><p>{{content}}</p>";
$html = g::run("tpl.render", $template, array(
    "title" => "Hello",
    "content" => "World"
));
```

## Routing Functions

### route.handle
Process the current request and route to clone function.

```php
g::def("clone", array(
    "Index" => function($bits, $lang, $path) {
        // Handle route
    }
));

g::run("route.handle");
```

**Clone function parameters:**
- `$bits` - Array with `translations`, `config`, etc.
- `$lang` - Current language code (e.g., "en")
- `$path` - Current URL path

**Accessing route segments:**
```php
$request = g::get("request");
$segments = $request['route_segments'];
// For /blog/my-post => ["my-post"]
```

## Authentication Functions

### auth.hash
Hash a password.

```php
$hashed = g::run("auth.hash", "password123");
```

### auth.verify
Verify a password.

```php
$valid = g::run("auth.verify", "password123", $hashedPassword);
// Returns: true or false
```

### auth.login
Authenticate a user.

```php
$user = g::run("auth.login", "email@example.com", "password");
// Returns: user array or false
```

### auth.logout
Log out current user.

```php
g::run("auth.logout");
```

### auth.check
Check if user is logged in.

```php
$user = g::run("auth.check");
// Returns: user array or false
```

## Utility Functions

### util.generateHash
Generate a unique hash.

```php
$hash = g::run("util.generateHash");
// Returns: 32-character hash
```

### util.sanitize
Sanitize HTML.

```php
$safe = g::run("util.sanitize", $userInput);
```

### util.slugify
Create URL-friendly slug.

```php
$slug = g::run("util.slugify", "Hello World!");
// Returns: "hello-world"
```

## Configuration Functions

### config.load
Load configuration from file.

```php
$config = g::run("config.load", "data/config.json");
```

### config.save
Save configuration to file.

```php
g::run("config.save", "data/config.json", $configArray);
```

## Template Directives

Use in HTML templates (ui/*.html):

### data-g-if
Conditional rendering.

```html
<div data-g-if="variable">Show if truthy</div>
<div data-g-if="!variable">Show if falsy</div>
<div data-g-if="status:active">Show if status equals "active"</div>
<div data-g-if="count:0">Show if count equals 0</div>
<div data-g-if="premium:true">Show if premium is boolean true</div>
```

### data-g-for
Loop rendering.

```html
<div data-g-for="item in items">
    <h3>{{item.title}}</h3>
    <p>Index: {{_index}}, First: {{_first}}, Last: {{_last}}</p>
</div>
```

**Special variables:**
- `{{_index}}` - Current index (0-based)
- `{{_first}}` - Boolean, true if first item
- `{{_last}}` - Boolean, true if last item

### data-g-load
Load partial template.

```html
<div data-g-load="partials/header.html"></div>
<div data-g-load="auth/login.html" data-g-with="user"></div>
```

**Partial locations** (searched in order):
1. `ui/partials/`
2. `ui/tmpls/`
3. `data/templates/`
4. `templates/`

### data-g-bind
Bind text content (HTML-escaped).

```html
<h1 data-g-bind="post.title">Default Title</h1>
<p data-g-bind="user.bio">Default bio</p>
```

### data-g-html
Bind raw HTML (NOT escaped).

```html
<div data-g-html="post.content">Default content</div>
```

⚠️ **Warning:** Only use with trusted content!

### data-g-attr
Bind attributes dynamically.

```html
<a data-g-attr="href:/post/{{post.safe_url}}">Link</a>
<img data-g-attr="src:{{image.url}};alt:{{image.title}}">
<div data-g-attr="class:{{status}};id:item-{{id}}">Content</div>
```

### {{variable}}
Variable replacement anywhere.

```html
<p>Hello, {{user.name}}!</p>
<a href="/users/{{user.id}}">Profile</a>
```

## CSS Framework

### Canvas Sizes

```html
<div class="canvas canvas-32">  <!-- Max 32rem width -->
<div class="canvas canvas-64">  <!-- Max 64rem width -->
<div class="canvas canvas-128"> <!-- Max 128rem width -->
```

### Spacing

**Padding:**
```html
<div class="pad-1">      <!-- padding: 1rem -->
<div class="pad-2">      <!-- padding: 2rem -->
<div class="pad-x-2">    <!-- padding-left/right: 2rem -->
<div class="pad-y-3">    <!-- padding-top/bottom: 3rem -->
<div class="pad-t-1">    <!-- padding-top: 1rem -->
```

**Margin:**
```html
<div class="mar-1">      <!-- margin: 1rem -->
<div class="mar-b-2">    <!-- margin-bottom: 2rem -->
<div class="mar-t-3">    <!-- margin-top: 3rem -->
```

### Layout

**Flex:**
```html
<div class="flex">              <!-- display: flex -->
<div class="flex flex-middle">  <!-- align-items: center -->
<div class="flex flex-between"><!-- justify-content: space-between -->
<div class="flex flex-center">  <!-- justify-content: center -->
<div class="flex flex-column">  <!-- flex-direction: column -->
```

**Grid:**
```html
<div class="grid grid-2">  <!-- 2 columns -->
<div class="grid grid-3">  <!-- 3 columns -->
<div class="grid grid-4">  <!-- 4 columns -->
<div class="gap-1">        <!-- gap: 1rem -->
```

### Components

**Buttons:**
```html
<button class="button">Default</button>
<button class="button button-sm">Small</button>
<button class="button button-primary">Primary</button>
<button class="button button-danger">Danger</button>
```

**Borders:**
```html
<div class="border">           <!-- border: 1px solid -->
<div class="border-top">       <!-- border-top only -->
<div class="radius">           <!-- border-radius -->
```

**Text:**
```html
<p class="text-muted">    <!-- Lighter text -->
<p class="text-sm">       <!-- Small text -->
<p class="text-center">   <!-- Text align center -->
```

## JavaScript API (genes.js)

### DOM Utilities

```javascript
// Select element
g.el('#id')           // querySelector
g.els('.class')       // querySelectorAll

// Event delegation
g.on('click', '.button', function(e) {
    // Handle click
});

// Add event listener
g.el('#btn').addEventListener('click', handler);
```

### AJAX

```javascript
// GET request
g.ajax({
    url: '/api/posts',
    method: 'GET',
    success: function(data) {
        console.log(data);
    }
});

// POST request
g.ajax({
    url: '/api/posts',
    method: 'POST',
    data: { title: 'New Post' },
    success: function(response) {
        console.log(response);
    }
});
```

## Best Practices

### Always Use Prepared Queries
✅ **DO:**
```php
$posts = g::run("db.select", "items", array("type" => "post"));
```

❌ **DON'T:**
```php
$db->query("SELECT * FROM items WHERE type = 'post'");
```

### Use the Items Table for Content
✅ **DO:**
```php
// Blog posts
g::run("db.insert", "items", array("type" => "post", ...));

// Products
g::run("db.insert", "items", array("type" => "product", ...));

// Comments
g::run("db.insert", "items", array("type" => "comment", ...));
```

❌ **DON'T:**
```php
// Create custom tables
CREATE TABLE posts ...
CREATE TABLE products ...
```

### Use JSON Fields for Flexibility
✅ **DO:**
```php
"meta" => json_encode(array(
    "views" => 100,
    "featured" => true,
    "author_bio" => "..."
))
```

### Set Clone Context
✅ **DO:**
```php
g::run("db.setClone", $cloneHash);
$posts = g::run("db.select", "items", array("type" => "post"));
// Automatically filtered by clone_id
```

### Use Safe URLs for Routing
✅ **DO:**
```php
"safe_url" => "my-blog-post"  // Unique slug
// Access as: /blog/my-blog-post
```

---

**See Also:**
- [Quickstart Guide](QUICKSTART.md)
- [Database Schema](../DATABASE-SCHEMA.md)
- [Examples](../examples/)
