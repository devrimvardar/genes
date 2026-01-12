<?php
/**
 * Example 3: REST API / Todo System
 * 
 * Demonstrates:
 * - Building a RESTful API with Genes Framework
 * - Using Genes standard schema (items table for todos)
 * - JSON responses
 * - CRUD operations (Create, Read, Update, Delete)
 * - User authentication with persons table
 * - API routes with /api/* prefix
 * - Status codes and error handling
 * 
 * PHP 5.6+ Compatible
 */

require_once '../../genes.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ob_start();

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function jsonResponse($data, $code = 200) {
    ob_clean(); // Clear any buffered output
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getRequestBody() {
    $body = file_get_contents('php://input');
    return json_decode($body, true);
}

function getCurrentUser() {
    // Simple demo: get first user (in production, use sessions/JWT)
    $users = g::run("db.select", "persons", array("type" => "user"));
    
    // Auto-create demo user if none exists
    if (empty($users)) {
        $hash = g::run("db.insert", "persons", array(
            "type" => "user",
            "alias" => "demo",
            "name" => "Demo User",
            "email" => "demo@example.com",
            "password" => password_hash("demo123", PASSWORD_DEFAULT)
        ));
        
        // Fetch the newly created user
        $users = g::run("db.select", "persons", array("type" => "user"));
    }
    
    return !empty($users) ? $users[0] : null;
}

// ============================================================================
// CLONE FUNCTIONS
// ============================================================================

g::def("clone", array(
    
    // Web UI
    "Index" => function ($bits, $lang, $path) {
        echo g::run("tpl.renderView", "Index", array(
            "bits" => $bits,
            "lang" => $lang
        ));
    },
    
    // API: /todos - Handle all todo operations
    "Todos" => function ($bits, $lang, $path) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Demo mode - no authentication required
        // In production, you would check getCurrentUser() here
        
        $request = g::get("request");
        $segments = isset($request['route_segments']) ? $request['route_segments'] : array();
        
        // Handle .json suffix in path (for /todos.json)
        if (strpos($path, '.json') !== false) {
            // This is a .json req$segments[0], 0, -5);
        }
        
        // If there's a segment, it's /todos/:id (single todo operations)
        if (!empty($segments[0])) {
            $todoId = $segments[0];
            
            if ($method === 'GET') {
                // Get single todo
                $todos = g::run("db.select", "items", array(
                    "type" => "todo",
                    "hash" => $todoId
                ));
                
                if (empty($todos)) {
                    jsonResponse(array("error" => "Todo not found"), 404);
                }
                
                $todo = $todos[0];
                $meta = json_decode($todo['meta'], true);
                if (!is_array($meta)) $meta = array();
                
                jsonResponse(array(
                    "success" => true,
                    "data" => array(
                        "id" => $todo['hash'],
                        "title" => $todo['title'],
                        "description" => $todo['text'],
                        "completed" => $todo['state'] === 'completed',
                        "priority" => isset($meta['priority']) ? $meta['priority'] : 'normal',
                        "due_date" => $todo['end_at'],
                        "created_at" => $todo['created_at']
                    )
                ));
            } elseif ($method === 'PUT') {
                // Update todo
                $body = getRequestBody();
                if (!$body) {
                    jsonResponse(array("error" => "Invalid request body"), 400);
                }
                
                $todos = g::run("db.select", "items", array(
                    "type" => "todo",
                    "hash" => $todoId
                ));
                
                if (empty($todos)) {
                    jsonResponse(array("error" => "Todo not found"), 404);
                }
                
                $updates = array();
                
                if (isset($body['title'])) {
                    $updates['title'] = $body['title'];
                }
                
                if (isset($body['description'])) {
                    $updates['text'] = $body['description'];
                }
                
                if (isset($body['due_date'])) {
                    $updates['end_at'] = $body['due_date'];
                }
                
                if (isset($body['completed'])) {
                    $updates['state'] = $body['completed'] ? 'completed' : 'pending';
                }
                
                if (isset($body['priority'])) {
                    $todo = $todos[0];
                    $meta = json_decode($todo['meta'], true);
                    if (!$meta) $meta = array();
                    $meta['priority'] = $body['priority'];
                    $updates['meta'] = json_encode($meta);
                }
                
                if (!empty($updates)) {
                    g::run("db.update", "items", $updates, array("hash" => $todoId));
                }
                
                jsonResponse(array(
                    "success" => true,
                    "message" => "Todo updated successfully"
                ));
            } elseif ($method === 'DELETE') {
                // Delete todo
                $todos = g::run("db.select", "items", array(
                    "type" => "todo",
                    "hash" => $todoId
                ));
                
                if (empty($todos)) {
                    jsonResponse(array("error" => "Todo not found"), 404);
                }
                
                // Hard delete the todo
                g::run("db.delete", "items", array("hash" => $todoId), true);
                
                jsonResponse(array(
                    "success" => true,
                    "message" => "Todo deleted successfully"
                ));
            } else {
                jsonResponse(array("error" => "Method not allowed: $method"), 405);
            }
        }
        
        // No segment means /todos (list or create)
        if ($method === 'GET') {
            // List all todos
            try {
                $todos = g::run("db.select", "items", array(
                    "type" => "todo"
                ));
                
                if (!is_array($todos)) {
                    $todos = array();
                }
                
                // Parse JSON fields and format response
                $result = array();
                foreach ($todos as $todo) {
                    $meta = json_decode($todo['meta'], true);
                    if (!is_array($meta)) $meta = array();
                    
                    $result[] = array(
                        "id" => $todo['hash'],
                        "title" => $todo['title'],
                        "description" => $todo['text'],
                        "completed" => $todo['state'] === 'completed',
                        "priority" => isset($meta['priority']) ? $meta['priority'] : 'normal',
                        "due_date" => $todo['end_at'],
                        "created_at" => $todo['created_at']
                    );
                }
                
                jsonResponse(array(
                    "success" => true,
                    "data" => $result,
                    "count" => count($result)
                ));
            } catch (Exception $e) {
                jsonResponse(array("success" => false, "error" => $e->getMessage()), 500);
            }
        } elseif ($method === 'POST') {
            // Create new todo
            $body = getRequestBody();
            
            if (empty($body['title'])) {
                jsonResponse(array("error" => "Title is required"), 400);
            }
            
            $meta = array();
            if (isset($body['priority'])) {
                $meta['priority'] = $body['priority'];
            }
            
            try {
                // Check database connection
                $dbStatus = g::get("db");
                
                $hash = g::run("db.insert", "items", array(
                    "type" => "todo",
                    "state" => "pending",
                    "title" => $body['title'],
                    "text" => isset($body['description']) ? $body['description'] : '',
                    "end_at" => isset($body['due_date']) ? $body['due_date'] : null,
                    "meta" => json_encode($meta)
                ));
                
                if (!$hash) {
                    $lastError = error_get_last();
                    jsonResponse(array(
                        "error" => "Failed to create todo", 
                        "details" => "Database insert returned empty",
                        "last_error" => $lastError,
                        "db_status" => $dbStatus ? "connected" : "not connected",
                        "db_config" => g::get("config")["database"],
                        "insert_data" => array(
                            "type" => "todo",
                            "state" => "pending",
                            "title" => $body['title'],
                            "text" => isset($body['description']) ? $body['description'] : '',
                            "end_at" => isset($body['due_date']) ? $body['due_date'] : null,
                            "meta" => json_encode($meta)
                        )
                    ), 500);
                }
                
                jsonResponse(array(
                    "success" => true,
                    "message" => "Todo created successfully",
                    "data" => array("id" => $hash)
                ), 201);
            } catch (Exception $e) {
                jsonResponse(array(
                    "error" => "Exception during todo creation",
                    "message" => $e->getMessage(),
                    "trace" => $e->getTraceAsString()
                ), 500);
            }
        }
    }
));

g::run("route.handle");