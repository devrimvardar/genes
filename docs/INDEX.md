# Genes Framework Documentation

> **Lightweight PHP & Vanilla JS Framework**  
> PHP 5.6+ | MySQL, SQLite | Zero Dependencies

## Quick Navigation

### Getting Started
- [Quickstart Guide](QUICKSTART.md) - Get up and running in 5 minutes
- [Architecture Overview](ARCHITECTURE.md) - Understand the framework design
- [Database Schema](../DATABASE-SCHEMA.md) - Complete schema reference

### Core Documentation
- [API Reference](API-REFERENCE.md) - Complete function reference
- [Examples](EXAMPLES.md) - Real-world usage patterns
- [Multi-Tenancy Guide](MULTI-TENANCY.md) - Clone-based isolation

### Additional Resources
- [GitHub Repository](https://github.com/devrimvardar/genes)
- [Changelog](../CHANGELOG.md)
- [Contributing](../CONTRIBUTING.md)

## What is Genes?

Genes is a **single-file PHP framework** designed for rapid development with:

- ✅ **Zero Dependencies** - No Composer, npm, or build tools required
- ✅ **Multi-Tenant Architecture** - Built-in clone-based isolation
- ✅ **Database Agnostic** - MySQL, SQLite (PostgreSQL planned)
- ✅ **AI-Friendly** - Clear, readable, self-documenting code
- ✅ **PHP 5.6+** - Works on legacy and modern servers
- ✅ **Single-File Design** - genes.php (~6,400 lines), genes.js (~1,300 lines)

## Core Tables

The framework uses a **5-table schema** for multi-tenant applications:

1. **`clones`** - Projects/Instances (master multi-tenancy table)
2. **`persons`** - Users with `clone_id` (per-clone users)
3. **`items`** - Content with `clone_id` (posts, pages, products, comments)
4. **`labels`** - Taxonomy with `clone_id` (categories, tags, statuses)
5. **`events`** - Audit log with `clone_id` (analytics, tracking)

All tables except `clones` have a `clone_id` field for automatic isolation.

## Quick Example

```php
<?php
require_once 'genes.php';

// Connect to database
g::run("db.connect", array(
    "driver" => "sqlite",
    "name" => "main",
    "database" => "data/blog.db"
));

// Create schema
g::run("db.createSchema", "main");

// Set clone context (multi-tenant isolation)
$clone = g::run("db.get", "clones", $cloneHash);
g::run("db.setClone", $cloneHash);

// Create a blog post (automatically filtered by clone_id)
$postHash = g::run("db.insert", "items", array(
    "type" => "post",
    "state" => "published",
    "title" => "Hello World",
    "text" => "My first post with the correct schema!"
));

// Retrieve posts (auto-filtered by current clone)
$posts = g::run("db.select", "items", array(
    "type" => "post",
    "state" => "published"
));
?>
```

## Framework Phases

Genes is organized into logical phases:

1. **Core** - Base utilities, path management
2. **Config** - Configuration management
3. **Logging** - Error handling, debugging
4. **Routing** - URL parsing, dispatching
5. **Auth** - Session management, authentication
6. **Cryptography** - Hashing, encryption
7. **Database** - Multi-tenant CRUD operations
8. **API** - RESTful API handlers

## Documentation Structure

```
docs/
├── INDEX.md              # This file - navigation hub
├── QUICKSTART.md         # 5-minute getting started guide
├── ARCHITECTURE.md       # Framework design & concepts
├── API-REFERENCE.md      # Complete function reference
├── EXAMPLES.md           # Cookbook-style examples
└── MULTI-TENANCY.md      # Clone isolation patterns
```

## Philosophy

**AI-First Development**: Genes is designed to be read by both humans and AI assistants. Clear naming, comprehensive comments, and logical organization make it easy to understand and extend.

**Convention over Configuration**: Sensible defaults with optional customization. Works out of the box for 80% of use cases.

**Progressive Enhancement**: Start simple, add complexity as needed. From a basic CRUD app to a multi-tenant SaaS platform.

## Next Steps

1. **New to Genes?** → Start with [Quickstart Guide](QUICKSTART.md)
2. **Building an API?** → See [API Reference](API-REFERENCE.md)
3. **Need examples?** → Check [Examples](EXAMPLES.md)
4. **Multi-tenant app?** → Read [Multi-Tenancy Guide](MULTI-TENANCY.md)
