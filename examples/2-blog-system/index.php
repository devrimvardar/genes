<?php
/**
 * Example 2: Multi-Language Blog System
 * 
 * Demonstrates:
 * - Using Genes standard 5-table schema (clones, persons, items, labels, events)
 * - SQLite database integration
 * - Multi-language content via labels
 * - List and single post views
 * - Database queries with db.select, db.insert
 * - Pagination
 * - Categories/tags using labels table
 * - data-g-for loops with database results
 * - Auto-setup on first run
 * 
 * PHP 5.6+ Compatible
 */

require_once '../../genes.php';

// ============================================================================
// DATABASE SETUP
// ============================================================================

function setupBlog() {
    // Database auto-connects from config.json (database.enabled = true)
    // Schema auto-creates during initialization if needed
    $dbPath = DATA_FOLDER . 'blog.db';
    
    // Get or create clone
    $clones = g::run("db.select", "clones", array("domain" => "blog.local"));
    
    if (empty($clones)) {
        // Create blog clone
        $cloneHash = g::run("db.insert", "clones", array(
            "type" => "blog",
            "name" => "Multi-Language Blog",
            "domain" => "blog.local",
            "settings" => json_encode(array("theme" => "default"))
        ));
    } else {
        $cloneHash = $clones[0]['hash'];
    }
    
    // Set current clone for multi-tenant isolation
    g::run("db.setClone", $cloneHash);
    
    // Always seed data on fresh database
    $posts = g::run("db.select", "items", array("type" => "post"));
    if (empty($posts) || !is_array($posts)) {
        seedBlogData();
    }
}

