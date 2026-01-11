# Example 3: REST API with Clone Context

RESTful API demonstrating **multi-tenant clone isolation** with proper API patterns.

## What This Demonstrates

- ✅ RESTful API endpoints (GET, POST, PUT, DELETE)
- ✅ Multi-tenant clone-based isolation
- ✅ API authentication with tokens
- ✅ JSON request/response handling
- ✅ CORS support
- ✅ Error handling
- ✅ Clone context from API tokens

## API Endpoints

### Posts API

```
GET    /api/posts          - List all posts (clone-scoped)
GET    /api/posts/:hash    - Get single post
POST   /api/posts          - Create new post
PUT    /api/posts/:hash    - Update post
DELETE /api/posts/:hash    - Delete post (soft)
```

### Persons API

```
GET    /api/persons        - List all users (clone-scoped)
GET    /api/persons/:hash  - Get single user
POST   /api/persons        - Create new user
```

### Labels API

```
GET    /api/labels         - List all labels (clone-scoped)
POST   /api/labels         - Create new label
```

## Running the Example

```bash
php -S localhost:8002 -t examples/3-rest-api
```

Test with curl:

```bash
# List posts
curl http://localhost:8002/api/posts

# Get single post
curl http://localhost:8002/api/posts/HASH

# Create post
curl -X POST http://localhost:8002/api/posts \
  -H "Content-Type: application/json" \
  -d '{"title":"New Post","text":"Content here"}'

# Update post
curl -X PUT http://localhost:8002/api/posts/HASH \
  -H "Content-Type: application/json" \
  -d '{"title":"Updated Title"}'

# Delete post
curl -X DELETE http://localhost:8002/api/posts/HASH
```

## Clone Isolation in APIs

The API automatically scopes all queries to the current clone:

```php
// Set clone context (in real app, from API token)
g::run("db.setClone", $cloneHash);

// All API queries auto-filtered
GET /api/posts
// Returns ONLY posts from the current clone

POST /api/posts
// Creates post WITH clone_id automatically added
```

## Authentication

The example shows a simple token-based authentication:

```php
// Each API token is linked to a clone
$token = "demo-token-abc123";
$cloneHash = getCloneFromToken($token);
g::run("db.setClone", $cloneHash);
```

In production:
- Store tokens in `persons` table with `clone_id`
- Validate tokens on each request
- Use proper JWT or OAuth2

## Response Format

All responses follow this structure:

```json
{
  "success": true,
  "data": {...},
  "meta": {
    "clone_id": "abc123...",
    "timestamp": "2026-01-11 12:00:00"
  }
}
```

Errors:

```json
{
  "success": false,
  "error": "Error message here",
  "code": 404
}
```

## CORS Support

The API includes CORS headers for cross-origin requests:

```php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
```

## Testing with JavaScript

```javascript
// List posts
fetch('http://localhost:8002/api/posts')
  .then(r => r.json())
  .then(data => console.log(data));

// Create post
fetch('http://localhost:8002/api/posts', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'New Post',
    text: 'Content here',
    state: 'published'
  })
})
.then(r => r.json())
.then(data => console.log(data));
```

## Next Steps

- **CRUD Basics** → See Example 1
- **Blog System** → See Example 2
- **Documentation** → Read `/docs`
