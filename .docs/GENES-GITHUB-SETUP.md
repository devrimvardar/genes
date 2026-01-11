# Genes Framework - GitHub Repository Setup Guide

> **Instructions for creating a public GitHub repository for Genes Framework**

---

## 📦 Files to Include in Public Repository

### Core Framework Files (from `.genes/`)

```
genes-framework/
├── genes.php           # Copy from .genes/genes.php
├── genes.js            # Copy from .genes/genes.js
└── genes.css           # Copy from .genes/genes.css
```

### Documentation (from `.docs/`)

```
genes-framework/
├── docs/
│   ├── GENES-AI-FRAMEWORK.md    # Complete AI agent guide
│   ├── GENES-QUICKREF.md        # Quick reference card
│   ├── GENES-EXAMPLES.md        # Real-world examples
│   └── API-REFERENCE.md         # Detailed API docs (create)
```

### Root Files

```
genes-framework/
├── README.md                     # Main readme (use GENES-README.md)
├── LICENSE                       # MIT License
├── .gitignore                    # Git ignore file
├── CHANGELOG.md                  # Version history
├── CONTRIBUTING.md               # Contribution guidelines
└── SECURITY.md                   # Security policy
```

### Examples Directory

```
genes-framework/
├── examples/
│   ├── 1-hello-world/
│   │   └── index.php
│   ├── 2-database-crud/
│   │   ├── index.php
│   │   └── config.json
│   ├── 3-rest-api/
│   │   ├── index.php
│   │   └── README.md
│   ├── 4-blog-system/
│   │   ├── index.php
│   │   ├── ui/
│   │   └── README.md
│   └── 5-spa/
│       ├── index.html
│       └── README.md
```

---

## 📋 Setup Checklist

### 1. Create GitHub Repository

```bash
# On GitHub.com
# - Create new repository: "genes-framework"
# - Description: "Lightweight PHP & Vanilla JS framework for rapid web development"
# - Public repository
# - Add README, .gitignore (PHP), MIT License
```

### 2. Clone and Setup

```bash
# Clone new repository
git clone https://github.com/[your-org]/genes-framework.git
cd genes-framework

# Create directory structure
mkdir -p docs examples/1-hello-world examples/2-database-crud examples/3-rest-api examples/4-blog-system examples/5-spa
```

### 3. Copy Files

**From your expo_live project:**

```bash
# Copy core framework files
cp /path/to/expo_live/.genes/genes.php ./genes.php
cp /path/to/expo_live/.genes/genes.js ./genes.js
cp /path/to/expo_live/.genes/genes.css ./genes.css

# Copy documentation
cp /path/to/expo_live/.docs/GENES-AI-FRAMEWORK.md ./docs/
cp /path/to/expo_live/.docs/GENES-QUICKREF.md ./docs/
cp /path/to/expo_live/.docs/GENES-EXAMPLES.md ./docs/
cp /path/to/expo_live/.docs/GENES-README.md ./README.md
```

### 4. Create Additional Files

**`.gitignore`:**
```gitignore
# Dependencies
vendor/
node_modules/

# Environment
.env
.env.local

# IDE
.vscode/
.idea/
*.swp
*.swo
*~

# OS
.DS_Store
Thumbs.db

# Project specific
data/config.json
cache/
uploads/
*.log

# Keep example configs
!examples/**/config.json
```

**`LICENSE`:**
```
MIT License

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
```

**`CHANGELOG.md`:**
```markdown
# Changelog

All notable changes to Genes Framework will be documented in this file.

## [2.0.0] - 2025-01-11

### Added
- Complete framework rewrite with AI-optimized architecture
- Single-file design (genes.php, genes.js, genes.css)
- Universal 5-table database schema
- Built-in REST API layer
- Enhanced security with bcrypt + pepper
- Performance tracking and logging
- Template system with helpers
- Module/plugin system
- Comprehensive documentation for AI agents

### Changed
- Simplified namespace from multiple classes to single `g` class
- Improved consistency across PHP and JavaScript APIs
- Better error handling and logging

### Security
- Added HMAC pepper to password hashing
- Improved session security
- CSRF token support
- SQL injection prevention with PDO

## [1.0.0] - 2024-01-01

### Added
- Initial release
- Basic CRUD operations
- Simple authentication
- Database abstraction
```

