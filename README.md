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

**`data/config.json`:**
```json
{
    "database": {
        "enabled": true,
        "type": "sqlite",
        "database": "data/app.db"
    }
}
```

**`index.php`:**
```php
<?php
require_once './genes.php';

// Database auto-connects from config.json
// Schema auto-creates if database doesn't exist

// Insert data
$hash = g::run("db.insert", "persons", array(
    "name" => "John Doe",
    "email" => "john@example.com"
));

// Select data
$users = g::run("db.select", "persons");
print_r($users);
```

### 3. Built-in REST API

**`data/config.json`:**
```json
{
    "database": {
        "enabled": true,
        "type": "sqlite",
        "database": "data/app.db"
    }
}
```

**`index.php`:**
```php
<?php
require_once './genes.php';

// That's it! API is ready at /api/* routes
g::run("route.handle");
```

**Built-in API Endpoints:**
```bash
# List all items
GET /api/items

# Filter items
GET /api/items?filters[type]=todo

# Get single item
GET /api/items/:hash

# Create item
POST /api/items -d '{"type":"todo","title":"My Todo"}'

# Update item
PUT /api/items/:hash -d '{"state":"completed"}'

# Delete item
DELETE /api/items/:hash
```

Works on all tables: `/api/items`, `/api/persons`, `/api/labels`, `/api/clones`, `/api/events`

---

## 📖 Documentation

### Getting Started
- **[Quickstart Guide](docs/QUICKSTART.md)** - Get up and running in 5 minutes
- **[Examples](examples/)** - Complete working applications

### Reference
- **[API Reference](docs/API.md)** - Complete function reference
- **[Database Schema](DATABASE-SCHEMA.md)** - 5-table schema documentation
- **[Architecture](docs/ARCHITECTURE.md)** - Framework design & philosophy
- **[Multi-Tenancy](docs/MULTI-TENANCY.md)** - Building multi-tenant apps

### Core Concepts

**The `g` Class** - Everything runs through one global class:

```php
// Database
g::run("db.insert", "items", $data)
g::run("db.select", "items", $conditions)

// Templates
g::run("tpl.renderView", "Index", $data)

// Authentication
g::run("auth.login", $email, $password)
```

**5-Table Schema** - Universal structure for all content:
- `clones` - Projects/instances (multi-tenant master)
- `persons` - Users (with clone_id)
- `items` - Content (posts, products, etc - with clone_id)
- `labels` - Categories/tags (with clone_id)
- `events` - Activity log (with clone_id)

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

### [Example 1: Landing Page](examples/1-landing-page/)
Multi-language landing page with no database. Demonstrates template engine, routing, partials, and responsive CSS.

### [Example 2: Blog System](examples/2-blog-system/)
Full-featured blog with multi-language support. Shows proper use of the 5-table schema, pagination, and related content.

### [Example 3: REST API / Todo](examples/3-rest-api/)
Complete RESTful API with CRUD operations. Includes interactive demo UI and proper HTTP method routing.

**[See all examples →](examples/README.md)**

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
