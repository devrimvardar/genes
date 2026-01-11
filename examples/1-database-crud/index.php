<?php
/**
 * Example 1: Database CRUD Operations
 * 
 * Demonstrates:
 * - Correct 5-table schema (clones, persons, items, labels, events)
 * - Both MySQL and SQLite drivers
 * - Multi-tenant clone-based isolation
 * - Complete CRUD operations
 * - Auto-generated hashes and timestamps
 * - Soft delete pattern
 * 
 * PHP 5.6+ Compatible
 */

require_once '../../genes.php';

// ============================================================================
// CONFIGURATION
// ============================================================================

// Choose your database driver
$USE_SQLITE = true; // Set to false to use MySQL

if ($USE_SQLITE) {
    $dbConfig = array(
        "driver" => "sqlite",
        "name" => "main",
        "database" => "data/crud-example.db"
    );
} else {
    $dbConfig = array(
        "driver" => "mysql",
        "name" => "main",
        "host" => "localhost",
        "database" => "genes_crud",
        "username" => "root",
        "password" => ""
    );
}

// ============================================================================
// SETUP
// ============================================================================

echo "<h1>Genes Framework: Database CRUD Example</h1>";
echo "<p><strong>Database:</strong> " . strtoupper($dbConfig['driver']) . "</p>";
echo "<hr>";

// Connect to database
$connection = g::run("db.connect", $dbConfig);

if (!$connection) {
    die("<p style='color:red;'>Database connection failed!</p>");
}

echo "<p style='color:green;'>✓ Database connected</p>";

// Create schema (safe to run multiple times)
$schemaCreated = g::run("db.createSchema", "main");

if ($schemaCreated) {
    echo "<p style='color:green;'>✓ Schema created/verified</p>";
} else {
    die("<p style='color:red;'>Schema creation failed!</p>");
}

echo "<hr>";

// ============================================================================
// STEP 1: CREATE CLONE (Master Record)
// ============================================================================

echo "<h2>Step 1: Create Clone</h2>";

// Check if clone already exists
$existingClones = g::run("db.select", "clones", array("domain" => "crud-example.local"));

if (!empty($existingClones)) {
    $clone = $existingClones[0];
    echo "<p>Using existing clone: <strong>" . htmlspecialchars($clone['name']) . "</strong></p>";
} else {
    $cloneHash = g::run("db.insert", "clones", array(
        "name" => "CRUD Example Blog",
        "domain" => "crud-example.local",
        "type" => "blog",
        "state" => "active",
        "settings" => json_encode(array(
            "theme" => "default",
            "language" => "en"
        ))
    ));
    
    if ($cloneHash) {
        $clone = g::run("db.get", "clones", $cloneHash);
        echo "<p style='color:green;'>✓ Clone created: <strong>" . htmlspecialchars($clone['name']) . "</strong></p>";
        echo "<p>Hash: <code>" . htmlspecialchars($cloneHash) . "</code></p>";
    } else {
        die("<p style='color:red;'>Clone creation failed!</p>");
    }
}

// Set clone context - all subsequent queries will auto-filter by this clone
g::run("db.setClone", $clone['hash']);
echo "<p style='color:blue;'>→ Clone context set. All queries now scoped to this clone.</p>";

echo "<hr>";

// ============================================================================
// STEP 2: CREATE PERSON (User)
// ============================================================================

echo "<h2>Step 2: Create Person (User)</h2>";

$personHash = g::run("db.insert", "persons", array(
    "email" => "john@example.com",
    "name" => "John Doe",
    "alias" => "johndoe",
    "type" => "user",
    "state" => "active",
    "meta" => json_encode(array(
        "bio" => "Example user for CRUD demo",
        "website" => "https://johndoe.com"
    ))
    // Note: clone_id automatically added by framework!
));

if ($personHash) {
    $person = g::run("db.get", "persons", $personHash);
    echo "<p style='color:green;'>✓ Person created: <strong>" . htmlspecialchars($person['name']) . "</strong></p>";
    echo "<p>Hash: <code>" . htmlspecialchars($personHash) . "</code></p>";
    echo "<p>Email: " . htmlspecialchars($person['email']) . "</p>";
    echo "<p>Clone ID: <code>" . htmlspecialchars($person['clone_id']) . "</code> (auto-added!)</p>";
} else {
    echo "<p style='color:orange;'>Person might already exist (email unique constraint)</p>";
    
    // Get existing person
    $persons = g::run("db.select", "persons", array("email" => "john@example.com"));
    if (!empty($persons)) {
        $person = $persons[0];
        $personHash = $person['hash'];
        echo "<p>Using existing person: <strong>" . htmlspecialchars($person['name']) . "</strong></p>";
    }
}

