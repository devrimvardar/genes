# Examples Index

This folder contains complete working examples demonstrating Genes Framework capabilities.

## 📚 Available Examples

### [1. Hello World](1-hello-world/)
**Difficulty**: Beginner  
**Time**: 2 minutes  
**Topics**: Basic setup, framework inclusion

The simplest possible Genes application. Perfect for getting started.

```bash
cd 1-hello-world
php -S localhost:8000
```

---

### [2. Database CRUD](2-database-crud/)
**Difficulty**: Beginner  
**Time**: 10 minutes  
**Topics**: Database connection, schema creation, CRUD operations

Complete demonstration of Create, Read, Update, Delete operations with the universal 5-table schema.

**Prerequisites**: MySQL/MariaDB database

```bash
cd 2-database-crud
php -S localhost:8000
```

---

### [3. REST API](3-rest-api/)
**Difficulty**: Intermediate  
**Time**: 15 minutes  
**Topics**: Routing, API endpoints, JSON responses, HTTP methods

A complete RESTful API server with automatic CRUD endpoints for all tables.

**Prerequisites**: MySQL/MariaDB database, cURL or API client

```bash
cd 3-rest-api
php -S localhost:8000
```

Test with:
```bash
curl http://localhost:8000/api/persons
```

---

### [4. Blog System](4-blog-system/)
**Difficulty**: Intermediate  
**Time**: 20 minutes  
**Topics**: Authentication, user registration, sessions, forms, relationships

A complete blog application with user accounts, login/logout, and post creation.

**Prerequisites**: MySQL/MariaDB database

```bash
cd 4-blog-system
php -S localhost:8000
```

---

### [5. Single Page App](5-spa/)
**Difficulty**: Intermediate  
**Time**: 15 minutes  
**Topics**: Frontend JavaScript, state management, events, localStorage

A frontend-only todo list application demonstrating Genes JS capabilities.

**Prerequisites**: None (no backend required!)

```bash
cd 5-spa
php -S localhost:8000
```

OR open `index.html` directly in your browser.

---

## 🎯 Learning Path

**New to Genes?** Follow this sequence:

1. **Hello World** - Get familiar with basic setup
2. **Database CRUD** - Learn database operations
3. **REST API** - Build API endpoints
4. **Blog System** - Create a full application
5. **Single Page App** - Master frontend features

## 🛠️ Running Examples

### Using PHP Built-in Server

All examples can run with PHP's built-in development server:

```bash
cd examples/[example-name]
php -S localhost:8000
```

Then visit: http://localhost:8000

### Database Setup

Examples 2, 3, and 4 require a database. Create databases:

```sql
CREATE DATABASE genes_test;    -- For example 2
CREATE DATABASE genes_api;     -- For example 3
CREATE DATABASE genes_blog;    -- For example 4
```

Update database credentials in each `index.php` if needed.

### No Server Required

Example 5 (SPA) runs directly in your browser without a server!

## 📖 Documentation

Each example includes:
- `README.md` - Detailed explanation and setup
- Commented source code
- Prerequisites and requirements
- Expected output
- What's demonstrated
- Ideas for extension

## 🔧 Customization

All examples are designed to be:
- **Self-contained** - Copy and modify freely
- **Well-commented** - Understand what's happening
- **Extensible** - Build on top of them
- **Production-ready patterns** - Use in real projects

## 🆘 Troubleshooting

### Database Connection Issues
```
Error: SQLSTATE[HY000] [1045] Access denied
```

**Solution**: Check database credentials in `index.php`:
```php
g::run("db.connect", array(
    "host" => "localhost",
    "name" => "your_database",
    "user" => "your_username",
    "pass" => "your_password"
));
```

### Port Already in Use
```
Failed to listen on localhost:8000
```

**Solution**: Use a different port:
```bash
php -S localhost:8001
```

### Missing Extensions

**Solution**: Ensure PHP has PDO and PDO_MySQL:
```bash
php -m | grep -i pdo
```

## 💡 Next Steps

After completing the examples:

1. Read the **[AI Framework Guide](../docs/GENES-AI-FRAMEWORK.md)** for comprehensive documentation
2. Check **[Quick Reference](../docs/GENES-QUICKREF.md)** for API cheat sheet
3. Explore **[More Examples](../docs/GENES-EXAMPLES.md)** for additional patterns
4. Build your own application!

## 🤝 Contributing Examples

Have a great example to share? We'd love to include it!

1. Create your example following the existing structure
2. Include a detailed README.md
3. Comment your code thoroughly
4. Submit a pull request

See [CONTRIBUTING.md](../CONTRIBUTING.md) for guidelines.

---

**Happy coding with Genes Framework! 🚀**
