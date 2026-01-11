<?php
/**
 * Example 2: Multi-Tenant Blog System
 * 
 * Demonstrates:
 * - Multi-tenant clone-based isolation
 * - Items table for blog posts
 * - Labels for categories
 * - Person (author) relationships
 * - Events for analytics
 * - URL routing with safe_url
 * 
 * PHP 5.6+ Compatible
 */

require_once '../../genes.php';

// ============================================================================
// SETUP
// ============================================================================

// Connect to database (SQLite for simplicity)
g::run("db.connect", array(
    "driver" => "sqlite",
    "name" => "main",
    "database" => "data/blog.db"
));

// Create schema
g::run("db.createSchema", "main");

// ============================================================================
// CLONE SETUP
// ============================================================================

// Get or create clone
$existingClones = g::run("db.select", "clones", array("domain" => "myblog.local"));

if (!empty($existingClones)) {
    $clone = $existingClones[0];
} else {
    $cloneHash = g::run("db.insert", "clones", array(
        "name" => "My Tech Blog",
        "domain" => "myblog.local",
        "type" => "blog",
        "state" => "active",
        "settings" => json_encode(array(
            "tagline" => "Exploring technology and code",
            "theme" => "default"
        ))
    ));
    $clone = g::run("db.get", "clones", $cloneHash);
}

// Set clone context - ALL queries now scoped to this blog
g::run("db.setClone", $clone['hash']);

// ============================================================================
// SEED DATA (First run only)
// ============================================================================

// Create author if doesn't exist
$authors = g::run("db.select", "persons", array("email" => "author@myblog.local"));

if (empty($authors)) {
    $authorHash = g::run("db.insert", "persons", array(
        "email" => "author@myblog.local",
        "name" => "Jane Smith",
        "alias" => "janesmith",
        "type" => "user",
        "state" => "active",
        "meta" => json_encode(array(
            "bio" => "Tech blogger and developer",
            "twitter" => "@janesmith"
        ))
    ));
} else {
    $authorHash = $authors[0]['hash'];
}

// Create categories if don't exist
$categories = array(
    array("key" => "php", "name" => "PHP"),
    array("key" => "databases", "name" => "Databases"),
    array("key" => "frameworks", "name" => "Frameworks")
);

$categoryHashes = array();
foreach ($categories as $cat) {
    $existing = g::run("db.select", "labels", array(
        "type" => "category",
        "key" => $cat['key']
    ));
    
    if (empty($existing)) {
        $hash = g::run("db.insert", "labels", array(
            "type" => "category",
            "key" => $cat['key'],
            "name" => $cat['name'],
            "state" => "active"
        ));
        $categoryHashes[$cat['key']] = $hash;
    } else {
        $categoryHashes[$cat['key']] = $existing[0]['hash'];
    }
}

// Create sample posts if don't exist
$samplePosts = array(
    array(
        "title" => "Understanding the Genes Framework",
        "safe_url" => "understanding-genes-framework",
        "blurb" => "An introduction to the Genes Framework and its multi-tenant architecture.",
        "text" => "The Genes Framework is a lightweight PHP framework designed for building multi-tenant applications. It uses a clone-based architecture where each 'clone' represents an isolated workspace.\n\nKey features:\n- Zero dependencies\n- Multi-tenant by design\n- Single-file framework\n- PHP 5.6+ compatible\n\nThe framework uses a 5-table schema: clones (master), persons (users), items (content), labels (taxonomy), and events (audit log).",
        "category" => "frameworks"
    ),
    array(
        "title" => "Multi-Tenant Database Design",
        "safe_url" => "multi-tenant-database-design",
        "blurb" => "Learn how to design databases for multi-tenant applications.",
        "text" => "Multi-tenant database design requires careful consideration of isolation, performance, and scalability.\n\nThree main approaches:\n1. Separate database per tenant (most isolated, expensive)\n2. Separate schema per tenant (balanced)\n3. Shared schema with tenant_id (most efficient)\n\nGenes Framework uses approach #3 with clone_id for automatic filtering and isolation.",
        "category" => "databases"
    ),
    array(
        "title" => "PHP 5.6 Compatibility Tips",
        "safe_url" => "php-56-compatibility-tips",
        "blurb" => "Writing PHP code that works on both legacy and modern servers.",
        "text" => "When building frameworks that need to work on PHP 5.6+, avoid these features:\n\n- Null coalescing operator (??)\n- Return type declarations\n- Scalar type hints\n- Anonymous classes\n- Spaceship operator\n\nInstead use:\n- isset() with ternary operator\n- PHPDoc comments for types\n- Traditional array() syntax for consistency",
        "category" => "php"
    )
);

foreach ($samplePosts as $postData) {
    $existing = g::run("db.select", "items", array(
        "type" => "post",
        "safe_url" => $postData['safe_url']
    ));
    
    if (empty($existing)) {
        $categoryHash = isset($categoryHashes[$postData['category']]) ? $categoryHashes[$postData['category']] : null;
        
        g::run("db.insert", "items", array(
            "type" => "post",
            "state" => "published",
            "title" => $postData['title'],
            "safe_url" => $postData['safe_url'],
            "blurb" => $postData['blurb'],
            "text" => $postData['text'],
            "labels" => json_encode($categoryHash ? array($categoryHash) : array()),
            "created_by" => $authorHash
        ));
    }
}

// ============================================================================
// ROUTING
// ============================================================================

// Parse URL to determine what to show
$postSlug = isset($_GET['post']) ? $_GET['post'] : null;

