# Genes Framework - AI Coding Agent Documentation

> **Version**: 2.0.0  
> **License**: MIT  
> **Repository**: https://github.com/[your-org]/genes-framework  
> **Website**: https://genes.one  
> **Best For**: AI coding agents building PHP backends + Vanilla JS frontends

---

## 🎯 What is Genes Framework?

**Genes** is a lightweight, single-file framework for **rapid web development** using **PHP (backend)** and **Vanilla JavaScript (frontend)**. It's designed to be:

- ✅ **AI-Friendly**: Minimal syntax, predictable patterns, single global namespace
- ✅ **Zero Dependencies**: No Composer, no npm, no build tools required
- ✅ **Single-File Architecture**: One PHP file (~6400 lines), one JS file (~1300 lines), one CSS file (~1700 lines)
- ✅ **Batteries Included**: Database ORM, authentication, routing, templates, API layer, crypto
- ✅ **Production Ready**: Built-in security, sessions, logging, performance tracking
- ✅ **Flexible**: Works as library or full framework, adaptable to any project structure

---

## 📦 Core Files

```
.genes/
  ├── genes.php    (~6400 lines) - Backend framework
  ├── genes.js     (~1300 lines) - Frontend library  
  └── genes.css    (~1700 lines) - UI framework
```

**Total**: ~9400 lines of code providing complete full-stack capability.

---

## 🏗️ Architecture Overview

### PHP Backend (`genes.php`)

The framework uses a **single global class `g`** with a function registry pattern:

```php
// Define namespaced functions
g::def("core", array(
    "init" => function() { /* ... */ },
    "log" => function($msg) { /* ... */ }
));

// Call registered functions
g::run("core.init");
g::run("core.log", "Hello World");

// Store/retrieve data
g::set("user.name", "John");
$name = g::get("user.name");
```

**Why This Matters for AI:**
- Single namespace (`g`) reduces confusion
- Dot notation for organization (e.g., `db.select`, `auth.login`)
- Consistent calling pattern (`g::run`, `g::set`, `g::get`)
- All functions documented with examples

### JavaScript Frontend (`genes.js`)

Similar pattern using global `window.g`:

```javascript
// Define functions
g.def("app.init", function() { /* ... */ });

// Run functions
g.run("app.init");

// State management
g.set("user.isLoggedIn", true);
const loggedIn = g.get("user.isLoggedIn");

// DOM utilities
const el = g.el("#myButton");
g.on("click", ".btn", function(element) { /* ... */ });

// API calls
g.api.list("users", function(data) {
    console.log(data);
});
```

---

## 📚 Module Reference

### PHP Backend Modules

| Module | Purpose | Key Functions |
|--------|---------|---------------|
| **core** | System initialization, configuration | `init`, `log`, `timestamp`, `generateRandomKey` |
| **config** | Configuration management | `get`, `update`, `load`, `getValue`, `has` |
| **log** | Logging and debugging | `write`, `error`, `warning`, `info`, `performance` |
| **route** | URL routing and request parsing | `parseUrl`, `match`, `redirect`, `notFound` |
| **crypt** | Cryptography and hashing | `hashPassword`, `verifyPassword`, `token`, `hash`, `hmac`, `encrypt`, `decrypt` |
| **db** | Database connection and queries | `connect`, `query`, `select`, `insert`, `update`, `delete`, `createSchema` |
| **db.crud** | RESTful CRUD operations | `create`, `read`, `update`, `delete`, `list` |
| **api** | REST API layer | `handle`, `respond`, `checkAuth` |
| **auth** | Authentication and sessions | `init`, `login`, `logout`, `user`, `check`, `hash`, `verify`, `token` |
| **mod** | Module/plugin system | `load`, `register`, `run` |
| **data** | Data manipulation | `json`, `validate`, `sanitize`, `transform` |
| **tpl** | Template rendering | `render`, `helper`, `include` |

### JavaScript Frontend Modules

