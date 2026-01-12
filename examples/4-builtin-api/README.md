# Example 4: Built-in REST API

This example demonstrates **Genes Framework's built-in REST API system**. Unlike Example 3 (custom implementation), this shows how to use the framework's automatic CRUD API with **zero custom code**.

## Key Difference from Example 3

**Example 3 (Custom API):**
- Custom handlers for each operation
- Manual routing and validation
- ~300 lines of code
- Full control over logic
- Good for learning or special requirements

**Example 4 (Built-in API):**
- Zero API code - framework handles everything
- Automatic CRUD on all 5 tables
- ~50 lines of setup code
- Built-in validation, pagination, search
- **Production-ready immediately**

## What This Example Teaches

### 1. **Zero-Code REST API**
- Just configure the database - API is ready
- Automatic `/api/*` route handling
- No custom handlers needed

### 2. **Built-in Features**
- Full CRUD on all tables (items, persons, labels, clones, events)
- Filtering: `?filters[type]=todo`
- Pagination: `?page=1&limit=10`
- Search: `?search=keyword`
- Sorting: `?order=created_at DESC`
- Single record access: `/api/items/:hash`

### 3. **Standard Response Format**
- Consistent JSON structure
- Success/error handling
- Automatic validation
- Proper HTTP status codes

### 4. **When to Use**
- Standard CRUD operations
- Rapid prototyping
- Admin panels
- Mobile app backends
- Standard database operations

## Built-in API Endpoints

All 5 database tables have automatic REST endpoints:

### Items Table
```
GET    /api/items                     - List all items
GET    /api/items?filters[type]=todo  - Filter by type
GET    /api/items/:hash               - Get single item
POST   /api/items                     - Create item
PUT    /api/items/:hash               - Update item
DELETE /api/items/:hash               - Delete item
```

### Persons Table
```
GET    /api/persons                   - List all users
GET    /api/persons/:hash             - Get single user
POST   /api/persons                   - Create user
PUT    /api/persons/:hash             - Update user
DELETE /api/persons/:hash             - Delete user
```

### Labels Table
```
GET    /api/labels                    - List all labels
POST   /api/labels                    - Create label
```

### Clones Table
```
GET    /api/clones                    - List all projects
```

### Events Table
```
GET    /api/events                    - List all events
POST   /api/events                    - Log event
```

## Query Parameters

### Filtering
```
GET /api/items?filters[type]=todo
GET /api/items?filters[state]=published
GET /api/items?filters[type]=post&filters[state]=published
```

### Pagination
```
GET /api/items?page=1&limit=10
GET /api/items?page=2&limit=20
```

### Sorting
```
GET /api/items?order=created_at DESC
GET /api/items?order=title ASC
```

### Search
```
GET /api/items?search=keyword
GET /api/items?search=keyword&searchFields[]=title&searchFields[]=text
```

### Combined
```
GET /api/items?filters[type]=todo&page=1&limit=10&order=created_at DESC
```

## Complete Implementation

```php
<?php
require_once 'genes.php';

// Setup database
g::run("db.connect", array(
    "driver" => "sqlite",
    "database" => "app.db"
));

// Create schema if needed
if (!file_exists("app.db")) {
    g::run("db.createSchema", "main");
}

// Define homepage view
g::def("clone", array(
    "Index" => function ($bits, $lang, $path) {
        echo file_get_contents('ui/index.html');
    }
));

// That's it! API is ready at /api/*
g::run("route.handle");
```

## Frontend Usage

### Create Item
```javascript
fetch('/api/items', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        type: 'todo',
        state: 'pending',
        title: 'My Todo',
        text: 'Description',
        meta: JSON.stringify({ priority: 'high' })
    })
})
```

### List Items with Filters
```javascript
fetch('/api/items?filters[type]=todo')
    .then(res => res.json())
    .then(data => console.log(data.data))
```

### Get Single Item
```javascript
fetch('/api/items/abc123')
    .then(res => res.json())
    .then(data => console.log(data.data))
```

### Update Item
```javascript
fetch('/api/items/abc123', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        state: 'completed'
    })
})
```

### Delete Item
```javascript
fetch('/api/items/abc123', {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ hard: true })
})
```

## Response Format

### Success Response
```json
{
    "success": true,
    "data": {
        "hash": "abc123",
        "type": "todo",
        "state": "pending",
        "title": "My Todo",
        "created_at": "2026-01-12 10:30:00"
    }
}
```

### List Response
```json
{
    "success": true,
    "data": [
        { "hash": "abc123", "title": "Todo 1" },
        { "hash": "def456", "title": "Todo 2" }
    ],
    "total": 2,
    "page": 1,
    "limit": 50
}
```

### Error Response
```json
{
    "success": false,
    "error": "Record not found"
}
```

## When to Use Built-in vs Custom

### Use Built-in API When:
- ✅ Standard CRUD operations
- ✅ Rapid prototyping
- ✅ Admin panels
- ✅ Mobile backends
- ✅ You want pagination/search/filters out-of-the-box
- ✅ Standard validation is enough

### Use Custom Implementation When:
- ❌ Complex business logic
- ❌ Custom validation rules
- ❌ Non-standard operations
- ❌ Special authorization requirements
- ❌ Custom response formats
- ❌ Multi-step workflows

## Run the Example

```bash
cd examples/4-builtin-api
php -S localhost:8000
```

Then visit: http://localhost:8000

## Summary

The built-in API gives you **production-ready REST endpoints** with zero code. Just configure your database and you have:

- ✅ Full CRUD on all tables
- ✅ Filtering & pagination
- ✅ Search functionality
- ✅ Validation
- ✅ Consistent responses
- ✅ Error handling

Perfect for **AI development** - no need to build what's already there!
