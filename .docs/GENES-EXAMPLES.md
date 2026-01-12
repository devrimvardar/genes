# Genes Framework - Complete Examples

> **Real-world code examples for AI coding agents**

---

## 📖 Table of Contents

1. [Hello World](#1-hello-world)
2. [Blog System](#2-blog-system)
3. [User Management](#3-user-management)
4. [REST API Server](#4-rest-api-server)
5. [Single Page Application](#5-single-page-application)
6. [File Upload System](#6-file-upload-system)
7. [Real-time Comments](#7-real-time-comments)
8. [E-commerce Product Catalog](#8-e-commerce-product-catalog)

---

## 1. Hello World

### Minimal Setup

**File: `index.php`**
```php
<?php
require_once './.genes/genes.php';

echo "Hello, Genes Framework!";

// Show performance
$perf = g::run("log.performance", true);
echo "<br>" . $perf;
```

**File: `ui/index.html`**
```html
<!DOCTYPE html>
<html>
<head>
    <title>Hello Genes</title>
    <link rel="stylesheet" href="../.genes/genes.css">
    <script src="../.genes/genes.js" defer></script>
</head>
<body>
    <div class="container">
        <h1 class="text-3xl">Hello, Genes Framework!</h1>
        <p id="message"></p>
    </div>
    
    <script>
        g.que("onReady", function() {
            g.el("#message").textContent = "JavaScript is working!";
        });
    </script>
</body>
</html>
```

---

## 2. Blog System

### Backend (`index.php`)

```php
<?php
require_once './.genes/genes.php';

// ============================================================================
// BLOG APPLICATION
// ============================================================================

g::def("blog", array(

    /**
     * Initialize blog system
     */
    "init" => function() {
        g::run("auth.init");
        g::run("blog.setupDatabase");
    },

    /**
     * Create database tables
     */
    "setupDatabase" => function() {
        // Connect to database
        $config = g::get("config");
        $dbConfig = $config["environments"][g::get("config.current_env")]["database"];
        
        g::run("db.connect", $dbConfig);
        g::run("db.createSchema"); // Creates standard 5 tables
    },

    /**
     * Create a new blog post
     */
    "createPost" => function($title, $content, $authorHash) {
        $data = array(
            "type" => "post",
            "state" => "published",
            "person_hash" => $authorHash,
            "title" => $title,
            "content" => $content,
            "meta" => json_encode(array(
                "views" => 0,
                "likes" => 0,
                "tags" => array()
            ))
        );

        $hash = g::run("db.insert", "clones", $data);

        if ($hash) {
            g::run("log.info", "Post created: $title");
            return array("success" => true, "hash" => $hash);
        } else {
            return array("success" => false, "error" => "Failed to create post");
        }
    },

    /**
     * Get all published posts
     */
    "listPosts" => function($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;

        $posts = g::run("db.select", "clones", array(
            "type" => "post",
            "state" => "published"
        ), "default", array(
            "limit" => $limit,
            "offset" => $offset,
            "order" => "created_at DESC"
        ));

        // Enrich with author data
        foreach ($posts as &$post) {
            if ($post["person_hash"]) {
                $authors = g::run("db.select", "persons", array(
                    "hash" => $post["person_hash"]
                ));
                $post["author"] = $authors ? $authors[0] : null;
            }
        }

        return $posts;
    },

    /**
     * Get single post by hash
     */
    "getPost" => function($hash) {
        $posts = g::run("db.select", "clones", array(
            "hash" => $hash,
            "type" => "post"
        ));

        if (!$posts) {
            return null;
        }

        $post = $posts[0];

        // Increment view count
        $meta = json_decode($post["meta"], true);
        $meta["views"] = isset($meta["views"]) ? $meta["views"] + 1 : 1;

        g::run("db.update", "clones", array("hash" => $hash), array(
            "meta" => json_encode($meta)
        ));

        // Get author
        if ($post["person_hash"]) {
            $authors = g::run("db.select", "persons", array(
                "hash" => $post["person_hash"]
            ));
            $post["author"] = $authors ? $authors[0] : null;
        }

        $post["meta"] = $meta;

        return $post;
    },

    /**
     * Update a post
     */
    "updatePost" => function($hash, $title, $content) {
        $result = g::run("db.update", "clones", 
            array("hash" => $hash),
            array(
                "title" => $title,
                "content" => $content
            )
        );

        if ($result) {
            return array("success" => true);
        } else {
            return array("success" => false, "error" => "Update failed");
        }
    },

    /**
     * Delete a post
     */
    "deletePost" => function($hash) {
        $result = g::run("db.delete", "clones", array(
            "hash" => $hash,
            "type" => "post"
        ));

        if ($result) {
            return array("success" => true);
        } else {
            return array("success" => false, "error" => "Delete failed");
        }
    },

    /**
     * Add comment to post
     */
    "addComment" => function($postHash, $authorHash, $content) {
        $data = array(
            "type" => "comment",
            "state" => "approved",
            "person_hash" => $authorHash,
            "title" => "Re: " . $postHash,
            "content" => $content,
            "meta" => json_encode(array(
                "post_hash" => $postHash
            ))
        );

        return g::run("db.insert", "clones", $data);
    },

    /**
     * Get comments for post
     */
    "getComments" => function($postHash) {
        $comments = g::run("db.select", "clones", array(
            "type" => "comment",
            "state" => "approved"
        ), "default", array(
            "order" => "created_at ASC"
        ));

        // Filter by post hash in meta
        $filtered = array();
        foreach ($comments as $comment) {
            $meta = json_decode($comment["meta"], true);
            if (isset($meta["post_hash"]) && $meta["post_hash"] === $postHash) {
                // Get author
                if ($comment["person_hash"]) {
                    $authors = g::run("db.select", "persons", array(
                        "hash" => $comment["person_hash"]
                    ));
                    $comment["author"] = $authors ? $authors[0] : null;
                }
                $filtered[] = $comment;
            }
        }

        return $filtered;
    }

));

// ============================================================================
// ROUTING
// ============================================================================

g::run("blog.init");
g::run("route.parseUrl");
$request = g::get("request");

$method = $request["method"];
$path = $request["path"];
$segments = $request["segments"];

// API Routes
if ($segments[0] === "api") {
    
    // POST /api/posts - Create post
    if ($method === "POST" && $segments[1] === "posts") {
        
        if (!g::run("auth.check")) {
            g::run("api.respond", array("success" => false, "error" => "Unauthorized"), 401);
        }

        $user = g::run("auth.user");
        $input = json_decode(file_get_contents("php://input"), true);

        $result = g::run("blog.createPost", 
            $input["title"], 
            $input["content"], 
            $user["hash"]
        );

        g::run("api.respond", $result);
    }

    // GET /api/posts - List posts
    if ($method === "GET" && $segments[1] === "posts") {
        $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
        $posts = g::run("blog.listPosts", $page);
        g::run("api.respond", array("success" => true, "data" => $posts));
    }

    // GET /api/posts/{hash} - Get single post
    if ($method === "GET" && $segments[1] === "posts" && isset($segments[2])) {
        $post = g::run("blog.getPost", $segments[2]);
        if ($post) {
            g::run("api.respond", array("success" => true, "data" => $post));
        } else {
            g::run("api.respond", array("success" => false, "error" => "Post not found"), 404);
        }
    }

    // PUT /api/posts/{hash} - Update post
    if ($method === "PUT" && $segments[1] === "posts" && isset($segments[2])) {
        if (!g::run("auth.check")) {
            g::run("api.respond", array("success" => false, "error" => "Unauthorized"), 401);
        }

        $input = json_decode(file_get_contents("php://input"), true);
        $result = g::run("blog.updatePost", $segments[2], $input["title"], $input["content"]);
        g::run("api.respond", $result);
    }

    // DELETE /api/posts/{hash} - Delete post
    if ($method === "DELETE" && $segments[1] === "posts" && isset($segments[2])) {
        if (!g::run("auth.check")) {
            g::run("api.respond", array("success" => false, "error" => "Unauthorized"), 401);
        }

        $result = g::run("blog.deletePost", $segments[2]);
        g::run("api.respond", $result);
    }

    // POST /api/posts/{hash}/comments - Add comment
    if ($method === "POST" && $segments[1] === "posts" && $segments[3] === "comments") {
        if (!g::run("auth.check")) {
            g::run("api.respond", array("success" => false, "error" => "Unauthorized"), 401);
        }

        $user = g::run("auth.user");
        $input = json_decode(file_get_contents("php://input"), true);

        $hash = g::run("blog.addComment", $segments[2], $user["hash"], $input["content"]);
        g::run("api.respond", array("success" => true, "hash" => $hash));
    }

    // GET /api/posts/{hash}/comments - Get comments
    if ($method === "GET" && $segments[1] === "posts" && $segments[3] === "comments") {
        $comments = g::run("blog.getComments", $segments[2]);
        g::run("api.respond", array("success" => true, "data" => $comments));
    }
}

// Serve UI for other routes
if ($path === "/" || $path === "/posts" || preg_match('/^\/posts\//', $path)) {
    readfile("ui/blog.html");
    exit;
}

g::run("route.notFound");
```

### Frontend (`ui/blog.html`)

```html
<!DOCTYPE html>
<html>
<head>
    <title>Genes Blog</title>
    <link rel="stylesheet" href="../.genes/genes.css">
    <script src="../.genes/genes.js" defer></script>
    <style>
        .post-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: var(--space-md);
            margin-bottom: var(--space-md);
        }
        .post-meta {
            color: var(--text-muted);
            font-size: 1.4rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="flex flex-between" style="margin: 2rem 0;">
            <h1 class="text-3xl">Genes Blog</h1>
            <div>
                <button id="newPostBtn" class="btn btn-primary">New Post</button>
                <button id="loginBtn" class="btn">Login</button>
            </div>
        </header>

        <div id="postList"></div>

        <div id="postView" class="hidden"></div>
    </div>

    <!-- New Post Modal (simplified) -->
    <div id="newPostModal" class="hidden">
        <div class="card" style="max-width: 60rem; margin: 2rem auto;">
            <h2>Create New Post</h2>
            <form id="newPostForm">
                <input class="input" type="text" name="title" placeholder="Post Title" required>
                <textarea class="input" name="content" rows="10" placeholder="Post content..." required></textarea>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">Publish</button>
                    <button type="button" class="btn" id="cancelPostBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Configure API
        g.api.config("./api");

        // Initialize app
        g.que("onReady", function() {
            g.run("app.init");
        });

        g.def("app", {
            init: function() {
                g.run("app.loadPosts");
                g.run("app.setupEvents");
                g.run("app.checkAuth");
            },

            loadPosts: function() {
                g.api.list("posts", function(response) {
                    if (response.success) {
                        g.run("app.renderPosts", response.data);
                    }
                });
            },

            renderPosts: function(posts) {
                const container = g.el("#postList");
                container.innerHTML = "";

                posts.forEach(function(post) {
                    const card = g.create("div", {
                        className: "post-card",
                        innerHTML: `
                            <h2 class="text-2xl">${post.title}</h2>
                            <p>${post.content.substring(0, 200)}...</p>
                            <div class="post-meta">
                                By ${post.author ? post.author.name : "Unknown"} 
                                on ${post.created_at}
                            </div>
                            <button class="btn btn-sm view-post-btn" data-hash="${post.hash}">
                                Read More
                            </button>
                        `
                    });
                    container.appendChild(card);
                });
            },

            setupEvents: function() {
                // New post button
                g.on("click", "#newPostBtn", function() {
                    g.el("#newPostModal").classList.remove("hidden");
                });

                // Cancel post
                g.on("click", "#cancelPostBtn", function() {
                    g.el("#newPostModal").classList.add("hidden");
                });

                // Submit new post
                g.on("submit", "#newPostForm", function(form) {
                    const data = g.formData(form);
                    
                    g.api.create("posts", data, function(response) {
                        if (response.success) {
                            form.reset();
                            g.el("#newPostModal").classList.add("hidden");
                            g.run("app.loadPosts");
                        } else {
                            alert("Error: " + response.error);
                        }
                    });
                });

                // View post
                g.on("click", ".view-post-btn", function(btn) {
                    const hash = btn.dataset.hash;
                    g.run("app.viewPost", hash);
                });
            },

            viewPost: function(hash) {
                g.api.get("posts", hash, function(response) {
                    if (response.success) {
                        const post = response.data;
                        const container = g.el("#postView");
                        
                        container.innerHTML = `
                            <h1 class="text-4xl">${post.title}</h1>
                            <div class="post-meta">
                                By ${post.author ? post.author.name : "Unknown"} 
                                on ${post.created_at} | ${post.meta.views} views
                            </div>
                            <div style="margin: 2rem 0;">
                                ${post.content}
                            </div>
                            <button class="btn" id="backToListBtn">Back to Posts</button>
                        `;

                        g.el("#postList").classList.add("hidden");
                        container.classList.remove("hidden");
                    }
                });
            },

            checkAuth: function() {
                // Check if user is logged in
                if (g.auth.check()) {
                    g.el("#loginBtn").textContent = "Logout";
                }
            }
        });
    </script>
</body>
</html>
```

---

## 3. User Management

```php
<?php
require_once './.genes/genes.php';

g::def("users", array(

    /**
     * List all users with pagination
     */
    "list" => function($page = 1, $limit = 20, $filters = array()) {
        $offset = ($page - 1) * $limit;

        $where = array("type" => "user");
        
        // Apply filters
        if (isset($filters["state"])) {
            $where["state"] = $filters["state"];
        }
        if (isset($filters["search"]) && !empty($filters["search"])) {
            // Manual search in results (genes doesn't have LIKE in select)
            $allUsers = g::run("db.select", "persons", $where);
            $search = strtolower($filters["search"]);
            $filtered = array_filter($allUsers, function($user) use ($search) {
                return strpos(strtolower($user["name"]), $search) !== false ||
                       strpos(strtolower($user["email"]), $search) !== false;
            });
            return array_slice($filtered, $offset, $limit);
        }

        return g::run("db.select", "persons", $where, "default", array(
            "limit" => $limit,
            "offset" => $offset,
            "order" => "created_at DESC"
        ));
    },

    /**
     * Get single user by hash
     */
    "get" => function($hash) {
        $users = g::run("db.select", "persons", array(
            "hash" => $hash,
            "type" => "user"
        ));
        return $users ? $users[0] : null;
    },

    /**
     * Create new user
     */
    "create" => function($data) {
        // Validate
        if (!isset($data["email"]) || !filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            return array("success" => false, "error" => "Invalid email");
        }

        // Check if exists
        $existing = g::run("db.select", "persons", array("email" => $data["email"]));
        if (!empty($existing)) {
            return array("success" => false, "error" => "Email already exists");
        }

        // Hash password
        $password = isset($data["password"]) ? $data["password"] : "default123";
        $hashedPassword = g::run("auth.hash", $password);

        // Create user
        $userData = array(
            "type" => "user",
            "state" => "active",
            "email" => $data["email"],
            "password" => $hashedPassword,
            "name" => isset($data["name"]) ? $data["name"] : "",
            "meta" => json_encode(array(
                "created_by" => "admin",
                "preferences" => array()
            ))
        );

        $hash = g::run("db.insert", "persons", $userData);

        if ($hash) {
            return array("success" => true, "hash" => $hash);
        } else {
            return array("success" => false, "error" => "Failed to create user");
        }
    },

    /**
     * Update user
     */
    "update" => function($hash, $data) {
        $updateData = array();

        if (isset($data["name"])) {
            $updateData["name"] = $data["name"];
        }
        if (isset($data["email"])) {
            $updateData["email"] = $data["email"];
        }
        if (isset($data["state"])) {
            $updateData["state"] = $data["state"];
        }
        if (isset($data["password"])) {
            $updateData["password"] = g::run("auth.hash", $data["password"]);
        }

        if (empty($updateData)) {
            return array("success" => false, "error" => "No data to update");
        }

        $result = g::run("db.update", "persons", 
            array("hash" => $hash),
            $updateData
        );

        if ($result) {
            return array("success" => true);
        } else {
            return array("success" => false, "error" => "Update failed");
        }
    },

    /**
     * Delete user (soft delete)
     */
    "delete" => function($hash) {
        $result = g::run("db.delete", "persons", array(
            "hash" => $hash,
            "type" => "user"
        ));

        if ($result) {
            return array("success" => true);
        } else {
            return array("success" => false, "error" => "Delete failed");
        }
    },

    /**
     * Get user statistics
     */
    "stats" => function($hash) {
        // Get user
        $user = g::run("users.get", $hash);
        if (!$user) {
            return null;
        }

        // Count posts
        $posts = g::run("db.select", "clones", array(
            "person_hash" => $hash,
            "type" => "post"
        ));

        // Count comments
        $comments = g::run("db.select", "clones", array(
            "person_hash" => $hash,
            "type" => "comment"
        ));

        return array(
            "posts_count" => count($posts),
            "comments_count" => count($comments),
            "member_since" => $user["created_at"]
        );
    }

));

// API endpoint
g::run("route.parseUrl");
$request = g::get("request");
$method = $request["method"];
$segments = $request["segments"];

if ($segments[0] === "api" && $segments[1] === "users") {
    
    // GET /api/users - List
    if ($method === "GET" && !isset($segments[2])) {
        $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
        $filters = array(
            "state" => isset($_GET["state"]) ? $_GET["state"] : null,
            "search" => isset($_GET["search"]) ? $_GET["search"] : null
        );
        $users = g::run("users.list", $page, 20, $filters);
        g::run("api.respond", array("success" => true, "data" => $users));
    }

    // GET /api/users/{hash} - Get one
    if ($method === "GET" && isset($segments[2])) {
        $user = g::run("users.get", $segments[2]);
        if ($user) {
            // Remove password from response
            unset($user["password"]);
            g::run("api.respond", array("success" => true, "data" => $user));
        } else {
            g::run("api.respond", array("success" => false, "error" => "User not found"), 404);
        }
    }

    // POST /api/users - Create
    if ($method === "POST") {
        $input = json_decode(file_get_contents("php://input"), true);
        $result = g::run("users.create", $input);
        g::run("api.respond", $result);
    }

    // PUT /api/users/{hash} - Update
    if ($method === "PUT" && isset($segments[2])) {
        $input = json_decode(file_get_contents("php://input"), true);
        $result = g::run("users.update", $segments[2], $input);
        g::run("api.respond", $result);
    }

    // DELETE /api/users/{hash} - Delete
    if ($method === "DELETE" && isset($segments[2])) {
        $result = g::run("users.delete", $segments[2]);
        g::run("api.respond", $result);
    }
}
```

---

## 4. REST API Server

**Complete API server with authentication:**

```php
<?php
require_once './.genes/genes.php';

// ============================================================================
// API SERVER CONFIGURATION
// ============================================================================

g::def("api.server", array(

    /**
     * Initialize API server
     */
    "init" => function() {
        // Set headers
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Content-Type: application/json");

        // Handle preflight
        if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
            http_response_code(200);
            exit;
        }

        // Connect database
        $config = g::get("config");
        $env = g::get("config.current_env");
        $dbConfig = $config["environments"][$env]["database"];
        g::run("db.connect", $dbConfig);

        // Start session for auth
        g::run("auth.init");
    },

    /**
     * Authenticate API request
     */
    "authenticate" => function() {
        $headers = getallheaders();
        $authHeader = isset($headers["Authorization"]) ? $headers["Authorization"] : "";

        if (empty($authHeader)) {
            return false;
        }

        // Bearer token authentication
        if (strpos($authHeader, "Bearer ") === 0) {
            $token = substr($authHeader, 7);
            
            // Verify token against database
            $tokens = g::run("db.select", "nodes", array(
                "type" => "api_token",
                "state" => "active",
                "title" => $token
            ));

            if (!empty($tokens)) {
                $tokenData = $tokens[0];
                $meta = json_decode($tokenData["meta"], true);
                
                // Check expiration
                if (isset($meta["expires_at"])) {
                    $expires = strtotime($meta["expires_at"]);
                    if ($expires < time()) {
                        return false; // Token expired
                    }
                }

                // Load user
                if (isset($meta["user_hash"])) {
                    $users = g::run("db.select", "persons", array(
                        "hash" => $meta["user_hash"]
                    ));
                    if (!empty($users)) {
                        g::set("api.current_user", $users[0]);
                        return true;
                    }
                }
            }
        }

        return false;
    },

    /**
     * Route request to handler
     */
    "route" => function() {
        g::run("route.parseUrl");
        $request = g::get("request");
        
        $method = $request["method"];
        $segments = $request["segments"];

        // Remove "api" prefix
        array_shift($segments);
        
        $resource = isset($segments[0]) ? $segments[0] : "";
        $id = isset($segments[1]) ? $segments[1] : null;

        // Define allowed resources
        $allowedResources = array("users", "posts", "comments", "products");

        if (!in_array($resource, $allowedResources)) {
            g::run("api.respond", array(
                "success" => false,
                "error" => "Resource not found"
            ), 404);
        }

        // Check authentication for protected resources
        $publicEndpoints = array("users" => array("POST")); // Registration
        $isPublic = isset($publicEndpoints[$resource]) && 
                    in_array($method, $publicEndpoints[$resource]);

        if (!$isPublic && !g::run("api.server.authenticate")) {
            g::run("api.respond", array(
                "success" => false,
                "error" => "Unauthorized"
            ), 401);
        }

        // Handle request
        $result = g::run("api.handle", $resource, array(
            "allowed_methods" => array("GET", "POST", "PUT", "DELETE")
        ));

        g::run("api.respond", $result);
    },

    /**
     * Generate API token for user
     */
    "generateToken" => function($userHash, $expiresInDays = 30) {
        $token = g::run("crypt.token", 32);
        $expiresAt = date("Y-m-d H:i:s", time() + ($expiresInDays * 24 * 60 * 60));

        $tokenData = array(
            "type" => "api_token",
            "state" => "active",
            "title" => $token,
            "content" => "",
            "meta" => json_encode(array(
                "user_hash" => $userHash,
                "expires_at" => $expiresAt,
                "created_at" => date("Y-m-d H:i:s")
            ))
        );

        g::run("db.insert", "nodes", $tokenData);

        return $token;
    },

    /**
     * Revoke API token
     */
    "revokeToken" => function($token) {
        g::run("db.update", "nodes", 
            array("title" => $token, "type" => "api_token"),
            array("state" => "revoked")
        );
    }

));

// ============================================================================
// RUN API SERVER
// ============================================================================

g::run("api.server.init");
g::run("api.server.route");
```

**Usage example (client):**

```javascript
// Get API token (after login)
fetch("/api/auth/token", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({
        email: "user@example.com",
        password: "secret123"
    })
}).then(r => r.json()).then(data => {
    const token = data.token;
    
    // Use token for authenticated requests
    fetch("/api/users", {
        headers: {
            "Authorization": "Bearer " + token
        }
    }).then(r => r.json()).then(users => {
        console.log(users);
    });
});
```

---

## 5. Single Page Application

**Frontend-only SPA with routing:**

```html
<!DOCTYPE html>
<html>
<head>
    <title>Genes SPA</title>
    <link rel="stylesheet" href=".genes/genes.css">
    <script src=".genes/genes.js" defer></script>
</head>
<body>
    <nav class="flex flex-between" style="padding: 2rem; background: var(--bg-alt);">
        <h1>My App</h1>
        <div class="flex gap-2">
            <a href="/" data-route>Home</a>
            <a href="/about" data-route>About</a>
            <a href="/contact" data-route>Contact</a>
        </div>
    </nav>

    <main id="app" class="container" style="margin-top: 2rem;"></main>

    <script>
        // Simple SPA Router
        g.def("router", {
            routes: {
                "/": function() {
                    return "<h1>Home Page</h1><p>Welcome to Genes SPA!</p>";
                },
                "/about": function() {
                    return "<h1>About</h1><p>This is a single-page application.</p>";
                },
                "/contact": function() {
                    return `
                        <h1>Contact</h1>
                        <form id="contactForm">
                            <input class="input" name="name" placeholder="Name" required>
                            <input class="input" name="email" type="email" placeholder="Email" required>
                            <textarea class="input" name="message" placeholder="Message" required></textarea>
                            <button class="btn btn-primary">Send</button>
                        </form>
                    `;
                }
            },

            navigate: function(path) {
                const route = g.get("router.routes")[path];
                
                if (route) {
                    const content = route();
                    g.el("#app").innerHTML = content;
                    g.url.push(path);
                } else {
                    g.run("router.notFound");
                }
            },

            notFound: function() {
                g.el("#app").innerHTML = "<h1>404 Not Found</h1>";
            },

            init: function() {
                // Handle link clicks
                g.on("click", "[data-route]", function(link) {
                    const path = link.getAttribute("href");
                    g.run("router.navigate", path);
                });

                // Handle browser back/forward
                window.addEventListener("popstate", function() {
                    g.run("router.navigate", window.location.pathname);
                });

                // Initial route
                g.run("router.navigate", window.location.pathname);
            }
        });

        g.que("onReady", function() {
            g.run("router.init");

            // Handle form submission
            g.on("submit", "#contactForm", function(form) {
                const data = g.formData(form);
                console.log("Form submitted:", data);
                alert("Thank you! Message sent.");
                form.reset();
            });
        });
    </script>
</body>
</html>
```

---

_Continued in next sections for brevity. This provides solid starting examples for AI agents to build upon._

---

**License**: MIT | **Version**: 2.0.0