| Module | Purpose | Key Functions |
|--------|---------|---------------|
| **Core** | State & function registry | `set`, `get`, `def`, `run`, `que` |
| **DOM** | Element manipulation | `el`, `els`, `create`, `hc`, `ac`, `rc`, `tc` |
| **Events** | Delegated event handling | `on`, `off`, `once`, `trigger` |
| **AJAX** | HTTP requests | `ajax`, `get`, `post`, `put`, `del` |
| **API** | Backend integration | `api.list`, `api.get`, `api.create`, `api.update`, `api.delete` |
| **Auth** | Client-side auth | `auth.login`, `auth.logout`, `auth.check`, `auth.user` |
| **Storage** | localStorage & cookies | `ls.set`, `ls.get`, `cookie.set`, `cookie.get` |
| **URL** | URL manipulation | `url.params`, `url.param`, `url.push` |
| **Utilities** | Helpers | `is`, `now`, `encode`, `decode`, `debounce`, `throttle` |
| **Timers** | Timer management | `si`, `st`, `ci`, `ct` |

### CSS Framework Features

| Feature | Description |
|---------|-------------|
| **Reset** | Universal cross-browser reset |
| **Viewport System** | REM-based responsive (32/64/128rem canvas) |
| **Theme Variables** | CSS custom properties for dark/light themes |
| **Layout Utilities** | Flexbox, grid, positioning classes |
| **Typography** | Responsive font sizes (.text-xs to .text-4xl) |
| **Form Components** | Beautiful input, button, select styling |
| **Utilities** | Spacing, colors, shadows, borders |
| **Components** | Cards, modals, tables, badges |

---

## 🚀 Quick Start Guide for AI Agents

### Step 1: Project Setup

```bash
# Create project structure
mkdir myapp
cd myapp
mkdir .genes data cache ui mods uploads

# Create genes files (copy from repository)
# - .genes/genes.php
# - .genes/genes.js
# - .genes/genes.css
```

### Step 2: Basic `index.php`

```php
<?php
require_once './.genes/genes.php';

// Define your application logic
g::def("app", array(
    "init" => function() {
        echo "Hello, World!";
    }
));

// Run application
g::run("app.init");
```

### Step 3: Configuration (`data/config.json`)

```json
{
  "environments": {
    "localhost": {
      "database": {
        "host": "localhost",
        "name": "myapp",
        "user": "root",
        "pass": "",
        "type": "mysql"
      },
      "settings": {
        "debug": true,
        "log_level": 3
      }
    }
  },
  "security": {
    "secret": "auto-generated-key",
    "salt": "auto-generated-salt"
  }
}
```

### Step 4: HTML Template (`ui/index.html`)

```html
<!DOCTYPE html>
<html>
<head>
    <title>My App</title>
    <link rel="stylesheet" href=".genes/genes.css">
    <script src=".genes/genes.js" defer></script>
</head>
<body>
    <div class="container">
        <h1>Welcome to Genes Framework</h1>
        <div id="content"></div>
    </div>
    
    <script>
        g.que("onReady", function() {
            console.log("App ready!");
            
            // Load data from API
            g.api.list("items", function(data) {
                console.log(data);
            });
        });
    </script>
</body>
</html>
```

---

## 💡 Common Patterns for AI Implementation

### Pattern 1: Database CRUD

```php
// Create
g::run("db.connect", array(
    "host" => "localhost",
    "name" => "mydb",
    "user" => "root",
    "pass" => ""
));

// Insert
$hash = g::run("db.insert", "users", array(
    "name" => "John Doe",
    "email" => "john@example.com"
));

// Select
$users = g::run("db.select", "users", array(
    "state" => "active"
), "default", array("limit" => 10));

// Update
g::run("db.update", "users", array("hash" => $hash), array(
    "name" => "Jane Doe"
));

// Delete (soft delete by default)
g::run("db.delete", "users", array("hash" => $hash));
```

### Pattern 2: RESTful API Endpoint

```php
<?php
require_once './.genes/genes.php';

// Parse request
g::run("route.parseUrl");
$request = g::get("request");
$method = $request["method"];
$segments = $request["segments"];

// Route: /api/users
if ($segments[0] === "api" && $segments[1] === "users") {
    
    // Handle request
    $result = g::run("api.handle", "users", array(
        "allowed_methods" => array("GET", "POST", "PUT", "DELETE"),
        "auth_required" => true
    ));
    
    // Send response
    g::run("api.respond", $result);
}
```

### Pattern 3: Authentication Flow