function seedBlogData() {
    // Create categories as labels
    $categories = array(
        array("key" => "tutorial", "name" => "Tutorial"),
        array("key" => "guide", "name" => "Guide"),
        array("key" => "news", "name" => "News")
    );
    
    foreach ($categories as $cat) {
        g::run("db.insert", "labels", array(
            "type" => "category",
            "key" => $cat['key'],
            "name" => $cat['name']
        ));
    }
    
    // Create language tags
    $langs = array(
        array("key" => "en", "name" => "English"),
        array("key" => "tr", "name" => "Türkçe"),
        array("key" => "de", "name" => "Deutsch")
    );
    
    foreach ($langs as $lang) {
        g::run("db.insert", "labels", array(
            "type" => "language",
            "key" => $lang['key'],
            "name" => $lang['name']
        ));
    }
    
    // Create blog posts using items table
    $posts = array(
        // English posts
        array(
            "type" => "post",
            "state" => "published",
            "title" => "Getting Started with Genes Framework",
            "safe_url" => "getting-started-with-genes",
            "blurb" => "Learn how to build modern web applications with Genes Framework in minutes.",
            "text" => "<p>Genes Framework is a lightweight, powerful PHP framework that requires zero dependencies. In this post, we'll explore how to get started building modern web applications.</p><p>The framework consists of just three files: genes.php, genes.js, and genes.css. This simplicity makes it incredibly easy to understand and customize.</p><h3>Key Features</h3><ul><li>Zero dependencies - no Composer, no npm</li><li>Modern template engine with progressive enhancement</li><li>Built-in multi-language support</li><li>Responsive CSS framework</li></ul>",
            "labels" => json_encode(array("en", "tutorial")),
            "meta" => json_encode(array("author" => "John Doe", "reading_time" => "5 min"))
        ),
        array(
            "type" => "post",
            "state" => "published",
            "title" => "Building Multi-Language Websites",
            "safe_url" => "building-multi-language-sites",
            "blurb" => "A complete guide to creating multilingual websites with config-based routing.",
            "text" => "<p>Multi-language support is built into Genes Framework from the ground up. You can easily create websites that support multiple languages without complex setup.</p><h3>How It Works</h3><p>Define your routes and translations in config.json, and the framework automatically handles language detection and routing.</p>",
            "labels" => json_encode(array("en", "guide")),
            "meta" => json_encode(array("author" => "Jane Smith", "reading_time" => "7 min"))
        ),
        array(
            "type" => "post",
            "state" => "published",
            "title" => "Modern Templating with data-g-* Attributes",
            "safe_url" => "modern-templating-system",
            "blurb" => "Discover the power of progressive enhancement with Genes template engine.",
            "text" => "<p>Genes uses a modern templating system based on data-g-* attributes. This approach provides progressive enhancement - your HTML is valid and shows defaults, but gets enhanced with actual data when rendered.</p><h3>Available Directives</h3><ul><li>data-g-if - Conditional rendering</li><li>data-g-for - Loop rendering</li><li>data-g-load - Partial templates</li><li>data-g-bind - Text binding</li><li>data-g-html - HTML binding</li></ul>",
            "labels" => json_encode(array("en", "tutorial")),
            "meta" => json_encode(array("author" => "John Doe", "reading_time" => "10 min"))
        ),
        
        // Turkish posts
        array(
            "type" => "post",
            "state" => "published",
            "title" => "Genes Framework ile Başlangıç",
            "safe_url" => "genes-ile-baslangic",
            "blurb" => "Genes Framework ile dakikalar içinde modern web uygulamaları oluşturmayı öğrenin.",
            "text" => "<p>Genes Framework, sıfır bağımlılık gerektiren hafif ve güçlü bir PHP framework'tür. Bu yazıda, modern web uygulamaları oluşturmaya nasıl başlanacağını keşfedeceğiz.</p><p>Framework sadece üç dosyadan oluşur: genes.php, genes.js ve genes.css. Bu basitlik, anlamayı ve özelleştirmeyi inanılmaz derecede kolaylaştırır.</p>",
            "labels" => json_encode(array("tr", "tutorial")),
            "meta" => json_encode(array("author" => "Ahmet Yılmaz", "reading_time" => "5 dk"))
        ),
        array(
            "type" => "post",
            "state" => "published",
            "title" => "Çok Dilli Web Siteleri Oluşturma",
            "safe_url" => "cok-dilli-siteler",
            "blurb" => "Yapılandırma tabanlı yönlendirme ile çok dilli web siteleri oluşturma rehberi.",
            "text" => "<p>Çok dilli destek, Genes Framework'e en başından itibaren yerleşiktir. Karmaşık kurulum olmadan birden fazla dili destekleyen web siteleri kolayca oluşturabilirsiniz.</p>",
            "labels" => json_encode(array("tr", "guide")),
            "meta" => json_encode(array("author" => "Ayşe Demir", "reading_time" => "6 dk"))
        ),
        
        // German posts
        array(
            "type" => "post",
            "state" => "published",
            "title" => "Einstieg in Genes Framework",
            "safe_url" => "einstieg-in-genes",
            "blurb" => "Lernen Sie, wie Sie in wenigen Minuten moderne Webanwendungen mit Genes Framework erstellen.",
            "text" => "<p>Genes Framework ist ein leichtgewichtiges, leistungsstarkes PHP-Framework, das keine Abhängigkeiten erfordert. In diesem Beitrag werden wir untersuchen, wie man mit der Erstellung moderner Webanwendungen beginnt.</p>",
            "labels" => json_encode(array("de", "tutorial")),
            "meta" => json_encode(array("author" => "Hans Mueller", "reading_time" => "5 Min"))
        )
    );
    
    foreach ($posts as $post) {
        g::run("db.insert", "items", $post);
    }
}

setupBlog();

// ============================================================================
// CLONE FUNCTIONS
// ============================================================================

