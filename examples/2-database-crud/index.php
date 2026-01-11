<?php
/*!
 * ============================================================================
 * Example 2: Database CRUD
 * ============================================================================
 * Complete CRUD operations using Genes Framework
 */

require_once '../../genes.php';

// ============================================================================
// DATABASE SETUP
// ============================================================================

// Connect to database
g::run("db.connect", array(
    "host" => "localhost",
    "name" => "genes_test",
    "user" => "root",
    "pass" => ""
));

// Create standard tables (persons, clones, links, nodes, events)
g::run("db.createSchema");

// ============================================================================
// CREATE - Insert new records
// ============================================================================

echo "<h2>CREATE</h2>";

// Insert a person
$personHash = g::run("db.insert", "persons", array(
    "type" => "user",
    "state" => "active",
    "name" => "John Doe",
    "email" => "john@example.com",
    "meta" => json_encode(array(
        "age" => 30,
        "city" => "New York"
    ))
));

echo "Created person with hash: <code>$personHash</code><br>";

// Insert another person
$personHash2 = g::run("db.insert", "persons", array(
    "type" => "user",
    "state" => "active",
    "name" => "Jane Smith",
    "email" => "jane@example.com",
    "meta" => json_encode(array(
        "age" => 28,
        "city" => "Los Angeles"
    ))
));

echo "Created person with hash: <code>$personHash2</code><br>";

// Insert a post (clone)
$postHash = g::run("db.insert", "clones", array(
    "type" => "post",
    "state" => "published",
    "person_hash" => $personHash,
    "title" => "My First Post",
    "content" => "This is the content of my first post.",
    "meta" => json_encode(array(
        "views" => 0,
        "likes" => 0
    ))
));

echo "Created post with hash: <code>$postHash</code><br>";

// ============================================================================
// READ - Select records
// ============================================================================

echo "<h2>READ</h2>";

// Select all active persons
$persons = g::run("db.select", "persons", array(
    "state" => "active"
));

echo "<h3>All Active Persons:</h3>";
echo "<pre>";
print_r($persons);
echo "</pre>";

// Select single person by hash
$person = g::run("db.select", "persons", array(
    "hash" => $personHash
));

echo "<h3>Single Person (John Doe):</h3>";
echo "<pre>";
print_r($person);
echo "</pre>";

// Select posts with limit and order
$posts = g::run("db.select", "clones", array(
    "type" => "post"
), "default", array(
    "limit" => 10,
    "order" => "created_at DESC"
));

echo "<h3>Recent Posts:</h3>";
echo "<pre>";
print_r($posts);
echo "</pre>";

// ============================================================================
// UPDATE - Modify records
// ============================================================================

echo "<h2>UPDATE</h2>";

// Update person's name
$updated = g::run("db.update", "persons", 
    array("hash" => $personHash),
    array("name" => "John Updated Doe")
);

echo "Updated person: " . ($updated ? "✓ Success" : "✗ Failed") . "<br>";

// Verify update
$person = g::run("db.select", "persons", array("hash" => $personHash));
echo "New name: <strong>" . $person[0]["name"] . "</strong><br>";

// Update multiple fields
g::run("db.update", "clones",
    array("hash" => $postHash),
    array(
        "title" => "My Updated Post Title",
        "meta" => json_encode(array(
            "views" => 100,
            "likes" => 15
        ))
    )
);

echo "Updated post with new title and meta<br>";

// ============================================================================
// DELETE - Remove records
// ============================================================================

echo "<h2>DELETE</h2>";

// Soft delete (sets deleted_at timestamp)
$deleted = g::run("db.delete", "persons", array(
    "hash" => $personHash2
));

echo "Soft deleted Jane Smith: " . ($deleted ? "✓ Success" : "✗ Failed") . "<br>";

// Verify soft delete
$deletedPerson = g::run("db.select", "persons", array(
    "hash" => $personHash2
));

if ($deletedPerson && $deletedPerson[0]["deleted_at"]) {
    echo "Deleted at: <code>" . $deletedPerson[0]["deleted_at"] . "</code><br>";
}

// ============================================================================
// ADVANCED QUERIES
// ============================================================================

echo "<h2>ADVANCED QUERIES</h2>";

// Select with custom query
$result = g::run("db.query", 
    "SELECT * FROM persons WHERE state = :state AND deleted_at IS NULL",
    array("state" => "active")
);

echo "<h3>Active Persons (Custom Query):</h3>";
echo "<pre>";
print_r($result);
echo "</pre>";

// ============================================================================
// PERFORMANCE
// ============================================================================

echo "<hr>";
$perf = g::run("log.performance", true);
echo "<pre>$perf</pre>";
