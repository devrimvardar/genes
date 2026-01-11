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

### Option 1: Download Files

Download the 3 framework files and start building:

```bash
# Create project
mkdir myapp && cd myapp

# Download framework files
curl -O https://raw.githubusercontent.com/devrimvardar/genes/main/genes.php
curl -O https://raw.githubusercontent.com/devrimvardar/genes/main/genes.js
curl -O https://raw.githubusercontent.com/devrimvardar/genes/main/genes.css

# Create entry point
echo '<?php require_once "./genes.php"; echo "Hello, Genes!";' > index.php

# Run
php -S localhost:8000
```

### Option 2: Git Clone

```bash
git clone https://github.com/devrimvardar/genes.git
cd genes
cd examples/1-hello-world
php -S localhost:8000
```

---

## 🚀 Quick Start

### 1. Minimal App

**`index.php`:**
```php
<?php
require_once './genes.php';

echo "Hello, Genes!";
```

### 2. Database App

**`index.php`:**
```php
<?php
require_once './genes.php';

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
$hash = g::run("db.insert", "persons", array(
    "name" => "John Doe",
    "email" => "john@example.com"
));

// Select data
$users = g::run("db.select", "persons");
print_r($users);
```

### 3. REST API

**`index.php`:**
```php
<?php
require_once './genes.php';

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
curl http://localhost:8000/api/persons

# CREATE user
curl -X POST http://localhost:8000/api/persons \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com"}'
```

---

## 📖 Documentation

- **[AI Framework Guide](docs/GENES-AI-FRAMEWORK.md)** - Complete guide for AI coding agents (~40 pages)
- **[Quick Reference](docs/GENES-QUICKREF.md)** - One-page cheat sheet
- **[Examples](docs/GENES-EXAMPLES.md)** - Real-world code examples (~20 pages)
- **[Installation Guide](docs/INSTALLATION.md)** - Detailed setup instructions
- **[API Reference](docs/API.md)** - Complete function reference

### Core Concepts

#### PHP Backend (Class `g`)

```php
// State management
g::set("key", $value)              // Store data
g::get("key")                      // Retrieve data

// Function registry
g::run("db.select", "persons")     // Execute function
g::run("auth.login", $email, $pass) // With arguments
```

#### JavaScript Frontend (Object `g`)

```javascript
// DOM utilities
g.el("#id")                        // querySelector
g.on("click", ".btn", fn)          // Delegated events

// API integration
g.api.list("persons", callback)    // GET /api/persons
g.api.create("persons", data, cb)  // POST /api/persons
```

---

## 🗄️ Database Schema

Genes uses a **multi-tenant 5-table schema** for maximum flexibility:

| Table | Purpose | Example Usage |
|-------|---------|---------------|
| **clones** | Projects/instances (multi-tenancy) | Blog, shop, platform |
| **persons** | Users, accounts (per clone) | Users, admins, authors |
| **items** | Content (per clone) | Posts, pages, comments, products |
| **labels** | Taxonomy/tags (per clone) | Categories, tags, statuses |
| **events** | Audit log, analytics (per clone) | Logins, views, actions |

**Multi-Tenancy**: Run multiple projects from one database. Each clone has isolated `persons`, `items`, `labels`, and `events` via `clone_id`.

**Supported Databases**: MySQL, MariaDB, SQLite

See [DATABASE-SCHEMA.md](DATABASE-SCHEMA.md) for complete schema reference.

---

## 🔒 Security Features

- **Password Hashing**: Bcrypt + HMAC pepper
- **SQL Injection**: PDO prepared statements (automatic)
- **XSS Prevention**: Template auto-escaping
- **CSRF Protection**: Built-in token generation
- **Session Security**: Secure defaults, regeneration

---

## 💡 Examples

Check out the [examples/](examples/) folder for complete working applications:

- **Hello World** - Minimal setup
- **Database CRUD** - Full database operations
- **REST API** - Complete API server
- **Blog System** - Real-world blog with auth
- **Single Page App** - Frontend-driven application

---

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

---

## 📄 License

MIT License - see [LICENSE](LICENSE) file for details

Copyright (c) 2024-2026 NodOnce OÜ

---

## 🌟 Why Genes?

**For Developers:**
- Build CRUD apps in minutes
- No build tools required
- Works anywhere PHP runs
- Use as much or as little as you need

**For AI Coding Agents:**
- Consistent patterns everywhere
- Self-documenting code
- Single namespace (`g`)
- Minimal syntax, maximum clarity

**For Projects:**
- Quick prototypes and MVPs
- Simple tools and dashboards
- Clean REST APIs
- Learning web frameworks

---

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/devrimvardar/genes/issues)
- **Discussions**: [GitHub Discussions](https://github.com/devrimvardar/genes/discussions)
- **Security**: See [SECURITY.md](SECURITY.md)

---

**Ready to build something amazing? 🚀**

```bash
curl -O https://raw.githubusercontent.com/devrimvardar/genes/main/genes.php
echo '<?php require "./genes.php"; echo "Hello!";' > index.php
php -S localhost:8000
```
