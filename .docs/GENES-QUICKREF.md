# Genes Framework - Quick Reference Card

> **One-page cheat sheet for AI coding agents**

---

## 🎯 Core Concepts

```php
// PHP: Single global class 'g'
g::set("key", $value)      // Store data
g::get("key")              // Retrieve data
g::def("fn", $callable)    // Define function
g::run("fn", ...$args)     // Execute function
```

```javascript
// JS: Global object 'g'
g.set("key", value)        // Store data
g.get("key")               // Retrieve data
g.def("fn", callback)      // Define function
g.run("fn", ...args)       // Execute function
```

---

## 📦 Project Structure

```
myapp/
├── .genes/
│   ├── genes.php          # Backend framework (include once)
│   ├── genes.js           # Frontend library (script tag)
│   └── genes.css          # UI framework (link tag)
├── data/
│   └── config.json        # Configuration (auto-created)
├── cache/                 # Auto-created cache folder
├── ui/                    # HTML templates
│   ├── index.html
│   └── assets/            # Your CSS/JS
├── mods/                  # Feature modules
├── uploads/               # User uploads
└── index.php              # Entry point
```

---

## ⚡ Quick Patterns

### Minimal App

```php
<?php
// index.php
require_once './.genes/genes.php';

g::def("app.init", function() {
    echo "Hello World!";
});

g::run("app.init");
```

### Database CRUD

```php
// Connect
g::run("db.connect", ["host" => "localhost", "name" => "db", "user" => "root", "pass" => ""]);

// Insert
$id = g::run("db.insert", "users", ["name" => "John", "email" => "john@example.com"]);

// Select
$users = g::run("db.select", "users", ["state" => "active"]);

// Update
g::run("db.update", "users", ["hash" => $id], ["name" => "Jane"]);

// Delete
g::run("db.delete", "users", ["hash" => $id]);
```

### REST API Endpoint

```php
<?php
require_once './.genes/genes.php';

g::run("route.parseUrl");
$request = g::get("request");

if ($request["segments"][0] === "api") {
    $table = $request["segments"][1];
    $result = g::run("api.handle", $table);
    g::run("api.respond", $result);
}
```

### Authentication

```php
// Initialize
g::run("auth.init");

// Register
$hash = g::run("auth.register", [
    "email" => "user@example.com",
    "password" => "secret123",
    "name" => "User"
]);

// Login
$success = g::run("auth.login", "user@example.com", "secret123");

// Check
if (g::run("auth.check")) {
    $user = g::run("auth.user");
}

// Logout
g::run("auth.logout");
```

### Frontend API Calls

```javascript
// List
g.api.list("users", {state: "active"}, function(data) {
    console.log(data);
});

// Get
g.api.get("users", hash, function(user) {
    console.log(user);
});

// Create
g.api.create("users", {name: "John"}, function(res) {
    console.log(res.hash);
});

// Update
g.api.update("users", hash, {name: "Jane"}, function(res) {
    console.log("Updated");
});

// Delete
g.api.delete("users", hash, function(res) {
    console.log("Deleted");
});
```

### Event Handling

```javascript
// Click event
g.on("click", ".btn", function(element) {
    console.log("Clicked:", element);
});

// Form submit
g.on("submit", "#myForm", function(form) {
    const data = g.formData(form);
    g.api.create("items", data, function(res) {
        console.log("Created");
    });
});
```

### DOM Manipulation

```javascript
// Select
const el = g.el("#myId");
const els = g.els(".myClass");

// Create
const div = g.create("div", {
    className: "card",
    innerHTML: "<h2>Title</h2>"
});

// Classes
g.ac(el, "active");        // Add class
g.rc(el, "active");        // Remove class
g.tc(el, "active");        // Toggle class
g.hc(el, "active");        // Has class (boolean)
```

### Data Binding

```html
<div data-g-bind="name"></div>
<img data-g-attr="src:avatar" />
<div data-g-if="isAdmin">Admin Content</div>

<script>
g.bind(container, {
    name: "John Doe",
    avatar: "/img/john.jpg",
    isAdmin: true
});
</script>
```

---

## 🗄️ Standard Database Schema

