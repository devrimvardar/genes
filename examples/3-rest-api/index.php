<?php
/**
 * Example 3: REST API with Clone Context
 * 
 * Demonstrates:
 * - RESTful API endpoints
 * - Multi-tenant clone isolation
 * - JSON request/response
 * - CORS support
 * - Error handling
 * 
 * PHP 5.6+ Compatible
 */

require_once '../../genes.php';

// ============================================================================
// CORS HEADERS
// ============================================================================

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ============================================================================
// SETUP
// ============================================================================

// Connect to database
g::run("db.connect", array(
    "driver" => "sqlite",
    "name" => "main",
    "database" => "data/api.db"
));

// Create schema
g::run("db.createSchema", "main");

// ============================================================================
// AUTHENTICATION & CLONE CONTEXT
// ============================================================================

// In a real application, you would:
// 1. Get API token from Authorization header
// 2. Look up token in database to find clone_id
// 3. Set clone context based on token

// For this demo, we'll get or create a default clone
$existingClones = g::run("db.select", "clones", array("domain" => "api-demo.local"));

if (!empty($existingClones)) {
    $clone = $existingClones[0];
} else {
    $cloneHash = g::run("db.insert", "clones", array(
        "name" => "API Demo Clone",
        "domain" => "api-demo.local",
        "type" => "api",
        "state" => "active"
    ));
    $clone = g::run("db.get", "clones", $cloneHash);
}

// Set clone context - ALL API queries now scoped to this clone
g::run("db.setClone", $clone['hash']);

// ============================================================================
// ROUTING
// ============================================================================

// Parse request URL
$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

// Remove query string
$path = strtok($requestUri, '?');

// Remove leading/trailing slashes
$path = trim($path, '/');

// Split into segments
$segments = !empty($path) ? explode('/', $path) : array();

// API routes should start with 'api'
if (empty($segments) || $segments[0] !== 'api') {
    sendError("Invalid API endpoint", 404);
}

// Remove 'api' from segments
array_shift($segments);

// Get resource and ID
$resource = isset($segments[0]) ? $segments[0] : '';
$id = isset($segments[1]) ? $segments[1] : null;

// Route to appropriate handler
switch ($resource) {
    case 'posts':
        handlePosts($requestMethod, $id);
        break;
    
    case 'persons':
        handlePersons($requestMethod, $id);
        break;
    
    case 'labels':
        handleLabels($requestMethod, $id);
        break;
    
    case 'events':
        handleEvents($requestMethod, $id);
        break;
    
    case '':
        // API root - show available endpoints
        sendSuccess(array(
            "message" => "Genes Framework REST API",
            "version" => "2.0",
            "endpoints" => array(
                "GET /api/posts" => "List all posts",
                "GET /api/posts/:hash" => "Get single post",
                "POST /api/posts" => "Create post",
                "PUT /api/posts/:hash" => "Update post",
                "DELETE /api/posts/:hash" => "Delete post",
                "GET /api/persons" => "List all persons",
                "GET /api/labels" => "List all labels",
                "GET /api/events" => "List all events"
            ),
            "clone" => array(
                "hash" => $clone['hash'],
                "name" => $clone['name']
            )
        ));
        break;
    
    default:
        sendError("Unknown resource: " . htmlspecialchars($resource), 404);
}

// ============================================================================
// HANDLERS
// ============================================================================

function handlePosts($method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Get single post
                $post = g::run("db.get", "items", $id);
                if ($post && $post['type'] === 'post') {
                    sendSuccess($post);
                } else {
                    sendError("Post not found", 404);
                }
            } else {
                // List posts
                $posts = g::run("db.select", "items",
                    array("type" => "post"),
                    "main",
                    array("order" => "created_at DESC", "limit" => 50)
                );
                sendSuccess($posts);
            }
            break;
        
        case 'POST':
            // Create post
            $data = getJsonInput();
            
            if (empty($data['title'])) {
                sendError("Title is required", 400);
            }
            
            $postData = array(
                "type" => "post",
                "state" => isset($data['state']) ? $data['state'] : "draft",
                "title" => $data['title'],
                "blurb" => isset($data['blurb']) ? $data['blurb'] : "",
                "text" => isset($data['text']) ? $data['text'] : "",
                "safe_url" => isset($data['safe_url']) ? $data['safe_url'] : generateSlug($data['title'])
            );
            
            $hash = g::run("db.insert", "items", $postData);
            
            if ($hash) {
                $post = g::run("db.get", "items", $hash);
                
                // Log event
                g::run("db.insert", "events", array(
                    "type" => "post.created",
                    "item_id" => $hash,
                    "data" => json_encode(array("title" => $postData['title']))
                ));
                
                sendSuccess($post, 201);
            } else {
                sendError("Failed to create post", 500);
            }
            break;
        
        case 'PUT':
            // Update post
            if (!$id) {
                sendError("Post ID required", 400);
            }
            
            $data = getJsonInput();
            
            $updateData = array();
            if (isset($data['title'])) $updateData['title'] = $data['title'];
            if (isset($data['blurb'])) $updateData['blurb'] = $data['blurb'];
            if (isset($data['text'])) $updateData['text'] = $data['text'];
            if (isset($data['state'])) $updateData['state'] = $data['state'];
            if (isset($data['safe_url'])) $updateData['safe_url'] = $data['safe_url'];
            
            if (empty($updateData)) {
                sendError("No data to update", 400);
            }
            
            $result = g::run("db.update", "items", $updateData, array("hash" => $id));
            
            if ($result) {
                $post = g::run("db.get", "items", $id);
                
                // Log event
                g::run("db.insert", "events", array(
                    "type" => "post.updated",
                    "item_id" => $id,
                    "data" => json_encode($updateData)
                ));
                
                sendSuccess($post);
            } else {
                sendError("Failed to update post", 500);
            }
            break;
        
        case 'DELETE':
            // Delete post (soft delete)
            if (!$id) {
                sendError("Post ID required", 400);
            }
            
            $result = g::run("db.delete", "items", array("hash" => $id));
            
            if ($result) {
                // Log event
                g::run("db.insert", "events", array(
                    "type" => "post.deleted",
                    "item_id" => $id
                ));
                
                sendSuccess(array("deleted" => true, "hash" => $id));
            } else {
                sendError("Failed to delete post", 500);
            }
            break;
        
        default:
            sendError("Method not allowed", 405);
    }
}

