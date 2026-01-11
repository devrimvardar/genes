<?php
/**
 * Example 5: HTML Partials & Templating
 * 
 * Demonstrates:
 * - Static HTML partial files
 * - Template composition (header, footer, content)
 * - Variable substitution
 * - Component reusability
 * - No database required
 * 
 * PHP 5.6+ Compatible
 */

require_once '../../genes.php';

// ============================================================================
// TEMPLATE FUNCTIONS
// ============================================================================

/**
 * Load and render an HTML partial with variable substitution
 */
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

/**
 * Render a complete page with header, content, footer
 */
function renderPage($pageFile, $vars = array()) {
    $header = loadPartial('header.html', $vars);
    $nav = loadPartial('nav.html', $vars);
    $content = loadPartial('pages/' . $pageFile, $vars);
    $footer = loadPartial('footer.html', $vars);
    
    return $header . $nav . $content . $footer;
}

// ============================================================================
// CONFIGURATION
// ============================================================================

$siteVars = array(
    'site_name' => 'HTML Partials Demo',
    'tagline' => 'Static templates with Genes Framework',
    'year' => date('Y'),
    'current_page' => isset($_GET['page']) ? $_GET['page'] : 'home'
);

// ============================================================================
// ROUTING
// ============================================================================

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Sanitize page name (security)
$page = preg_replace('/[^a-z0-9\-]/', '', strtolower($page));

// Check if page exists
$pageFile = 'pages/' . $page . '.html';
$pagePath = __DIR__ . '/templates/' . $pageFile;

if (!file_exists($pagePath)) {
    $page = 'home';
    $pageFile = 'pages/home.html';
}

// Update current page in vars
$siteVars['current_page'] = $page;

// Set active class for navigation
$siteVars['home_active'] = $page === 'home' ? 'active' : '';
$siteVars['about_active'] = $page === 'about' ? 'active' : '';
$siteVars['services_active'] = $page === 'services' ? 'active' : '';
$siteVars['contact_active'] = $page === 'contact' ? 'active' : '';

// ============================================================================
// RENDER
// ============================================================================

echo renderPage($pageFile, $siteVars);
?>