**`CONTRIBUTING.md`:**
```markdown
# Contributing to Genes Framework

We welcome contributions! Here's how you can help:

## Code of Conduct

Be respectful, inclusive, and collaborative.

## How to Contribute

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Commit** your changes (`git commit -m 'Add amazing feature'`)
4. **Push** to the branch (`git push origin feature/amazing-feature`)
5. **Open** a Pull Request

## Development Setup

```bash
git clone https://github.com/[your-org]/genes-framework.git
cd genes-framework
```

## Coding Standards

- **PHP**: Follow PSR-2 coding style
- **JavaScript**: Use 4-space indentation, ES5 compatible
- **CSS**: Use 4-space indentation, follow BEM naming
- **Documentation**: Update docs for any API changes

## Testing

- Test on PHP 5.6, 7.4, and 8.x
- Ensure backward compatibility
- Test in Chrome, Firefox, Safari, Edge

## Documentation

- Add PHPDoc/JSDoc comments to all functions
- Include usage examples in comments
- Update relevant markdown docs

## Submitting Issues

- Use issue templates
- Provide clear reproduction steps
- Include PHP version, browser, OS

Thank you for contributing! 🎉
```

**`SECURITY.md`:**
```markdown
# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 2.0.x   | :white_check_mark: |
| 1.0.x   | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability, please email:

**security@genes.one**

Please include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

We will respond within 48 hours.

## Security Best Practices

When using Genes Framework:

1. **Always** change `config.security.secret` and `config.security.salt` in production
2. **Never** set `debug: true` in production
3. **Use** HTTPS in production
4. **Keep** file permissions strict (755 for directories, 644 for files)
5. **Validate** all user input
6. **Sanitize** all output
7. **Use** prepared statements (built-in with Genes)
8. **Enable** CSRF protection for forms
9. **Review** logs regularly

## Known Issues

None currently reported.

Last updated: 2025-01-11
```

### 5. Create Example Projects

**`examples/1-hello-world/index.php`:**
```php
<?php
require_once '../../genes.php';

echo "Hello, Genes Framework!";

// Show performance
$perf = g::run("log.performance", true);
echo "<br>" . $perf;
```

**`examples/2-database-crud/index.php`:**
```php
<?php
require_once '../../genes.php';

// Connect to database
g::run("db.connect", array(
    "host" => "localhost",
    "name" => "genes_example",
    "user" => "root",
    "pass" => ""
));

// Create schema
g::run("db.createSchema");

// Insert
$hash = g::run("db.insert", "persons", array(
    "type" => "user",
    "state" => "active",
    "name" => "John Doe",
    "email" => "john@example.com",
    "password" => g::run("auth.hash", "secret123")
));

echo "User created: $hash<br>";

// Select
$users = g::run("db.select", "persons", array("type" => "user"));
echo "<pre>";
print_r($users);
echo "</pre>";

// Update
g::run("db.update", "persons", 
    array("hash" => $hash),
    array("name" => "Jane Doe")
);

echo "User updated<br>";

// Performance
echo g::run("log.performance", true);
```

**`examples/3-rest-api/index.php`:**
```php
<?php
require_once '../../genes.php';

// Setup
g::run("db.connect", array(
    "host" => "localhost",
    "name" => "genes_api",
    "user" => "root",
    "pass" => ""
));

g::run("db.createSchema");
g::run("route.parseUrl");

$request = g::get("request");
$segments = $request["segments"];

// API Routes
if ($segments[0] === "api") {
    $table = $segments[1];
    $result = g::run("api.handle", $table);
    g::run("api.respond", $result);
}

// Default response
echo "API Server running. Use /api/persons, /api/clones, /api/nodes";
```

**`examples/3-rest-api/README.md`:**
```markdown
# REST API Example

Simple REST API server with CRUD operations.

## Setup

1. Create database: `CREATE DATABASE genes_api;`
2. Run: `php -S localhost:8000`

## Usage

### List all
```bash
curl http://localhost:8000/api/persons
```

### Create
```bash
curl -X POST http://localhost:8000/api/persons \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com"}'
```

### Get one
```bash
curl http://localhost:8000/api/persons/{hash}
```

### Update
```bash
curl -X PUT http://localhost:8000/api/persons/{hash} \
  -H "Content-Type: application/json" \
  -d '{"name":"Jane"}'
```

### Delete
```bash
curl -X DELETE http://localhost:8000/api/persons/{hash}
```
```

### 6. Commit and Push

