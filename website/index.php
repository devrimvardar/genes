<?php
/**
 * Genes Framework Official Website
 * genes.one
 * 
 * A complete example of building a professional website with Genes Framework
 * Uses HTML partials and config.json (no database required)
 * 
 * PHP 5.6+ Compatible
 */

// Load Genes Framework
require_once '../genes.php';

// ============================================================================
// TEMPLATE FUNCTIONS
// ============================================================================

function loadPartial($filename, $vars = array()) {
    $path = __DIR__ . '/templates/' . $filename;
    
    if (!file_exists($path)) {
        return "<!-- Partial not found: $filename -->";
    }
    
    $content = file_get_contents($path);
    
    // Replace variables
    foreach ($vars as $key => $value) {
        $content = str_replace('{{' . $key . '}}', $value, $content);
    }
    
    return $content;
}

function renderPage($pageFile, $vars = array()) {
    // Load layout components
    $header = loadPartial('layout/header.html', $vars);
    $nav = loadPartial('layout/nav.html', $vars);
    $content = loadPartial('pages/' . $pageFile, $vars);
    $footer = loadPartial('layout/footer.html', $vars);
    
    return $header . $nav . $content . $footer;
}

// ============================================================================
// LOAD CONFIGURATION
// ============================================================================

$configPath = __DIR__ . '/data/config.json';

if (file_exists($configPath)) {
    g::run("config.load", $configPath);
}

$config = g::get("config");

// Site variables
$siteVars = array(
    'site_name' => isset($config['site']['name']) ? $config['site']['name'] : 'Genes Framework',
    'tagline' => isset($config['site']['tagline']) ? $config['site']['tagline'] : 'Lightweight PHP Framework',
    'version' => isset($config['site']['version']) ? $config['site']['version'] : '2.0',
    'year' => date('Y'),
    'github_url' => isset($config['site']['github_url']) ? $config['site']['github_url'] : 'https://github.com/devrimvardar/genes',
    'current_page' => isset($_GET['page']) ? $_GET['page'] : 'home'
);

// ============================================================================
// ROUTING
// ============================================================================

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Sanitize page name
$page = preg_replace('/[^a-z0-9\-]/', '', strtolower($page));

// Valid pages
$validPages = array('home', 'docs', 'examples', 'download', 'about');

if (!in_array($page, $validPages)) {
    $page = 'home';
}

$pageFile = $page . '.html';

// Update current page
$siteVars['current_page'] = $page;

// Set active navigation classes
foreach ($validPages as $p) {
    $siteVars[$p . '_active'] = ($p === $page) ? 'active' : '';
}

// ============================================================================
// RENDER
// ============================================================================

echo renderPage($pageFile, $siteVars);
?>
