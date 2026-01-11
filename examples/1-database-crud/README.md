# Example 1: Database CRUD Operations

Complete CRUD operations demonstrating the **correct 5-table schema** with both **MySQL** and **SQLite**.

## What This Demonstrates

- ✅ Correct tables: `clones`, `persons`, `items`, `labels`, `events`
- ✅ Both MySQL and SQLite database drivers
- ✅ Multi-tenant clone-based isolation
- ✅ CRUD operations (Create, Read, Update, Delete)
- ✅ Auto-generated hashes and timestamps
- ✅ Soft delete pattern
- ✅ Event logging

## Database Choice

Uncomment your preferred database:

```php
// Option 1: SQLite (default - no setup needed)
$dbConfig = array(
    "driver" => "sqlite",
    "name" => "main",
    "database" => "data/crud-example.db"
);

// Option 2: MySQL (requires server)
// $dbConfig = array(
//     "driver" => "mysql",
//     "name" => "main",
//     "host" => "localhost",
//     "database" => "genes_crud",
//     "username" => "root",
//     "password" => ""
// );
```

## Running the Example

```bash
php -S localhost:8000
```

Visit: `http://localhost:8000`

## File Structure

```
1-database-crud/
├── index.php          # Main demo file
├── config.json        # Database configuration
└── README.md          # This file
```

## Code Walkthrough

### 1. Setup

The example creates a clone and sets the clone context for multi-tenant isolation.

### 2. Create Operations

- Create clone (master record)
- Create person (user with clone_id)
- Create item (blog post with clone_id)
- Create label (category with clone_id)
- Create event (audit log with clone_id)

### 3. Read Operations

- Get single record by hash
- Select multiple records with filters
- List with pagination

### 4. Update Operations

- Update single field
- Update multiple fields
- Automatic updated_at timestamp

### 5. Delete Operations

- Soft delete (sets state = 'deleted')
- Hard delete (permanent removal)

### 6. Clone Isolation

- Set clone context
- Automatic filtering by clone_id
- Cross-clone queries

## Key Concepts

### Auto-Generated Fields

When inserting, the framework automatically adds:

```php
$hash = g::run("db.insert", "items", array(
    "title" => "My Post"
));

// Framework adds:
// - hash (unique ID)
// - clone_id (from current clone context)
// - created_at (current timestamp)
// - updated_at (current timestamp)
```

### Clone Context

```php
// Set clone context
g::run("db.setClone", $cloneHash);

// All queries now auto-filter by clone_id
$items = g::run("db.select", "items", array("type" => "post"));
// SQL: SELECT * FROM items WHERE type='post' AND clone_id='...'
```

### Soft Delete

```php
// Soft delete (recommended)
g::run("db.delete", "items", array("hash" => $hash));
// Sets: state = 'deleted'

// Hard delete (permanent)
g::run("db.delete", "items", array("hash" => $hash), true);
// Removes from database
```

## Schema Reference

### clones
- `hash` - Unique identifier
- `name` - Clone name
- `domain` - Domain name
- `type` - Clone type (blog, ecommerce, etc.)
- `state` - active, suspended, deleted
- `settings` - JSON configuration

### persons
- `hash` - Unique identifier
- `clone_id` - **Links to clones.hash**
- `email` - User email (unique)
- `name` - Display name
- `type` - user, admin, persona
- `state` - active, suspended, deleted

### items
- `hash` - Unique identifier
- `clone_id` - **Links to clones.hash**
- `type` - post, page, product, comment
- `title` - Item title
- `safe_url` - URL slug
- `blurb` - Short description
- `text` - Full content
- `state` - draft, published, deleted

### labels
- `hash` - Unique identifier
- `clone_id` - **Links to clones.hash**
- `type` - category, tag, status
- `key` - Machine name
- `name` - Display name
- `state` - active, deleted

### events
- `hash` - Unique identifier
- `clone_id` - **Links to clones.hash** (required)
- `type` - Event type (post.created, user.login, etc.)
- `person_id` - Who did it
- `item_id` - What was affected
- `data` - JSON payload

## Next Steps

- **Multi-Tenant Blog** → See Example 2
- **REST API** → See Example 3
- **Documentation** → Read `/docs`
