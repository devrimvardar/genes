<?php
/*!
 * ============================================================================
 * Example 4: Blog System
 * ============================================================================
 * Complete blog with authentication, posts, and comments
 */

require_once '../../genes.php';

// ============================================================================
// DATABASE & AUTH SETUP
// ============================================================================

g::run("db.connect", array(
    "host" => "localhost",
    "name" => "genes_blog",
    "user" => "root",
    "pass" => ""
));

g::run("db.createSchema");
g::run("auth.init");

// ============================================================================
// HANDLE FORM SUBMISSIONS
// ============================================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // REGISTER
    if (isset($_POST["action"]) && $_POST["action"] === "register") {
        $hash = g::run("auth.register", array(
            "email" => $_POST["email"],
            "password" => $_POST["password"],
            "name" => $_POST["name"]
        ));
        
        if ($hash) {
            header("Location: ?message=registered");
            exit;
        }
    }
    
    // LOGIN
    if (isset($_POST["action"]) && $_POST["action"] === "login") {
        $success = g::run("auth.login", $_POST["email"], $_POST["password"]);
        
        if ($success) {
            header("Location: ?message=loggedin");
            exit;
        } else {
            $error = "Invalid credentials";
        }
    }
    
    // CREATE POST
    if (isset($_POST["action"]) && $_POST["action"] === "createPost") {
        if (g::run("auth.check")) {
            $user = g::run("auth.user");
            
            $hash = g::run("db.insert", "clones", array(
                "type" => "post",
                "state" => "published",
                "person_hash" => $user["hash"],
                "title" => $_POST["title"],
                "content" => $_POST["content"],
                "meta" => json_encode(array(
                    "views" => 0,
                    "comments" => 0
                ))
            ));
            
            if ($hash) {
                header("Location: ?message=posted");
                exit;
            }
        }
    }
}

// LOGOUT
if (isset($_GET["logout"])) {
    g::run("auth.logout");
    header("Location: ?message=loggedout");
    exit;
}

// ============================================================================
// GET DATA
// ============================================================================

$loggedIn = g::run("auth.check");
$user = $loggedIn ? g::run("auth.user") : null;

// Get all published posts
$posts = g::run("db.select", "clones", array(
    "type" => "post",
    "state" => "published"
), "default", array(
    "order" => "created_at DESC",
    "limit" => 20
));

// Enrich posts with author data
foreach ($posts as &$post) {
    if ($post["person_hash"]) {
        $authors = g::run("db.select", "persons", array("hash" => $post["person_hash"]));
        $post["author"] = $authors ? $authors[0] : null;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genes Blog System</title>
    <link rel="stylesheet" href="../../genes.css">
    <style>
        .container { max-width: 64rem; margin: 2rem auto; padding: 0 1rem; }
        .post { 
            background: var(--bg-secondary); 
            padding: 1.5rem; 
            margin: 1rem 0; 
            border-radius: 0.5rem;
        }
        .post-header { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 1rem;
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        .post-title { margin: 0 0 0.5rem 0; }
        .post-content { line-height: 1.6; }
        .auth-section { 
            background: var(--bg-tertiary); 
            padding: 1.5rem; 
            border-radius: 0.5rem;
            margin: 1rem 0;
        }
        .message {
            padding: 1rem;
            border-radius: 0.25rem;
            margin: 1rem 0;
        }
        .message-success { background: #4CAF50; color: white; }
        .message-error { background: #F44336; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Genes Blog System</h1>
        
        <?php if (isset($_GET["message"])): ?>
            <div class="message message-success">
                <?php
                $messages = array(
                    "registered" => "✓ Registration successful! Please log in.",
                    "loggedin" => "✓ Welcome back!",
                    "loggedout" => "✓ You have been logged out.",
                    "posted" => "✓ Post published successfully!"
                );
                echo isset($messages[$_GET["message"]]) ? $messages[$_GET["message"]] : "Success!";
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message message-error">✗ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($loggedIn): ?>
            <!-- LOGGED IN VIEW -->
            <div class="auth-section">
                <p>Welcome, <strong><?php echo htmlspecialchars($user["name"]); ?></strong>! 
                   (<a href="?logout=1">Logout</a>)</p>
            </div>
            
            <!-- CREATE POST FORM -->
            <div class="auth-section">
                <h2>Create New Post</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="createPost">
                    
                    <div style="margin-bottom: 1rem;">
                        <label>Title:</label><br>
                        <input type="text" name="title" class="input" required style="width: 100%;">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label>Content:</label><br>
                        <textarea name="content" class="input" rows="6" required style="width: 100%;"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Publish Post</button>
                </form>
            </div>
            
        <?php else: ?>
            <!-- NOT LOGGED IN VIEW -->
            <div class="auth-section">
                <h2>Login</h2>
                <form method="POST" style="margin-bottom: 2rem;">
                    <input type="hidden" name="action" value="login">
                    
                    <div style="margin-bottom: 1rem;">
                        <input type="email" name="email" placeholder="Email" class="input" required>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <input type="password" name="password" placeholder="Password" class="input" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                
                <h2>Register</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    
                    <div style="margin-bottom: 1rem;">
                        <input type="text" name="name" placeholder="Full Name" class="input" required>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <input type="email" name="email" placeholder="Email" class="input" required>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <input type="password" name="password" placeholder="Password" class="input" required>
                    </div>
                    
                    <button type="submit" class="btn">Register</button>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- POSTS LIST -->
        <h2>Recent Posts</h2>
        
        <?php if (empty($posts)): ?>
            <p style="color: var(--text-muted);">No posts yet. Be the first to post!</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="post">
                    <div class="post-header">
                        <span>
                            By <strong><?php echo htmlspecialchars($post["author"]["name"] ?? "Unknown"); ?></strong>
                        </span>
                        <span><?php echo date("M d, Y", strtotime($post["created_at"])); ?></span>
                    </div>
                    
                    <h3 class="post-title"><?php echo htmlspecialchars($post["title"]); ?></h3>
                    
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($post["content"])); ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
        
    </div>
</body>
</html>