```sql
-- 5 core tables, universal structure

CREATE TABLE persons (
    hash VARCHAR(32) PRIMARY KEY,
    type VARCHAR(32),           -- "user", "admin", etc.
    state VARCHAR(32),          -- "active", "pending", "deleted"
    email VARCHAR(255),
    password VARCHAR(255),
    name VARCHAR(255),
    meta TEXT,                  -- JSON flexible data
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE clones (
    hash VARCHAR(32) PRIMARY KEY,
    type VARCHAR(32),           -- "post", "comment", etc.
    state VARCHAR(32),
    person_hash VARCHAR(32),    -- Foreign key
    title VARCHAR(512),
    content TEXT,
    meta TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE links (
    hash VARCHAR(32) PRIMARY KEY,
    type VARCHAR(32),           -- "follow", "like", "vote"
    state VARCHAR(32),
    from_hash VARCHAR(32),      -- Source entity
    to_hash VARCHAR(32),        -- Target entity
    value DECIMAL(10,2),        -- Numeric value (votes, etc.)
    meta TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE nodes (
    hash VARCHAR(32) PRIMARY KEY,
    type VARCHAR(32),           -- "page", "category", "tag"
    state VARCHAR(32),
    title VARCHAR(512),
    content TEXT,
    meta TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE events (
    hash VARCHAR(32) PRIMARY KEY,
    type VARCHAR(32),           -- "login", "view", "action"
    state VARCHAR(32),
    ref_hash VARCHAR(32),       -- Reference to related entity
    data TEXT,                  -- JSON event data
    created_at TIMESTAMP
);
```

**Usage Examples:**
- **persons**: Users, accounts, admins
- **clones**: Posts, comments, shares (user-generated)
- **links**: Follows, likes, upvotes, relationships
- **nodes**: Pages, categories, tags (static content)
- **events**: Audit log, analytics, activity tracking

---

## 🔒 Security Essentials

```php
// Password hashing (bcrypt + pepper)
$hash = g::run("auth.hash", "password123");
$valid = g::run("auth.verify", "password123", $hash);

// Generate secure token
$token = g::run("crypt.token", 32);

// HMAC signing
$signature = g::run("crypt.hmac", $data, $secret);

// Encrypt/decrypt data
$encrypted = g::run("crypt.encrypt", $data);
$decrypted = g::run("crypt.decrypt", $encrypted);
```

---

## 🎨 CSS Utility Classes

```html
<!-- Layout -->
<div class="container">              <!-- Centered container -->
<div class="flex flex-between">     <!-- Flexbox space-between -->
<div class="grid grid-3">            <!-- 3-column grid -->

<!-- Spacing -->
<div class="p-2 m-4 gap-2">         <!-- Padding, margin, gap -->

<!-- Typography -->
<h1 class="text-3xl font-bold">     <!-- Large bold text -->
<p class="text-muted">               <!-- Muted text -->

<!-- Visibility -->
<div class="hidden">                 <!-- Display none -->

<!-- Components -->
<button class="btn btn-primary">    <!-- Primary button -->
<input class="input">                <!-- Styled input -->
<div class="card">                   <!-- Card container -->
```

---

## 📊 Logging & Debugging

```php
// Log messages
g::run("log.error", "Critical error");      // Level 1
g::run("log.warning", "Warning message");   // Level 2
g::run("log.info", "Info message");         // Level 3

// Debug print (only if debug=true)
g::run("log.debugPrint", $data, "Label");

// Performance metrics
$perf = g::run("log.performance");
// Returns: execution_time_ms, memory_used_kb, peak_memory_kb
```

```javascript
// Console logging
g.cl("Debug message");  // Timestamped console.log
```

---

## 📡 AJAX Requests

```javascript
// GET request
g.get("/api/data", {id: 123}, function(response) {
    console.log(response);
});

// POST request
g.post("/api/create", {name: "John"}, function(response) {
    console.log(response);
});

// PUT request
g.put("/api/update/123", {name: "Jane"}, function(response) {
    console.log(response);
});

// DELETE request
g.del("/api/delete/123", function(response) {
    console.log(response);
});

// Full AJAX
g.ajax({
    url: "/api/endpoint",
    method: "POST",
    data: {key: "value"},
    success: function(response) { },
    error: function(err) { }
});
```

---

## 💾 Storage Functions

```javascript
// LocalStorage
g.ls.set("key", {foo: "bar"});     // Auto JSON.stringify
const data = g.ls.get("key");      // Auto JSON.parse
g.ls.del("key");
g.ls.clear();

// Cookies
g.cookie.set("key", "value", 30);  // 30 days
const val = g.cookie.get("key");
g.cookie.del("key");

// State (in-memory)
g.set("app.user", userData);
const user = g.get("app.user");
```

---

## 🔄 Routing & URLs

```php
// PHP: Parse current URL
g::run("route.parseUrl");
$request = g::get("request");
// Returns: protocol, host, path, query, segments, method

// Redirect
g::run("route.redirect", "/dashboard");

// 404 handler
g::run("route.notFound", "Custom message");
```

