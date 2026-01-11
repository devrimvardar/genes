<?php
/*!
 * ============================================================================
 * Example 3: REST API Server
 * ============================================================================
 * Complete RESTful API with automatic CRUD endpoints
 */

require_once '../../genes.php';

// ============================================================================
// DATABASE SETUP
// ============================================================================

g::run("db.connect", array(
    "host" => "localhost",
    "name" => "genes_api",
    "user" => "root",
    "pass" => ""
));

g::run("db.createSchema");

// ============================================================================
// SEED DATA (on first run)
// ============================================================================

// Check if we have any persons
$existing = g::run("db.select", "persons", array(), "default", array("limit" => 1));

if (empty($existing)) {
    // Create sample users
    g::run("db.insert", "persons", array(
        "type" => "user",
        "state" => "active",
        "name" => "Alice Johnson",
        "email" => "alice@example.com",
        "meta" => json_encode(array("role" => "admin"))
    ));

    g::run("db.insert", "persons", array(
        "type" => "user",
        "state" => "active",
        "name" => "Bob Smith",
        "email" => "bob@example.com",
        "meta" => json_encode(array("role" => "user"))
    ));
    
    // Create sample posts
    g::run("db.insert", "clones", array(
        "type" => "post",
        "state" => "published",
        "title" => "Getting Started with Genes",
        "content" => "This is a sample blog post.",
        "meta" => json_encode(array("views" => 0))
    ));
}

// ============================================================================
// ROUTING & API HANDLING
// ============================================================================

g::run("route.parseUrl");
$request = g::get("request");

// Check if this is an API request
if (isset($request["segments"][0]) && $request["segments"][0] === "api") {
    
    // Get table name from URL
    $table = isset($request["segments"][1]) ? $request["segments"][1] : null;
    
    // Validate table name (security)
    $allowedTables = array("persons", "clones", "links", "nodes", "events");
    
    if (!in_array($table, $allowedTables)) {
        g::run("api.respond", array(
            "success" => false,
            "error" => "Invalid table name"
        ), 400);
        exit;
    }
    
    // Handle the API request
    $result = g::run("api.handle", $table);
    
    // Send response
    g::run("api.respond", $result);
    exit;
}

// ============================================================================
// DOCUMENTATION PAGE (if not API request)
// ============================================================================
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genes REST API Example</title>
    <link rel="stylesheet" href="../../genes.css">
    <style>
        .endpoint { 
            background: var(--bg-secondary); 
            padding: 1rem; 
            margin: 0.5rem 0; 
            border-radius: 0.25rem;
        }
        .method { 
            display: inline-block; 
            padding: 0.25rem 0.5rem; 
            border-radius: 0.25rem; 
            font-weight: bold;
            margin-right: 0.5rem;
        }
        .get { background: #4CAF50; color: white; }
        .post { background: #2196F3; color: white; }
        .put { background: #FF9800; color: white; }
        .delete { background: #F44336; color: white; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 64rem; margin: 2rem auto; padding: 0 1rem;">
        <h1>Genes REST API Example</h1>
        <p>A complete RESTful API server with automatic CRUD endpoints.</p>

        <h2>Available Tables</h2>
        <ul>
            <li><code>persons</code> - Users and accounts</li>
            <li><code>clones</code> - User-generated content</li>
            <li><code>links</code> - Relationships between entities</li>
            <li><code>nodes</code> - Static content</li>
            <li><code>events</code> - Audit log</li>
        </ul>

        <h2>API Endpoints</h2>

        <div class="endpoint">
            <span class="method get">GET</span>
            <code>/api/{table}</code>
            <p>List all records in a table</p>
            <pre>curl http://localhost:8000/api/persons</pre>
        </div>

        <div class="endpoint">
            <span class="method get">GET</span>
            <code>/api/{table}?state=active</code>
            <p>Filter records by query parameters</p>
            <pre>curl http://localhost:8000/api/persons?state=active</pre>
        </div>

        <div class="endpoint">
            <span class="method get">GET</span>
            <code>/api/{table}/{hash}</code>
            <p>Get a single record by hash</p>
            <pre>curl http://localhost:8000/api/persons/{hash}</pre>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span>
            <code>/api/{table}</code>
            <p>Create a new record</p>
            <pre>curl -X POST http://localhost:8000/api/persons \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","state":"active"}'</pre>
        </div>

        <div class="endpoint">
            <span class="method put">PUT</span>
            <code>/api/{table}/{hash}</code>
            <p>Update an existing record</p>
            <pre>curl -X PUT http://localhost:8000/api/persons/{hash} \
  -H "Content-Type: application/json" \
  -d '{"name":"Jane Doe"}'</pre>
        </div>

        <div class="endpoint">
            <span class="method delete">DELETE</span>
            <code>/api/{table}/{hash}</code>
            <p>Delete a record (soft delete)</p>
            <pre>curl -X DELETE http://localhost:8000/api/persons/{hash}</pre>
        </div>

        <h2>Example Requests</h2>

        <h3>List all persons</h3>
        <pre>curl http://localhost:8000/api/persons</pre>

        <h3>Get active persons only</h3>
        <pre>curl http://localhost:8000/api/persons?state=active</pre>

        <h3>Create a new person</h3>
        <pre>curl -X POST http://localhost:8000/api/persons \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Charlie Brown",
    "email": "charlie@example.com",
    "state": "active",
    "type": "user"
  }'</pre>

        <h3>Create a new post</h3>
        <pre>curl -X POST http://localhost:8000/api/clones \
  -H "Content-Type: application/json" \
  -d '{
    "type": "post",
    "state": "published",
    "title": "My New Post",
    "content": "This is the content of my post."
  }'</pre>

        <h2>Response Format</h2>

        <p>All API responses are in JSON format:</p>

        <h3>Success Response</h3>
        <pre>{
  "success": true,
  "data": [...],
  "count": 2
}</pre>

        <h3>Error Response</h3>
        <pre>{
  "success": false,
  "error": "Error message"
}</pre>

        <h2>Try It Now</h2>

        <p>Open a terminal and try these commands:</p>

        <pre># List all persons
curl http://localhost:8000/api/persons

# Create a new person
curl -X POST http://localhost:8000/api/persons \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","state":"active"}'

# List persons again to see the new one
curl http://localhost:8000/api/persons</pre>

        <hr style="margin: 2rem 0;">
        
        <p><a href="../4-blog-system/">Next: Example 4 - Blog System →</a></p>
    </div>
</body>
</html>