```php
// Initialize auth system
g::run("auth.init");

// Register user
$hash = g::run("auth.register", array(
    "email" => "user@example.com",
    "password" => "secret123",
    "name" => "User Name"
));

// Login
$success = g::run("auth.login", "user@example.com", "secret123");

// Check if logged in
if (g::run("auth.check")) {
    $user = g::run("auth.user");
    echo "Welcome, " . $user["name"];
}

// Logout
g::run("auth.logout");
```

### Pattern 4: Frontend Data Binding

```html
<div id="userCard" data-g-bind="name"></div>
<img data-g-attr="src:avatar" />
<div data-g-if="isAdmin">Admin Panel</div>
<span data-g-class="active:isOnline"></span>

<script>
    // Bind data to elements
    const container = g.el("#userCard");
    g.bind(container, {
        name: "John Doe",
        avatar: "/images/john.jpg",
        isAdmin: true,
        isOnline: true
    });
</script>
```

### Pattern 5: Event Delegation

```javascript
// Global delegated events (efficient for dynamic content)
g.on("click", ".delete-btn", function(element) {
    const id = element.dataset.id;
    
    if (confirm("Delete this item?")) {
        g.api.delete("items", id, function(response) {
            if (response.success) {
                element.closest(".item").remove();
            }
        });
    }
});

// Form submission
g.on("submit", "#loginForm", function(form) {
    const data = g.formData(form);
    
    g.api.post("auth/login", data, function(response) {
        if (response.success) {
            window.location = "/dashboard";
        }
    });
});
```

### Pattern 6: Template System

```php
// Register template helper
g::run("tpl.helper", "foreach", function($content, $data, $element) {
    $items = isset($data['items']) ? $data['items'] : array();
    $output = "";
    
    foreach ($items as $item) {
        $rendered = g::run("tpl.render", $content, $item);
        $output .= $rendered;
    }
    
    return $output;
});

// Use in template
$html = '<ul>
    <li data-helper="foreach">
        <strong>{{name}}</strong> - {{email}}
    </li>
</ul>';

$result = g::run("tpl.render", $html, array(
    "items" => array(
        array("name" => "John", "email" => "john@example.com"),
        array("name" => "Jane", "email" => "jane@example.com")
    )
));
```

---

## 🔒 Security Features

### Password Hashing with Pepper

```php
// Hash password (bcrypt + HMAC pepper from config.security.secret)
$hash = g::run("auth.hash", "userPassword123");

// Verify password
$valid = g::run("auth.verify", "userPassword123", $hash);
```

**How it works:**
1. Applies HMAC-SHA256 with application secret (pepper)
2. Then applies bcrypt with random salt
3. Even if database is compromised, attacker needs `config.json` secret

### CSRF Protection

```php
// Generate token
$token = g::run("auth.token");
g::set("csrf.token", $token);

// Validate token
$submittedToken = $_POST["csrf_token"];
if ($submittedToken === g::get("csrf.token")) {
    // Valid request
}
```

### SQL Injection Prevention

All database queries use PDO prepared statements:

```php
// Safe - uses prepared statements internally
g::run("db.select", "users", array(
    "email" => $_POST["email"] // Automatically escaped
));
```

### XSS Prevention

```php
// Sanitize output
$safe = htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// Or use template engine (auto-escapes {{vars}})
g::run("tpl.render", "<p>{{userContent}}</p>", array(
    "userContent" => $untrustedInput
));
```

---

## 📊 Database Schema Convention

Genes uses a standard 5-table schema that works for most applications:

```sql
-- Core tables
CREATE TABLE persons (
    hash VARCHAR(32) PRIMARY KEY,
    type VARCHAR(32),
    state VARCHAR(32),
    email VARCHAR(255),
    password VARCHAR(255),
    name VARCHAR(255),
    meta TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE clones (
    hash VARCHAR(32) PRIMARY KEY,
    type VARCHAR(32),
    state VARCHAR(32),
    person_hash VARCHAR(32),
    title VARCHAR(512),
    content TEXT,
    meta TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE links (
    hash VARCHAR(32) PRIMARY KEY,
    type VARCHAR(32),
    state VARCHAR(32),
    from_hash VARCHAR(32),
    to_hash VARCHAR(32),
    value DECIMAL(10,2),
    meta TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE nodes (
    hash VARCHAR(32) PRIMARY KEY,
    type VARCHAR(32),
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
    type VARCHAR(32),
    state VARCHAR(32),
    ref_hash VARCHAR(32),
    data TEXT,
    created_at TIMESTAMP
);
```

