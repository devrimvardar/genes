<?php
/**
 * Example 4: Config-Based Data (No Database)
 * 
 * Demonstrates:
 * - Using config.json as data source (no database)
 * - Static content management
 * - File-based routing
 * - Simple templating
 * 
 * PHP 5.6+ Compatible
 */

require_once '../../genes.php';

// ============================================================================
// LOAD CONFIGURATION (NO DATABASE!)
// ============================================================================

// Load config.json as data source
$configPath = __DIR__ . '/data/config.json';

if (!file_exists($configPath)) {
    die("Config file not found. Please create data/config.json");
}

g::run("config.load", $configPath);
$config = g::get("config");

// ============================================================================
// ROUTING
// ============================================================================

// Get requested page
$pageSlug = isset($_GET['page']) ? $_GET['page'] : 'home';

// Find page in config
$currentPage = null;
if (isset($config['pages']) && is_array($config['pages'])) {
    foreach ($config['pages'] as $page) {
        if ($page['slug'] === $pageSlug) {
            $currentPage = $page;
            break;
        }
    }
}

// Default to home if not found
if (!$currentPage && $pageSlug !== 'home') {
    $pageSlug = 'home';
    foreach ($config['pages'] as $page) {
        if ($page['slug'] === 'home') {
            $currentPage = $page;
            break;
        }
    }
}

if (!$currentPage) {
    die("No pages found in config.json");
}

// ============================================================================
// RENDER PAGE
// ============================================================================

$siteName = isset($config['site']['name']) ? $config['site']['name'] : 'My Site';
$tagline = isset($config['site']['tagline']) ? $config['site']['tagline'] : '';
$pages = isset($config['pages']) ? $config['pages'] : array();
$projects = isset($config['projects']) ? $config['projects'] : array();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($currentPage['title']); ?> - <?php echo htmlspecialchars($siteName); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
        }
        header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        header p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        nav {
            background: #f8f9fa;
            padding: 15px 0;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        nav a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        nav a:hover, nav a.active {
            background: #667eea;
            color: white;
        }
        .content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .content h2 {
            color: #667eea;
            margin-bottom: 20px;
        }
        .info-box {
            background: #e8f4f8;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box strong {
            color: #667eea;
        }
        .projects {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .project-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .project-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        }
        .project-card h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .project-card a {
            color: #667eea;
            text-decoration: none;
        }
        footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
            margin-top: 40px;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #e83e8c;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo htmlspecialchars($siteName); ?></h1>
            <?php if ($tagline): ?>
                <p><?php echo htmlspecialchars($tagline); ?></p>
            <?php endif; ?>
        </div>
    </header>

    <div class="container">
        <nav>
            <ul>
                <?php foreach ($pages as $page): ?>
                    <li>
                        <a href="?page=<?php echo urlencode($page['slug']); ?>" 
                           class="<?php echo $page['slug'] === $pageSlug ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($page['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="info-box">
            <strong>💡 No Database Demo:</strong> All content loaded from 
            <code>data/config.json</code> - no SQL queries, no database server needed!
        </div>

        <div class="content">
            <h2><?php echo htmlspecialchars($currentPage['title']); ?></h2>
            <div><?php echo nl2br(htmlspecialchars($currentPage['content'])); ?></div>

            <?php if ($pageSlug === 'home' && !empty($projects)): ?>
                <h3 style="margin-top: 30px; color: #667eea;">Featured Projects</h3>
                <div class="projects">
                    <?php foreach ($projects as $project): ?>
                        <div class="project-card">
                            <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                            <p><?php echo htmlspecialchars($project['description']); ?></p>
                            <?php if (isset($project['url'])): ?>
                                <p style="margin-top: 10px;">
                                    <a href="<?php echo htmlspecialchars($project['url']); ?>" target="_blank">
                                        View Project →
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <footer>
            <p>Powered by <strong>Genes Framework</strong> (No Database Mode) | 
            Data loaded from config.json</p>
        </footer>
    </div>
</body>
</html>