```bash
# Add all files
git add .

# Commit
git commit -m "Initial release of Genes Framework v2.0"

# Push
git push origin main

# Create release tag
git tag -a v2.0.0 -m "Version 2.0.0 - AI-optimized framework"
git push origin v2.0.0
```

### 7. GitHub Repository Settings

**Topics to add:**
- php
- javascript
- framework
- web-development
- fullstack
- ai-friendly
- single-file
- zero-dependencies
- rest-api
- crud

**Description:**
```
Lightweight PHP & Vanilla JS framework for rapid web development. 
Single-file architecture, zero dependencies, AI-optimized.
```

**Website:**
```
https://genes.one
```

### 8. Create GitHub Release

1. Go to "Releases" → "Create a new release"
2. Tag: `v2.0.0`
3. Title: `Genes Framework v2.0.0 - AI-Optimized`
4. Description:
```markdown
## 🎉 Genes Framework v2.0.0

Complete rewrite of Genes Framework with AI-optimized architecture.

### 🌟 Highlights

- **Single-file framework**: Just 3 files (genes.php, genes.js, genes.css)
- **Zero dependencies**: No Composer, no npm, no build tools
- **AI-friendly**: Consistent patterns, predictable APIs
- **Production ready**: Security, sessions, logging built-in
- **Full-stack**: Complete backend + frontend solution

### 📦 What's New

- Complete framework rewrite
- Universal 5-table database schema
- Built-in REST API layer
- Enhanced security (bcrypt + pepper)
- Performance tracking
- Template system
- Comprehensive AI agent documentation

### 📚 Documentation

- [AI Framework Guide](docs/GENES-AI-FRAMEWORK.md)
- [Quick Reference](docs/GENES-QUICKREF.md)
- [Examples](docs/GENES-EXAMPLES.md)

### 🚀 Quick Start

```bash
curl -O https://github.com/[your-org]/genes-framework/releases/download/v2.0.0/genes.php
echo '<?php require_once "genes.php"; echo "Hello, Genes!";' > index.php
php -S localhost:8000
```

Full installation instructions in [README.md](README.md).
```

5. Attach files:
   - genes.php
   - genes.js
   - genes.css
   - genes-framework-v2.0.0.zip (archive of all files)

---

## 🎯 Marketing & Promotion

### 1. Create Landing Page (genes.one)

- Feature overview
- Live demos
- Interactive playground
- Documentation links
- Download buttons

### 2. Social Media

**Twitter/X:**
```
🚀 Introducing Genes Framework v2.0!

✅ Single-file PHP & JS framework
✅ Zero dependencies
✅ AI-optimized for coding agents
✅ Production ready
✅ Complete full-stack solution

Perfect for rapid web development! 

https://github.com/[your-org]/genes-framework
#PHP #JavaScript #WebDev #AI
```

**Reddit:** Post in r/PHP, r/webdev, r/programming

**Hacker News:** Submit as "Show HN"

### 3. Blog Posts

- "Building a Full-Stack App with Genes Framework"
- "Why We Built a Single-File Framework"
- "AI-Friendly Web Development with Genes"

### 4. YouTube Videos

- "Genes Framework Introduction (10 min)"
- "Build a Blog in 30 Minutes with Genes"
- "REST API Tutorial with Genes Framework"

---

## 📊 Metrics to Track

- GitHub stars
- Downloads/clones
- Issues/PRs
- Community engagement
- Documentation views
- Example usage in wild

---

## 🎓 Community Building

1. **Discord Server**: Create community space
2. **Discussion Forum**: Enable GitHub Discussions
3. **Newsletter**: Monthly updates
4. **Showcase**: Gallery of projects built with Genes
5. **Contributions**: Welcome and mentor contributors

---

## ✅ Pre-Launch Checklist

- [ ] All framework files copied
- [ ] Documentation complete
- [ ] Examples working
- [ ] Tests passing
- [ ] License added
- [ ] README polished
- [ ] GitHub repo created
- [ ] Release published
- [ ] Landing page live
- [ ] Social media prepared
- [ ] Blog post ready
- [ ] Video tutorial recorded

---

**You're ready to launch! Good luck with Genes Framework! 🚀**

---

## 📞 Need Help?

If you have questions about setting up the repository:

1. Review this guide
2. Check existing documentation
3. Test examples locally
4. Ask in GitHub Discussions

Remember: The goal is to make Genes accessible to developers and AI agents alike!
