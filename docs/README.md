# Genes Framework Documentation

Complete documentation for Genes Framework v2.0.

## Getting Started

### New to Genes?
Start here to get up and running in 5 minutes.

📘 **[Quickstart Guide](QUICKSTART.md)**
- Installation
- Hello World
- Database setup
- CRUD operations
- Templates & routing

## Core Documentation

### 📚 [API Reference](API.md)
Complete reference for all framework functions.
- Database operations (insert, select, update, delete)
- Template rendering
- Routing
- Authentication
- Utilities

### 🏗️ [Architecture](ARCHITECTURE.md)
Framework design and philosophy.
- Design principles
- The `g` class
- Framework phases
- Code organization

### 🗄️ [Database Schema](../DATABASE-SCHEMA.md)
The 5-table multi-tenant schema.
- clones (projects/instances)
- persons (users/accounts)
- items (all content)
- labels (categories/tags)
- events (activity log)

### 🏢 [Multi-Tenancy](MULTI-TENANCY.md)
Building multi-tenant applications.
- Clone isolation
- Data segregation
- Best practices

## Examples

Learn by example with complete working applications.

### 📄 [Example 1: Landing Page](../examples/1-landing-page/)
Multi-language landing page with no database.
- Template engine
- Routing
- Translations
- Partials

### 📝 [Example 2: Blog System](../examples/2-blog-system/)
Full-featured blog with multi-language support.
- Database integration
- CRUD operations
- Pagination
- Related content

### 🔌 [Example 3: REST API](../examples/3-rest-api/)
RESTful API with interactive demo.
- HTTP methods
- JSON responses
- Error handling
- Frontend integration

**[See all examples →](../examples/README.md)**

## Quick Reference

### The 5 Tables
```
clones  → Projects/instances (multi-tenant master)
persons → Users/accounts (with clone_id)
items   → Content (posts, pages, products - with clone_id)
labels  → Categories/tags (with clone_id)
events  → Activity log (with clone_id)
```

### Essential Functions
```php
// Database
g::run("db.connect", $config)
g::run("db.createSchema", "main")
g::run("db.setClone", $hash)
g::run("db.insert", "items", $data)
g::run("db.select", "items", $conditions)
g::run("db.update", "items", $hash, $data)
g::run("db.delete", "items", $hash)

// Templates
g::run("tpl.renderView", "Index", $data)
g::run("tpl.render", $template, $data)

// Routing
g::run("route.handle")

// Authentication
g::run("auth.login", $email, $pass)
g::run("auth.check")
g::run("auth.logout")
```

### Template Directives
```html
<div data-g-if="variable">Conditional</div>
<div data-g-for="item in items">Loop</div>
<div data-g-load="partials/header.html">Partial</div>
<h1 data-g-bind="title">Bind text</h1>
<div data-g-html="content">Raw HTML</div>
<a data-g-attr="href:{{url}}">Link</a>
<p>{{variable}} replacement</p>
```

## Contributing

Want to contribute to Genes?

📝 **[Contributing Guide](../CONTRIBUTING.md)**
- Code standards
- Pull request process
- Testing guidelines

🔒 **[Security](../SECURITY.md)**
- Reporting vulnerabilities
- Security best practices

## Project Info

📄 **[Changelog](../CHANGELOG.md)** - Version history  
📜 **[License](../LICENSE)** - MIT License  
🏠 **[Main README](../README.md)** - Project overview

## Need Help?

1. Check the **[Quickstart Guide](QUICKSTART.md)**
2. Browse the **[Examples](../examples/)**
3. Read the **[API Reference](API.md)**
4. Review **[Architecture](ARCHITECTURE.md)**

## What Makes Genes Different?

✅ **Zero Dependencies** - No Composer, no npm, no build tools  
✅ **Single-File Framework** - Just 3 files (~9,400 lines total)  
✅ **Universal Schema** - 5 tables handle everything  
✅ **Multi-Tenant Ready** - Built-in isolation  
✅ **AI-Friendly** - Consistent patterns, predictable APIs  
✅ **PHP 5.6+** - Works anywhere  

Start building now: **[Quickstart Guide →](QUICKSTART.md)**