echo "<hr>";

// ============================================================================
// STEP 3: CREATE ITEM (Blog Post)
// ============================================================================

echo "<h2>Step 3: Create Item (Blog Post)</h2>";

$itemHash = g::run("db.insert", "items", array(
    "type" => "post",
    "state" => "published",
    "title" => "Understanding the Genes Framework Schema",
    "safe_url" => "understanding-genes-schema",
    "blurb" => "Learn about the 5-table multi-tenant schema",
    "text" => "This post explains how Genes uses clones, persons, items, labels, and events to build multi-tenant applications...",
    "created_by" => isset($personHash) ? $personHash : null
    // Note: clone_id automatically added by framework!
));

if ($itemHash) {
    $item = g::run("db.get", "items", $itemHash);
    echo "<p style='color:green;'>✓ Item created: <strong>" . htmlspecialchars($item['title']) . "</strong></p>";
    echo "<p>Hash: <code>" . htmlspecialchars($itemHash) . "</code></p>";
    echo "<p>Type: " . htmlspecialchars($item['type']) . "</p>";
    echo "<p>State: " . htmlspecialchars($item['state']) . "</p>";
    echo "<p>Clone ID: <code>" . htmlspecialchars($item['clone_id']) . "</code> (auto-added!)</p>";
} else {
    echo "<p style='color:red;'>Item creation failed!</p>";
}

echo "<hr>";

// ============================================================================
// STEP 4: CREATE LABEL (Category)
// ============================================================================

echo "<h2>Step 4: Create Label (Category)</h2>";

$labelHash = g::run("db.insert", "labels", array(
    "type" => "category",
    "key" => "tutorials",
    "name" => "Tutorials",
    "state" => "active",
    "meta" => json_encode(array(
        "color" => "#3498db",
        "icon" => "book"
    ))
    // Note: clone_id automatically added by framework!
));

if ($labelHash) {
    $label = g::run("db.get", "labels", $labelHash);
    echo "<p style='color:green;'>✓ Label created: <strong>" . htmlspecialchars($label['name']) . "</strong></p>";
    echo "<p>Hash: <code>" . htmlspecialchars($labelHash) . "</code></p>";
    echo "<p>Type: " . htmlspecialchars($label['type']) . "</p>";
    echo "<p>Key: " . htmlspecialchars($label['key']) . "</p>";
    echo "<p>Clone ID: <code>" . htmlspecialchars($label['clone_id']) . "</code> (auto-added!)</p>";
} else {
    echo "<p style='color:orange;'>Label might already exist (unique constraint on clone_id + type + key)</p>";
    
    // Get existing label
    $labels = g::run("db.select", "labels", array("type" => "category", "key" => "tutorials"));
    if (!empty($labels)) {
        $label = $labels[0];
        $labelHash = $label['hash'];
        echo "<p>Using existing label: <strong>" . htmlspecialchars($label['name']) . "</strong></p>";
    }
}

echo "<hr>";

// ============================================================================
// STEP 5: CREATE EVENT (Audit Log)
// ============================================================================

echo "<h2>Step 5: Create Event (Audit Log)</h2>";

$eventHash = g::run("db.insert", "events", array(
    "type" => "post.created",
    "state" => "active",
    "person_id" => isset($personHash) ? $personHash : null,
    "item_id" => isset($itemHash) ? $itemHash : null,
    "data" => json_encode(array(
        "title" => "Understanding the Genes Framework Schema",
        "ip" => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
        "user_agent" => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'CLI'
    ))
    // Note: clone_id automatically added by framework!
));

if ($eventHash) {
    $event = g::run("db.get", "events", $eventHash);
    echo "<p style='color:green;'>✓ Event created: <strong>" . htmlspecialchars($event['type']) . "</strong></p>";
    echo "<p>Hash: <code>" . htmlspecialchars($eventHash) . "</code></p>";
    echo "<p>Clone ID: <code>" . htmlspecialchars($event['clone_id']) . "</code> (auto-added!)</p>";
} else {
    echo "<p style='color:red;'>Event creation failed!</p>";
}

echo "<hr>";

// ============================================================================
// STEP 6: READ OPERATIONS
// ============================================================================

echo "<h2>Step 6: Read Operations</h2>";

// Get single record by hash
if (isset($itemHash)) {
    $retrievedItem = g::run("db.get", "items", $itemHash);
    echo "<p><strong>Get by hash:</strong> " . htmlspecialchars($retrievedItem['title']) . "</p>";
}

