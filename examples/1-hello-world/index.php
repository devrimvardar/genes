<?php
/*!
 * ============================================================================
 * Example 1: Hello World
 * ============================================================================
 * Minimal Genes Framework setup
 */

require_once '../../genes.php';

echo "<h1>Hello, Genes Framework!</h1>";
echo "<p>This is the simplest possible Genes application.</p>";

// Show performance
$perf = g::run("log.performance", true);
echo "<hr><pre>$perf</pre>";
