# Example 3: REST API Server

Complete RESTful API with automatic CRUD endpoints for all tables.

## What This Demonstrates

- URL routing and parsing
- Automatic API endpoint handling
- RESTful CRUD operations (GET, POST, PUT, DELETE)
- JSON request/response handling
- Query parameter filtering
- API security (table validation)
- Error handling

## Prerequisites

- MySQL or MariaDB database
- cURL or API testing tool (Postman, Insomnia, etc.)

## Setup

1. Create database:
```sql
CREATE DATABASE genes_api;
```

2. Update credentials in `index.php` if needed

## How to Run

```bash
cd examples/3-rest-api
php -S localhost:8000
```

## API Endpoints

### List All Records
```bash
GET /api/{table}

curl http://localhost:8000/api/persons
```

### Filter Records
```bash
GET /api/{table}?key=value

curl http://localhost:8000/api/persons?state=active
```

### Get Single Record
```bash
GET /api/{table}/{hash}

curl http://localhost:8000/api/persons/{hash}
```

### Create Record
```bash
POST /api/{table}

curl -X POST http://localhost:8000/api/persons \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","state":"active"}'
```

### Update Record
```bash
PUT /api/{table}/{hash}

curl -X PUT http://localhost:8000/api/persons/{hash} \
  -H "Content-Type: application/json" \
  -d '{"name":"Jane"}'
```

### Delete Record
```bash
DELETE /api/{table}/{hash}

curl -X DELETE http://localhost:8000/api/persons/{hash}
```

## Supported Tables

- `persons` - Users and accounts
- `clones` - User-generated content (posts, comments)
- `links` - Relationships (follows, likes)
- `nodes` - Static content (pages, categories)
- `events` - Audit log

## Response Format

### Success
```json
{
  "success": true,
  "data": [...],
  "count": 2
}
```

### Error
```json
{
  "success": false,
  "error": "Error message"
}
```

## Code Highlights

### Routing
```php
g::run("route.parseUrl");
$request = g::get("request");

if ($request["segments"][0] === "api") {
    $table = $request["segments"][1];
    // Handle API request
}
```

### API Handling
```php
// Automatically handles GET, POST, PUT, DELETE
$result = g::run("api.handle", $table);
g::run("api.respond", $result);
```

## Security Features

- Table name validation (whitelist)
- SQL injection protection (automatic)
- JSON input validation
- HTTP method validation

## Testing

Visit http://localhost:8000 for interactive documentation and example requests.

## What's Next

Check out [Example 4: Blog System](../4-blog-system/) for a complete application with auth.
