<?php
/**
 * Example 4: Using Genes Built-in REST API
 * 
 * This example demonstrates Genes Framework's built-in REST API system.
 * No custom handlers needed - just configure the database and go!
 * 
 * Built-in API features:
 * - Automatic CRUD for all 5 tables (items, persons, labels, clones, events)
 * - Filtering: /api/items?filters[type]=todo
 * - Pagination: /api/items?page=1&limit=10
 * - Search: /api/items?search=keyword&searchFields[]=title
 * - Sorting: /api/items?order=created_at DESC
 * - Single record: /api/items/:hash
 * - Validation built-in
 */

// Load Genes Framework
require_once '../../genes.php';

// Database is automatically connected from config.json
// Genes calls core.autoConnectDatabase() during initialization

// Seed demo data if needed
$dbPath = __DIR__ . '/data/app.db';
if (!file_exists($dbPath)) {
    // Schema is auto-created by Genes from config.json
    
    // Seed with demo data
    g::run("db.insert", "persons", array(
        "type" => "user",
        "alias" => "demo",
        "name" => "Demo User",
        "email" => "demo@example.com",
        "state" => "active"
    ));
    
    // Create some sample todos
    $todos = array(
        array(
            "type" => "todo",
            "state" => "pending",
            "title" => "Try GET request",
            "text" => "Fetch all todos from /api/items?filters[type]=todo",
            "meta" => json_encode(array("priority" => "high"))
        ),
        array(
            "type" => "todo",
            "state" => "pending",
            "title" => "Try POST request",
            "text" => "Create a new todo via POST /api/items",
            "meta" => json_encode(array("priority" => "normal"))
        ),
        array(
            "type" => "todo",
            "state" => "completed",
            "title" => "Try PUT request",
            "text" => "Update a todo via PUT /api/items/:id",
            "meta" => json_encode(array("priority" => "low"))
        )
    );
    
    foreach ($todos as $todo) {
        g::run("db.insert", "items", $todo);
    }
}

// Define views
g::def("clone", array(
    
    // Home page - API documentation and demo UI
    "Index" => function ($bits, $lang, $path) {
        echo g::run("tpl.renderView", "Index", array(
            "bits" => $bits,
            "lang" => $lang
        ));
    }
    
));

// That's it! Genes automatically handles /api/* routes
// No custom API handlers needed - the framework does it all

g::run("route.handle");