g::def("clone", array(
    "Index" => function ($bits, $lang, $path) {
        // Handle POST requests for editing (admin mode)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = isset($_POST['action']) ? $_POST['action'] : '';
            
            if ($action === 'update' && isset($_POST['hash'])) {
                g::run("db.update", "items", $_POST['hash'], array(
                    "title" => $_POST['title'],
                    "safe_url" => $_POST['safe_url'],
                    "blurb" => $_POST['blurb'],
                    "text" => $_POST['text']
                ));
                echo "success";
                exit;
            } elseif ($action === 'create') {
                g::run("db.insert", "items", array(
                    "type" => "post",
                    "state" => "published",
                    "title" => $_POST['title'],
                    "safe_url" => $_POST['safe_url'],
                    "blurb" => $_POST['blurb'],
                    "text" => $_POST['text'],
                    "labels" => json_encode(array($lang, 'tutorial')),
                    "meta" => json_encode(array("author" => "Admin", "reading_time" => "5 min"))
                ));
                echo "success";
                exit;
            }
        }
        
        // Check if user is admin (via /admin segment)
        $request = g::get("request");
        $segments = isset($request['route_segments']) ? $request['route_segments'] : array();
        $isAdmin = in_array('admin', $segments);
        
        // Get all published posts from items table
        $allPosts = g::run("db.select", "items", array(
            "type" => "post",
            "state" => "published"
        ));
        
        // Ensure allPosts is an array
        if (!is_array($allPosts)) {
            $allPosts = array();
        }
        
        // Filter by language (from labels JSON field)
        $posts = array();
        foreach ($allPosts as $post) {
            // Genes auto-decodes JSON fields
            $labels = is_array($post['labels']) ? $post['labels'] : json_decode($post['labels'], true);
            if ($labels && in_array($lang, $labels)) {
                // Parse meta for display (auto-decoded by Genes)
                $meta = is_array($post['meta']) ? $post['meta'] : json_decode($post['meta'], true);
                $post['author'] = isset($meta['author']) ? $meta['author'] : 'Anonymous';
                $post['reading_time'] = isset($meta['reading_time']) ? $meta['reading_time'] : '';
                $post['category'] = '';
                foreach ($labels as $label) {
                    if (!in_array($label, array('en', 'tr', 'de'))) {
                        $post['category'] = ucfirst($label);
                        break;
                    }
                }
                $posts[] = $post;
            }
        }
        
        // Sort by created_at DESC
        usort($posts, function($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });
        
        // Pagination
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 5;
        $total = count($posts);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $posts = array_slice($posts, $offset, $perPage);
        
        $data = array(
            "bits" => $bits,
            "lang" => $lang,
            "show_list" => true,
            "is_admin" => $isAdmin,
            "has_posts" => count($posts) > 0,
            "posts" => $posts,
            "has_pagination" => $totalPages > 1,
            "pagination" => array(
                "current_page" => $page,
                "total_pages" => $totalPages,
                "has_prev" => $page > 1,
                "has_next" => $page < $totalPages,
                "prev_page" => $page - 1,
                "next_page" => $page + 1
            )
        );
        
        echo g::run("tpl.renderView", "Index", $data);
    },
    
    "Post" => function ($bits, $lang, $path) {
        $request = g::get("request");
        $segments = isset($request['route_segments']) ? $request['route_segments'] : array();
        
        if (empty($segments[0])) {
            header("Location: /");
            exit;
        }
        
        $slug = $segments[0];
        
        // Find post by safe_url
        $posts = g::run("db.select", "items", array(
            "type" => "post",
            "state" => "published",
            "safe_url" => $slug
        ));
        
        if (empty($posts)) {
            http_response_code(404);
            echo "<h1>Post Not Found</h1>";
            return;
        }
        
        $post = $posts[0];
        
        // Parse meta and labels (auto-decoded by Genes)
        $labels = is_array($post['labels']) ? $post['labels'] : json_decode($post['labels'], true);
        $meta = is_array($post['meta']) ? $post['meta'] : json_decode($post['meta'], true);
        $post['author'] = isset($meta['author']) ? $meta['author'] : 'Anonymous';
        $post['reading_time'] = isset($meta['reading_time']) ? $meta['reading_time'] : '';
        $post['category'] = '';
        foreach ($labels as $label) {
            if (!in_array($label, array('en', 'tr', 'de'))) {
                $post['category'] = ucfirst($label);
                $categoryKey = $label;
                break;
            }
        }
        
        // Get related posts (same category, different slug)
        $allPosts = g::run("db.select", "items", array(
            "type" => "post",
            "state" => "published"
        ));
        
        $related = array();
        $count = 0;
        foreach ($allPosts as $item) {
            if ($count >= 3) break;
            if ($item['safe_url'] === $slug) continue;
            
            $itemLabels = is_array($item['labels']) ? $item['labels'] : json_decode($item['labels'], true);
            if ($itemLabels && in_array($categoryKey, $itemLabels) && in_array($lang, $itemLabels)) {
                $itemMeta = is_array($item['meta']) ? $item['meta'] : json_decode($item['meta'], true);
                $item['author'] = isset($itemMeta['author']) ? $itemMeta['author'] : 'Anonymous';
                $related[] = $item;
                $count++;
            }
        }
        
        $data = array(
            "bits" => $bits,
            "lang" => $lang,
            "show_single" => true,
            "post" => $post,
            "related_posts" => $related,
            "has_related" => count($related) > 0
        );
        
        echo g::run("tpl.renderView", "Post", $data);
    }
));

g::run("route.handle");
