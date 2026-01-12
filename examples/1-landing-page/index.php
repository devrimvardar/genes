<?php
/**
 * Example 1: Multi-Language Landing Page
 * 
 * Demonstrates:
 * - View-based routing with config.json
 * - Multi-language support (en, tr, de)
 * - Modern data-g-* template directives
 * - Partial template loading (data-g-load)
 * - Conditional rendering (data-g-if)
 * - Loop rendering (data-g-for)
 * - Proper genes.css usage
 * - No database required
 * 
 * PHP 5.6+ Compatible
 */

require_once '../../genes.php';

// ============================================================================
// CLONE FUNCTIONS
// ============================================================================

g::def("clone", array(
    
    /**
     * Index View - Main landing page
     * 
     * Called automatically by route.handle() when URL matches
     * Receives translated bits, language code, and path
     */
    "Index" => function ($bits, $lang, $path) {
        // Prepare data for template
        $data = array(
            "bits" => $bits,
            "lang" => $lang,
            "current_page" => "home",
            
            // Features list for data-g-for demonstration
            "features" => array(
                array(
                    "icon" => "⚡",
                    "title" => ($lang === "en") ? "Zero Dependencies" : (($lang === "tr") ? "Bağımlılık Yok" : "Keine Abhängigkeiten"),
                    "description" => ($lang === "en") ? "No Composer, npm, or build tools. Just copy and code." : (($lang === "tr") ? "Composer, npm veya build araçları yok. Sadece kopyala ve kodla." : "Kein Composer, npm oder Build-Tools. Einfach kopieren und coden.")
                ),
                array(
                    "icon" => "🎯",
                    "title" => ($lang === "en") ? "Simple & Powerful" : (($lang === "tr") ? "Basit & Güçlü" : "Einfach & Mächtig"),
                    "description" => ($lang === "en") ? "Clean API, progressive enhancement, works everywhere." : (($lang === "tr") ? "Temiz API, kademeli geliştirme, her yerde çalışır." : "Saubere API, progressive Enhancement, funktioniert überall.")
                ),
                array(
                    "icon" => "🌐",
                    "title" => ($lang === "en") ? "Multi-Language" : (($lang === "tr") ? "Çok Dilli" : "Mehrsprachig"),
                    "description" => ($lang === "en") ? "Built-in i18n with config-based routing." : (($lang === "tr") ? "Yerleşik i18n ve yapılandırma tabanlı yönlendirme." : "Eingebaute i18n mit config-basiertem Routing.")
                )
            ),
            
            // Stats for display
            "stats" => array(
                array("value" => "6.4K", "label" => ($lang === "en") ? "Lines of PHP" : (($lang === "tr") ? "Satır PHP" : "Zeilen PHP")),
                array("value" => "1.3K", "label" => ($lang === "en") ? "Lines of JS" : (($lang === "tr") ? "Satır JS" : "Zeilen JS")),
                array("value" => "1.7K", "label" => ($lang === "en") ? "Lines of CSS" : (($lang === "tr") ? "Satır CSS" : "Zeilen CSS")),
                array("value" => "0", "label" => ($lang === "en") ? "Dependencies" : (($lang === "tr") ? "Bağımlılık" : "Abhängigkeiten"))
            )
        );
        
        // Render the view template with data
        $html = g::run("tpl.renderView", "Index", $data);
        echo $html;
    },
    
    /**
     * Product View
     */
    "Product" => function ($bits, $lang, $path) {
        $data = array(
            "bits" => $bits,
            "lang" => $lang,
            "current_page" => "product",
            "show_product" => true
        );
        
        $html = g::run("tpl.renderView", "Product", $data);
        echo $html;
    },
    
    /**
     * Pricing View
     */
    "Pricing" => function ($bits, $lang, $path) {
        $data = array(
            "bits" => $bits,
            "lang" => $lang,
            "current_page" => "pricing",
            "show_pricing" => true,
            "plans" => array(
                array(
                    "name" => ($lang === "en") ? "Free" : (($lang === "tr") ? "Ücretsiz" : "Kostenlos"),
                    "price" => "$0",
                    "features" => array(
                        ($lang === "en") ? "Single project" : (($lang === "tr") ? "Tek proje" : "Einzelnes Projekt"),
                        ($lang === "en") ? "Community support" : (($lang === "tr") ? "Topluluk desteği" : "Community-Support")
                    )
                ),
                array(
                    "name" => ($lang === "en") ? "Pro" : "Pro",
                    "price" => "$29",
                    "features" => array(
                        ($lang === "en") ? "Unlimited projects" : (($lang === "tr") ? "Sınırsız proje" : "Unbegrenzte Projekte"),
                        ($lang === "en") ? "Priority support" : (($lang === "tr") ? "Öncelikli destek" : "Prioritäts-Support"),
                        ($lang === "en") ? "Advanced features" : (($lang === "tr") ? "Gelişmiş özellikler" : "Erweiterte Funktionen")
                    )
                )
            )
        );
        
        $html = g::run("tpl.renderView", "Pricing", $data);
        echo $html;
    },
    
    /**
     * About View
     */
    "About" => function ($bits, $lang, $path) {
        $data = array(
            "bits" => $bits,
            "lang" => $lang,
            "current_page" => "about",
            "show_about" => true
        );
        
        $html = g::run("tpl.renderView", "About", $data);
        echo $html;
    },
    
    /**
     * Contact View
     */
    "Contact" => function ($bits, $lang, $path) {
        $data = array(
            "bits" => $bits,
            "lang" => $lang,
            "current_page" => "contact",
            "show_contact" => true
        );
        
        $html = g::run("tpl.renderView", "Contact", $data);
        echo $html;
    }
));

// ============================================================================
// HANDLE ROUTING
// ============================================================================

// This will:
// 1. Parse the current URL
// 2. Match it to a view in config.json
// 3. Detect language from URL
// 4. Call the appropriate clone function
// 5. Pass translated bits to the function
g::run("route.handle");
