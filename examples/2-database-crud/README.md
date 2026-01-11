# Example 2: Database CRUD

Complete demonstration of Create, Read, Update, Delete operations.

## What This Demonstrates

- Database connection
- Schema creation (5-table universal schema)
- INSERT operations
- SELECT with conditions and options
- UPDATE operations
- DELETE (soft delete)
- Custom SQL queries
- Performance tracking

## Prerequisites

- MySQL or MariaDB database
- Database credentials

## Setup

1. Create a database:
```sql
CREATE DATABASE genes_test;
```

2. Update database credentials in `index.php` if needed:
```php
g::run("db.connect", array(
    "host" => "localhost",
    "name" => "genes_test",
    "user" => "root",
    "pass" => ""  // Update this
));
```

## How to Run

```bash
cd examples/2-database-crud
php -S localhost:8000
```

Visit: http://localhost:8000

## Database Schema

The example creates these tables:

- **persons** - Users and accounts
- **clones** - User-generated content (posts, comments)
- **links** - Relationships (follows, likes)
- **nodes** - Static content (pages, categories)
- **events** - Audit log and analytics

All tables include:
- `hash` (VARCHAR 32) - Unique ID
- `type` (VARCHAR 32) - Subtype
- `state` (VARCHAR 32) - Status
- `meta` (TEXT) - JSON flexible data
- Timestamps (created_at, updated_at, deleted_at)

## Code Highlights

### CREATE
```php
$hash = g::run("db.insert", "persons", array(
    "name" => "John Doe",
    "email" => "john@example.com"
));
```

### READ
```php
$users = g::run("db.select", "persons", array(
    "state" => "active"
));
```

### UPDATE
```php
g::run("db.update", "persons", 
    array("hash" => $hash),
    array("name" => "New Name")
);
```

### DELETE
```php
g::run("db.delete", "persons", array("hash" => $hash));
// Soft delete - sets deleted_at timestamp
```

## What's Next

Check out [Example 3: REST API](../3-rest-api/) to see API endpoints.