**Design Philosophy:**
- **`persons`**: Users, accounts, profiles
- **`clones`**: User-generated content (posts, shares, comments)
- **`links`**: Relationships between entities (follows, likes, votes)
- **`nodes`**: Static content (pages, categories, tags)
- **`events`**: Audit log, analytics, activity tracking

**Universal Fields:**
- `hash`: 32-char unique ID (MD5 of timestamp + random)
- `type`: Subtype classifier (e.g., "admin", "post", "follow")
- `state`: Status (e.g., "active", "pending", "deleted")
- `meta`: JSON field for flexible data
- `created_at`, `updated_at`, `deleted_at`: Timestamps

---

## 🎨 CSS Framework Guide

### Viewport System (REM-based Scaling)

Genes CSS uses a **REM-based viewport system** that automatically scales:

```
Mobile:  32rem canvas  (320-639px)   - 1rem = 3.125vw
Tablet:  64rem canvas  (640-1279px)  - 1rem = 1.5625vw
Desktop: 128rem canvas (1280px+)     - 1rem = 0.78125vw
```

**What this means:**
- Write `width: 10rem` once
- It's 32px on mobile, 64px on tablet, 80px on desktop
- Everything scales proportionally - no media query madness!

### Common Utility Classes

```html
<!-- Layout -->
<div class="container">Centered max-width container</div>
<div class="flex flex-between">Space between items</div>
<div class="grid grid-3">3-column grid</div>

<!-- Spacing -->
<div class="p-2">Padding 2rem</div>
<div class="m-4">Margin 4rem</div>
<div class="gap-2">Gap 2rem</div>

<!-- Typography -->
<h1 class="text-3xl font-bold">Large bold heading</h1>
<p class="text-muted">Muted text</p>

<!-- Components -->
<button class="btn btn-primary">Primary Button</button>
<input class="input" type="text" placeholder="Input field">
<div class="card">Card container</div>
```

### Dark/Light Theme

```html
<!-- Toggle theme -->
<html data-theme="light">
<html data-theme="dark">

<script>
    // Toggle theme
    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        g.ls.set('theme', next);
    }
</script>
```

---

## 🧪 Testing Patterns

### Manual API Testing

```php
// test.php
require_once './.genes/genes.php';

// Test database connection
$result = g::run("db.connect", array(
    "host" => "localhost",
    "name" => "testdb",
    "user" => "root",
    "pass" => ""
));

echo $result ? "✅ Database connected\n" : "❌ Connection failed\n";

// Test CRUD operations
$hash = g::run("db.insert", "users", array(
    "name" => "Test User",
    "email" => "test@example.com"
));

echo $hash ? "✅ Insert successful: $hash\n" : "❌ Insert failed\n";

$users = g::run("db.select", "users", array("hash" => $hash));
echo count($users) > 0 ? "✅ Select successful\n" : "❌ Select failed\n";
```

### Frontend Console Testing

```javascript
// Open browser console (F12) and test:

// Test API
g.api.list("users", function(data) {
    console.log("Users:", data);
});

// Test state management
g.set("test.value", 123);
console.log(g.get("test.value")); // 123

// Test storage
g.ls.set("myKey", {foo: "bar"});
console.log(g.ls.get("myKey")); // {foo: "bar"}
```

---

## 🎯 AI Agent Tips & Best Practices

### 1. **Start Simple, Add Complexity**

```php
// ✅ Good: Start with basic pattern
g::def("app.users", array(
    "list" => function() {
        return g::run("db.select", "users");
    }
));

// Later expand:
g::def("app.users", array(
    "list" => function($filters = array()) {
        return g::run("db.select", "users", $filters);
    },
    "get" => function($hash) {
        $result = g::run("db.select", "users", array("hash" => $hash));
        return $result ? $result[0] : null;
    },
    "create" => function($data) {
        return g::run("db.insert", "users", $data);
    }
));
```

### 2. **Use Consistent Naming Conventions**