if ($postSlug) {
    // SINGLE POST VIEW
    showSinglePost($postSlug, $authorHash);
} else {
    // HOME PAGE - LIST POSTS
    showPostList();
}

// ============================================================================
// FUNCTIONS
// ============================================================================

function showPostList() {
    global $clone;
    
    // Get all published posts, ordered by newest first
    $posts = g::run("db.select", "items",
        array("type" => "post", "state" => "published"),
        "main",
        array("limit" => 20, "order" => "created_at DESC")
    );
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($clone['name']); ?></title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
            h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
            h2 { color: #34495e; }
            .tagline { color: #7f8c8d; font-style: italic; margin-top: -10px; }
            .post { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #3498db; }
            .post h2 { margin-top: 0; }
            .post h2 a { color: #2c3e50; text-decoration: none; }
            .post h2 a:hover { color: #3498db; }
            .post .meta { color: #7f8c8d; font-size: 0.9em; margin-bottom: 10px; }
            .post .blurb { margin: 10px 0; }
            .post .read-more { color: #3498db; text-decoration: none; font-weight: bold; }
            .post .read-more:hover { text-decoration: underline; }
            .info { background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .info strong { color: #2980b9; }
        </style>
    </head>
    <body>
        <h1><?php echo htmlspecialchars($clone['name']); ?></h1>
        <?php
        $settings = json_decode($clone['settings'], true);
        if (isset($settings['tagline'])) {
            echo '<p class="tagline">' . htmlspecialchars($settings['tagline']) . '</p>';
        }
        ?>
        
        <div class="info">
            <strong>Multi-Tenant Demo:</strong> This blog is running in clone context 
            <code><?php echo htmlspecialchars(substr($clone['hash'], 0, 8)); ?>...</code>
            All posts are automatically filtered by clone_id.
        </div>
        
        <h2>Recent Posts</h2>
        
        <?php if (empty($posts)): ?>
            <p>No posts yet. Check the README to seed sample data.</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <?php
                // Get author info
                $author = null;
                if ($post['created_by']) {
                    $author = g::run("db.get", "persons", $post['created_by']);
                }
                ?>
                <div class="post">
                    <h2><a href="?post=<?php echo urlencode($post['safe_url']); ?>">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </a></h2>
                    
                    <div class="meta">
                        By <strong><?php echo $author ? htmlspecialchars($author['name']) : 'Unknown'; ?></strong>
                        on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                    </div>
                    
                    <div class="blurb">
                        <?php echo htmlspecialchars($post['blurb']); ?>
                    </div>
                    
                    <a href="?post=<?php echo urlencode($post['safe_url']); ?>" class="read-more">Read more →</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <hr>
        <p><small>Powered by <strong>Genes Framework</strong> | Clone ID: <?php echo htmlspecialchars($clone['hash']); ?></small></p>
    </body>
    </html>
    <?php
}

function showSinglePost($slug, $defaultAuthorHash) {
    global $clone;
    
    // Get post by safe_url
    $posts = g::run("db.select", "items", array(
        "type" => "post",
        "safe_url" => $slug,
        "state" => "published"
    ));
    
    if (empty($posts)) {
        echo "<h1>Post Not Found</h1>";
        echo "<p><a href='index.php'>← Back to blog</a></p>";
        return;
    }
    
    $post = $posts[0];
    
    // Get author
    $author = null;
    if ($post['created_by']) {
        $author = g::run("db.get", "persons", $post['created_by']);
    }
    
    // Log view event
    g::run("db.insert", "events", array(
        "type" => "post.viewed",
        "item_id" => $post['hash'],
        "person_id" => $defaultAuthorHash,
        "data" => json_encode(array(
            "post_title" => $post['title'],
            "ip" => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            "user_agent" => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 100) : 'Unknown'
        ))
    ));
    
    // Get view count for this post
    $viewEvents = g::run("db.select", "events", array(
        "type" => "post.viewed",
        "item_id" => $post['hash']
    ));
    $viewCount = count($viewEvents);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($post['title']); ?> - <?php echo htmlspecialchars($clone['name']); ?></title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
            h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
            .meta { color: #7f8c8d; font-size: 0.9em; margin: 20px 0; }
            .meta strong { color: #2c3e50; }
            .content { margin: 30px 0; font-size: 1.1em; }
            .back-link { display: inline-block; margin: 20px 0; color: #3498db; text-decoration: none; }
            .back-link:hover { text-decoration: underline; }
            .info { background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .info strong { color: #2980b9; }
        </style>
    </head>
    <body>
        <a href="index.php" class="back-link">← Back to blog</a>
        
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        
        <div class="meta">
            By <strong><?php echo $author ? htmlspecialchars($author['name']) : 'Unknown'; ?></strong>
            on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
            | <?php echo $viewCount; ?> views
        </div>
        
        <div class="info">
            <strong>Event Tracking:</strong> This view was logged to the <code>events</code> table
            (type: <code>post.viewed</code>, item_id: <code><?php echo htmlspecialchars(substr($post['hash'], 0, 8)); ?>...</code>)
        </div>
        
        <div class="content">
            <?php echo nl2br(htmlspecialchars($post['text'])); ?>
        </div>
        
        <hr>
        <a href="index.php" class="back-link">← Back to blog</a>
        
        <hr>
        <p><small>Powered by <strong>Genes Framework</strong> | Clone ID: <?php echo htmlspecialchars($clone['hash']); ?></small></p>
    </body>
    </html>
    <?php
}
?>
