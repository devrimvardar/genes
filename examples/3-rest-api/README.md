# Example 3: Custom REST API / Todo System

This example demonstrates how to build a **custom RESTful API** by manually implementing CRUD operations using Genes' database functions.

> **Note:** Genes Framework includes a **built-in REST API** system (see Example 4). This example shows the manual approach for learning purposes and custom implementations.

## What This Example Teaches

### 1. **Manual API Implementation**
- Building custom API endpoints from scratch
- Standard HTTP methods: GET, POST, PUT, DELETE
- Custom routing with route segments
- JSON request/response handling
- Proper HTTP status codes (200, 201, 400, 401, 404)
- Error handling and validation

### 2. **Direct Database Operations**
- Using `db.insert()`, `db.select()`, `db.update()`, `db.delete()`
- Working with the 5-table schema (items, persons)
- Todos stored as `items` with `type="todo"`
- User authentication with `persons` table
- Using `state` field for todo status (pending/completed)
- JSON `meta` field for additional data (priority)
- `end_at` field for due dates

### 3. **HTTP Method Routing**
- Checking `$_SERVER['REQUEST_METHOD']`
- Handling multiple methods in one view function
- Route parameter extraction from `route_segments`
- Different logic for list vs. single-item operations

### 4. **Custom vs. Built-in API**
- When to use custom handlers (special business logic)
- When to use built-in API (standard CRUD)
- Trade-offs between control and convenience

### 5. **Frontend Integration**
- JavaScript fetch API for HTTP requests
- Async/await pattern
- Dynamic DOM manipulation
- Real-time API response logging

## File Structure

```
3-rest-api/
├── index.php              # API implementation
├── data/
│   ├── config.json        # API routes configuration
│   └── todos.db           # SQLite database (auto-created)
├── ui/
│   ├── index.html         # Interactive demo UI
│   └── assets/
│       ├── app.css        # Demo UI styles
│       └── app.js         # API client JavaScript
├── cache/                 # Template cache
└── README.md             # This file
```

## API Endpoints

> These are **custom endpoints** implemented manually. For built-in API endpoints, see Example 4.

### List All Todos
```http
GET /todos
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "abc123",
            "title": "Learn Genes Framework",
            "completed": false,
            "priority": "high",
            "due_date": "2026-01-15",
            "created_at": "2026-01-12 10:30:00"
        }
    ],
    "count": 1
}
```

### Gingle Todo
```http
GET /api/todos/:id
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": "abc123",
        "title": "Learn Genes Framework",
        "description": "Complete all examples",
        "completed": false,
        "priority": "high",
        "due_date": "2026-01-15",
        "created_at": "2026-01-12 10:30:00",
        "updated_at": "2026-01-12 10:30:00"
    }
}
```

### Create Todo
```http
POST /todos
Content-Type: application/json

{
    "title": "New Todo",
    "description": "Optional description",
    "priority": "normal",
    "due_date": "2026-01-20"
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Todo created successfully",
    "data": {
        "id": "xyz789"
    }
}
```

### Update Todo
```http
PUT /todos/:id
Content-Type: application/json

{
    "title": "Updated Title",
    "completed": true,
    "priority": "low"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Todo updated successfully"
}
```

### Delete Todo
```http
DELETE /todos/:id
```

**Response:**
```json
{
    "success": true,
    "message": "Todo deleted successfully"
}
```

## Database Schema Usage

### Items Table (Todos)
```php
array(
    "type" => "todo",                    // Content type
    "state" => "pending",                // pending or completed
    "title" => "Task title",             // Todo title
    "text" => "Description...",          // Full description
    "end_at" => "2026-01-15",           // Due date
    "meta" => json_encode(array(         // Metadata
        "priority" => "high"
    ))
)
```

### Persons Table (Users)
```php
array(
    "type" => "user",
    "alias" => "demo",
    "name" => "Demo User",
    "email" => "demo@example.com",
    "password" => password_hash("demo123", PASSWORD_DEFAULT)
)
```

## How It Works

### 1. Database Auto-Connection
Database is configured in `data/config.json` and auto-connects during initialization:
```json
{
    "database": {
        "enabled": true,
        "type": "sqlite",
        "database": "todos.db"
    }
}
```

### 2. Route Configuration
In `data/config.json`, views map to URL patterns:
```json
{
    "views": {
        "Todos": {
            "function": "clone.Todos",
            "urls": { "en": "todos" }
        }
    }
}
        "POST": "CreateTodo"
    },
    "/api/todos/*": {
        "GET": "GetTodo",
        "PUT": "UpdateTodo",
        "DELETE": "DeleteTodo"
    }
}
```

### 2. Clone Functions
Each function handles one API endpoint:

**ListTodos** - Query all todos, format JSON response  
**GetTodo** - Extract ID from route, query single todo  
**CreateTodo** - Parse request body, validate, insert  
**UpdateTodo** - Extract ID, parse body, update record  
**DeleteTodo** - Extract ID, delete record

### 3. Helper Functions
- `jsonResponse($data, $code)` - Send JSON with status code
- `getRequestBody()` - Parse JSON request body
- `getCurrentUser()` - Get authenticated user (demo: returns first user)

### 4. Frontend Client
JavaScript in `ui/assets/app.js`:
- Fetches todos on page load
- Submits new todos via form
- Updates/deletes todos with button clicks
- Logs all API requests/responses

## Running the Example

1. **Access the demo UI:**
   ```
   http://localhost/examples/3-rest-api/
   ```

2. **Test API endpoints:**
   Use the interactive UI or tools like:
   - Browser DevTools Network tab
   - Postman
   - cURL:
     ```bash
     curl http://localhost/examples/3-rest-api/todos
     ```

3. **Create a todo:**
   - Fill the form in the UI
   - Or use cURL:
     ```bash
     curl -X POST http://localhost/examples/3-rest-api/todos \
       -H "Content-Type: application/json" \
       -d '{"title":"Test Todo","priority":"high"}'
     ```

## Key Takeaways

### ✅ DO:
- Use items table with appropriate `type` field
- Set proper HTTP status codes
- Validate input data
- Return JSON responses with consistent structure
- Handle errors gracefully
- Use JSON `meta` field for extensible data
- Map HTTP methods in config.json routes

### ❌ DON'T:
- Create custom tables for different resource types
- Return raw database rows (format for API)
- Ignore HTTP method semantics
- Skip error handling
- Use raw SQL queries

## Adapting for Your Project

### Adding Authentication
Use JWT tokens or sessions:
```php
function getAuthToken() {
    $headers = getallheaders();
    return isset($headers['Authorization']) 
        ? str_replace('Bearer ', '', $headers['Authorization'])
        : null;
}

function verifyToken($token) {
    // Verify JWT or session
}
```

### Adding Pagination
```php
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Implement in db.select or filter array
```

### Adding Filtering
```php
$filters = array("type" => "todo");
if (isset($_GET['status'])) {
    $filters['state'] = $_GET['status'];
}
$todos = g::run("db.select", "items", $filters);
```

### Adding Relationships
Link todos to users via `created_by`:
```php
$todos = g::run("db.select", "items", array(
    "type" => "todo",
    "created_by" => $userHash
));
```

### Rate Limiting
Track requests in `events` table:
```php
g::run("db.insert", "events", array(
    "type" => "api_request",
    "person_id" => $userHash,
    "ref1" => $endpoint
));
```

## Learn More

- See `GENES-V2-CAPABILITIES.md` for database functions reference
- See `DATABASE-SCHEMA.md` for schema documentation
- Check Example 1 for template engine basics
- Check Example 2 for database queries