function handlePersons($method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Get single person
                $person = g::run("db.get", "persons", $id);
                if ($person) {
                    // Remove sensitive data
                    unset($person['password']);
                    sendSuccess($person);
                } else {
                    sendError("Person not found", 404);
                }
            } else {
                // List persons
                $persons = g::run("db.select", "persons",
                    array(),
                    "main",
                    array("order" => "created_at DESC", "limit" => 50)
                );
                
                // Remove passwords
                foreach ($persons as &$person) {
                    unset($person['password']);
                }
                
                sendSuccess($persons);
            }
            break;
        
        case 'POST':
            // Create person
            $data = getJsonInput();
            
            if (empty($data['email']) || empty($data['name'])) {
                sendError("Email and name are required", 400);
            }
            
            $personData = array(
                "email" => $data['email'],
                "name" => $data['name'],
                "alias" => isset($data['alias']) ? $data['alias'] : null,
                "type" => isset($data['type']) ? $data['type'] : "user",
                "state" => isset($data['state']) ? $data['state'] : "active"
            );
            
            if (isset($data['password'])) {
                $personData['password'] = g::run("crypt.hashPassword", $data['password']);
            }
            
            $hash = g::run("db.insert", "persons", $personData);
            
            if ($hash) {
                $person = g::run("db.get", "persons", $hash);
                unset($person['password']);
                sendSuccess($person, 201);
            } else {
                sendError("Failed to create person (email might already exist)", 400);
            }
            break;
        
        default:
            sendError("Method not allowed", 405);
    }
}

function handleLabels($method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $label = g::run("db.get", "labels", $id);
                if ($label) {
                    sendSuccess($label);
                } else {
                    sendError("Label not found", 404);
                }
            } else {
                $labels = g::run("db.select", "labels",
                    array(),
                    "main",
                    array("order" => "type, name")
                );
                sendSuccess($labels);
            }
            break;
        
        case 'POST':
            $data = getJsonInput();
            
            if (empty($data['type']) || empty($data['key']) || empty($data['name'])) {
                sendError("Type, key, and name are required", 400);
            }
            
            $labelData = array(
                "type" => $data['type'],
                "key" => $data['key'],
                "name" => $data['name'],
                "state" => isset($data['state']) ? $data['state'] : "active"
            );
            
            $hash = g::run("db.insert", "labels", $labelData);
            
            if ($hash) {
                $label = g::run("db.get", "labels", $hash);
                sendSuccess($label, 201);
            } else {
                sendError("Failed to create label (might already exist)", 400);
            }
            break;
        
        default:
            sendError("Method not allowed", 405);
    }
}

function handleEvents($method, $id) {
    if ($method === 'GET') {
        $events = g::run("db.select", "events",
            array(),
            "main",
            array("order" => "created_at DESC", "limit" => 50)
        );
        sendSuccess($events);
    } else {
        sendError("Method not allowed", 405);
    }
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function getJsonInput() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    return $data ? $data : array();
}

function sendSuccess($data, $code = 200) {
    global $clone;
    
    http_response_code($code);
    echo json_encode(array(
        "success" => true,
        "data" => $data,
        "meta" => array(
            "clone_id" => $clone['hash'],
            "timestamp" => date('Y-m-d H:i:s')
        )
    ));
    exit;
}

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(array(
        "success" => false,
        "error" => $message,
        "code" => $code
    ));
    exit;
}

function generateSlug($text) {
    // Simple slug generation
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}
?>