```php
// ✅ Recommended patterns:
g::def("app.resource", array(
    "list"   => function() { /* ... */ },  // Get all
    "get"    => function($id) { /* ... */ }, // Get one
    "create" => function($data) { /* ... */ }, // Create
    "update" => function($id, $data) { /* ... */ }, // Update
    "delete" => function($id) { /* ... */ }  // Delete
));
```

### 3. **Leverage the `meta` Field**

```php
// Store flexible JSON data in meta field
g::run("db.insert", "users", array(
    "name" => "John Doe",
    "email" => "john@example.com",
    "meta" => json_encode(array(
        "preferences" => array(
            "theme" => "dark",
            "notifications" => true
        ),
        "stats" => array(
            "posts" => 42,
            "followers" => 128
        )
    ))
));

// Retrieve and decode
$user = g::run("db.select", "users", array("hash" => $hash))[0];
$meta = json_decode($user["meta"], true);
echo $meta["preferences"]["theme"]; // "dark"
```

### 4. **Error Handling Pattern**

```php
// Backend
g::def("app.users.create", function($data) {
    try {
        // Validate
        if (empty($data["email"])) {
            return array("success" => false, "error" => "Email required");
        }
        
        // Process
        $hash = g::run("db.insert", "users", $data);
        
        // Return
        if ($hash) {
            return array("success" => true, "hash" => $hash);
        } else {
            return array("success" => false, "error" => "Insert failed");
        }
        
    } catch (Exception $e) {
        g::run("log.error", $e->getMessage());
        return array("success" => false, "error" => "Internal error");
    }
});

// Frontend
g.api.create("users", userData, function(response) {
    if (response.success) {
        console.log("Created:", response.hash);
    } else {
        alert("Error: " + response.error);
    }
});
```

### 5. **Organize Code by Feature**

```
myapp/
├── .genes/           # Framework files
├── data/             # Configuration & data
│   └── config.json
├── ui/               # Frontend templates
│   ├── index.html
│   ├── assets/
│   │   ├── app.js
│   │   └── app.css
├── mods/             # Feature modules
│   ├── users.php
│   ├── posts.php
│   └── comments.php
└── index.php         # Main entry point
```

```php
// index.php
require_once './.genes/genes.php';
require_once './mods/users.php';
require_once './mods/posts.php';

// Route requests
g::run("route.parseUrl");
$request = g::get("request");

if ($request["path"] === "/api/users") {
    $result = g::run("app.users.handle");
    g::run("api.respond", $result);
}
```

### 6. **Use Lifecycle Hooks**

```javascript
// Frontend initialization sequence
g.que("onReady", function() {
    console.log("DOM ready");
    
    // Initialize app
    g.run("app.init");
});

g.def("app.init", function() {
    // Check authentication
    if (g.auth.check()) {
        g.run("app.loadUserData");
    }
    
    // Set up global event handlers
    g.on("click", ".logout-btn", function() {
        g.auth.logout();
    });
});

// Run when auth state changes
g.que("onAuthChange", function(user) {
    if (user) {
        g.run("app.showDashboard");
    } else {
        g.run("app.showLogin");
    }
});
```

---

## 📖 Complete API Reference

### PHP Core Functions

#### `g::set($key, $value)`
Store data in application state using dot notation.

```php
g::set("user.name", "John");
g::set("config.theme", "dark");
g::set("app.settings", array("debug" => true));
```

#### `g::get($key)`
Retrieve data from application state.

```php
$name = g::get("user.name"); // "John"
$settings = g::get("app.settings"); // array
```

#### `g::del($key)`
Delete data from application state.

```php
g::del("user.name");
```

#### `g::def($key, $callable)`
Define a function or function namespace.

```php
// Single function
g::def("app.init", function() {
    echo "App initialized";
});

// Namespace of functions
g::def("app.users", array(
    "list" => function() { /* ... */ },
    "get" => function($id) { /* ... */ }
));
```

#### `g::run($key, ...$args)`
Execute a registered function with arguments.

```php
g::run("app.init");
$user = g::run("app.users.get", $userId);
```

#### `g::has($key)`
Check if a function exists.

```php
if (g::has("app.users.list")) {
    g::run("app.users.list");
}
```

#### `g::log($key)`
Debug output for development.

```php
g::log(0); // Print all app data
g::log(1); // Print all registered functions
g::log("user"); // Print specific data
```

### JavaScript Core Functions

#### `g.set(key, value)`
Store data in application state.

