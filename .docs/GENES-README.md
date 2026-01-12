# Genes Framework v2.0

> **A lightweight, AI-friendly PHP & Vanilla JS framework for rapid web development**

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-5.6%2B-blue)](https://php.net)
[![Framework](https://img.shields.io/badge/Framework-Single--File-green)]()

---

## 🎯 What is Genes?

**Genes** is a complete full-stack framework contained in just **3 files** (~9,400 lines total):

- **`genes.php`** (~6,400 lines) - Full backend framework with database, auth, routing, API, templating
- **`genes.js`** (~1,300 lines) - Frontend library with DOM utilities, AJAX, events, state management
- **`genes.css`** (~1,700 lines) - Responsive CSS framework with dark/light themes

### Key Features

✅ **Zero Dependencies** - No Composer, no npm, no build tools  
✅ **Single-File Architecture** - Easy to understand, modify, and deploy  
✅ **AI-Optimized** - Consistent patterns, predictable APIs, self-documenting code  
✅ **Production Ready** - Built-in security, sessions, logging, performance tracking  
✅ **Full-Stack** - Complete backend + frontend solution  
✅ **Framework or Library** - Use as full framework or cherry-pick features  
✅ **PHP 5.6+** - Works on virtually any hosting environment  

---

## 📦 Installation

### Option 1: Direct Download

```bash
# Create project
mkdir myapp && cd myapp

# Create folders
mkdir .genes data cache ui mods uploads

# Download framework files
curl -o .genes/genes.php https://cdn.genes.one/genes.php
curl -o .genes/genes.js https://cdn.genes.one/genes.js
curl -o .genes/genes.css https://cdn.genes.one/genes.css

# Create entry point
echo '<?php require_once "./.genes/genes.php";' > index.php
```

### Option 2: Git Clone

```bash
git clone https://github.com/[your-org]/genes-framework.git myapp
cd myapp
```

### Option 3: CDN (Frontend Only)

```html
<link rel="stylesheet" href="https://cdn.genes.one/genes.css">
<script src="https://cdn.genes.one/genes.js"></script>
```

---

## 🚀 Quick Start

### 1. Minimal App

**`index.php`:**
```php
<?php
require_once './.genes/genes.php';

echo "Hello, Genes!";
```

### 2. Database App

**`index.php`:**
```php
<?php
require_once './.genes/genes.php';

// Connect to database
g::run("db.connect", array(
    "host" => "localhost",
    "name" => "mydb",
    "user" => "root",
    "pass" => ""
));

// Create tables
g::run("db.createSchema");

// Insert data
$hash = g::run("db.insert", "users", array(
    "name" => "John Doe",
    "email" => "john@example.com"
));

// Select data
$users = g::run("db.select", "users");
print_r($users);
```

### 3. REST API

**`index.php`:**
```php
<?php
require_once './.genes/genes.php';

// Setup
g::run("db.connect", array(
    "host" => "localhost",
    "name" => "mydb",
    "user" => "root",
    "pass" => ""
));

g::run("route.parseUrl");
$request = g::get("request");

// Handle API requests
if ($request["segments"][0] === "api") {
    $table = $request["segments"][1];
    $result = g::run("api.handle", $table);
    g::run("api.respond", $result);
}
```

**Usage:**
```bash
# GET all users
curl http://localhost/api/users

# GET single user
curl http://localhost/api/users/{hash}

# CREATE user
curl -X POST http://localhost/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com"}'

# UPDATE user
curl -X PUT http://localhost/api/users/{hash} \
  -H "Content-Type: application/json" \
  -d '{"name":"Jane"}'

# DELETE user
curl -X DELETE http://localhost/api/users/{hash}
```

### 4. Full-Stack App with Frontend

**`ui/index.html`:**
```html
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../.genes/genes.css">
    <script src="../.genes/genes.js" defer></script>
</head>
<body>
    <div class="container">
        <h1>My App</h1>
        <div id="userList"></div>
    </div>

    <script>
        g.que("onReady", function() {
            // Load users from API
            g.api.list("users", function(data) {
                const container = g.el("#userList");
                data.forEach(user => {
                    const div = g.create("div", {
                        innerHTML: user.name + " - " + user.email
                    });
                    container.appendChild(div);
                });
            });
        });
    </script>
</body>
</html>
```

---

## 📖 Documentation

### Core Documentation

- **[AI Framework Guide](docs/GENES-AI-FRAMEWORK.md)** - Complete guide for AI coding agents
- **[Quick Reference](docs/GENES-QUICKREF.md)** - One-page cheat sheet
- **[Examples](docs/GENES-EXAMPLES.md)** - Real-world code examples
- **[API Reference](docs/API.md)** - Complete function reference
- **[Security Guide](docs/SECURITY.md)** - Security best practices

### Core Concepts

#### PHP Backend (Class `g`)

```php
// State management
g::set("key", $value)              // Store data
g::get("key")                      // Retrieve data
g::del("key")                      // Delete data

// Function registry
g::def("namespace.function", $callable)  // Define function
g::run("namespace.function", $args)      // Execute function
g::has("namespace.function")             // Check if exists
```

#### JavaScript Frontend (Object `g`)

```javascript
// State management
g.set("key", value)                // Store data
g.get("key")                       // Retrieve data

// Function registry
g.def("name", callback)            // Define function
g.run("name", args)                // Execute function
g.que("hook", callback)            // Queue on lifecycle hook

// DOM utilities
g.el("#id")                        // querySelector
g.els(".class")                    // querySelectorAll
g.on("click", ".btn", fn)          // Delegated events

// API integration
g.api.list("table", callback)      // GET /api/table
g.api.get("table", hash, callback) // GET /api/table/{hash}
g.api.create("table", data, callback) // POST /api/table
g.api.update("table", hash, data, callback) // PUT /api/table/{hash}
g.api.delete("table", hash, callback) // DELETE /api/table/{hash}
```

---

## 🗄️ Database Schema

Genes uses a universal 5-table schema that adapts to most applications:

| Table | Purpose | Example Usage |
|-------|---------|---------------|
| **persons** | Users, accounts, profiles | Users, admins, moderators |
| **clones** | User-generated content | Posts, comments, shares |
| **links** | Relationships between entities | Follows, likes, votes |
| **nodes** | Static content | Pages, categories, tags |
| **events** | Audit log, analytics | Logins, views, actions |

All tables share these fields:
- `hash` (VARCHAR 32) - Unique ID
- `type` (VARCHAR 32) - Subtype classifier
- `state` (VARCHAR 32) - Status (active, pending, deleted)
- `meta` (TEXT) - JSON flexible data
- `created_at`, `updated_at`, `deleted_at` (TIMESTAMP)

---

## 🔒 Security Features

### Password Hashing (Bcrypt + Pepper)

```php
// Hash password (bcrypt + HMAC pepper)
$hash = g::run("auth.hash", "password123");

// Verify password
$valid = g::run("auth.verify", "password123", $hash);
```

### SQL Injection Prevention

All database queries use PDO prepared statements automatically:

```php
// Safe - automatically uses prepared statements
g::run("db.select", "users", array(
    "email" => $_POST["email"]  // Automatically escaped
));
```

### XSS Prevention

```php
// Manual sanitization
$safe = htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// Template auto-escaping
g::run("tpl.render", "<p>{{userContent}}</p>", $data);
```

### CSRF Protection

```php
// Generate token
$token = g::run("auth.token");
$_SESSION["csrf_token"] = $token;

// Validate
if ($_POST["csrf_token"] === $_SESSION["csrf_token"]) {
    // Valid request
}
```

---

## 🎨 CSS Framework

### Responsive Viewport System

Genes CSS uses a **REM-based viewport system** that automatically scales:

```
Mobile:  32rem canvas  (320-639px)
Tablet:  64rem canvas  (640-1279px)  
Desktop: 128rem canvas (1280px+)
```

**Example:** `width: 10rem` → 32px mobile, 64px tablet, 80px desktop

### Utility Classes

```html
<!-- Layout -->
<div class="container flex flex-between">
    <div class="flex-1">Content</div>
    <div class="grid grid-3">Grid items</div>
</div>

<!-- Typography -->
<h1 class="text-3xl font-bold">Large heading</h1>
<p class="text-muted">Muted text</p>

<!-- Components -->
<button class="btn btn-primary">Primary Button</button>
<input class="input" type="text">
<div class="card">Card content</div>
```

### Dark/Light Theme

```html
<html data-theme="dark">  <!-- or "light" -->
```

---

## 🏗️ Project Structure

```
myapp/
├── .genes/              # Framework files (don't modify)
│   ├── genes.php
│   ├── genes.js
│   └── genes.css
├── data/                # Configuration & data
│   ├── config.json      # Auto-generated configuration
│   └── users.json       # Optional file-based auth
├── cache/               # Auto-generated cache
├── ui/                  # Frontend templates
│   ├── index.html
│   └── assets/          # Your CSS/JS
├── mods/                # Feature modules
│   ├── users.php
│   └── posts.php
├── uploads/             # User uploads
└── index.php            # Entry point
```

---

## 📊 Performance

- **Initialization**: ~2-5ms (without database)
- **Database query**: ~1-3ms per query
- **Template render**: ~0.5-2ms per template
- **Memory usage**: ~2-4MB base (scales with data)
- **File size**: 
  - genes.php: ~200KB (unminified)
  - genes.js: ~23KB (unminified)
  - genes.css: ~25KB (unminified)

---

## 🤝 Contributing

We welcome contributions! Here's how:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow existing code style (4 spaces, PSR-2 for PHP)
- Document all functions with PHPDoc/JSDoc
- Include examples in function comments
- Test on PHP 5.6, 7.4, and 8.x
- Ensure backward compatibility

---

## 📝 Changelog

### v2.0.0 (2025-10-11)
- Complete rewrite with AI-optimized architecture
- Single-file framework design
- Universal database schema
- Built-in API layer
- Enhanced security (bcrypt + pepper)
- Performance tracking
- Template system
- Module system

### v1.0.0 (2024-01-01)
- Initial release

---

## 📄 License

**MIT License**

Copyright (c) 2024-2025 NodOnce OÜ

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

---

## 🌟 Why Genes?

### For Developers

- **Fast Development**: Build CRUD apps in minutes
- **No Build Tools**: No webpack, no babel, no headaches
- **Portable**: Works anywhere PHP runs
- **Predictable**: No magic, no surprises
- **Flexible**: Use as much or as little as you need

### For AI Coding Agents

- **Consistent Patterns**: Same API everywhere
- **Self-Documenting**: Examples in every function
- **Single Namespace**: No confusion with `g::` and `g.`
- **Minimal Syntax**: Less tokens, clearer intent
- **Complete**: Everything needed in 3 files

### For Projects

- **Quick Prototypes**: Get MVPs running fast
- **Small Apps**: Perfect for simple tools and dashboards
- **APIs**: Clean REST API layer built-in
- **Learning**: Great for understanding web frameworks
- **Legacy**: Modernize old PHP sites incrementally

---

## 📞 Support & Community

- **Email**: support@genes.one
- **Discord**: [Join our community](https://discord.gg/genes)
- **Docs**: https://docs.genes.one
- **Issues**: [GitHub Issues](https://github.com/[your-org]/genes-framework/issues)
- **Discussions**: [GitHub Discussions](https://github.com/[your-org]/genes-framework/discussions)

---

## 🎓 Learning Resources

- [Getting Started Tutorial](https://genes.one/tutorial)
- [Video Course](https://genes.one/course)
- [Example Projects](https://genes.one/examples)
- [Code Snippets](https://genes.one/snippets)
- [Blog](https://genes.one/blog)

---

## 🙏 Acknowledgments

Built with ❤️ by [Devrim Vardar](https://github.com/devrimvardar)

Special thanks to:
- Everyone who contributed to making web development simpler
- The PHP and JavaScript communities
- AI coding assistants that inspired this framework's design

---

**Ready to build something amazing? Get started now! 🚀**

```bash
mkdir myapp && cd myapp
curl -o .genes/genes.php https://cdn.genes.one/genes.php
echo '<?php require_once "./.genes/genes.php"; echo "Hello, Genes!";' > index.php
php -S localhost:8000
```

Visit http://localhost:8000 and you're live!