// Select multiple records with filters
$publishedPosts = g::run("db.select", "items", array(
    "type" => "post",
    "state" => "published"
));

echo "<p><strong>Published posts count:</strong> " . count($publishedPosts) . "</p>";

// Select with options (limit, order)
$recentPosts = g::run("db.select", "items",
    array("type" => "post"),
    "main",
    array("limit" => 5, "order" => "created_at DESC")
);

echo "<p><strong>Recent posts (limit 5):</strong> " . count($recentPosts) . " found</p>";

echo "<hr>";

// ============================================================================
// STEP 7: UPDATE OPERATIONS
// ============================================================================

echo "<h2>Step 7: Update Operations</h2>";

if (isset($itemHash)) {
    // Update single field
    $updateResult = g::run("db.update", "items",
        array("blurb" => "UPDATED: Learn about the 5-table multi-tenant schema (updated!)"),
        array("hash" => $itemHash)
    );
    
    if ($updateResult) {
        echo "<p style='color:green;'>✓ Item updated</p>";
        
        // Verify update
        $updatedItem = g::run("db.get", "items", $itemHash);
        echo "<p>New blurb: <em>" . htmlspecialchars($updatedItem['blurb']) . "</em></p>";
        echo "<p>Updated at: " . htmlspecialchars($updatedItem['updated_at']) . " (auto-updated!)</p>";
    }
}

echo "<hr>";

// ============================================================================
// STEP 8: DELETE OPERATIONS
// ============================================================================

echo "<h2>Step 8: Delete Operations</h2>";

// Create a temporary item to demonstrate soft delete
$tempItemHash = g::run("db.insert", "items", array(
    "type" => "post",
    "state" => "draft",
    "title" => "Temporary Post for Delete Demo"
));

if ($tempItemHash) {
    echo "<p>Created temporary item: <code>" . htmlspecialchars($tempItemHash) . "</code></p>";
    
    // Soft delete (sets state = 'deleted')
    g::run("db.delete", "items", array("hash" => $tempItemHash));
    
    $softDeletedItem = g::run("db.get", "items", $tempItemHash);
    echo "<p style='color:green;'>✓ Soft delete complete</p>";
    echo "<p>Item state: <strong>" . htmlspecialchars($softDeletedItem['state']) . "</strong> (changed to 'deleted')</p>";
    echo "<p>Record still exists in database (soft delete)</p>";
    
    // Hard delete (permanent removal)
    g::run("db.delete", "items", array("hash" => $tempItemHash), true);
    
    $hardDeletedItem = g::run("db.get", "items", $tempItemHash);
    if ($hardDeletedItem === false) {
        echo "<p style='color:green;'>✓ Hard delete complete</p>";
        echo "<p>Record removed from database (permanent)</p>";
    }
}

echo "<hr>";

// ============================================================================
// STEP 9: CLONE ISOLATION DEMO
// ============================================================================

echo "<h2>Step 9: Clone Isolation Demo</h2>";

// Show current clone context
$currentCloneHash = g::get("db.current_clone");
echo "<p><strong>Current clone context:</strong> <code>" . htmlspecialchars($currentCloneHash) . "</code></p>";

// Count items in current clone
$currentCloneItems = g::run("db.select", "items", array());
echo "<p><strong>Items in current clone:</strong> " . count($currentCloneItems) . "</p>";

// Temporarily clear clone context to see ALL items across ALL clones
$originalClone = $currentCloneHash;
g::set("db.current_clone", null);

$allItems = g::run("db.select", "items", array(), "main");
echo "<p><strong>Total items across ALL clones:</strong> " . count($allItems) . "</p>";

// Restore clone context
g::run("db.setClone", $originalClone);
echo "<p style='color:blue;'>→ Clone context restored</p>";

echo "<hr>";

// ============================================================================
// SUMMARY
// ============================================================================

echo "<h2>Summary</h2>";
echo "<ul>";
echo "<li>✓ Database driver: <strong>" . strtoupper($dbConfig['driver']) . "</strong></li>";
echo "<li>✓ Schema: <strong>5 tables</strong> (clones, persons, items, labels, events)</li>";
echo "<li>✓ Multi-tenancy: <strong>Clone-based isolation</strong></li>";
echo "<li>✓ CRUD operations: <strong>All working</strong></li>";
echo "<li>✓ Auto-fields: <strong>hash, clone_id, timestamps</strong></li>";
echo "<li>✓ Soft delete: <strong>Demonstrated</strong></li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>See Example 2: Multi-Tenant Blog System</li>";
echo "<li>See Example 3: REST API with Clone Context</li>";
echo "<li>Read documentation in <code>/docs</code></li>";
echo "</ul>";

?>