```javascript
g.set("user.isLoggedIn", true);
g.set("app.theme", "dark");
```

#### `g.get(key)`
Retrieve data from application state.

```javascript
const theme = g.get("app.theme"); // "dark"
```

#### `g.def(key, fn)`
Define a function.

```javascript
g.def("app.init", function() {
    console.log("App initialized");
});
```

#### `g.run(key, ...args)`
Execute a registered function.

```javascript
g.run("app.init");
g.run("app.loadData", userId);
```

#### `g.que(key, fn)`
Queue a function to run on lifecycle hook.

```javascript
g.que("onReady", function() {
    console.log("DOM ready");
});
```

### Database Functions

#### `g::run("db.connect", $params)`
Connect to database.

```php
g::run("db.connect", array(
    "host" => "localhost",
    "name" => "mydb",
    "user" => "root",
    "pass" => "",
    "type" => "mysql"
));
```

#### `g::run("db.select", $table, $where, $dbName, $options)`
Select records from database.

```php
// Simple select
$users = g::run("db.select", "users", array(
    "state" => "active"
));

// With options
$users = g::run("db.select", "users", array(
    "state" => "active"
), "default", array(
    "limit" => 10,
    "offset" => 0,
    "order" => "created_at DESC"
));
```

#### `g::run("db.insert", $table, $data, $dbName)`
Insert a record.

```php
$hash = g::run("db.insert", "users", array(
    "name" => "John Doe",
    "email" => "john@example.com",
    "state" => "active"
));
```

#### `g::run("db.update", $table, $where, $data, $dbName)`
Update records.

```php
g::run("db.update", "users", 
    array("hash" => $userHash),
    array("name" => "Jane Doe")
);
```

#### `g::run("db.delete", $table, $where, $hard, $dbName)`
Delete records (soft delete by default).

```php
// Soft delete (sets deleted_at timestamp)
g::run("db.delete", "users", array("hash" => $hash));

// Hard delete (removes from database)
g::run("db.delete", "users", array("hash" => $hash), true);
```

### Authentication Functions

#### `g::run("auth.init")`
Initialize authentication system (starts session).

```php
g::run("auth.init");
```

#### `g::run("auth.register", $data)`
Register a new user.

```php
$hash = g::run("auth.register", array(
    "email" => "user@example.com",
    "password" => "secret123",
    "name" => "User Name"
));
```

#### `g::run("auth.login", $email, $password)`
Authenticate a user.

```php
$success = g::run("auth.login", "user@example.com", "secret123");
if ($success) {
    echo "Logged in!";
}
```

#### `g::run("auth.check")`
Check if user is authenticated.

```php
if (g::run("auth.check")) {
    // User is logged in
}
```

#### `g::run("auth.user")`
Get current authenticated user.

```php
$user = g::run("auth.user");
echo $user["name"];
```

#### `g::run("auth.logout")`
Log out current user.

```php
g::run("auth.logout");
```

#### `g::run("auth.hash", $password)`
Hash a password with bcrypt + pepper.

```php
$hash = g::run("auth.hash", "secret123");
```

#### `g::run("auth.verify", $password, $hash)`
Verify a password against hash.

```php
$valid = g::run("auth.verify", "secret123", $storedHash);
```

### Frontend API Functions

#### `g.api.list(table, params, success, error)`
Get list of records.

```javascript
g.api.list("users", {state: "active"}, function(data) {
    console.log(data);
});
```

#### `g.api.get(table, hash, success, error)`
Get single record by hash.

```javascript
g.api.get("users", userHash, function(user) {
    console.log(user);
});
```

#### `g.api.create(table, data, success, error)`
Create a new record.

```javascript
g.api.create("users", {
    name: "John Doe",
    email: "john@example.com"
}, function(response) {
    console.log("Created:", response.hash);
});
```

#### `g.api.update(table, hash, data, success, error)`
Update a record.

```javascript
g.api.update("users", userHash, {
    name: "Jane Doe"
}, function(response) {
    console.log("Updated");
});
```

#### `g.api.delete(table, hash, success, error)`
Delete a record.

```javascript
g.api.delete("users", userHash, function(response) {
    console.log("Deleted");
});
```

### Frontend DOM Functions

#### `g.el(selector, parent)`
Get first matching element (like `querySelector`).

