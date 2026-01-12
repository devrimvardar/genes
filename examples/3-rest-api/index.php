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

// Suppress all output and errors for clean JSON responses
error_reporting(0);
ini_set('display_errors', '0');
ob_start();

// ============================================================================
// DATABASE SETUP
// ============================================================================

function setupTodoAPI() {
    // Database auto-connects from config.json (database.enabled = true)
    // Schema auto-creates during initialization if needed
    $dbPath = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'todos.db';
    
    // Get or create clone
    $clones = g::run("db.select", "clones", array("domain" => "todo.local"), "main");
    
    if (empty($clones)) {
        $cloneHash = g::run("db.insert", "clones", array(
            "type" => "api",
            "name" => "Todo API",
            "domain" => "todo.local"
        ), "main");
    } else {
        $cloneHash = $clones[0]['hash'];
    }
    
    g::run("db.setClone", $cloneHash);
    
    // Create demo user if none exists
    $users = g::run("db.select", "persons", array("type" => "user"), "main");
    if (empty($users)) {
        g::run("db.insert", "persons", array(
            "type" => "user",
            "alias" => "demo",
            "name" => "Demo User",
            "email" => "demo@example.com",
            "password" => password_hash("demo123", PASSWORD_DEFAULT)
        ), "main");
    }
}

setupTodoAPI();

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
    $users = g::run("db.select", "persons", array("type" => "user"), "main");
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
        $user = getCurrentUser();
        if (!$user) jsonResponse(array("error" => "Unauthorized"), 401);
        
        $request = g::get("request");
        $segments = isset($request['route_segments']) ? $request['route_segments'] : array();
        
        // Handle .json suffix in segments
        if (!empty($segments[0]) && substr($segments[0], -5) === '.json') {
            $segments[0] = substr($segments[0], 0, -5);
        }
        
        // If there's a segment, it's /todos/:id (single todo operations)
        if (!empty($segments[0])) {
            $todoId = $segments[0];
            
            if ($method === 'GET') {
                // Get single todo
                $todos = g::run("db.select", "items", array(
                    "type" => "todo",
                    "hash" => $todoId
                ), "main");
                
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
                ), "main");
                
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
                    g::run("db.update", "items", $updates, array("hash" => $todoId), "main");
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
                ), "main");
                
                if (empty($todos)) {
                    jsonResponse(array("error" => "Todo not found"), 404);
                }
                
                // Hard delete the todo
                g::run("db.delete", "items", array("hash" => $todoId), true, "main");
                
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
                $user = getCurrentUser();
                if (!$user) jsonResponse(array("error" => "Unauthorized"), 401);
                
                $todos = g::run("db.select", "items", array(
                    "type" => "todo"
                ), "main");
                
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
            $user = getCurrentUser();
            if (!$user) jsonResponse(array("error" => "Unauthorized"), 401);
            
            $body = getRequestBody();
            
            if (empty($body['title'])) {
                jsonResponse(array("error" => "Title is required"), 400);
            }
            
            $meta = array();
            if (isset($body['priority'])) {
                $meta['priority'] = $body['priority'];
            }
            
            $hash = g::run("db.insert", "items", array(
                "type" => "todo",
                "state" => "pending",
                "title" => $body['title'],
                "text" => isset($body['description']) ? $body['description'] : '',
                "end_at" => isset($body['due_date']) ? $body['due_date'] : null,
                "meta" => json_encode($meta)
            ), "main");
            
            jsonResponse(array(
                "success" => true,
                "message" => "Todo created successfully",
                "data" => array("id" => $hash)
            ), 201);
        }
    }
));

g::run("route.handle");