```javascript
// JS: URL utilities
const params = g.url.params();     // Get all params
const id = g.url.param("id");      // Get single param
g.url.push("/new-url");            // Change URL (no reload)
g.url.replace("/new-url");         // Replace URL (no reload)
```

---

## ⏱️ Timers

```javascript
// Named timers (auto-cleanup)
g.si("myInterval", function() {
    console.log("Tick");
}, 1000);

g.st("myTimeout", function() {
    console.log("Done");
}, 5000);

// Clear timers
g.ci("myInterval");
g.ct("myTimeout");
```

---

## 🛠️ Utilities

```javascript
// Check if value exists
g.is(value)              // Not null/undefined/empty/false
g.is_fnc(value)          // Is function
g.is_obj(value)          // Is object (not array)

// Date/time
g.now()                  // "2025-01-11 14:30:00"
g.now(timestamp, "Y-m-d") // Custom format

// Encoding
g.encode(str)            // Base64 encode
g.decode(str)            // Base64 decode

// Performance
g.debounce(fn, 300)      // Debounce function
g.throttle(fn, 1000)     // Throttle function
```

---

## 🎯 Config Management

```php
// Get config
$config = g::run("config.get");

// Get value with default
$debug = g::run("config.getValue", "settings.debug", false);

// Update config
g::run("config.update", "settings.debug", true, true); // Save to file

// Load external config
g::run("config.load", "plugins/my-plugin/config.json");

// Check if key exists
if (g::run("config.has", "database.host")) { }
```

---

## 🧩 Template System

```php
// Simple variable replacement
$html = "<h1>{{title}}</h1><p>{{content}}</p>";
$rendered = g::run("tpl.render", $html, [
    "title" => "Hello",
    "content" => "World"
]);

// Register custom helper
g::run("tpl.helper", "uppercase", function($content, $data) {
    return strtoupper($content);
});

// Use helper in template
$html = '<div data-helper="uppercase">{{text}}</div>';
```

---

## 📋 Form Handling

```javascript
// Get form data as object
g.on("submit", "#myForm", function(form) {
    const data = g.formData(form);
    // Returns: {field1: "value1", field2: "value2"}
    
    g.api.create("items", data, function(response) {
        if (response.success) {
            form.reset();
        }
    });
});
```

---

## 🚀 Production Deployment

```json
// data/config.json
{
  "environments": {
    "yourdomain.com": {
      "database": {
        "host": "localhost",
        "name": "prod_db",
        "user": "prod_user",
        "pass": "secure_password"
      },
      "settings": {
        "debug": false,
        "log_level": 1
      },
      "security": {
        "secret": "RANDOM_64_CHAR_STRING",
        "salt": "RANDOM_64_CHAR_STRING"
      }
    }
  }
}
```

```bash
# File permissions
chmod 755 data/ cache/ uploads/
chmod 644 .genes/* data/config.json
```

---

## 📖 Module Reference

| PHP Module | Purpose |
|------------|---------|
| `core.*` | Initialization, configuration |
| `config.*` | Configuration management |
| `log.*` | Logging and debugging |
| `route.*` | URL routing |
| `db.*` | Database operations |
| `db.crud.*` | RESTful CRUD |
| `api.*` | API handling |
| `auth.*` | Authentication |
| `crypt.*` | Cryptography |
| `mod.*` | Module system |
| `data.*` | Data manipulation |
| `tpl.*` | Templates |

| JS Module | Purpose |
|-----------|---------|
| `g.set/get` | State management |
| `g.el/els` | DOM selection |
| `g.on/off` | Events |
| `g.ajax` | AJAX requests |
| `g.api.*` | Backend API |
| `g.auth.*` | Authentication |
| `g.ls.*` | LocalStorage |
| `g.cookie.*` | Cookies |
| `g.url.*` | URL utilities |
| `g.si/st` | Timers |

---

## 💡 AI Agent Tips

1. **Start minimal**: Single `index.php` + `require genes.php`
2. **Use dot notation**: Organize functions as `app.users.list`
3. **Leverage meta field**: Store JSON in database `meta` column
4. **Follow RESTful patterns**: `list`, `get`, `create`, `update`, `delete`
5. **Error handling**: Always return `{success: bool, error?: string}`
6. **Security first**: Never disable `auth.check()` in production
7. **Use lifecycle hooks**: `g.que("onReady", fn)` for initialization
8. **Consistent naming**: Keep function names predictable

---

**Full Documentation**: See `GENES-AI-FRAMEWORK.md`  
**License**: MIT  
**Version**: 2.0.0