```javascript
const btn = g.el("#myButton");
const firstItem = g.el(".item", container);
```

#### `g.els(selector, parent)`
Get all matching elements (like `querySelectorAll`).

```javascript
const buttons = g.els(".btn");
buttons.forEach(btn => console.log(btn));
```

#### `g.create(tag, attrs)`
Create an element with attributes.

```javascript
const div = g.create("div", {
    className: "card",
    innerHTML: "<h2>Title</h2>"
});
```

#### `g.on(event, selector, callback)`
Delegated event listener.

```javascript
g.on("click", ".delete-btn", function(element) {
    console.log("Clicked:", element);
});
```

---

## 🚀 Deployment Checklist

### Production Configuration

```json
{
  "environments": {
    "yourdomain.com": {
      "database": {
        "host": "localhost",
        "name": "prod_db",
        "user": "prod_user",
        "pass": "strong_password"
      },
      "settings": {
        "debug": false,
        "log_level": 1,
        "timezone": "UTC"
      },
      "security": {
        "secret": "CHANGE_THIS_64_CHAR_SECRET",
        "salt": "CHANGE_THIS_64_CHAR_SALT"
      }
    }
  }
}
```

### Apache `.htaccess`

Genes auto-generates this, but verify:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

### File Permissions

```bash
# Make writable
chmod 755 data/
chmod 755 cache/
chmod 755 uploads/
chmod 644 data/config.json

# Protect sensitive files
chmod 644 .genes/genes.php
chmod 644 index.php
```

### Security Checklist

- [ ] Change `config.security.secret` to random 64-char string
- [ ] Change `config.security.salt` to random 64-char string
- [ ] Set `settings.debug` to `false` in production
- [ ] Set `log_level` to `1` (errors only)
- [ ] Enable HTTPS
- [ ] Configure CORS if needed
- [ ] Review database permissions
- [ ] Enable rate limiting for API endpoints

---

## 🤝 Contributing & Support

### GitHub Repository Structure

```
genes-framework/
├── .genes/
│   ├── genes.php
│   ├── genes.js
│   └── genes.css
├── examples/
│   ├── basic-app/
│   ├── blog/
│   ├── api-server/
│   └── spa/
├── docs/
│   ├── README.md
│   ├── API.md
│   ├── GUIDE.md
│   └── CHANGELOG.md
├── tests/
│   ├── php/
│   └── js/
├── LICENSE (MIT)
└── README.md
```

### Version History

- **v2.0** (2025-10-11): Complete rewrite, AI-optimized, single-file architecture
- **v1.0** (2024): Initial release

### Community & Support

- 📧 Email: support@genes.one
- 💬 Discord: [Join Community]
- 📖 Docs: https://docs.genes.one
- 🐛 Issues: https://github.com/[org]/genes-framework/issues

---

## 🎓 Learning Resources

### Example Projects

1. **Basic CRUD App**: See `examples/basic-app/`
2. **Blog System**: See `examples/blog/`
3. **REST API Server**: See `examples/api-server/`
4. **Single Page App**: See `examples/spa/`

### Video Tutorials

- Getting Started (10 min)
- Building a Blog (30 min)
- Authentication System (20 min)
- Advanced Patterns (45 min)

### Code Snippets

Visit https://genes.one/snippets for copy-paste examples:
- User registration flow
- File upload handler
- Image processing
- Email sending
- Payment integration
- Real-time notifications

---

## 📄 License

**MIT License**

Copyright (c) 2024-2025 NodOnce OÜ

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

---

## 🌟 Why Genes for AI Agents?

### Traditional Framework Challenges for AI:

❌ Complex folder structures  
❌ Multiple configuration files  
❌ Build tools and dependencies  
❌ Framework-specific conventions  
❌ Heavy abstractions  

### Genes Advantages:

✅ **Single-file architecture** - Easy to understand and modify  
✅ **Consistent API** - Same patterns everywhere (`g::run`, `g.api`)  
✅ **Zero dependencies** - No package management  
✅ **Self-documenting** - Examples in every function  
✅ **Production-ready** - Security, logging, performance built-in  
✅ **Flexible** - Use as library or framework  
✅ **Predictable** - No magic, no surprises  

---

**Ready to build? Start with the Quick Start Guide above! 🚀**
