<?php
/*!
 * ============================================================================
 * Genes Framework v2.0
 * ============================================================================
 * A lightweight, single-file PHP framework for rapid web development
 * 
 * @version     2.0.0
 * @date        2025-10-06
 * @author      Devrim Vardar
 * @copyright   (c) 2024-2025 NodOnce OÜ
 * @license     MIT
 * @link        https://genes.one
 * 
 * QUICK START:
 * 1. Include this file: require 'genes.php';
 * 2. Framework auto-initializes
 * 3. Define your app and routes
 * 4. Profit!
 *
 * Minimum PHP Version: 5.6
 * Recommended: PHP 7.4 or higher
 */

// ============================================================================
// THE G CLASS - Core Framework Container
// ============================================================================

class g
{
    private static $app = array();
    private static $fns = array();

    // Data storage methods
    public static function set($key, $value)
    {
        $ref = &self::find($key);
        $ref = $value;
    }

    // Set timezone early to avoid warnings
    private static $initialized = false;

    public static function get($key)
    {
        return self::find($key);
    }

    public static function del($key)
    {
        self::find($key, true);
    }

    // Function management methods
    public static function def($key, $callback)
    {
        $ref = &self::find($key, false, true);
        $ref = $callback;
    }

    public static function ret($key)
    {
        return self::find($key, false, true);
    }

    public static function run()
    {
        $args = func_get_args();
        $key = array_shift($args);
        $ref = &self::find($key, false, true);
        if (is_callable($ref)) {
            return call_user_func_array($ref, $args);
        }
        return null;
    }

    public static function has($key)
    {
        $ref = &self::find($key, false, true);
        return is_callable($ref);
    }

    public static function kill($key)
    {
        self::find($key, true, true);
    }

    // Debug output
    public static function log($key)
    {
        if ($key === 0) {
            echo "<pre>APP DATA:\n";
            print_r(self::$app);
            echo "</pre>";
        } else if ($key === 1) {
            echo "<pre>FUNCTIONS:\n";
            print_r(array_keys(self::$fns));
            echo "</pre>";
        } else {
            $val = self::find($key);
            echo "<pre>";
            if (is_array($val)) {
                print_r($val);
            } else {
                echo $val;
            }
            echo "</pre>";
        }
    }

    // Internal helper
    private static function &find($key, $remove = false, $fns_mode = false)
    {
        if ($fns_mode) {
            $ref = &self::$fns;
        } else {
            $ref = &self::$app;
        }

        if (strpos($key, ".") > -1) {
            $ps = explode('.', $key);
            $c = count($ps);
            foreach ($ps as $part) {
                $c--;
                if ($remove && $c === 0) {
                    unset($ref[$part]);
                    $null = null;
                    return $null;
                } else {
                    if (!isset($ref[$part])) {
                        $ref[$part] = array();
                    }
                    $ref = &$ref[$part];
                }
            }
            return $ref;
        } else {
            if ($remove) {
                unset($ref[$key]);
                $null = null;
                return $null;
            } else {
                if (!isset($ref[$key])) {
                    $ref[$key] = null;
                }
                return $ref[$key];
            }
        }
    }
}

// ============================================================================
// CONSTANTS
// ============================================================================

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('GENES_DIR') or define('GENES_DIR', __DIR__);
defined('PROJECT_ROOT') or define('PROJECT_ROOT', dirname(GENES_DIR) . DS);
defined('WORK_DIR') or define('WORK_DIR', getcwd() . DS);

// Folders
defined('DATA_FOLDER') or define('DATA_FOLDER', WORK_DIR . 'data' . DS);
defined('CACHE_FOLDER') or define('CACHE_FOLDER', WORK_DIR . 'cache' . DS);
defined('UI_FOLDER') or define('UI_FOLDER', WORK_DIR . 'ui' . DS);
defined('MODS_FOLDER') or define('MODS_FOLDER', WORK_DIR . 'mods' . DS);
defined('UPLOADS_FOLDER') or define('UPLOADS_FOLDER', WORK_DIR . 'uploads' . DS);

// Files
defined('CONFIG_FILE') or define('CONFIG_FILE', DATA_FOLDER . 'config.json');
defined('LOG_FILE') or define('LOG_FILE', DATA_FOLDER . 'system.log');
defined('UI_TEMPLATE') or define('UI_TEMPLATE', UI_FOLDER . 'index.html');

// URLs
defined('GENES_CDN_URL') or define('GENES_CDN_URL', 'https://cdn.genes.one/');

// Set default timezone early to prevent warnings
@date_default_timezone_set('UTC');

// ============================================================================
// CORE FUNCTIONS
// ============================================================================

g::def("core", array(

    "init" => function () {
        g::set("system.time.start", microtime(true));
        g::set("system.memory.start", memory_get_usage());

        g::run("core.prepareConfig");
        g::run("core.setEnvironment");
        g::run("core.createFolders");
        g::run("core.setApplicationInfo");
        g::run("core.autoConnectDatabase");
        g::run("core.autoInitializeData");

        g::set("system.initialized", true);

        $time = g::run("core.timestamp");
        g::run("core.log", "[INIT] Framework initialized at $time");
    },

    "prepareConfig" => function () {
        // Detect current environment URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

        // Detect base path from SCRIPT_NAME
        $basePath = '';
        if (isset($_SERVER['SCRIPT_NAME'])) {
            $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
            if ($scriptPath !== '/' && $scriptPath !== '.') {
                $basePath = $scriptPath;
            }
        }

        // Build full base URL
        $baseUrl = $protocol . '://' . $host . $basePath;
        if (substr($baseUrl, -1) !== '/') {
            $baseUrl .= '/';
        }

        // Create URL-safe environment key (remove protocol and special chars)
        $envKey = str_replace(array('://', '/', '\\', ':'), '_', $baseUrl);
        $envKey = trim($envKey, '_');

        // Just use domain for clone.domain (without path)
        $cloneDomain = $host;

        $defaults = array(
            "clone" => array(
                "name" => "My Clone",
                "domain" => $cloneDomain,
                "type" => "platform",
                "state" => "active",
                "settings" => array(
                    "theme" => "default",
                    "features" => array()
                )
            ),
            "urls" => array(
                "cdn" => GENES_CDN_URL
            ),
            "settings" => array(
                "debug" => false,
                "timezone" => "UTC",
                "language" => "en",
                "date_format" => "Y-m-d H:i:s.u",
                "log_level" => 1,
                "session_name" => "genes_session",
                "environment" => "development",
            ),
            "application" => array(
                "name" => "Genes App",
                "version" => "1.0.0",
                "description" => "Built with Genes Framework",
            ),
            "meta" => array(
                "title" => "Genes App",
                "description" => "Built with Genes Framework",
                "keywords" => "",
                "og_image" => "ui/img/share.jpg"
            ),
            "database" => array(
                "enabled" => false,
                "type" => "mysql",
                "host" => "127.0.0.1",
                "port" => 3306,
                "name" => "",
                "user" => "",
                "password" => "",
                "charset" => "utf8mb4",
            ),
            "security" => array(
                "salt" => "",
                "secret" => "",
            ),
            "admin" => array(
                "name" => "Admin User",
                "alias" => "admin",
                "email" => "admin@localhost",
                "password" => "admin123",
                "type" => "admin",
                "state" => "active"
            ),
            "seed" => array(
                "labels" => array(
                    // Add your custom labels here
                    // Example: array("name" => "label.custom", "label" => "Custom", "value" => "custom")
                )
            ),
            "setup" => array(
                "completed" => false
            ),
            "views" => array(
                "Index" => array(
                    "function" => "clone.Index",
                    "template" => "index",
                    "urls" => array(
                        "en" => "index"
                    )
                ),
                "Admin" => array(
                    "function" => "clone.Admin",
                    "template" => "admin",
                    "urls" => array(
                        "en" => "admin"
                    )
                )
            ),
            "bits" => array(
                "site_name" => array(
                    "en" => "My Site"
                )
            ),
            "env" => array(
                $envKey => array(
                    "base_url" => $baseUrl,
                    "paths" => array(
                        "data" => DATA_FOLDER,
                        "cache" => CACHE_FOLDER,
                        "ui" => UI_FOLDER,
                        "mods" => MODS_FOLDER,
                        "uploads" => UPLOADS_FOLDER,
                    )
                )
            )
        );

        if (file_exists(CONFIG_FILE)) {
            $json = file_get_contents(CONFIG_FILE);
            $userConfig = json_decode($json, true);

            if ($userConfig && is_array($userConfig)) {
                $config = g::run("core.arrayMergeRecursive", $defaults, $userConfig);
            } else {
                $config = $defaults;
                g::run("core.log", "[WARNING] Could not parse config.json");
            }
        } else {
            $config = $defaults;

            if (empty($config["security"]["salt"])) {
                $config["security"]["salt"] = g::run("core.generateRandomKey", 64);
            }
            if (empty($config["security"]["secret"])) {
                $config["security"]["secret"] = g::run("core.generateRandomKey", 64);
            }

            g::run("core.saveConfig", $config);
        }

        // Detect and merge environment-specific config
        if (isset($config["env"]) && is_array($config["env"])) {
            // Detect current environment key (same logic as default generation)
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

            $basePath = '';
            if (isset($_SERVER['SCRIPT_NAME'])) {
                $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                if ($scriptPath !== '/' && $scriptPath !== '.') {
                    $basePath = $scriptPath;
                }
            }

            $baseUrl = $protocol . '://' . $host . $basePath;
            if (substr($baseUrl, -1) !== '/') {
                $baseUrl .= '/';
            }

            $currentEnvKey = str_replace(array('://', '/', '\\', ':'), '_', $baseUrl);
            $currentEnvKey = trim($currentEnvKey, '_');

            // Try to find matching environment
            $envConfig = null;
            if (isset($config["env"][$currentEnvKey])) {
                $envConfig = $config["env"][$currentEnvKey];
                g::run("core.log", "[ENV] Matched environment: $currentEnvKey");
            }

            if ($envConfig) {
                // Store base_url in config
                if (isset($envConfig["base_url"])) {
                    $config["base_url"] = $envConfig["base_url"];
                }

                // Merge environment paths into main config
                if (isset($envConfig["paths"])) {
                    if (!isset($config["paths"])) {
                        $config["paths"] = array();
                    }
                    $config["paths"] = array_merge($config["paths"], $envConfig["paths"]);
                }

                // Merge environment URLs into main config
                if (isset($envConfig["urls"])) {
                    $config["urls"] = array_merge($config["urls"], $envConfig["urls"]);
                }

                g::run("core.log", "[ENV] Loaded environment config: $currentEnvKey");
            } else {
                g::run("core.log", "[ENV] No specific environment config found, using defaults");
            }
        }

        g::set("config", $config);
    },

    "setEnvironment" => function () {
        $config = g::get("config");

        $timezone = isset($config["settings"]["timezone"]) ? $config["settings"]["timezone"] : "UTC";
        date_default_timezone_set($timezone);

        $env = isset($config["settings"]["environment"]) ? $config["settings"]["environment"] : "production";
        if ($env === "development") {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
            ini_set('display_errors', '0');
        }

        ini_set('default_charset', 'UTF-8');

        g::run("core.log", "[ENV] Environment set: $env, timezone: $timezone");
    },

    "createFolders" => function () {
        $folders = array(
            DATA_FOLDER,
            CACHE_FOLDER,
            UI_FOLDER,
            MODS_FOLDER,
            UPLOADS_FOLDER,
        );

        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                if (mkdir($folder, 0755, true)) {
                    g::run("core.log", "[FOLDER] Created: $folder");
                } else {
                    g::run("core.log", "[ERROR] Could not create folder: $folder");
                }
            }
        }

        $htaccess = DATA_FOLDER . '.htaccess';
        if (!file_exists($htaccess)) {
            $content = "# Deny all access\nDeny from all";
            file_put_contents($htaccess, $content);
        }

        $protectedFolders = array(DATA_FOLDER, MODS_FOLDER);
        foreach ($protectedFolders as $folder) {
            $indexFile = $folder . 'index.html';
            if (!file_exists($indexFile)) {
                file_put_contents($indexFile, '<!-- Protected -->');
            }
        }

        // Create root .htaccess for routing
        g::run("core.createRootHtaccess");
    },

    "createRootHtaccess" => function () {
        // Determine if index.php is in current directory or we're in a subfolder
        $scriptDir = dirname($_SERVER['SCRIPT_FILENAME']);
        $htaccessFile = $scriptDir . DIRECTORY_SEPARATOR . '.htaccess';

        // Only create if it doesn't exist
        if (!file_exists($htaccessFile)) {
            $content = "# Genes Framework - URL Rewriting\n# Portable configuration - works in any directory without modification\n\n<IfModule mod_rewrite.c>\n    RewriteEngine On\n    \n    # Redirect all requests to index.php except existing files/directories\n    RewriteCond %{REQUEST_FILENAME} !-f\n    RewriteCond %{REQUEST_FILENAME} !-d\n    RewriteRule ^(.*)$ index.php [QSA,L]\n</IfModule>\n\n# Security Headers\n<IfModule mod_headers.c>\n    Header set X-Content-Type-Options \"nosniff\"\n    Header set X-Frame-Options \"SAMEORIGIN\"\n    Header set X-XSS-Protection \"1; mode=block\"\n</IfModule>\n\n# Disable directory browsing\nOptions -Indexes\n\n# Protect sensitive files\n<FilesMatch \"^(config\\.json|\\.env|composer\\.json|composer\\.lock)$\">\n    Order allow,deny\n    Deny from all\n</FilesMatch>";

            $result = file_put_contents($htaccessFile, $content);
            if ($result !== false) {
                g::run("core.log", "[HTACCESS] Created portable .htaccess at: $htaccessFile");
            } else {
                g::run("core.log", "[ERROR] Could not create .htaccess file");
            }
        }
    },

    "setApplicationInfo" => function () {
        $config = g::get("config");

        $appName = isset($config["application"]["name"]) ? $config["application"]["name"] : "Genes App";
        $appVersion = isset($config["application"]["version"]) ? $config["application"]["version"] : "1.0.0";
        $appDesc = isset($config["application"]["description"]) ? $config["application"]["description"] : "";
        $appEnv = isset($config["settings"]["environment"]) ? $config["settings"]["environment"] : "production";

        g::set("app.name", $appName);
        g::set("app.version", $appVersion);
        g::set("app.description", $appDesc);
        g::set("app.environment", $appEnv);
    },

    "timestamp" => function ($timestamp = null) {
        if ($timestamp === null) {
            $timestamp = microtime(true);
        }

        $seconds = floor($timestamp);
        $milliseconds = round(($timestamp - $seconds) * 1000);

        if ($milliseconds < 10) {
            $milliseconds = "00" . $milliseconds;
        } elseif ($milliseconds < 100) {
            $milliseconds = "0" . $milliseconds;
        }

        $format = g::get("config.settings.date_format");
        if (!$format) {
            $format = "Y-m-d H:i:s.u";
        }
        $formatted = date($format, $seconds);
        $formatted = str_replace('.u', '.' . $milliseconds, $formatted);

        return $formatted;
    },

    "arrayMergeRecursive" => function ($array1, $array2) {
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
                $array1[$key] = g::run("core.arrayMergeRecursive", $array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }
        return $array1;
    },

    "generateRandomKey" => function ($length = 32, $type = "alnum") {
        $charsets = array(
            "hex" => "0123456789abcdef",
            "alpha" => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",
            "alnum" => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
            "full" => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{}|;:,.<>?",
        );

        $charset = isset($charsets[$type]) ? $charsets[$type] : $charsets["alnum"];
        $charsetLength = strlen($charset);
        $key = "";

        for ($i = 0; $i < $length; $i++) {
            $key .= $charset[mt_rand(0, $charsetLength - 1)];
        }

        return $key;
    },

    "getBaseUrl" => function () {
        // Detect protocol - check multiple ways for HTTPS
        $isHttps = false;
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $isHttps = true;
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $isHttps = true;
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
            $isHttps = true;
        } else if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            $isHttps = true;
        }

        $protocol = $isHttps ? 'https://' : 'http://';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

        // Get the detected base path from parseUrl (same smart detection)
        $request = g::get("request");
        $basePath = '';

        if ($request && isset($request['path'])) {
            // Extract base path by comparing REQUEST_URI with the cleaned path
            $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
            $pathOnly = parse_url($requestUri, PHP_URL_PATH);
            $cleanPath = $request['path'];

            // The base path is what was stripped: REQUEST_URI - clean path
            if ($pathOnly !== $cleanPath && strpos($pathOnly, $cleanPath) !== false) {
                $basePath = substr($pathOnly, 0, strlen($pathOnly) - strlen($cleanPath));
                $basePath = rtrim($basePath, '/');
            }
        }

        // Ensure trailing slash
        if ($basePath && substr($basePath, -1) !== '/') {
            $basePath .= '/';
        } elseif (!$basePath) {
            $basePath = '/';
        }

        return $protocol . $host . $basePath;
    },

    "saveConfig" => function ($config) {
        if (!is_dir(DATA_FOLDER)) {
            mkdir(DATA_FOLDER, 0755, true);
        }

        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $result = file_put_contents(CONFIG_FILE, $json);

        if ($result !== false) {
            g::run("core.log", "[CONFIG] Configuration saved to " . CONFIG_FILE);
            return true;
        } else {
            g::run("core.log", "[ERROR] Could not save configuration to " . CONFIG_FILE);
            return false;
        }
    },

    "log" => function ($message, $level = 3) {
        $configLevel = g::get("config.settings.log_level");
        if (!$configLevel) {
            $configLevel = 1;
        }

        if ($level > $configLevel) {
            return;
        }

        if (!is_dir(DATA_FOLDER)) {
            mkdir(DATA_FOLDER, 0755, true);
        }

        $timestamp = g::run("core.timestamp");
        $logEntry = "[$timestamp] $message\n";

        file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
    },

    /**
     * Auto-connect to database if enabled in config
     */
    "autoConnectDatabase" => function () {
        $config = g::get("config");

        if (!isset($config["database"]) || !isset($config["database"]["enabled"])) {
            return false;
        }

        if ($config["database"]["enabled"] !== true) {
            return false;
        }

        $dbConfig = $config["database"];
        $params = array(
            "driver" => isset($dbConfig["type"]) ? $dbConfig["type"] : "mysql",
            "name" => "default"
        );

        if ($params["driver"] === "sqlite") {
            $params["database"] = isset($dbConfig["database"]) ? $dbConfig["database"] : "data/database.db";
        } else {
            $params["host"] = isset($dbConfig["host"]) ? $dbConfig["host"] : "localhost";
            $params["port"] = isset($dbConfig["port"]) ? $dbConfig["port"] : 3306;
            $params["database"] = isset($dbConfig["name"]) ? $dbConfig["name"] : "";
            $params["username"] = isset($dbConfig["user"]) ? $dbConfig["user"] : "root";
            $params["password"] = isset($dbConfig["password"]) ? $dbConfig["password"] : "";
            if (isset($dbConfig["charset"])) {
                $params["charset"] = $dbConfig["charset"];
            }
        }

        $connected = g::run("db.connect", $params);

        if ($connected) {
            g::run("db.createSchema");
            g::run("core.log", "[DB] Auto-connected and schema created");
            return true;
        } else {
            g::run("core.log", "[DB] Auto-connect failed");
            return false;
        }
    },

    /**
     * Auto-initialize clone and admin data from config
     * DEPRECATED: Now handled by db.autoSeed (called automatically after schema creation)
     */
    "autoInitializeData" => function () {
        // No longer needed - db.autoSeed handles this automatically
        return true;
    },

));

// ============================================================================
// PHASE 2: CONFIGURATION MANAGEMENT
// ============================================================================

g::def("config", array(

    /**
     * Get the current configuration
     * 
     * Returns the entire loaded configuration object.
     * 
     * @return array Configuration array
     * 
     * @example $config = g::run("config.get");
     * @example echo $config['settings']['timezone'];
     */
    "get" => function () {
        return g::get("config");
    },

    /**
     * Read and decode a JSON file
     * 
     * Safely reads a JSON file and returns decoded array.
     * Returns false if file doesn't exist or JSON is invalid.
     * 
     * @param string $filePath Full path to JSON file
     * @return array|false Decoded array or false on failure
     * 
     * @example $data = g::run("config.readJson", "/path/to/file.json");
     */
    "readJson" => function ($filePath) {
        if (!file_exists($filePath)) {
            g::run("core.log", "[WARNING] File not found: $filePath", 2);
            return false;
        }

        $json = file_get_contents($filePath);
        if ($json === false) {
            g::run("core.log", "[ERROR] Could not read file: $filePath", 1);
            return false;
        }

        $data = json_decode($json, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            g::run("core.log", "[ERROR] Invalid JSON in file: $filePath - " . json_last_error_msg(), 1);
            return false;
        }

        return $data;
    },

    /**
     * Write data to JSON file
     * 
     * Encodes data as JSON and writes to file.
     * Creates directory if it doesn't exist.
     * 
     * @param mixed $data Data to encode (array, object, etc.)
     * @param string $filePath Full path to JSON file
     * @param bool $pretty Whether to pretty-print JSON (default: true)
     * @return bool Success status
     * 
     * @example g::run("config.writeJson", $data, "/path/to/file.json");
     */
    "writeJson" => function ($data, $filePath, $pretty = true) {
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                g::run("core.log", "[ERROR] Could not create directory: $dir", 1);
                return false;
            }
        }

        // Encode data
        $options = JSON_UNESCAPED_SLASHES;
        if ($pretty) {
            $options |= JSON_PRETTY_PRINT;
        }

        $json = json_encode($data, $options);
        if ($json === false) {
            g::run("core.log", "[ERROR] Could not encode JSON: " . json_last_error_msg(), 1);
            return false;
        }

        // Write to file
        $result = file_put_contents($filePath, $json);
        if ($result === false) {
            g::run("core.log", "[ERROR] Could not write to file: $filePath", 1);
            return false;
        }

        g::run("core.log", "[CONFIG] Written to file: $filePath (" . strlen($json) . " bytes)", 3);
        return true;
    },

    /**
     * Update a specific configuration value
     * 
     * Updates config in memory and optionally saves to file.
     * Supports dot notation for nested keys.
     * 
     * @param string $key Config key (supports dot notation: "database.host")
     * @param mixed $value New value
     * @param bool $save Whether to save to config file (default: true)
     * @return bool Success status
     * 
     * @example g::run("config.update", "database.host", "localhost", true);
     * @example g::run("config.update", "settings.debug", true);
     */
    "update" => function ($key, $value, $save = true) {
        $config = g::get("config");
        if (!$config) {
            g::run("core.log", "[ERROR] Config not loaded", 1);
            return false;
        }

        // Update in memory using dot notation
        $keys = explode('.', $key);
        $ref = &$config;

        foreach ($keys as $k) {
            if (!isset($ref[$k])) {
                $ref[$k] = array();
            }
            $ref = &$ref[$k];
        }
        $ref = $value;

        // Update stored config
        g::set("config", $config);

        // Also update in the nested location for backwards compatibility
        g::set("config." . $key, $value);

        // Save to file if requested
        if ($save) {
            return g::run("core.saveConfig", $config);
        }

        return true;
    },

    /**
     * Load and merge an external config file
     * 
     * Loads a JSON config file and merges it with existing config.
     * Useful for environment-specific configs or plugin configs.
     * 
     * @param string $filePath Path to config file
     * @param bool $overwrite Whether external config overwrites existing (default: true)
     * @return bool Success status
     * 
     * @example g::run("config.load", "config.local.json");
     * @example g::run("config.load", "plugins/my-plugin/config.json", false);
     */
    "load" => function ($filePath, $overwrite = true) {
        $externalConfig = g::run("config.readJson", $filePath);

        if ($externalConfig === false) {
            return false;
        }

        $currentConfig = g::get("config");

        if ($overwrite) {
            // External config takes precedence
            $mergedConfig = g::run("core.arrayMergeRecursive", $currentConfig, $externalConfig);
        } else {
            // Current config takes precedence
            $mergedConfig = g::run("core.arrayMergeRecursive", $externalConfig, $currentConfig);
        }

        g::set("config", $mergedConfig);
        g::run("core.log", "[CONFIG] Loaded external config: $filePath", 3);

        return true;
    },

    /**
     * Get a specific configuration value with default fallback
     * 
     * Retrieves a config value using dot notation.
     * Returns default value if key doesn't exist.
     * 
     * @param string $key Config key (dot notation: "database.host")
     * @param mixed $default Default value if key not found
     * @return mixed Config value or default
     * 
     * @example $host = g::run("config.getValue", "database.host", "localhost");
     * @example $debug = g::run("config.getValue", "settings.debug", false);
     */
    "getValue" => function ($key, $default = null) {
        $config = g::get("config");
        if (!$config || !is_array($config)) {
            return $default;
        }

        // Navigate through nested keys
        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    },

    /**
     * Check if a configuration key exists
     * 
     * @param string $key Config key (dot notation)
     * @return bool Whether the key exists
     * 
     * @example if (g::run("config.has", "database.host")) { ... }
     */
    "has" => function ($key) {
        $config = g::get("config");
        if (!$config || !is_array($config)) {
            return false;
        }

        // Navigate through nested keys
        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return false;
            }
            $value = $value[$k];
        }

        return true;
    },

    /**
     * Reset configuration to defaults
     * 
     * Useful for testing or resetting after errors.
     * Optionally saves the reset config to file.
     * 
     * @param bool $save Whether to save reset config to file
     * @return bool Success status
     * 
     * @example g::run("config.reset", true);
     */
    "reset" => function ($save = false) {
        // Trigger prepareConfig again to load defaults
        g::run("core.prepareConfig");

        if ($save) {
            $config = g::get("config");
            return g::run("core.saveConfig", $config);
        }

        g::run("core.log", "[CONFIG] Configuration reset to defaults", 2);
        return true;
    },

));

// ============================================================================
// PHASE 3: LOGGING & MESSAGING
// ============================================================================

g::def("log", array(

    /**
     * Write a message with importance level
     * 
     * Logs to file and optionally stores in memory for display to user.
     * Level determines if message is logged/shown based on config settings.
     * 
     * Levels:
     * 1 = ERROR   - Critical errors
     * 2 = WARNING - Important warnings
     * 3 = INFO    - Informational messages
     * 4 = DEBUG   - Debug information
     * 
     * @param string $message The message to log
     * @param int $level Message importance level (1-4)
     * @param bool $display Whether to also store for user display (default: false)
     * @return void
     * 
     * @example g::run("log.write", "Database connected", 3);
     * @example g::run("log.write", "Invalid login attempt", 2, true);
     */
    "write" => function ($message, $level = 3, $display = false) {
        $configLevel = g::run("config.getValue", "settings.log_level", 1);

        // Only log if message level is <= configured level
        if ($level <= $configLevel) {
            if (!is_dir(DATA_FOLDER)) {
                mkdir(DATA_FOLDER, 0755, true);
            }

            $timestamp = g::run("core.timestamp");
            $levelName = g::run("log.getLevelName", $level);
            $logEntry = "[$timestamp] [$levelName] $message\n";

            file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
        }

        // Store for user display if requested
        if ($display) {
            g::run("log.addMessage", $message, $level);
        }
    },

    /**
     * Get level name from number
     * 
     * @param int $level Level number (1-4)
     * @return string Level name
     */
    "getLevelName" => function ($level) {
        $levels = array(
            1 => "ERROR",
            2 => "WARNING",
            3 => "INFO",
            4 => "DEBUG"
        );
        return isset($levels[$level]) ? $levels[$level] : "UNKNOWN";
    },

    /**
     * Add a message for user display
     * 
     * Stores messages in memory that can be retrieved and shown to users.
     * Supports translation and categorization by level.
     * 
     * @param string $message The message (can be a translation key)
     * @param int $level Message level (1-4)
     * @return void
     * 
     * @example g::run("log.addMessage", "user.login.success", 3);
     */
    "addMessage" => function ($message, $level = 3) {
        $messages = g::get("messages");
        if (!is_array($messages)) {
            $messages = array();
        }

        $timestamp = g::run("core.timestamp");
        $translated = g::run("log.translate", $message);

        $messages[] = array(
            "message" => $translated,
            "original" => $message,
            "level" => $level,
            "time" => $timestamp
        );

        g::set("messages", $messages);
    },

    /**
     * Get all stored messages
     * 
     * Retrieves all messages stored for user display.
     * Optionally filter by level.
     * 
     * @param int|null $filterLevel Only return messages of this level (optional)
     * @return array Array of message objects
     * 
     * @example $messages = g::run("log.getMessages");
     * @example $errors = g::run("log.getMessages", 1);
     */
    "getMessages" => function ($filterLevel = null) {
        $messages = g::get("messages");
        if (!is_array($messages)) {
            return array();
        }

        if ($filterLevel === null) {
            return $messages;
        }

        // Filter by level
        $filtered = array();
        foreach ($messages as $msg) {
            if ($msg["level"] === $filterLevel) {
                $filtered[] = $msg;
            }
        }

        return $filtered;
    },

    /**
     * Clear all stored messages
     * 
     * @return void
     */
    "clearMessages" => function () {
        g::set("messages", array());
    },

    /**
     * Translate a message
     * 
     * Looks up message in translation dictionary (bits).
     * Falls back to original message if translation not found.
     * 
     * Translation files should be loaded into g::set("translations.{lang}", array)
     * 
     * @param string $key Translation key (e.g., "user.login.success")
     * @return string Translated message or original key
     * 
     * @example $msg = g::run("log.translate", "error.not.found");
     */
    "translate" => function ($key) {
        $lang = g::run("config.getValue", "settings.language", "en");
        $translations = g::get("translations.$lang");

        if ($translations && is_array($translations) && isset($translations[$key])) {
            return $translations[$key];
        }

        // Try default language if current language fails
        if ($lang !== "en") {
            $translations = g::get("translations.en");
            if ($translations && is_array($translations) && isset($translations[$key])) {
                return $translations[$key];
            }
        }

        // Return original key if no translation found
        return $key;
    },

    /**
     * Load translations from a JSON file
     * 
     * Loads translation dictionary for a specific language.
     * Translations should be in format: {"key": "value"}
     * 
     * @param string $filePath Path to translation JSON file
     * @param string $lang Language code (e.g., "en", "es", "fr")
     * @return bool Success status
     * 
     * @example g::run("log.loadTranslations", "data/lang/en.json", "en");
     */
    "loadTranslations" => function ($filePath, $lang) {
        $translations = g::run("config.readJson", $filePath);

        if ($translations === false) {
            g::run("log.write", "Could not load translations from: $filePath", 2);
            return false;
        }

        g::set("translations.$lang", $translations);
        g::run("log.write", "Loaded translations for language: $lang", 3);

        return true;
    },

    /**
     * Get performance metrics
     * 
     * Returns execution time and memory usage since framework start.
     * Useful for debugging and optimization.
     * 
     * @param bool $formatted Return formatted string vs array (default: false)
     * @return array|string Performance data
     * 
     * @example $perf = g::run("log.performance");
     * @example echo g::run("log.performance", true);
     */
    "performance" => function ($formatted = false) {
        $startTime = g::get("system.time.start");
        $startMemory = g::get("system.memory.start");

        if (!$startTime) {
            $startTime = microtime(true);
        }
        if (!$startMemory) {
            $startMemory = 0;
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = round(($endTime - $startTime) * 1000, 2); // milliseconds
        $memoryUsed = round(($endMemory - $startMemory) / 1024, 2); // kilobytes
        $peakMemory = round(memory_get_peak_usage() / 1024, 2); // kilobytes

        $data = array(
            "execution_time_ms" => $executionTime,
            "memory_used_kb" => $memoryUsed,
            "peak_memory_kb" => $peakMemory,
            "start_time" => $startTime,
            "end_time" => $endTime
        );

        if ($formatted) {
            return "Execution: {$executionTime}ms | Memory: {$memoryUsed}KB | Peak: {$peakMemory}KB";
        }

        return $data;
    },

    /**
     * Debug print - enhanced output for development
     * 
     * Pretty-prints data with optional label and HTML formatting.
     * Only outputs if debug mode is enabled.
     * 
     * @param mixed $data Data to print
     * @param string $label Optional label for the output
     * @param bool $return Return string instead of echo (default: false)
     * @return string|void Output string if $return is true
     * 
     * @example g::run("log.debugPrint", $userData, "User Object");
     * @example g::run("log.debugPrint", $config);
     */
    "debugPrint" => function ($data, $label = "", $return = false) {
        $debug = g::run("config.getValue", "settings.debug", false);

        if (!$debug) {
            return $return ? "" : null;
        }

        $output = "";

        if ($label) {
            $output .= "<strong>DEBUG: $label</strong>\n";
        } else {
            $output .= "<strong>DEBUG:</strong>\n";
        }

        $output .= "<pre>";
        if (is_array($data) || is_object($data)) {
            $output .= print_r($data, true);
        } else {
            $output .= var_export($data, true);
        }
        $output .= "</pre>\n";

        if ($return) {
            return $output;
        }

        echo $output;
    },

    /**
     * Quick logging shortcuts for common levels
     */
    "error" => function ($message, $display = true) {
        return g::run("log.write", $message, 1, $display);
    },

    "warning" => function ($message, $display = false) {
        return g::run("log.write", $message, 2, $display);
    },

    "info" => function ($message, $display = false) {
        return g::run("log.write", $message, 3, $display);
    },

    "debugLog" => function ($message) {
        return g::run("log.write", $message, 4, false);
    },

));

// ============================================================================
// PHASE 4: ROUTING & URL PARSING
// ============================================================================

g::def("route", array(

    /**
     * Parse the current request URL
     * 
     * Extracts and parses the current request URL into components:
     * - protocol (http/https)
     * - host (domain)
     * - path (URI path)
     * - query (query string)
     * - segments (path split into array)
     * 
     * Stores result in g::set("request.*")
     * 
     * @return array Parsed URL components
     * 
     * @example $url = g::run("route.parseUrl");
     * @example // Returns: ["protocol" => "https", "host" => "example.com", "path" => "/blog/post", ...]
     */
    "parseUrl" => function () {
        // Get protocol
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

        // Get host
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

        // Get request URI (path + query string)
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

        // Split path and query string (support both ? and ; delimiters)
        $path = $requestUri;
        $queryString = '';

        // Check for semicolon first (preferred), then question mark
        if (strpos($requestUri, ';') !== false) {
            $parts = explode(';', $requestUri, 2);
            $path = $parts[0];
            $queryString = isset($parts[1]) ? $parts[1] : '';
        } elseif (strpos($requestUri, '?') !== false) {
            $parts = explode('?', $requestUri, 2);
            $path = $parts[0];
            $queryString = isset($parts[1]) ? $parts[1] : '';
        }

        // Auto-detect and remove base path (for apps in subfolders)
        // Extract the actual web-accessible base path by comparing SCRIPT_NAME with REQUEST_URI
        $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
        $requestPath = $path; // This is the path from REQUEST_URI

        // Get the directory name from SCRIPT_NAME (e.g., /dv-private/genes_one/index.php -> genes_one)
        $scriptDir = dirname($scriptName);
        $scriptDirName = basename($scriptDir);

        // Find where this directory name appears in the REQUEST_URI
        $basePath = '';
        if ($scriptDirName && strpos($requestPath, $scriptDirName) !== false) {
            // Find the position of the script directory in the request path
            $pos = strpos($requestPath, '/' . $scriptDirName);
            if ($pos !== false) {
                // Extract everything up to and including the directory name as base path
                $basePath = substr($requestPath, 0, $pos + strlen($scriptDirName) + 1);
                $basePath = rtrim($basePath, '/');
            }
        }

        // Strip base path from the beginning of the path
        if ($basePath && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
            if (empty($path) || $path[0] !== '/') {
                $path = '/' . $path;
            }
        }

        // Ensure path has leading slash
        if (empty($path)) {
            $path = '/';
        } elseif ($path[0] !== '/') {
            $path = '/' . $path;
        }

        // Parse query string into array
        $query = array();
        if ($queryString) {
            parse_str($queryString, $query);
        }

        // Split path into segments (remove empty segments)
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        // Get request method
        $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';

        // Get IP address
        $ip = g::run("route.getClientIp");

        $urlData = array(
            "protocol" => $protocol,
            "host" => $host,
            "path" => $path,
            "query_string" => $queryString,
            "query" => $query,
            "segments" => $segments,
            "method" => $method,
            "ip" => $ip,
            "full_url" => $protocol . '://' . $host . $requestUri
        );

        // Store for later use
        g::set("request", $urlData);

        return $urlData;
    },

    /**
     * Get client IP address
     * 
     * Tries to determine real client IP even behind proxies.
     * 
     * @return string Client IP address
     */
    "getClientIp" => function () {
        $ipKeys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return 'unknown';
    },

    /**
     * Match a URL pattern to current request
     * 
     * Supports wildcards and parameters:
     * - "/blog" - Exact match
     * - "/blog/*" - Match /blog/anything
     * - "/blog/:id" - Match /blog/123 and capture id=123
     * - "/blog/:id/edit" - Capture parameter in middle
     * 
     * @param string $pattern URL pattern to match
     * @param array|null $request Request data (optional, uses current if null)
     * @return array|false Matched parameters or false
     * 
     * @example $params = g::run("route.match", "/blog/:id");
     * @example if ($params) { $id = $params['id']; }
     */
    "match" => function ($pattern, $request = null) {
        if ($request === null) {
            $request = g::get("request");
        }

        if (!$request || !isset($request['path'])) {
            return false;
        }

        $path = $request['path'];

        // Normalize pattern and path
        $pattern = '/' . trim($pattern, '/');
        $path = '/' . trim($path, '/');

        // Exact match (easiest case)
        if ($pattern === $path) {
            return array();
        }

        // Convert pattern to regex
        $regex = $pattern;

        // Replace :param with named capture groups
        $regex = preg_replace_callback('/:([a-zA-Z0-9_]+)/', function ($matches) {
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $regex);

        // Replace * with wildcard
        $regex = str_replace('*', '.*', $regex);

        // Add anchors
        $regex = '#^' . $regex . '$#';

        // Try to match
        if (preg_match($regex, $path, $matches)) {
            // Extract only named captures
            $params = array();
            foreach ($matches as $key => $value) {
                if (!is_numeric($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }

        return false;
    },

    /**
     * Define a route with handler
     * 
     * Associates a URL pattern with a handler function.
     * Routes are stored and can be dispatched later.
     * 
     * @param string $pattern URL pattern (e.g., "/blog/:id")
     * @param callable $handler Function to call if route matches
     * @param string $method HTTP method (GET, POST, etc.) or * for all
     * @return void
     * 
     * @example g::run("route.define", "/blog/:id", function($params) {
     *     echo "Blog post: " . $params['id'];
     * });
     */
    "define" => function ($pattern, $handler, $method = '*') {
        $routes = g::get("routes");
        if (!is_array($routes)) {
            $routes = array();
        }

        $method = strtoupper($method);

        if (!isset($routes[$method])) {
            $routes[$method] = array();
        }

        $routes[$method][] = array(
            "pattern" => $pattern,
            "handler" => $handler
        );

        g::set("routes", $routes);
    },

    /**
     * Dispatch - find and execute matching route
     * 
     * Tries to match current request against defined routes.
     * Executes the first matching route's handler.
     * 
     * @param array|null $request Request data (optional)
     * @return mixed Result of handler function, or false if no match
     * 
     * @example g::run("route.dispatch");
     */
    "dispatch" => function ($request = null) {
        if ($request === null) {
            $request = g::get("request");
        }

        if (!$request) {
            g::run("log.warning", "No request data for routing");
            return false;
        }

        $routes = g::get("routes");
        if (!is_array($routes) || empty($routes)) {
            g::run("log.warning", "No routes defined");
            return false;
        }

        $method = $request['method'];

        // Check method-specific routes first, then * (wildcard) routes
        $routesToCheck = array();
        if (isset($routes[$method])) {
            $routesToCheck = array_merge($routesToCheck, $routes[$method]);
        }
        if (isset($routes['*'])) {
            $routesToCheck = array_merge($routesToCheck, $routes['*']);
        }

        // Try to match routes
        foreach ($routesToCheck as $route) {
            $params = g::run("route.match", $route['pattern'], $request);

            if ($params !== false) {
                // Match found - merge params with query params
                $allParams = array_merge($request['query'], $params);

                // Execute handler
                if (is_callable($route['handler'])) {
                    g::run("log.info", "Route matched: {$route['pattern']}");
                    return call_user_func($route['handler'], $allParams, $request);
                }

                return true;
            }
        }

        // No match found
        g::run("log.info", "No route matched for: {$request['path']}");
        return false;
    },

    /**
     * Build a URL from pattern and parameters
     * 
     * Replaces parameters in pattern with actual values.
     * Uses semicolon-delimited query parameters instead of ? and &
     * 
     * @param string $pattern URL pattern with :params
     * @param array $params Parameter values
     * @param array $query Query string parameters (optional)
     * @return string Built URL
     * 
     * @example $url = g::run("route.build", "/blog/:id", ["id" => 123]);
     * @example // Returns: "/blog/123"
     * 
     * @example $url = g::run("route.build", "/blog/:id", ["id" => 123], ["page" => 2]);
     * @example // Returns: "/blog/123;page=2"
     */
    "build" => function ($pattern, $params = array(), $query = array()) {
        $url = $pattern;

        // Replace :param with values
        foreach ($params as $key => $value) {
            $url = str_replace(':' . $key, $value, $url);
        }

        // Add query string with semicolon delimiters
        if (!empty($query)) {
            $queryParts = array();
            foreach ($query as $key => $value) {
                $queryParts[] = $key . '=' . urlencode($value);
            }
            $url .= ';' . implode(';', $queryParts);
        }

        return $url;
    },

    /**
     * Redirect to a URL
     * 
     * Sends HTTP redirect header and optionally exits.
     * 
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code (301, 302, etc.)
     * @param bool $exit Whether to exit after redirect
     * @return void
     * 
     * @example g::run("route.redirect", "/login");
     * @example g::run("route.redirect", "/blog/123", 301);
     */
    "redirect" => function ($url, $statusCode = 302, $exit = true) {
        // Ensure no output has been sent
        if (headers_sent()) {
            g::run("log.error", "Cannot redirect - headers already sent");
            echo "<script>window.location.href='$url';</script>";
            return;
        }

        // Set status code
        http_response_code($statusCode);

        // Send location header
        header("Location: $url");

        g::run("log.info", "Redirecting to: $url");

        if ($exit) {
            exit;
        }
    },

    /**
     * Define route aliases
     * 
     * Maps multiple URL patterns to the same handler.
     * Useful for multilingual URLs or alternate paths.
     * 
     * @param array $patterns Array of URL patterns
     * @param callable $handler Function to call for all patterns
     * @param string $method HTTP method (GET, POST, etc.) or * for all
     * @return void
     * 
     * @example g::run("route.alias", array("/membership", "/uyelik", "/mitgliedschaft"), function($params) {
     *     echo "Membership page - Lang: " . $params['_lang'];
     * });
     */
    "alias" => function ($patterns, $handler, $method = '*') {
        if (!is_array($patterns)) {
            $patterns = array($patterns);
        }

        foreach ($patterns as $pattern) {
            g::run("route.define", $pattern, $handler, $method);
        }

        g::run("log.debug", "Registered " . count($patterns) . " route aliases");
    },

    /**
     * Define translated routes
     * 
     * Maps localized URLs to handler with language context.
     * Automatically passes language code to handler.
     * 
     * @param array $translations Associative array: lang => pattern
     * @param callable $handler Function to call (receives $params with '_lang' key)
     * @param string $method HTTP method
     * @return void
     * 
     * @example g::run("route.translate", array(
     *     "en" => "/membership",
     *     "tr" => "/uyelik",
     *     "de" => "/mitgliedschaft"
     * ), function($params) {
     *     echo "Language: " . $params['_lang'];
     * });
     */
    "translate" => function ($translations, $handler, $method = '*') {
        if (!is_array($translations)) {
            g::run("log.error", "route.translate expects array of translations");
            return;
        }

        foreach ($translations as $lang => $pattern) {
            // Wrap handler to inject language
            $langHandler = function ($params, $request = null) use ($handler, $lang) {
                $params['_lang'] = $lang;
                return call_user_func($handler, $params, $request);
            };

            g::run("route.define", $pattern, $langHandler, $method);
        }

        g::run("log.debug", "Registered translated routes for " . count($translations) . " languages");
    },

    /**
     * Resolve route with fallback chain
     * 
     * Smart resolution: tries mod routes → clone functions → default handler → 404
     * This is the main dispatcher that checks multiple sources.
     * 
     * @param array|null $request Request data (optional)
     * @param callable|null $notFoundHandler Custom 404 handler
     * @return mixed Result of handler or false
     * 
     * @example g::run("route.resolve");
     * 
     * @example g::run("route.resolve", null, function() {
     *     echo "404 - Page Not Found";
     * });
     */
    "resolve" => function ($request = null, $notFoundHandler = null) {
        if ($request === null) {
            $request = g::get("request");
        }

        if (!$request) {
            g::run("log.warning", "No request data for route resolution");
            return false;
        }

        $path = $request['path'];

        g::run("log.debug", "Resolving route: $path");

        // STEP 1: Try mod routes first (from Phase 8)
        $modRoutes = g::get("mods.routes");
        if ($modRoutes && isset($modRoutes[$path])) {
            $modRoute = $modRoutes[$path];
            g::run("log.info", "Route resolved to mod: {$modRoute['mod']}");

            $handler = $modRoute['handler'];
            if (g::has($handler)) {
                return g::run($handler, array_merge($request['query'], array('_request' => $request)));
            }
        }

        // STEP 2: Try standard route dispatch (includes translated routes, aliases, etc.)
        $result = g::run("route.dispatch", $request);
        if ($result !== false) {
            return $result;
        }

        // STEP 3: Try clone-defined functions
        // Clone functions should be named like "clone.home", "clone.about", etc.
        $pathClean = trim($path, '/');
        if (empty($pathClean)) {
            $pathClean = 'index';
        }

        // Try: clone.{path}
        $cloneFunction = "clone." . str_replace('/', '.', $pathClean);
        if (g::has($cloneFunction)) {
            g::run("log.info", "Route resolved to clone function: $cloneFunction");
            return g::run($cloneFunction, array_merge($request['query'], array('_request' => $request)));
        }

        // STEP 4: Try default handlers (from route.defaults)
        $defaults = g::get("route.defaults");
        if ($defaults && isset($defaults[$path])) {
            g::run("log.info", "Route resolved to default handler");
            $handler = $defaults[$path];
            if (is_callable($handler)) {
                return call_user_func($handler, $request['query'], $request);
            }
        }

        // STEP 5: 404 - Not Found
        g::run("log.info", "Route not found: $path");

        if ($notFoundHandler && is_callable($notFoundHandler)) {
            return call_user_func($notFoundHandler, $request);
        }

        // Try registered 404 handler
        $default404 = g::get("route.404");
        if ($default404 && is_callable($default404)) {
            return call_user_func($default404, $request);
        }

        // Default 404 response
        http_response_code(404);
        echo "404 - Page Not Found: $path";
        return false;
    },

    /**
     * Set default route handlers
     * 
     * Define handlers for common routes like /, /index, 404, etc.
     * These are fallback handlers used when no other route matches.
     * 
     * @param array $defaults Associative array: path => handler
     * @return void
     * 
     * @example g::run("route.defaults", array(
     *     "/" => function() { echo "Home"; },
     *     "/index" => function() { echo "Home"; },
     *     "404" => function() { echo "Not Found"; }
     * ));
     */
    "defaults" => function ($defaults) {
        if (!is_array($defaults)) {
            g::run("log.error", "route.defaults expects array");
            return;
        }

        $existing = g::get("route.defaults");
        if (!is_array($existing)) {
            $existing = array();
        }

        // Handle special 404 handler
        if (isset($defaults['404'])) {
            g::set("route.404", $defaults['404']);
            unset($defaults['404']);
        }

        // Merge with existing defaults
        $merged = array_merge($existing, $defaults);
        g::set("route.defaults", $merged);

        g::run("log.debug", "Registered " . count($defaults) . " default route handlers");
    },

    /**
     * Handle routing with views from config.json
     * Detects language, matches route to view, runs mapped function
     * Automatically handles API routes (/api/*) through api.handle
     */
    "handle" => function () {
        $config = g::get("config");
        $request = g::get("request");

        if (!$request) {
            $request = g::run("route.parseUrl");
        }

        $path = $request['path'];
        $isJson = false;

        // Remove trailing slash for consistency
        $path = rtrim($path, '/');

        // ============================================================
        // API ROUTE DETECTION - Auto-handle /api/* requests
        // ============================================================
        $pathSegments = array_values(array_filter(explode('/', trim($path, '/'))));

        if (isset($pathSegments[0]) && $pathSegments[0] === 'api') {
            // API request detected - send JSON response
            header('Content-Type: application/json');

            // Build API path (remove 'api' prefix)
            $apiSegments = array_slice($pathSegments, 1);
            $apiPath = '/' . implode('/', $apiSegments);

            // Get request method
            $method = isset($request['method']) ? $request['method'] : 'GET';

            // Get request data
            $data = null;
            if ($method === 'GET') {
                $data = $request['query'];
            } elseif (in_array($method, array('POST', 'PUT', 'PATCH', 'DELETE'))) {
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);
                if ($data === null) {
                    $data = $_POST;
                }
            }

            // Handle the API request
            $response = g::run("api.handle", $apiPath, $method, $data);

            echo json_encode($response);
            exit; // Stop further processing
        }
        // ============================================================

        // Check for AJAX request (for client-side router)
        if (isset($_GET['ajax']) || isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            g::set("response.is_ajax", true);
        }

        // Check for .json suffix BEFORE extracting segments
        if (substr($path, -5) === '.json') {
            $isJson = true;
            $path = substr($path, 0, -5);
            g::set("response.format", "json");
        }

        // Clean path and split into segments
        $path = trim($path, '/');
        $pathSegments = !empty($path) ? explode('/', $path) : array();

        // First segment is the route, rest are additional route segments
        // If empty path, default to 'index'
        if (empty($pathSegments)) {
            $routePath = 'index';
            $additionalSegments = array();
        } else {
            $routePath = $pathSegments[0];
            $additionalSegments = array_slice($pathSegments, 1);
        }

        // Store additional segments in request for view functions to use
        $request['route_segments'] = $additionalSegments;
        g::set("request", $request);

        // Get views from config
        $views = isset($config["views"]) ? $config["views"] : array();
        $bits = isset($config["bits"]) ? $config["bits"] : array();

        // Find matching view and detect language from URL
        $matchedView = null;
        $viewName = null;
        $lang = null;

        foreach ($views as $vName => $viewConfig) {
            $urls = isset($viewConfig["urls"]) ? $viewConfig["urls"] : array();

            foreach ($urls as $l => $url) {
                // Match if routePath equals URL
                if ($routePath === $url) {
                    $matchedView = $viewConfig;
                    $viewName = $vName;
                    $lang = $l; // Set language based on which URL matched
                    break 2;
                }
            }
        }

        // If no match found, immediately return 404
        if (!$matchedView) {
            http_response_code(404);

            if (g::has("clone.NotFound")) {
                $lang = g::run("route.detectLanguage");
                return g::run("clone.NotFound", array(), $lang, $path);
            }
            echo "404 - Page Not Found";
            exit;
        }

        // Fallback to auto-detect if no language was set from URL
        if (!$lang) {
            $lang = g::run("route.detectLanguage");
        }

        if ($matchedView) {
            $function = isset($matchedView["function"]) ? $matchedView["function"] : "clone.Index";
            $viewBits = isset($matchedView["bits"]) ? $matchedView["bits"] : array();

            // Translate bits for current language
            $translatedBits = g::run("route.translateBits", $viewBits, $bits, $lang);

            // Run the function
            if (g::has($function)) {
                // If JSON format requested, capture output and return data
                if ($isJson) {
                    ob_start();
                    $result = g::run($function, $translatedBits, $lang, $path);
                    $output = ob_get_clean();

                    // Prepare JSON response with system info
                    $jsonData = array(
                        "success" => true,
                        "view" => $viewName,
                        "lang" => $lang,
                        "path" => $path,
                        "request" => array(
                            "method" => $request["method"],
                            "query" => $request["query"],
                            "segments" => $request["segments"],
                            "route_segments" => isset($request["route_segments"]) ? $request["route_segments"] : array(),
                            "ip" => $request["ip"]
                        )
                    );

                    // Add authentication info
                    $user = g::run("auth.user");
                    if ($user) {
                        $jsonData["auth"] = array(
                            "authenticated" => true,
                            "user" => $user
                        );
                    } else {
                        $jsonData["auth"] = array(
                            "authenticated" => false,
                            "user" => null
                        );
                    }

                    // If function returned data, use it
                    if ($result !== null && $result !== '') {
                        if (is_array($result)) {
                            $jsonData = array_merge($jsonData, $result);
                        } else {
                            $jsonData["data"] = $result;
                        }
                    }
                    // If there was output, include it
                    else if (!empty($output)) {
                        $jsonData["html"] = $output;
                    }

                    header('Content-Type: application/json');
                    echo json_encode($jsonData);
                    return $jsonData;
                } else {
                    // Normal HTML response
                    $result = g::run($function, $translatedBits, $lang, $path);
                    return $result;
                }
            } else {
                g::run("log.error", "View function not found: $function");

                // Show helpful error in debug mode
                if (g::get("config.settings.debug")) {
                    echo "<h1>Error: Function Not Found</h1>";
                    echo "<p>The function <code>$function</code> is not defined.</p>";
                    echo "<p>Make sure you have <code>g::def('clone', array(...))</code> in your index.php</p>";
                }
            }
        }

        // 404
        g::run("log.info", "Route not found: $path (lang: $lang)");

        if (g::has("clone.NotFound")) {
            return g::run("clone.NotFound", array(), $lang, $path);
        } else {
            http_response_code(404);

            // Show helpful error in debug mode
            if (g::get("config.settings.debug")) {
                echo "<h1>404 - Route Not Found (Debug Mode)</h1>";
                echo "<p>Requested path: <code>$path</code></p>";
                echo "<p>Language: <code>$lang</code></p>";
                echo "<h3>Available Views:</h3><ul>";
                foreach ($views as $vName => $vConfig) {
                    echo "<li><strong>$vName</strong>: " . (isset($vConfig["urls"]) ? implode(", ", $vConfig["urls"]) : "no URLs") . "</li>";
                }
                echo "</ul>";
            } else {
                echo "404 - Page Not Found";
            }
        }
    },

    /**
     * Detect language from URL, session, or config
     */
    "detectLanguage" => function () {
        $config = g::get("config");
        $defaultLang = isset($config["clone"]["default_language"]) ? $config["clone"]["default_language"] : "en";

        // Check session first
        if (isset($_SESSION["language"])) {
            return $_SESSION["language"];
        }

        // Check cookie
        if (isset($_COOKIE["language"])) {
            return $_COOKIE["language"];
        }

        // Check Accept-Language header
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $acceptLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            $languages = isset($config["clone"]["languages"]) ? $config["clone"]["languages"] : array("en");

            if (in_array($acceptLang, $languages)) {
                return $acceptLang;
            }
        }

        return $defaultLang;
    },

    /**
     * Translate bits to current language
     */
    "translateBits" => function ($viewBits, $globalBits, $lang) {
        $translated = array();

        // Merge global bits first
        foreach ($globalBits as $key => $translations) {
            if (is_array($translations) && isset($translations[$lang])) {
                $translated[$key] = $translations[$lang];
            } else if (isset($translations["en"])) {
                $translated[$key] = $translations["en"]; // Fallback to English
            } else {
                $translated[$key] = $key;
            }
        }

        // Then view-specific bits (overrides global)
        foreach ($viewBits as $key => $translations) {
            if (is_array($translations) && isset($translations[$lang])) {
                $translated[$key] = $translations[$lang];
            } else if (isset($translations["en"])) {
                $translated[$key] = $translations["en"];
            } else {
                $translated[$key] = $key;
            }
        }

        return $translated;
    },

));

// ============================================================================
// PHASE 5: AUTHENTICATION & SESSION MANAGEMENT
// ============================================================================
// Combined authentication system moved to PHASE 7.75 (auth.* namespace)
// Supports BOTH file-based (users.json) AND database-based authentication

// ============================================================================
// PHASE 6: CRYPTOGRAPHY
// ============================================================================

g::def("crypt", array(

    /**
     * Hash a password using bcrypt with application pepper
     * 
     * Uses password_hash with additional HMAC pepper from config.security.secret
     * This adds an application-wide secret layer on top of bcrypt's per-password salt.
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     * 
     * @example $hash = g::run("crypt.hashPassword", "secret123");
     */
    "hashPassword" => function ($password) {
        // Get application secret for peppering
        $config = g::get("config");
        $pepper = isset($config['security']['secret']) ? $config['security']['secret'] : '';

        // Apply pepper using HMAC before hashing
        // This means even if DB is compromised, attacker needs config.json secret
        $pepperedPassword = hash_hmac('sha256', $password, $pepper);

        // Now hash with bcrypt (which adds its own random salt)
        return password_hash($pepperedPassword, PASSWORD_DEFAULT);
    },

    /**
     * Verify a password against a peppered hash
     * 
     * Applies same HMAC pepper from config.security.secret before verifying.
     * 
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool Match status
     * 
     * @example if (g::run("crypt.verifyPassword", $input, $hash)) { }
     */
    "verifyPassword" => function ($password, $hash) {
        // Get application secret for peppering
        $config = g::get("config");
        $pepper = isset($config['security']['secret']) ? $config['security']['secret'] : '';

        // Apply same pepper transformation
        $pepperedPassword = hash_hmac('sha256', $password, $pepper);

        // Verify against bcrypt hash
        return password_verify($pepperedPassword, $hash);
    },

    /**
     * Generate a cryptographically secure token
     * 
     * Useful for API keys, password reset tokens, CSRF tokens, etc.
     * 
     * @param int $length Token length in bytes (default: 32)
     * @return string Hex token
     * 
     * @example $token = g::run("crypt.token");
     * @example $apiKey = g::run("crypt.token", 64);
     */
    "token" => function ($length = 32) {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        } else {
            // Fallback for older PHP
            return g::run("core.generateRandomKey", $length * 2, "hex");
        }
    },

    /**
     * Generate a hash of data
     * 
     * @param string $data Data to hash
     * @param string $algo Hash algorithm (default: sha256)
     * @return string Hash string
     * 
     * @example $hash = g::run("crypt.hash", "data");
     * @example $md5 = g::run("crypt.hash", "data", "md5");
     */
    "hash" => function ($data, $algo = 'sha256') {
        return hash($algo, $data);
    },

    /**
     * Create a keyed hash (HMAC)
     * 
     * Used for message authentication and API signatures.
     * 
     * @param string $data Data to hash
     * @param string $key Secret key
     * @param string $algo Hash algorithm (default: sha256)
     * @return string HMAC hash
     * 
     * @example $signature = g::run("crypt.hmac", $data, $secret);
     */
    "hmac" => function ($data, $key, $algo = 'sha256') {
        return hash_hmac($algo, $data, $key);
    },

    /**
     * Encrypt data using AES-256-CBC
     * 
     * Uses the secret from config.json as encryption key.
     * Returns base64-encoded encrypted data with IV prepended.
     * 
     * @param string $data Data to encrypt
     * @param string|null $key Encryption key (uses config secret if null)
     * @return string|false Encrypted data (base64) or false on failure
     * 
     * @example $encrypted = g::run("crypt.encrypt", "secret data");
     * @example $encrypted = g::run("crypt.encrypt", "data", "custom-key");
     */
    "encrypt" => function ($data, $key = null) {
        if (!function_exists('openssl_encrypt')) {
            g::run("log.error", "OpenSSL not available for encryption");
            return false;
        }

        // Use config secret if no key provided
        if ($key === null) {
            $config = g::run("config.get");
            $key = isset($config['security']['secret']) ? $config['security']['secret'] : 'genes-default-key';
        }

        // Generate a key from the provided key
        $encryptionKey = hash('sha256', $key, true);

        // Generate random IV
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($ivLength);

        // Encrypt the data
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            g::run("log.error", "Encryption failed");
            return false;
        }

        // Prepend IV to encrypted data and encode
        return base64_encode($iv . $encrypted);
    },

    /**
     * Decrypt data encrypted with crypt.encrypt
     * 
     * @param string $encryptedData Base64-encoded encrypted data
     * @param string|null $key Encryption key (uses config secret if null)
     * @return string|false Decrypted data or false on failure
     * 
     * @example $decrypted = g::run("crypt.decrypt", $encrypted);
     */
    "decrypt" => function ($encryptedData, $key = null) {
        if (!function_exists('openssl_decrypt')) {
            g::run("log.error", "OpenSSL not available for decryption");
            return false;
        }

        // Use config secret if no key provided
        if ($key === null) {
            $config = g::run("config.get");
            $key = isset($config['security']['secret']) ? $config['security']['secret'] : 'genes-default-key';
        }

        // Generate a key from the provided key
        $encryptionKey = hash('sha256', $key, true);

        // Decode the data
        $data = base64_decode($encryptedData);
        if ($data === false) {
            g::run("log.error", "Invalid base64 data for decryption");
            return false;
        }

        // Extract IV
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        // Decrypt
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            g::run("log.error", "Decryption failed");
            return false;
        }

        return $decrypted;
    },

    /**
     * Generate a UUID v4
     * 
     * Useful for unique identifiers.
     * 
     * @return string UUID string
     * 
     * @example $uuid = g::run("crypt.uuid");
     * @example // Returns: "550e8400-e29b-41d4-a716-446655440000"
     */
    "uuid" => function () {
        if (function_exists('random_bytes')) {
            $data = random_bytes(16);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $data = openssl_random_pseudo_bytes(16);
        } else {
            // Fallback
            $data = '';
            for ($i = 0; $i < 16; $i++) {
                $data .= chr(mt_rand(0, 255));
            }
        }

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    },

));

// ============================================================================
// PHASE 7: DATABASE ABSTRACTION
// ============================================================================

g::def("db", array(

    /**
     * Generate a unique hash ID from timestamp
     * 
     * Creates a hash-based ID using encrypted timestamp with microseconds.
     * IDs are chronologically sortable and unique.
     * 
     * @return string Hash ID (32 chars)
     * 
     * @example $hash = g::run("db.generateHash");
     * @example // Returns: "a7f3k9m2p8q1r5t8w2x6z9c4"
     */
    "generateHash" => function () {
        // Get timestamp with microseconds
        $timestamp = microtime(true);

        // Create unique string from timestamp + random component
        $unique = $timestamp . '.' . mt_rand(100000, 999999);

        // Get salt from config for encryption
        $config = g::run("config.get");
        $salt = isset($config['security']['salt']) ? $config['security']['salt'] : 'genes-salt';

        // Generate hash
        $hash = hash('sha256', $unique . $salt);

        // Return first 32 characters
        return substr($hash, 0, 32);
    },

    /**
     * Connect to a database
     * 
     * Supports MySQL, SQLite, PostgreSQL.
     * Connections are stored and reused.
     * 
     * @param array $config Connection config
     * @return bool|PDO Connection object or false
     * 
     * @example g::run("db.connect", array(
     *     "driver" => "mysql",
     *     "name" => "main",
     *     "host" => "localhost",
     *     "database" => "mydb",
     *     "username" => "root",
     *     "password" => ""
     * ));
     * 
     * @example g::run("db.connect", array(
     *     "driver" => "sqlite",
     *     "name" => "users",
     *     "database" => "data/users.db"
     * ));
     */
    "connect" => function ($config) {
        $driver = isset($config['driver']) ? $config['driver'] : 'mysql';
        $name = isset($config['name']) ? $config['name'] : 'default';

        // Check if already connected
        $existing = g::get("db.connections.$name");
        if ($existing) {
            return $existing;
        }

        try {
            $pdo = null;

            if ($driver === 'mysql') {
                $host = isset($config['host']) ? $config['host'] : 'localhost';
                $port = isset($config['port']) ? $config['port'] : 3306;
                $database = isset($config['database']) ? $config['database'] : '';
                $username = isset($config['username']) ? $config['username'] : 'root';
                $password = isset($config['password']) ? $config['password'] : '';
                $charset = isset($config['charset']) ? $config['charset'] : 'utf8mb4';

                $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=$charset";
                $pdo = new PDO($dsn, $username, $password, array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ));
            } elseif ($driver === 'sqlite') {
                $database = isset($config['database']) ? $config['database'] : 'data/database.db';

                // Create directory if needed
                $dir = dirname($database);
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }

                $dsn = "sqlite:$database";
                $pdo = new PDO($dsn, null, null, array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ));

                // Enable JSON1 extension if available
                $pdo->exec("PRAGMA foreign_keys = ON");
            } elseif ($driver === 'pgsql' || $driver === 'postgresql') {
                $host = isset($config['host']) ? $config['host'] : 'localhost';
                $port = isset($config['port']) ? $config['port'] : 5432;
                $database = isset($config['database']) ? $config['database'] : '';
                $username = isset($config['username']) ? $config['username'] : 'postgres';
                $password = isset($config['password']) ? $config['password'] : '';

                $dsn = "pgsql:host=$host;port=$port;dbname=$database";
                $pdo = new PDO($dsn, $username, $password, array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ));
            }

            if ($pdo) {
                // Store connection
                g::set("db.connections.$name", $pdo);
                g::set("db.config.$name", $config);

                g::run("log.info", "Database connected: $name ($driver)");
                return $pdo;
            }
        } catch (PDOException $e) {
            g::run("log.error", "Database connection failed: " . $e->getMessage());
            return false;
        }

        return false;
    },

    /**
     * Get a database connection
     * 
     * @param string $name Connection name (default: 'default')
     * @return PDO|false Connection object or false
     * 
     * @example $db = g::run("db.connection", "main");
     */
    "connection" => function ($name = 'default') {
        return g::get("db.connections.$name");
    },

    /**
     * Execute a raw SQL query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @param string $connection Connection name
     * @return array|false Query results or false
     * 
     * @example $results = g::run("db.query", "SELECT * FROM items WHERE type = ?", array("post"));
     */
    "query" => function ($sql, $params = array(), $connection = 'default') {
        $pdo = g::run("db.connection", $connection);
        if (!$pdo) {
            g::run("log.error", "No database connection: $connection");
            return false;
        }

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Check if it's a SELECT query
            if (stripos(trim($sql), 'SELECT') === 0) {
                return $stmt->fetchAll();
            }

            return true;
        } catch (PDOException $e) {
            g::run("log.error", "Query failed: " . $e->getMessage());
            return false;
        }
    },

    /**
     * Execute multiple queries in a transaction
     * 
     * @param callable $callback Function that receives PDO connection
     * @param string $connection Connection name
     * @return bool Success status
     * 
     * @example g::run("db.transaction", function($db) {
     *     $db->exec("INSERT INTO items ...");
     *     $db->exec("INSERT INTO events ...");
     * });
     */
    "transaction" => function ($callback, $connection = 'default') {
        $pdo = g::run("db.connection", $connection);
        if (!$pdo) {
            return false;
        }

        try {
            $pdo->beginTransaction();

            $result = call_user_func($callback, $pdo);

            $pdo->commit();
            g::run("log.info", "Transaction committed");

            return $result !== false;
        } catch (Exception $e) {
            $pdo->rollBack();
            g::run("log.error", "Transaction failed: " . $e->getMessage());
            return false;
        }
    },

    /**
     * Insert a record
     * 
     * Automatically generates hash if not provided.
     * Adds created_at, updated_at timestamps.
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @param string $connection Connection name
     * @return string|false Hash of inserted record or false
     * 
     * @example $hash = g::run("db.insert", "items", array(
     *     "title" => "My Post",
     *     "type" => "post",
     *     "state" => "published"
     * ));
     */
    "insert" => function ($table, $data, $connection = 'default') {
        $pdo = g::run("db.connection", $connection);
        if (!$pdo) {
            return false;
        }

        // Generate hash if not provided
        if (!isset($data['hash'])) {
            $data['hash'] = g::run("db.generateHash");
        }

        // Add timestamps
        $now = g::run("core.now");
        if (!isset($data['created_at'])) {
            $data['created_at'] = $now;
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = $now;
        }

        // Add clone_id if set and not provided (but not for clones table itself)
        $currentClone = g::get("db.current_clone");
        if ($currentClone && !isset($data['clone_id']) && $table !== 'clones') {
            $data['clone_id'] = $currentClone;
        }

        // Convert arrays/objects to JSON
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $data[$key] = json_encode($value);
            }
        }

        try {
            $columns = array_keys($data);
            $quotedColumns = array_map(function ($col) {
                return "`$col`";
            }, $columns);
            $placeholders = array_fill(0, count($columns), '?');

            $sql = "INSERT INTO $table (" . implode(', ', $quotedColumns) . ") VALUES (" . implode(', ', $placeholders) . ")";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));

            g::run("log.info", "Inserted into $table: {$data['hash']}");

            return $data['hash'];
        } catch (PDOException $e) {
            g::run("log.error", "Insert failed: " . $e->getMessage());
            return false;
        }
    },

    /**
     * Update records
     * 
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $where Where conditions
     * @param string $connection Connection name
     * @return bool Success status
     * 
     * @example g::run("db.update", "items", 
     *     array("state" => "published"),
     *     array("hash" => $hash)
     * );
     */
    "update" => function ($table, $data, $where, $connection = 'default') {
        $pdo = g::run("db.connection", $connection);
        if (!$pdo) {
            return false;
        }

        // Add updated_at
        $data['updated_at'] = g::run("core.now");

        // Convert arrays/objects to JSON
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $data[$key] = json_encode($value);
            }
        }

        try {
            $setClauses = array();
            $values = array();

            foreach ($data as $key => $value) {
                $setClauses[] = "$key = ?";
                $values[] = $value;
            }

            $whereClauses = array();
            foreach ($where as $key => $value) {
                $whereClauses[] = "$key = ?";
                $values[] = $value;
            }

            $sql = "UPDATE $table SET " . implode(', ', $setClauses) . " WHERE " . implode(' AND ', $whereClauses);

            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            g::run("log.info", "Updated $table: " . $stmt->rowCount() . " rows");

            return true;
        } catch (PDOException $e) {
            g::run("log.error", "Update failed: " . $e->getMessage());
            return false;
        }
    },

    /**
     * Delete records (soft delete by default)
     * 
     * @param string $table Table name
     * @param array $where Where conditions
     * @param bool $hard Hard delete (true) or soft delete (false)
     * @param string $connection Connection name
     * @return bool Success status
     * 
     * @example g::run("db.delete", "items", array("hash" => $hash));
     * @example g::run("db.delete", "items", array("hash" => $hash), true); // Hard delete
     */
    "delete" => function ($table, $where, $hard = false, $connection = 'default') {
        if ($hard) {
            // Hard delete
            $pdo = g::run("db.connection", $connection);
            if (!$pdo) {
                return false;
            }

            try {
                $whereClauses = array();
                $values = array();

                foreach ($where as $key => $value) {
                    $whereClauses[] = "$key = ?";
                    $values[] = $value;
                }

                $sql = "DELETE FROM $table WHERE " . implode(' AND ', $whereClauses);

                $stmt = $pdo->prepare($sql);
                $stmt->execute($values);

                g::run("log.warning", "Hard deleted from $table: " . $stmt->rowCount() . " rows");

                return true;
            } catch (PDOException $e) {
                g::run("log.error", "Delete failed: " . $e->getMessage());
                return false;
            }
        } else {
            // Soft delete (set state to 'deleted')
            return g::run("db.update", $table, array("state" => "deleted"), $where, $connection);
        }
    },

    /**
     * Select records
     * 
     * @param string $table Table name
     * @param array $where Where conditions
     * @param string $connection Connection name
     * @param array $options Additional options (limit, order, etc.)
     * @return array|false Results or false
     * 
     * @example $items = g::run("db.select", "items", array("type" => "post", "state" => "published"));
     * @example $items = g::run("db.select", "items", array("type" => "post"), "main", array("limit" => 10, "order" => "created_at DESC"));
     */
    "select" => function ($table, $where = array(), $connection = 'default', $options = array()) {
        $pdo = g::run("db.connection", $connection);
        if (!$pdo) {
            return false;
        }

        try {
            $whereClauses = array();
            $values = array();

            // Add clone_id if set
            $currentClone = g::get("db.current_clone");
            if ($currentClone && !isset($where['clone_id'])) {
                $where['clone_id'] = $currentClone;
            }

            foreach ($where as $key => $value) {
                $whereClauses[] = "$key = ?";
                $values[] = $value;
            }

            $sql = "SELECT * FROM $table";

            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }

            // Add ORDER BY
            if (isset($options['order'])) {
                $sql .= " ORDER BY " . $options['order'];
            }

            // Add LIMIT
            if (isset($options['limit'])) {
                $sql .= " LIMIT " . (int)$options['limit'];
            }

            // Add OFFSET
            if (isset($options['offset'])) {
                $sql .= " OFFSET " . (int)$options['offset'];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            $results = $stmt->fetchAll();

            // Decode JSON columns
            foreach ($results as &$row) {
                foreach ($row as $key => &$value) {
                    if (in_array($key, array('labels', 'meta', 'media', 'data', 'settings'))) {
                        $decoded = json_decode($value, true);
                        if ($decoded !== null) {
                            $value = $decoded;
                        }
                    }
                }
            }

            return $results;
        } catch (PDOException $e) {
            g::run("log.error", "Select failed: " . $e->getMessage());
            return false;
        }
    },

    /**
     * Get a single record by hash
     * 
     * @param string $table Table name
     * @param string $hash Record hash
     * @param string $connection Connection name
     * @return array|false Record or false
     * 
     * @example $item = g::run("db.get", "items", $hash);
     */
    "get" => function ($table, $hash, $connection = 'default') {
        $results = g::run("db.select", $table, array("hash" => $hash), $connection);

        if ($results && count($results) > 0) {
            return $results[0];
        }

        return false;
    },

    /**
     * Set current clone context
     * 
     * All subsequent queries will auto-filter by this clone_id.
     * 
     * @param string $cloneHash Clone hash
     * @return void
     * 
     * @example g::run("db.setClone", $cloneHash);
     */
    "setClone" => function ($cloneHash) {
        g::set("db.current_clone", $cloneHash);
        g::run("log.info", "Clone context set: $cloneHash");
    },

    /**
     * Create the genes schema (5 tables)
     * 
     * Creates: clones, persons, items, labels, events
     * 
     * @param string $connection Connection name
     * @return bool Success status
     * 
     * @example g::run("db.createSchema", "main");
     */
    "createSchema" => function ($connection = 'default') {
        $config = g::get("db.config.$connection");
        if (!$config) {
            g::run("log.error", "No config found for connection: $connection");
            return false;
        }

        $driver = isset($config['driver']) ? $config['driver'] : 'mysql';

        // Get appropriate schema
        $schema = g::run("db.getSchema", $driver);

        if (!$schema) {
            return false;
        }

        // Execute each table creation
        foreach ($schema as $tableName => $sql) {
            // Skip special keys
            if ($tableName === '_indexes') {
                continue;
            }

            $result = g::run("db.query", $sql, array(), $connection);
            if ($result === false) {
                g::run("log.error", "Failed to create table: $tableName");
                return false;
            }
            g::run("log.info", "Created table: $tableName");
        }

        // Execute indexes if present (SQLite)
        if (isset($schema['_indexes']) && is_array($schema['_indexes'])) {
            foreach ($schema['_indexes'] as $indexSql) {
                $result = g::run("db.query", $indexSql, array(), $connection);
                if ($result === false) {
                    g::run("log.warning", "Failed to create index");
                }
            }
            g::run("log.info", "Created indexes");
        }

        g::run("log.info", "Schema created successfully");

        // Check if setup is needed
        $mainConfig = g::get("config");
        if (!isset($mainConfig['setup']) || !isset($mainConfig['setup']['completed']) || $mainConfig['setup']['completed'] !== true) {
            g::run("log.info", "Running initial setup...");
            g::run("db.autoSeed", $connection);

            // Mark setup as completed
            $mainConfig['setup'] = array(
                'completed' => true,
                'completed_at' => date('Y-m-d H:i:s'),
                'version' => '1.0.0'
            );
            g::set("config", $mainConfig);

            // Save to config.json
            $configPath = DATA_FOLDER . "/config.json";
            file_put_contents($configPath, json_encode($mainConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            g::run("log.info", "Setup completed and saved to config.json");
        } else {
            g::run("log.info", "Setup already completed, skipping autoSeed");
        }

        return true;
    },

    /**
     * Auto-seed database with initial data
     * 
     * Seeds:
     * 1. Clone record (from config.clone) - only if not exists
     * 2. Admin user (from config.admin) - only if not exists
     * 3. Generic labels (person.state, item.state, etc.) - only if not exist
     * 4. Custom labels (from config.seed.labels) - only if not exist
     * 
     * Safe to run multiple times - checks for duplicates
     * 
     * @param string $connection Connection name
     * @return bool Success status
     */
    "autoSeed" => function ($connection = 'default') {
        g::run("log.info", "Auto-seeding database...");

        $config = g::get("config");

        // 1. Create clone record (only if not exists)
        if (isset($config['clone'])) {
            // Check if clone with this domain already exists
            $existingClone = g::run("db.select", "clones", array("domain" => $config['clone']['domain']), $connection, array("limit" => 1));

            if (empty($existingClone)) {
                $cloneData = array(
                    'name' => $config['clone']['name'],
                    'domain' => $config['clone']['domain'],
                    'type' => isset($config['clone']['type']) ? $config['clone']['type'] : 'platform',
                    'state' => isset($config['clone']['state']) ? $config['clone']['state'] : 'active'
                );

                // Add settings if exists
                if (isset($config['clone']['settings'])) {
                    $cloneData['settings'] = is_array($config['clone']['settings'])
                        ? json_encode($config['clone']['settings'])
                        : $config['clone']['settings'];
                }

                $cloneHash = g::run("db.insert", "clones", $cloneData, $connection);
                if ($cloneHash) {
                    g::run("log.info", "Created clone: " . $cloneData['name'] . " (hash: $cloneHash)");

                    // Set as current clone
                    g::set("db.current_clone", $cloneHash);

                    // Create setup event
                    $eventData = array(
                        'type' => 'clone.setup',
                        'ref1' => $cloneHash,
                        'data' => json_encode(array(
                            'action' => 'clone_created',
                            'domain' => $cloneData['domain'],
                            'timestamp' => date('Y-m-d H:i:s')
                        ))
                    );
                    g::run("db.insert", "events", $eventData, $connection);
                } else {
                    g::run("log.error", "Failed to create clone");
                }
            } else {
                g::run("log.info", "Clone already exists, skipping");
                // Set existing clone as current
                g::set("db.current_clone", $existingClone[0]['hash']);
            }
        } else {
            g::run("log.warning", "No clone config found in config.json");
        }

        // 2. Create admin user (only if not exists)
        if (isset($config['admin'])) {
            $adminConfig = $config['admin'];

            // Check if admin with this email already exists
            $existingAdmin = g::run("db.select", "persons", array("email" => $adminConfig['email']), $connection, array("limit" => 1));

            if (empty($existingAdmin)) {
                // Extract only the fields that match the persons schema
                $admin = array(
                    'email' => isset($adminConfig['email']) ? $adminConfig['email'] : '',
                    'alias' => isset($adminConfig['alias']) ? $adminConfig['alias'] : 'admin',
                    'name' => isset($adminConfig['name']) ? $adminConfig['name'] : 'Admin',
                    'type' => isset($adminConfig['type']) ? $adminConfig['type'] : 'admin',
                    'state' => isset($adminConfig['state']) ? $adminConfig['state'] : 'active'
                );

                // Hash password
                if (isset($adminConfig['password'])) {
                    $admin['password'] = g::run("auth.hash", $adminConfig['password']);
                }

                // Add meta
                $admin['meta'] = json_encode(array('role' => 'admin'));

                $adminHash = g::run("db.insert", "persons", $admin, $connection);
                if ($adminHash) {
                    g::run("log.info", "Created admin: " . $admin['email'] . " (hash: $adminHash)");

                    // Create admin setup event
                    $eventData = array(
                        'type' => 'admin.setup',
                        'person_id' => $adminHash,
                        'data' => json_encode(array(
                            'action' => 'admin_created',
                            'email' => $admin['email'],
                            'timestamp' => date('Y-m-d H:i:s')
                        ))
                    );
                    g::run("db.insert", "events", $eventData, $connection);
                } else {
                    g::run("log.error", "Failed to create admin user");
                }
            } else {
                g::run("log.info", "Admin user already exists, skipping");
            }
        } else {
            g::run("log.warning", "No admin config found in config.json");
        }

        // 3. Create generic labels
        $genericLabels = array(
            // Person states - using name field for dotted notation
            array("name" => "person.state.active", "key" => "active", "type" => "person.state"),
            array("name" => "person.state.suspended", "key" => "suspended", "type" => "person.state"),
            array("name" => "person.state.deleted", "key" => "deleted", "type" => "person.state"),

            // Person types
            array("name" => "person.type.user", "key" => "user", "type" => "person.type"),
            array("name" => "person.type.persona", "key" => "persona", "type" => "person.type"),

            // Item states
            array("name" => "item.state.draft", "key" => "draft", "type" => "item.state"),
            array("name" => "item.state.published", "key" => "published", "type" => "item.state"),
            array("name" => "item.state.deleted", "key" => "deleted", "type" => "item.state"),

            // Item types
            array("name" => "item.type.post", "key" => "post", "type" => "item.type"),
            array("name" => "item.type.page", "key" => "page", "type" => "item.type"),
            array("name" => "item.type.comment", "key" => "comment", "type" => "item.type")
        );

        $labelCount = 0;
        foreach ($genericLabels as $labelData) {
            // Check if label already exists
            $existingLabel = g::run("db.select", "labels", array("name" => $labelData['name']), $connection, array("limit" => 1));

            if (empty($existingLabel)) {
                $labelData['state'] = 'active';
                $labelData['meta'] = json_encode(array("generic" => true));

                $labelHash = g::run("db.insert", "labels", $labelData, $connection);
                if ($labelHash) {
                    $labelCount++;
                }
            }
        }
        g::run("log.info", "Created $labelCount generic labels (skipped existing)");

        // 4. Create custom labels from config
        if (isset($config['seed']['labels']) && is_array($config['seed']['labels'])) {
            $customCount = 0;
            foreach ($config['seed']['labels'] as $seedLabel) {
                // Map config format (name, label, value) to schema format (name, key, type)
                $labelData = array(
                    'name' => isset($seedLabel['name']) ? $seedLabel['name'] : '',
                    'key' => isset($seedLabel['value']) ? $seedLabel['value'] : (isset($seedLabel['key']) ? $seedLabel['key'] : ''),
                    'type' => isset($seedLabel['type']) ? $seedLabel['type'] : 'custom',
                    'state' => 'active'
                );

                // Check if label already exists
                $existingLabel = g::run("db.select", "labels", array("name" => $labelData['name']), $connection, array("limit" => 1));

                if (empty($existingLabel)) {
                    // Store the display label in meta
                    $meta = array('custom' => true);
                    if (isset($seedLabel['label'])) {
                        $meta['label'] = $seedLabel['label'];
                    }
                    $labelData['meta'] = json_encode($meta);

                    $labelHash = g::run("db.insert", "labels", $labelData, $connection);
                    if ($labelHash) {
                        $customCount++;
                    }
                }
            }
            g::run("log.info", "Created $customCount custom labels (skipped existing)");
        }

        // Create setup complete event
        $cloneHash = g::get("db.current_clone");
        if ($cloneHash) {
            $eventData = array(
                'type' => 'clone.setup_complete',
                'ref1' => $cloneHash,
                'data' => json_encode(array(
                    'action' => 'setup_completed',
                    'labels_created' => $labelCount,
                    'timestamp' => date('Y-m-d H:i:s')
                ))
            );
            g::run("db.insert", "events", $eventData, $connection);
        }

        g::run("log.info", "Auto-seed complete");
        return true;
    },

    /**
     * Get schema SQL for specific driver
     * 
     * @param string $driver Database driver
     * @return array|false Schema SQL statements
     */
    "getSchema" => function ($driver) {
        $schema = array();

        if ($driver === 'mysql') {
            $schema['clones'] = "CREATE TABLE IF NOT EXISTS clones (
                hash VARCHAR(32) PRIMARY KEY,
                type VARCHAR(50),
                state VARCHAR(20) DEFAULT 'active',
                name VARCHAR(255),
                domain VARCHAR(255),
                settings JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_by VARCHAR(32),
                updated_by VARCHAR(32),
                INDEX(state),
                INDEX(domain)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            $schema['persons'] = "CREATE TABLE IF NOT EXISTS persons (
                hash VARCHAR(32) PRIMARY KEY,
                clone_id VARCHAR(32),
                type VARCHAR(50),
                state VARCHAR(20) DEFAULT 'active',
                email VARCHAR(255) UNIQUE,
                alias VARCHAR(100),
                password VARCHAR(255),
                name VARCHAR(255),
                labels JSON,
                meta JSON,
                media JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_by VARCHAR(32),
                updated_by VARCHAR(32),
                INDEX(clone_id, state),
                INDEX(email),
                INDEX(alias)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            $schema['items'] = "CREATE TABLE IF NOT EXISTS items (
                hash VARCHAR(32) PRIMARY KEY,
                clone_id VARCHAR(32),
                type VARCHAR(50),
                state VARCHAR(20) DEFAULT 'active',
                title VARCHAR(500),
                link VARCHAR(1000),
                safe_url VARCHAR(500),
                blurb TEXT,
                text LONGTEXT,
                labels JSON,
                meta JSON,
                media JSON,
                data JSON,
                start_at TIMESTAMP NULL,
                end_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_by VARCHAR(32),
                updated_by VARCHAR(32),
                UNIQUE(clone_id, safe_url),
                INDEX(clone_id, type, state),
                INDEX(clone_id, safe_url),
                INDEX(start_at, end_at),
                INDEX(created_at),
                FULLTEXT(title, blurb, text)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            $schema['labels'] = "CREATE TABLE IF NOT EXISTS labels (
                hash VARCHAR(32) PRIMARY KEY,
                clone_id VARCHAR(32),
                type VARCHAR(50),
                state VARCHAR(20) DEFAULT 'active',
                `key` VARCHAR(255),
                name VARCHAR(255),
                labels JSON,
                meta JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_by VARCHAR(32),
                updated_by VARCHAR(32),
                UNIQUE(clone_id, type, `key`),
                INDEX(clone_id, type),
                INDEX(clone_id, `key`),
                INDEX(name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            $schema['events'] = "CREATE TABLE IF NOT EXISTS events (
                hash VARCHAR(32) PRIMARY KEY,
                clone_id VARCHAR(32) NOT NULL,
                person_id VARCHAR(32),
                item_id VARCHAR(32),
                type VARCHAR(50) NOT NULL,
                state VARCHAR(20) DEFAULT 'active',
                ref1 VARCHAR(255),
                ref2 VARCHAR(255),
                ref3 VARCHAR(255),
                ref4 VARCHAR(255),
                labels JSON,
                data JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX(clone_id, type, created_at),
                INDEX(person_id, created_at),
                INDEX(item_id, created_at),
                INDEX(ref1),
                INDEX(ref2),
                INDEX(created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        } elseif ($driver === 'sqlite') {
            $schema['clones'] = "CREATE TABLE IF NOT EXISTS clones (
                hash TEXT PRIMARY KEY,
                type TEXT,
                state TEXT DEFAULT 'active',
                name TEXT,
                domain TEXT,
                settings TEXT,
                created_at TEXT,
                updated_at TEXT,
                created_by TEXT,
                updated_by TEXT
            )";

            $schema['persons'] = "CREATE TABLE IF NOT EXISTS persons (
                hash TEXT PRIMARY KEY,
                clone_id TEXT,
                type TEXT,
                state TEXT DEFAULT 'active',
                email TEXT UNIQUE,
                alias TEXT,
                password TEXT,
                name TEXT,
                labels TEXT,
                meta TEXT,
                media TEXT,
                created_at TEXT,
                updated_at TEXT,
                created_by TEXT,
                updated_by TEXT
            )";

            $schema['items'] = "CREATE TABLE IF NOT EXISTS items (
                hash TEXT PRIMARY KEY,
                clone_id TEXT,
                type TEXT,
                state TEXT DEFAULT 'active',
                title TEXT,
                link TEXT,
                safe_url TEXT,
                blurb TEXT,
                text TEXT,
                labels TEXT,
                meta TEXT,
                media TEXT,
                data TEXT,
                start_at TEXT,
                end_at TEXT,
                created_at TEXT,
                updated_at TEXT,
                created_by TEXT,
                updated_by TEXT,
                UNIQUE(clone_id, safe_url)
            )";

            $schema['labels'] = "CREATE TABLE IF NOT EXISTS labels (
                hash TEXT PRIMARY KEY,
                clone_id TEXT,
                type TEXT,
                state TEXT DEFAULT 'active',
                key TEXT,
                name TEXT,
                labels TEXT,
                meta TEXT,
                created_at TEXT,
                updated_at TEXT,
                created_by TEXT,
                updated_by TEXT,
                UNIQUE(clone_id, type, key)
            )";

            $schema['events'] = "CREATE TABLE IF NOT EXISTS events (
                hash TEXT PRIMARY KEY,
                clone_id TEXT NOT NULL,
                person_id TEXT,
                item_id TEXT,
                type TEXT NOT NULL,
                state TEXT DEFAULT 'active',
                ref1 TEXT,
                ref2 TEXT,
                ref3 TEXT,
                ref4 TEXT,
                labels TEXT,
                data TEXT,
                created_at TEXT,
                updated_at TEXT
            )";

            // Create indexes for SQLite
            $schema['_indexes'] = array(
                "CREATE INDEX IF NOT EXISTS idx_clones_state ON clones(state)",
                "CREATE INDEX IF NOT EXISTS idx_clones_domain ON clones(domain)",
                "CREATE INDEX IF NOT EXISTS idx_persons_clone ON persons(clone_id, state)",
                "CREATE INDEX IF NOT EXISTS idx_persons_email ON persons(email)",
                "CREATE INDEX IF NOT EXISTS idx_persons_alias ON persons(alias)",
                "CREATE INDEX IF NOT EXISTS idx_items_clone_type ON items(clone_id, type, state)",
                "CREATE INDEX IF NOT EXISTS idx_items_safe_url ON items(clone_id, safe_url)",
                "CREATE INDEX IF NOT EXISTS idx_items_created ON items(created_at)",
                "CREATE INDEX IF NOT EXISTS idx_labels_clone_type ON labels(clone_id, type)",
                "CREATE INDEX IF NOT EXISTS idx_labels_key ON labels(clone_id, key)",
                "CREATE INDEX IF NOT EXISTS idx_events_clone_type ON events(clone_id, type, created_at)",
                "CREATE INDEX IF NOT EXISTS idx_events_person ON events(person_id, created_at)",
                "CREATE INDEX IF NOT EXISTS idx_events_item ON events(item_id, created_at)",
                "CREATE INDEX IF NOT EXISTS idx_events_ref1 ON events(ref1)",
                "CREATE INDEX IF NOT EXISTS idx_events_created ON events(created_at)"
            );
        }
        // Add PostgreSQL schemas here in future

        return $schema;
    },

));

// ============================================================================
// PHASE 7: DATABASE CRUD OPERATIONS
// ============================================================================

g::def("db.crud", array(

    /**
     * List records with pagination, filtering, and search
     * 
     * Generic list function that works for all tables.
     * Supports pagination, filtering, search, and sorting.
     * 
     * @param string $table Table name
     * @param array $filters WHERE conditions
     * @param array $options Options (page, limit, search, order)
     * @param string $connection Connection name
     * @return array Result with data, total, page info
     * 
     * @example $result = g::run("db.crud.list", "items", 
     *     array("type" => "post", "state" => "published"),
     *     array("page" => 1, "limit" => 10, "order" => "created_at DESC")
     * );
     * @example $result = g::run("db.crud.list", "items",
     *     array("type" => "post"),
     *     array("search" => "genes", "searchFields" => array("title", "blurb"))
     * );
     */
    "list" => function ($table, $filters = array(), $options = array(), $connection = 'default') {
        $pdo = g::run("db.connection", $connection);
        if (!$pdo) {
            return array('success' => false, 'error' => 'No database connection');
        }

        // Default options
        $page = isset($options['page']) ? max(1, (int)$options['page']) : 1;
        $limit = isset($options['limit']) ? max(1, min(1000, (int)$options['limit'])) : 50;
        $offset = ($page - 1) * $limit;
        $order = isset($options['order']) ? $options['order'] : 'created_at DESC';
        $search = isset($options['search']) ? trim($options['search']) : '';
        $searchFields = isset($options['searchFields']) ? $options['searchFields'] : array('title', 'name', 'blurb', 'text');

        try {
            // Build WHERE clause
            $whereClauses = array();
            $values = array();

            // Add clone_id if set
            $currentClone = g::get("db.current_clone");
            if ($currentClone && !isset($filters['clone_id'])) {
                $filters['clone_id'] = $currentClone;
            }

            // Add filters
            foreach ($filters as $key => $value) {
                if (is_array($value)) {
                    // IN clause
                    $placeholders = array_fill(0, count($value), '?');
                    $whereClauses[] = "$key IN (" . implode(',', $placeholders) . ")";
                    $values = array_merge($values, $value);
                } else {
                    $whereClauses[] = "$key = ?";
                    $values[] = $value;
                }
            }

            // Add search
            if ($search !== '') {
                $searchClauses = array();
                foreach ($searchFields as $field) {
                    $searchClauses[] = "$field LIKE ?";
                    $values[] = "%$search%";
                }
                if (!empty($searchClauses)) {
                    $whereClauses[] = "(" . implode(' OR ', $searchClauses) . ")";
                }
            }

            // Build SQL
            $whereSQL = !empty($whereClauses) ? " WHERE " . implode(' AND ', $whereClauses) : "";

            // Get total count
            $countSQL = "SELECT COUNT(*) as total FROM $table" . $whereSQL;
            $stmt = $pdo->prepare($countSQL);
            $stmt->execute($values);
            $total = (int)$stmt->fetch()['total'];

            // Get data
            $dataSQL = "SELECT * FROM $table" . $whereSQL . " ORDER BY $order LIMIT $limit OFFSET $offset";
            $stmt = $pdo->prepare($dataSQL);
            $stmt->execute($values);
            $data = $stmt->fetchAll();

            // Decode JSON columns
            foreach ($data as &$row) {
                foreach ($row as $key => &$value) {
                    if (in_array($key, array('labels', 'meta', 'media', 'data', 'settings'))) {
                        $decoded = json_decode($value, true);
                        if ($decoded !== null) {
                            $value = $decoded;
                        }
                    }
                }
            }

            return array(
                'success' => true,
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit),
                'showing' => count($data)
            );
        } catch (PDOException $e) {
            g::run("log.error", "CRUD list failed: " . $e->getMessage());
            return array('success' => false, 'error' => $e->getMessage());
        }
    },

    /**
     * Count records with filters
     * 
     * @param string $table Table name
     * @param array $filters WHERE conditions
     * @param string $connection Connection name
     * @return int|false Count or false
     * 
     * @example $count = g::run("db.crud.count", "items", array("type" => "post"));
     */
    "count" => function ($table, $filters = array(), $connection = 'default') {
        $pdo = g::run("db.connection", $connection);
        if (!$pdo) {
            return false;
        }

        try {
            $whereClauses = array();
            $values = array();

            // Add clone_id if set
            $currentClone = g::get("db.current_clone");
            if ($currentClone && !isset($filters['clone_id'])) {
                $filters['clone_id'] = $currentClone;
            }

            foreach ($filters as $key => $value) {
                $whereClauses[] = "$key = ?";
                $values[] = $value;
            }

            $sql = "SELECT COUNT(*) as total FROM $table";
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            return (int)$stmt->fetch()['total'];
        } catch (PDOException $e) {
            g::run("log.error", "CRUD count failed: " . $e->getMessage());
            return false;
        }
    },

    /**
     * Full-text search across tables
     * 
     * @param string $table Table name
     * @param string $query Search query
     * @param array $fields Fields to search
     * @param array $options Options (limit, filters)
     * @param string $connection Connection name
     * @return array|false Results or false
     * 
     * @example $results = g::run("db.crud.search", "items", "genes framework", 
     *     array("title", "blurb", "text"),
     *     array("limit" => 20, "filters" => array("type" => "post"))
     * );
     */
    "search" => function ($table, $query, $fields = array(), $options = array(), $connection = 'default') {
        if (empty($query)) {
            return array();
        }

        $pdo = g::run("db.connection", $connection);
        if (!$pdo) {
            return false;
        }

        $limit = isset($options['limit']) ? max(1, min(1000, (int)$options['limit'])) : 50;
        $filters = isset($options['filters']) ? $options['filters'] : array();

        try {
            $whereClauses = array();
            $values = array();

            // Add clone_id if set
            $currentClone = g::get("db.current_clone");
            if ($currentClone && !isset($filters['clone_id'])) {
                $filters['clone_id'] = $currentClone;
            }

            // Add filters
            foreach ($filters as $key => $value) {
                $whereClauses[] = "$key = ?";
                $values[] = $value;
            }

            // Add search
            $searchClauses = array();
            foreach ($fields as $field) {
                $searchClauses[] = "$field LIKE ?";
                $values[] = "%$query%";
            }

            if (!empty($searchClauses)) {
                $whereClauses[] = "(" . implode(' OR ', $searchClauses) . ")";
            }

            $sql = "SELECT * FROM $table";
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }
            $sql .= " LIMIT $limit";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            $results = $stmt->fetchAll();

            // Decode JSON columns
            foreach ($results as &$row) {
                foreach ($row as $key => &$value) {
                    if (in_array($key, array('labels', 'meta', 'media', 'data', 'settings'))) {
                        $decoded = json_decode($value, true);
                        if ($decoded !== null) {
                            $value = $decoded;
                        }
                    }
                }
            }

            return $results;
        } catch (PDOException $e) {
            g::run("log.error", "CRUD search failed: " . $e->getMessage());
            return false;
        }
    },

    /**
     * Validate data against table schema
     * 
     * @param string $table Table name
     * @param array $data Data to validate
     * @param bool $isUpdate Is this an update operation
     * @return array Result with valid flag and errors
     * 
     * @example $validation = g::run("db.crud.validate", "items", $data);
     */
    "validate" => function ($table, $data, $isUpdate = false) {
        $errors = array();

        // Define required fields per table
        $requiredFields = array(
            'clones' => array('name'),
            'persons' => array('email'),
            'items' => array('title'),
            'labels' => array('name'),
            'events' => array('type')
        );

        // Check required fields (only on insert)
        if (!$isUpdate && isset($requiredFields[$table])) {
            foreach ($requiredFields[$table] as $field) {
                if (!isset($data[$field]) || trim($data[$field]) === '') {
                    $errors[] = "Field '$field' is required";
                }
            }
        }

        // Email validation for persons
        if ($table === 'persons' && isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format";
            }
        }

        // URL validation
        if (isset($data['link']) && !empty($data['link'])) {
            if (!filter_var($data['link'], FILTER_VALIDATE_URL)) {
                $errors[] = "Invalid URL format for 'link'";
            }
        }

        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    },

    /**
     * Export table data to JSON or CSV
     * 
     * @param string $table Table name
     * @param array $filters WHERE conditions
     * @param string $format Format (json or csv)
     * @param string $connection Connection name
     * @return string|false Exported data or false
     * 
     * @example $json = g::run("db.crud.export", "items", array("type" => "post"), "json");
     * @example $csv = g::run("db.crud.export", "labels", array(), "csv");
     */
    "export" => function ($table, $filters = array(), $format = 'json', $connection = 'default') {
        $data = g::run("db.select", $table, $filters, $connection, array('limit' => 10000));

        if ($data === false) {
            return false;
        }

        if ($format === 'json') {
            return json_encode($data, JSON_PRETTY_PRINT);
        } elseif ($format === 'csv') {
            if (empty($data)) {
                return '';
            }

            $output = fopen('php://temp', 'r+');

            // Header row
            fputcsv($output, array_keys($data[0]));

            // Data rows
            foreach ($data as $row) {
                // Convert arrays to JSON in CSV
                foreach ($row as $key => $value) {
                    if (is_array($value)) {
                        $row[$key] = json_encode($value);
                    }
                }
                fputcsv($output, $row);
            }

            rewind($output);
            $csv = stream_get_contents($output);
            fclose($output);

            return $csv;
        }

        return false;
    },

    /**
     * Import data from JSON or CSV
     * 
     * @param string $table Table name
     * @param string $data Data to import
     * @param string $format Format (json or csv)
     * @param string $connection Connection name
     * @return array Result with success count and errors
     * 
     * @example $result = g::run("db.crud.import", "items", $jsonData, "json");
     */
    "import" => function ($table, $data, $format = 'json', $connection = 'default') {
        $records = array();
        $success = 0;
        $errors = array();

        try {
            if ($format === 'json') {
                $records = json_decode($data, true);
                if ($records === null) {
                    return array('success' => false, 'error' => 'Invalid JSON format');
                }
            } elseif ($format === 'csv') {
                $lines = explode("\n", $data);
                $headers = str_getcsv(array_shift($lines));

                foreach ($lines as $line) {
                    if (trim($line) === '') continue;
                    $values = str_getcsv($line);
                    $record = array();
                    foreach ($headers as $i => $header) {
                        $value = isset($values[$i]) ? $values[$i] : '';
                        // Try to decode JSON values
                        $decoded = json_decode($value, true);
                        $record[$header] = ($decoded !== null) ? $decoded : $value;
                    }
                    $records[] = $record;
                }
            }

            // Import each record
            foreach ($records as $record) {
                // Validate
                $validation = g::run("db.crud.validate", $table, $record);
                if (!$validation['valid']) {
                    $errors[] = array('record' => $record, 'errors' => $validation['errors']);
                    continue;
                }

                // Insert
                $hash = g::run("db.insert", $table, $record, $connection);
                if ($hash) {
                    $success++;
                } else {
                    $errors[] = array('record' => $record, 'error' => 'Insert failed');
                }
            }

            return array(
                'success' => true,
                'imported' => $success,
                'total' => count($records),
                'errors' => $errors
            );
        } catch (Exception $e) {
            g::run("log.error", "CRUD import failed: " . $e->getMessage());
            return array('success' => false, 'error' => $e->getMessage());
        }
    },

));

// ============================================================================
// PHASE 7.5: API SYSTEM
// ============================================================================

g::def("api", array(

    /**
     * Main API request handler
     * 
     * Handles REST API requests for CRUD operations.
     * Routes requests to appropriate handlers.
     * 
     * @param string $path API path
     * @param string $method HTTP method
     * @param array $data Request data
     * @return array API response
     * 
     * @example $response = g::run("api.handle", "/items", "GET", array("type" => "post"));
     * @example $response = g::run("api.handle", "/items/abc123", "GET");
     * @example $response = g::run("api.handle", "/items", "POST", $data);
     */
    "handle" => function ($path = null, $method = null, $data = null) {
        // Auto-detect from request if not provided
        if ($path === null) {
            $path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
        }
        if ($method === null) {
            $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        }
        if ($data === null) {
            if ($method === 'GET') {
                $data = $_GET;
            } elseif ($method === 'POST' || $method === 'PUT') {
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);
                if ($data === null) {
                    $data = $_POST;
                }
            }
        }

        // Remove leading/trailing slashes
        $path = trim($path, '/');
        $parts = explode('/', $path);

        // Get table name (first part)
        $table = isset($parts[0]) ? $parts[0] : '';

        // Valid tables
        $validTables = array('clones', 'persons', 'items', 'labels', 'events');
        if (!in_array($table, $validTables)) {
            return array('success' => false, 'error' => 'Invalid table');
        }

        // Check if specific hash is provided
        $hash = isset($parts[1]) ? $parts[1] : null;

        // Route to appropriate handler
        try {
            switch ($method) {
                case 'GET':
                    if ($hash) {
                        // Get single record
                        $record = g::run("db.get", $table, $hash);
                        if ($record) {
                            return array('success' => true, 'data' => $record);
                        } else {
                            return array('success' => false, 'error' => 'Record not found');
                        }
                    } else {
                        // List records
                        $filters = isset($data['filters']) ? $data['filters'] : array();
                        $options = array(
                            'page' => isset($data['page']) ? $data['page'] : 1,
                            'limit' => isset($data['limit']) ? $data['limit'] : 50,
                            'order' => isset($data['order']) ? $data['order'] : 'created_at DESC',
                            'search' => isset($data['search']) ? $data['search'] : '',
                            'searchFields' => isset($data['searchFields']) ? $data['searchFields'] : array('title', 'name', 'blurb')
                        );
                        return g::run("db.crud.list", $table, $filters, $options);
                    }
                    break;

                case 'POST':
                    // Create new record
                    $validation = g::run("db.crud.validate", $table, $data);
                    if (!$validation['valid']) {
                        return array('success' => false, 'errors' => $validation['errors']);
                    }

                    $newHash = g::run("db.insert", $table, $data);
                    if ($newHash) {
                        $record = g::run("db.get", $table, $newHash);
                        return array('success' => true, 'data' => $record, 'hash' => $newHash);
                    } else {
                        return array('success' => false, 'error' => 'Insert failed');
                    }
                    break;

                case 'PUT':
                case 'PATCH':
                    // Update record
                    if (!$hash) {
                        return array('success' => false, 'error' => 'Hash required for update');
                    }

                    $validation = g::run("db.crud.validate", $table, $data, true);
                    if (!$validation['valid']) {
                        return array('success' => false, 'errors' => $validation['errors']);
                    }

                    $updated = g::run("db.update", $table, $data, array('hash' => $hash));
                    if ($updated) {
                        $record = g::run("db.get", $table, $hash);
                        return array('success' => true, 'data' => $record);
                    } else {
                        return array('success' => false, 'error' => 'Update failed');
                    }
                    break;

                case 'DELETE':
                    // Delete record
                    if (!$hash) {
                        return array('success' => false, 'error' => 'Hash required for delete');
                    }

                    $hard = isset($data['hard']) && $data['hard'] === true;
                    $deleted = g::run("db.delete", $table, array('hash' => $hash), $hard);

                    if ($deleted) {
                        return array('success' => true, 'message' => 'Record deleted');
                    } else {
                        return array('success' => false, 'error' => 'Delete failed');
                    }
                    break;

                default:
                    return array('success' => false, 'error' => 'Method not allowed');
            }
        } catch (Exception $e) {
            g::run("log.error", "API error: " . $e->getMessage());
            return array('success' => false, 'error' => 'Internal server error');
        }
    },

    /**
     * Send JSON response
     * 
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     * @return void
     * 
     * @example g::run("api.respond", array("success" => true, "data" => $items));
     */
    "respond" => function ($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    },

    /**
     * Check API authentication
     * 
     * @return bool Authenticated status
     * 
     * @example if (g::run("api.checkAuth")) { ... }
     */
    "checkAuth" => function () {
        // Check for auth header
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

        if (empty($authHeader)) {
            return false;
        }

        // Parse Bearer token
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            // Verify token (implement your logic here)
            // For now, return true if token exists
            return !empty($token);
        }

        return false;
    },

));

// ============================================================================
// PHASE 7.75: AUTHENTICATION SYSTEM
// ============================================================================

g::def("auth", array(

    /**
     * Initialize authentication system
     * 
     * Starts secure session and loads auth config.
     * Call this at the start of your app if using auth.
     * 
     * @return bool Success status
     * 
     * @example g::run("auth.init");
     */
    "init" => function () {
        // Check if session already started
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }

        // Get config for session name
        $config = g::run("config.get");
        $sessionName = isset($config['settings']['session_name']) ? $config['settings']['session_name'] : 'genes_session';

        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Lax');

        // Use secure cookies if HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
        }

        // Set session name and start
        session_name($sessionName);

        if (session_start()) {
            g::run("log.info", "Authentication session initialized");

            // Regenerate session ID periodically for security
            if (!isset($_SESSION['created_at'])) {
                $_SESSION['created_at'] = time();
            } elseif (time() - $_SESSION['created_at'] > 3600) {
                session_regenerate_id(true);
                $_SESSION['created_at'] = time();
            }

            return true;
        }

        g::run("log.error", "Failed to start authentication session");
        return false;
    },

    /**
     * Get authentication mode from config
     * 
     * @return string 'config' or 'database'
     * 
     * @example $mode = g::run("auth.mode");
     */
    "mode" => function () {
        $config = g::run("config.get");

        if (isset($config['auth']['mode'])) {
            return $config['auth']['mode'];
        }

        // Default to config mode
        return 'config';
    },

    /**
     * Hash password securely
     * 
     * Uses PASSWORD_BCRYPT or PASSWORD_ARGON2ID if available.
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     * 
     * @example $hash = g::run("auth.hash", "mypassword123");
     */
    "hash" => function ($password) {
        // Use ARGON2ID if available (PHP 7.3+), otherwise BCRYPT
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID);
        }

        return password_hash($password, PASSWORD_BCRYPT);
    },

    /**
     * Verify password against hash
     * 
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool True if password matches
     * 
     * @example if (g::run("auth.verify", $password, $hash)) { ... }
     */
    "verify" => function ($password, $hash) {
        return password_verify($password, $hash);
    },

    /**
     * Login user (config or database mode)
     * 
     * Handles both config-based and database-based authentication.
     * Includes rate limiting and login attempt tracking.
     * 
     * @param string $username Username or email
     * @param string $password Plain text password
     * @return array Result with success status and user data or error
     * 
     * @example $result = g::run("auth.login", "admin", "password123");
     */
    "login" => function ($username, $password) {
        $config = g::run("config.get");
        $mode = g::run("auth.mode");

        // Check if auth is enabled
        if (isset($config['auth']['enabled']) && !$config['auth']['enabled']) {
            return array('success' => false, 'error' => 'Authentication is disabled');
        }

        // Rate limiting check
        $sessionKey = 'login_attempts_' . md5($username);
        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = array('count' => 0, 'last_attempt' => 0);
        }

        $attempts = &$_SESSION[$sessionKey];
        $maxAttempts = isset($config['auth']['max_login_attempts']) ? $config['auth']['max_login_attempts'] : 5;
        $lockoutDuration = isset($config['auth']['lockout_duration']) ? $config['auth']['lockout_duration'] : 900;

        // Check if locked out
        if ($attempts['count'] >= $maxAttempts) {
            $timeSinceLastAttempt = time() - $attempts['last_attempt'];
            if ($timeSinceLastAttempt < $lockoutDuration) {
                $remainingTime = $lockoutDuration - $timeSinceLastAttempt;
                return array(
                    'success' => false,
                    'error' => "Too many failed attempts. Try again in " . ceil($remainingTime / 60) . " minutes."
                );
            } else {
                // Reset after lockout period
                $attempts['count'] = 0;
            }
        }

        $attempts['last_attempt'] = time();

        // Authenticate based on mode
        $user = false;

        if ($mode === 'config') {
            // Config-based authentication
            $admins = isset($config['admins']) ? $config['admins'] : array();

            foreach ($admins as $admin) {
                if ($admin['username'] === $username || (isset($admin['email']) && $admin['email'] === $username)) {
                    if (g::run("auth.verify", $password, $admin['password'])) {
                        $user = $admin;
                        break;
                    }
                }
            }
        } elseif ($mode === 'database') {
            // Database-based authentication
            $pdo = g::run("db.connection");
            if (!$pdo) {
                return array('success' => false, 'error' => 'Database connection failed');
            }

            // Find user by username or email
            $users = g::run("db.select", "persons", array("alias" => $username));
            if (empty($users)) {
                $users = g::run("db.select", "persons", array("email" => $username));
            }

            if (!empty($users)) {
                $dbUser = $users[0];
                if (isset($dbUser['password']) && g::run("auth.verify", $password, $dbUser['password'])) {
                    $user = $dbUser;

                    // Update last login time
                    $meta = isset($user['meta']) ? $user['meta'] : array();
                    $meta['last_login'] = g::run("core.now");
                    $meta['login_attempts'] = 0;

                    g::run(
                        "db.update",
                        "persons",
                        array("meta" => $meta),
                        array("hash" => $user['hash'])
                    );
                }
            }
        } elseif ($mode === 'file') {
            // File-based authentication (users.json)
            $fileUser = g::run("auth.getUser", $username);
            if ($fileUser && isset($fileUser['password'])) {
                if (g::run("auth.verify", $password, $fileUser['password'])) {
                    $user = $fileUser;
                }
            }
        }

        // Fallback: Try all auth methods if mode is not set or user not found yet
        if (!$user && $mode !== 'config' && $mode !== 'database' && $mode !== 'file') {
            // Try config first
            $admins = isset($config['admins']) ? $config['admins'] : array();
            foreach ($admins as $admin) {
                if ($admin['username'] === $username || (isset($admin['email']) && $admin['email'] === $username)) {
                    if (g::run("auth.verify", $password, $admin['password'])) {
                        $user = $admin;
                        break;
                    }
                }
            }

            // Try file-based if config didn't work
            if (!$user) {
                $fileUser = g::run("auth.getUser", $username);
                if ($fileUser && isset($fileUser['password'])) {
                    if (g::run("auth.verify", $password, $fileUser['password'])) {
                        $user = $fileUser;
                    }
                }
            }

            // Try database last if enabled
            if (!$user) {
                $pdo = g::run("db.connection");
                if ($pdo) {
                    $users = g::run("db.select", "persons", array("alias" => $username));
                    if (empty($users)) {
                        $users = g::run("db.select", "persons", array("email" => $username));
                    }

                    if (!empty($users)) {
                        $dbUser = $users[0];
                        if (isset($dbUser['password']) && g::run("auth.verify", $password, $dbUser['password'])) {
                            $user = $dbUser;
                        }
                    }
                }
            }
        }

        // Check if authentication succeeded
        if ($user) {
            // Reset attempts on success
            $attempts['count'] = 0;

            // Store user in session
            $_SESSION['auth_user'] = $user;
            $_SESSION['auth_time'] = time();

            // Generate CSRF token
            $_SESSION['csrf_token'] = g::run("auth.generateToken");

            g::run("log.info", "User logged in: " . $username);

            return array(
                'success' => true,
                'user' => $user
            );
        } else {
            // Increment failed attempts
            $attempts['count']++;

            g::run("log.warning", "Failed login attempt: " . $username);

            return array(
                'success' => false,
                'error' => 'Invalid username or password'
            );
        }
    },

    /**
     * Logout current user
     * 
     * Clears session data and destroys session.
     * 
     * @return bool Success status
     * 
     * @example g::run("auth.logout");
     */
    "logout" => function () {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Clear auth data
            unset($_SESSION['auth_user']);
            unset($_SESSION['auth_time']);
            unset($_SESSION['csrf_token']);

            // Regenerate session ID
            session_regenerate_id(true);

            g::run("log.info", "User logged out");
            return true;
        }

        return false;
    },

    /**
     * Check if user is logged in
     * 
     * Also checks session timeout.
     * 
     * @return bool True if user is authenticated
     * 
     * @example if (g::run("auth.check")) { ... }
     */
    "check" => function () {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        if (!isset($_SESSION['auth_user']) || !isset($_SESSION['auth_time'])) {
            return false;
        }

        // Check session timeout
        $config = g::run("config.get");
        $timeout = isset($config['auth']['session_timeout']) ? $config['auth']['session_timeout'] : 3600;

        if (time() - $_SESSION['auth_time'] > $timeout) {
            g::run("auth.logout");
            return false;
        }

        // Update last activity time
        $_SESSION['auth_time'] = time();

        return true;
    },

    /**
     * Get current logged-in user
     * 
     * @return array|false User data or false if not logged in
     * 
     * @example $user = g::run("auth.user");
     */
    "user" => function () {
        if (!g::run("auth.check")) {
            return false;
        }

        return isset($_SESSION['auth_user']) ? $_SESSION['auth_user'] : false;
    },

    /**
     * Check if user can access a route
     * 
     * Uses route patterns from config with wildcard matching.
     * 
     * @param string $route Route to check (e.g., '/admin/items/create')
     * @return bool True if user has access
     * 
     * @example if (g::run("auth.can", "/admin/items/create")) { ... }
     */
    "can" => function ($route) {
        $user = g::run("auth.user");
        if (!$user) {
            return false;
        }

        // Get user's role
        $role = isset($user['role']) ? $user['role'] : 'viewer';

        // Get config
        $config = g::run("config.get");

        // Get routes for this role
        $allowedRoutes = isset($config['routes'][$role]) ? $config['routes'][$role] : array();

        // Check for wildcard (super admin)
        if (in_array('*', $allowedRoutes)) {
            return true;
        }

        // Check exact match
        if (in_array($route, $allowedRoutes)) {
            return true;
        }

        // Check pattern matches
        foreach ($allowedRoutes as $pattern) {
            // Convert route pattern to regex
            // /admin/* becomes /^\/admin\/.*$/
            // /admin/items/* becomes /^\/admin\/items\/.*$/
            $regex = '/^' . str_replace(
                array('/', '*'),
                array('\/', '.*'),
                $pattern
            ) . '$/';

            if (preg_match($regex, $route)) {
                return true;
            }
        }

        return false;
    },

    /**
     * Require authentication (or redirect/exit)
     * 
     * @param string $redirect Redirect URL if not logged in
     * @return void
     * 
     * @example g::run("auth.require", "/login");
     */
    "require" => function ($redirect = '/admin/login') {
        if (!g::run("auth.check")) {
            header("Location: $redirect");
            exit;
        }
    },

    /**
     * Require access to specific route (or redirect/exit)
     * 
     * @param string $route Route pattern to check
     * @param string $redirect Redirect URL if no access
     * @return void
     * 
     * @example g::run("auth.requireRoute", "/admin/items/delete");
     */
    "requireRoute" => function ($route, $redirect = '/admin') {
        if (!g::run("auth.check")) {
            header("Location: /admin/login");
            exit;
        }

        if (!g::run("auth.can", $route)) {
            g::run("log.warning", "Access denied to route: $route");
            header("Location: $redirect");
            exit;
        }
    },

    /**
     * Generate session token (for CSRF protection)
     * 
     * @return string Random token
     * 
     * @example $token = g::run("auth.generateToken");
     */
    "generateToken" => function () {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(32));
        } else {
            return bin2hex(openssl_random_pseudo_bytes(32));
        }
    },

    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool True if token is valid
     * 
     * @example if (g::run("auth.validateToken", $_POST['csrf_token'])) { ... }
     */
    "validateToken" => function ($token) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        // Use constant-time comparison to prevent timing attacks
        return hash_equals($_SESSION['csrf_token'], $token);
    },

    // ========================================================================
    // FILE-BASED USER MANAGEMENT (for clones without databases)
    // ========================================================================

    /**
     * Load users from JSON file
     * 
     * File-based user storage for simple setups.
     * 
     * @return array Users data
     * 
     * @example $users = g::run("auth.loadUsers");
     */
    "loadUsers" => function () {
        $usersFile = DATA_FOLDER . 'users.json';

        if (!file_exists($usersFile)) {
            // Create default users file with empty array
            $defaultUsers = array();
            g::run("config.writeJson", $defaultUsers, $usersFile);
            return $defaultUsers;
        }

        $users = g::run("config.readJson", $usersFile);
        return is_array($users) ? $users : array();
    },

    /**
     * Save users to JSON file
     * 
     * @param array $users Users data
     * @return bool Success status
     * 
     * @example g::run("auth.saveUsers", $users);
     */
    "saveUsers" => function ($users) {
        $usersFile = DATA_FOLDER . 'users.json';
        return g::run("config.writeJson", $users, $usersFile);
    },

    /**
     * Get user by username from file
     * 
     * @param string $username Username to find
     * @return array|null User data or null
     * 
     * @example $user = g::run("auth.getUser", "john");
     */
    "getUser" => function ($username) {
        $users = g::run("auth.loadUsers");

        foreach ($users as $user) {
            if (isset($user['username']) && $user['username'] === $username) {
                return $user;
            }
        }

        return null;
    },

    /**
     * Create or update a user in file
     * 
     * Creates new user or updates existing one.
     * Automatically hashes password if provided.
     * 
     * @param array $userData User data (username, password, role, etc.)
     * @return bool Success status
     * 
     * @example g::run("auth.saveUser", [
     *     "username" => "john",
     *     "password" => "secret123",
     *     "role" => "user",
     *     "email" => "john@example.com"
     * ]);
     */
    "saveUser" => function ($userData) {
        if (!isset($userData['username']) || empty($userData['username'])) {
            g::run("log.error", "Cannot save user: username required");
            return false;
        }

        $users = g::run("auth.loadUsers");
        $username = $userData['username'];
        $found = false;

        // Hash password if provided and not already hashed
        if (isset($userData['password']) && !empty($userData['password'])) {
            // Check if already hashed (bcrypt/argon2)
            if (substr($userData['password'], 0, 1) !== '$') {
                $userData['password'] = g::run("auth.hash", $userData['password']);
            }
        }

        // Set default role if not provided
        if (!isset($userData['role'])) {
            $userData['role'] = 'user';
        }

        // Add timestamps
        $now = date('Y-m-d H:i:s');
        if (!isset($userData['created_at'])) {
            $userData['created_at'] = $now;
        }
        $userData['updated_at'] = $now;

        // Update existing user or add new
        for ($i = 0; $i < count($users); $i++) {
            if ($users[$i]['username'] === $username) {
                // Keep created_at from original
                $userData['created_at'] = $users[$i]['created_at'];
                // Keep password if not provided
                if (!isset($userData['password']) || empty($userData['password'])) {
                    $userData['password'] = $users[$i]['password'];
                }
                $users[$i] = $userData;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $users[] = $userData;
        }

        $result = g::run("auth.saveUsers", $users);

        if ($result) {
            g::run("log.info", "User saved to file: $username");
        }

        return $result;
    },

    /**
     * Delete a user from file
     * 
     * @param string $username Username to delete
     * @return bool Success status
     * 
     * @example g::run("auth.deleteUser", "john");
     */
    "deleteUser" => function ($username) {
        $users = g::run("auth.loadUsers");
        $newUsers = array();
        $found = false;

        foreach ($users as $user) {
            if ($user['username'] !== $username) {
                $newUsers[] = $user;
            } else {
                $found = true;
            }
        }

        if (!$found) {
            g::run("log.warning", "User not found for deletion: $username");
            return false;
        }

        $result = g::run("auth.saveUsers", $newUsers);

        if ($result) {
            g::run("log.info", "User deleted from file: $username");
        }

        return $result;
    },

));

// ============================================================================
// PHASE 8: MOD SYSTEM
// ============================================================================

g::def("mod", array(

    /**
     * Get mod folder paths
     * 
     * Returns array of paths to scan for mods:
     * 1. Central mods (if genes.php is shared)
     * 2. Local mods (in current clone folder)
     * 
     * @return array Array of paths
     * 
     * @example $paths = g::run("mod.getPaths");
     */
    "getPaths" => function () {
        $paths = array();

        // Get config for custom paths
        $config = g::run("config.get");
        $modConfig = isset($config['mods']) ? $config['mods'] : array();

        // 1. Check for central mods path from config
        if (isset($modConfig['paths']['central']) && $modConfig['paths']['central']) {
            $centralPath = $modConfig['paths']['central'];
            if (!empty($centralPath) && is_dir($centralPath)) {
                $paths['central'] = rtrim($centralPath, '/\\');
            }
        }

        // 2. Auto-detect central mods (parent of genes.php location)
        if (!isset($paths['central'])) {
            $genesDir = dirname(__FILE__);
            $centralModsDir = $genesDir . DIRECTORY_SEPARATOR . 'mods';
            if (is_dir($centralModsDir)) {
                $paths['central'] = $centralModsDir;
            }
        }

        // 3. Check for local mods path from config
        if (isset($modConfig['paths']['local']) && $modConfig['paths']['local']) {
            $localPath = $modConfig['paths']['local'];
            if (!empty($localPath) && is_dir($localPath)) {
                $paths['local'] = rtrim($localPath, '/\\');
            }
        }

        // 4. Auto-detect local mods (current working directory)
        if (!isset($paths['local'])) {
            $localModsDir = getcwd() . DIRECTORY_SEPARATOR . 'mods';
            if (is_dir($localModsDir) && $localModsDir !== (isset($paths['central']) ? $paths['central'] : '')) {
                $paths['local'] = $localModsDir;
            }
        }

        return $paths;
    },

    /**
     * Scan for available mods
     * 
     * Scans central and local mod folders for PHP files.
     * Returns array of mod names without .php extension.
     * 
     * @return array Array of available mod names
     * 
     * @example $mods = g::run("mod.scan");
     * // Returns: array("analytics", "payment", "custom-blog")
     */
    "scan" => function () {
        $paths = g::run("mod.getPaths");
        $mods = array();

        // Scan each path
        foreach ($paths as $pathType => $path) {
            if (!is_dir($path)) {
                continue;
            }

            $files = scandir($path);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === 'index.php') {
                    continue;
                }

                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $modName = pathinfo($file, PATHINFO_FILENAME);

                    // Store with path info
                    if (!isset($mods[$modName])) {
                        $mods[$modName] = array();
                    }

                    $mods[$modName][$pathType] = $path . DIRECTORY_SEPARATOR . $file;
                }
            }
        }

        g::set("mods.available", $mods);
        g::run("log.info", "Scanned mods: " . count($mods) . " found");

        return $mods;
    },

    /**
     * Load enabled mods
     * 
     * Loads mods based on config.json settings.
     * Priority: Central first, then local (local can override).
     * 
     * @return array Array of loaded mod names
     * 
     * @example g::run("mod.load");
     */
    "load" => function () {
        $config = g::run("config.get");
        $modConfig = isset($config['mods']) ? $config['mods'] : array();

        // Get enabled mods from config
        $enabled = isset($modConfig['enabled']) ? $modConfig['enabled'] : array();
        $disabled = isset($modConfig['disabled']) ? $modConfig['disabled'] : array();

        // If no enabled list, load all available mods except disabled
        $availableMods = g::run("mod.scan");

        if (empty($enabled)) {
            // Load all except disabled
            $enabled = array_keys($availableMods);
            $enabled = array_diff($enabled, $disabled);
        }

        $loaded = array();

        foreach ($enabled as $modName) {
            if (in_array($modName, $disabled)) {
                continue;
            }

            if (!isset($availableMods[$modName])) {
                g::run("log.warning", "Mod not found: $modName");
                continue;
            }

            $modPaths = $availableMods[$modName];

            // Load central first (if exists)
            if (isset($modPaths['central'])) {
                try {
                    require_once $modPaths['central'];
                    $loaded[] = "$modName (central)";
                    g::run("log.info", "Loaded mod: $modName (central)");
                } catch (Exception $e) {
                    g::run("log.error", "Failed to load mod $modName (central): " . $e->getMessage());
                }
            }

            // Load local (can override central)
            if (isset($modPaths['local'])) {
                try {
                    require_once $modPaths['local'];
                    $loaded[] = "$modName (local)";
                    g::run("log.info", "Loaded mod: $modName (local)");
                } catch (Exception $e) {
                    g::run("log.error", "Failed to load mod $modName (local): " . $e->getMessage());
                }
            }
        }

        g::set("mods.loaded", $loaded);

        return $loaded;
    },

    /**
     * Register mod with hooks and routes
     * 
     * Mods call this to register their capabilities.
     * 
     * @param string $modName Mod identifier
     * @param array $config Mod configuration
     * @return bool Success status
     * 
     * @example g::run("mod.register", "analytics", array(
     *     "hooks" => array(
     *         "after_page_load" => "mod.analytics.track"
     *     ),
     *     "routes" => array(
     *         "/analytics" => "mod.analytics.dashboard"
     *     )
     * ));
     */
    "register" => function ($modName, $config) {
        $registry = g::get("mods.registry");
        if (!$registry) {
            $registry = array();
        }

        $registry[$modName] = $config;
        g::set("mods.registry", $registry);

        // Register hooks
        if (isset($config['hooks']) && is_array($config['hooks'])) {
            foreach ($config['hooks'] as $hookName => $functionName) {
                $hooks = g::get("mods.hooks.$hookName");
                if (!$hooks) {
                    $hooks = array();
                }
                $hooks[] = array(
                    "mod" => $modName,
                    "function" => $functionName
                );
                g::set("mods.hooks.$hookName", $hooks);
            }
        }

        // Register routes
        if (isset($config['routes']) && is_array($config['routes'])) {
            $routes = g::get("mods.routes");
            if (!$routes) {
                $routes = array();
            }
            foreach ($config['routes'] as $path => $handler) {
                $routes[$path] = array(
                    "mod" => $modName,
                    "handler" => $handler
                );
            }
            g::set("mods.routes", $routes);
        }

        g::run("log.info", "Registered mod: $modName");

        return true;
    },

    /**
     * Execute hook
     * 
     * Runs all functions registered for a specific hook.
     * 
     * @param string $hookName Hook identifier
     * @param mixed $data Data to pass to hook functions
     * @return mixed Last hook return value
     * 
     * @example g::run("mod.hook", "after_page_load", array("url" => "/home"));
     */
    "hook" => function ($hookName, $data = null) {
        $hooks = g::get("mods.hooks.$hookName");

        if (!$hooks || !is_array($hooks)) {
            return null;
        }

        $result = null;

        foreach ($hooks as $hook) {
            $modName = $hook['mod'];
            $functionName = $hook['function'];

            if (!g::has($functionName)) {
                g::run("log.warning", "Hook function not found: $functionName (mod: $modName)");
                continue;
            }

            try {
                $result = g::run($functionName, $data);
                g::run("log.debug", "Executed hook: $hookName -> $functionName");
            } catch (Exception $e) {
                g::run("log.error", "Hook execution failed: $functionName - " . $e->getMessage());
            }
        }

        return $result;
    },

    /**
     * Execute mod function
     * 
     * Runs a specific mod function by name.
     * Convenience wrapper around g::run() for mod functions.
     * 
     * @param string $functionName Full function name (e.g., "mod.analytics.track")
     * @param mixed $params Parameters to pass
     * @return mixed Function return value
     * 
     * @example g::run("mod.execute", "mod.analytics.track", array("event" => "page_view"));
     */
    "execute" => function ($functionName, $params = null) {
        if (!g::has($functionName)) {
            g::run("log.warning", "Mod function not found: $functionName");
            return false;
        }

        return g::run($functionName, $params);
    },

    /**
     * Get mod info
     * 
     * Returns information about loaded mods and their registrations.
     * 
     * @param string $modName Optional specific mod name
     * @return array Mod information
     * 
     * @example $info = g::run("mod.info", "analytics");
     */
    "info" => function ($modName = null) {
        if ($modName) {
            $registry = g::get("mods.registry");
            return isset($registry[$modName]) ? $registry[$modName] : null;
        }

        return array(
            "paths" => g::run("mod.getPaths"),
            "available" => g::get("mods.available"),
            "loaded" => g::get("mods.loaded"),
            "registry" => g::get("mods.registry"),
            "hooks" => g::get("mods.hooks"),
            "routes" => g::get("mods.routes")
        );
    },

));

// ============================================================================
// PHASE 9: DATA PROCESSING
// ============================================================================

g::def("data", array(

    /**
     * Validate data against rules
     * 
     * Validates input data against a set of rules.
     * Returns array with 'valid' boolean and 'errors' array.
     * 
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return array Result with 'valid' and 'errors' keys
     * 
     * @example g::run("data.validate", $_POST, array(
     *     "email" => "required|email",
     *     "age" => "required|integer|min:18",
     *     "username" => "required|alphanumeric|min:3|max:20"
     * ));
     */
    "validate" => function ($data, $rules) {
        $errors = array();
        $valid = true;

        foreach ($rules as $field => $ruleString) {
            $value = isset($data[$field]) ? $data[$field] : null;
            $fieldRules = explode('|', $ruleString);

            foreach ($fieldRules as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleParam = isset($ruleParts[1]) ? $ruleParts[1] : null;

                $error = null;

                switch ($ruleName) {
                    case 'required':
                        if (empty($value) && $value !== '0' && $value !== 0) {
                            $error = "$field is required";
                        }
                        break;

                    case 'email':
                        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $error = "$field must be a valid email";
                        }
                        break;

                    case 'url':
                        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                            $error = "$field must be a valid URL";
                        }
                        break;

                    case 'integer':
                    case 'int':
                        if ($value && !filter_var($value, FILTER_VALIDATE_INT)) {
                            $error = "$field must be an integer";
                        }
                        break;

                    case 'numeric':
                        if ($value && !is_numeric($value)) {
                            $error = "$field must be numeric";
                        }
                        break;

                    case 'alpha':
                        if ($value && !ctype_alpha($value)) {
                            $error = "$field must contain only letters";
                        }
                        break;

                    case 'alphanumeric':
                        if ($value && !ctype_alnum($value)) {
                            $error = "$field must be alphanumeric";
                        }
                        break;

                    case 'min':
                        if ($value) {
                            $len = is_string($value) ? strlen($value) : $value;
                            if ($len < $ruleParam) {
                                $error = "$field must be at least $ruleParam";
                            }
                        }
                        break;

                    case 'max':
                        if ($value) {
                            $len = is_string($value) ? strlen($value) : $value;
                            if ($len > $ruleParam) {
                                $error = "$field must not exceed $ruleParam";
                            }
                        }
                        break;

                    case 'in':
                        if ($value) {
                            $allowed = explode(',', $ruleParam);
                            if (!in_array($value, $allowed)) {
                                $error = "$field must be one of: $ruleParam";
                            }
                        }
                        break;

                    case 'hash':
                        if ($value && strlen($value) !== 32) {
                            $error = "$field must be a valid hash (32 characters)";
                        }
                        break;
                }

                if ($error) {
                    $errors[$field][] = $error;
                    $valid = false;
                }
            }
        }

        return array(
            'valid' => $valid,
            'errors' => $errors
        );
    },

    /**
     * Sanitize data for safe output
     * 
     * Cleans data to prevent XSS attacks.
     * Can sanitize strings, arrays, or objects recursively.
     * 
     * @param mixed $data Data to sanitize
     * @param string $mode Sanitization mode: 'html', 'attr', 'url', 'js'
     * @return mixed Sanitized data
     * 
     * @example $clean = g::run("data.sanitize", $_POST, "html");
     * @example $safeUrl = g::run("data.sanitize", $userInput, "url");
     */
    "sanitize" => function ($data, $mode = 'html') {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = g::run("data.sanitize", $value, $mode);
            }
            return $result;
        }

        if (is_object($data)) {
            $result = new stdClass();
            foreach ($data as $key => $value) {
                $result->$key = g::run("data.sanitize", $value, $mode);
            }
            return $result;
        }

        if (!is_string($data)) {
            return $data;
        }

        switch ($mode) {
            case 'html':
                return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

            case 'attr':
                return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            case 'url':
                return urlencode($data);

            case 'js':
                return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

            case 'sql':
                // Note: Use prepared statements instead!
                return addslashes($data);

            case 'plain':
                return strip_tags($data);

            default:
                return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
    },

    /**
     * Filter array elements
     * 
     * Filters array based on callback function or simple key-value match.
     * 
     * @param array $array Array to filter
     * @param mixed $callback Callback function or array of key=>value pairs
     * @return array Filtered array
     * 
     * @example $active = g::run("data.filter", $users, function($u) { 
     *     return $u['state'] === 'active'; 
     * });
     * 
     * @example $active = g::run("data.filter", $users, array("state" => "active"));
     */
    "filter" => function ($array, $callback) {
        if (!is_array($array)) {
            return array();
        }

        // Simple key-value filter
        if (is_array($callback)) {
            $result = array();
            foreach ($array as $item) {
                $match = true;
                foreach ($callback as $key => $value) {
                    if (!isset($item[$key]) || $item[$key] !== $value) {
                        $match = false;
                        break;
                    }
                }
                if ($match) {
                    $result[] = $item;
                }
            }
            return $result;
        }

        // Callback function filter
        if (is_callable($callback)) {
            return array_filter($array, $callback);
        }

        return $array;
    },

    /**
     * Map/transform array elements
     * 
     * Applies callback to each element and returns transformed array.
     * Can also extract specific keys from array of arrays.
     * 
     * @param array $array Array to map
     * @param mixed $callback Callback function or key name to extract
     * @return array Mapped array
     * 
     * @example $names = g::run("data.map", $users, function($u) { 
     *     return $u['name']; 
     * });
     * 
     * @example $names = g::run("data.map", $users, "name");
     */
    "map" => function ($array, $callback) {
        if (!is_array($array)) {
            return array();
        }

        // Extract key from each item
        if (is_string($callback)) {
            $key = $callback;
            return array_map(function ($item) use ($key) {
                return isset($item[$key]) ? $item[$key] : null;
            }, $array);
        }

        // Callback function
        if (is_callable($callback)) {
            return array_map($callback, $array);
        }

        return $array;
    },

    /**
     * Parse various data formats
     * 
     * Parses JSON, query strings, CSV, or other formats into PHP arrays.
     * 
     * @param string $data Data to parse
     * @param string $format Format: 'json', 'query', 'csv', 'xml'
     * @param array $options Optional parsing options
     * @return mixed Parsed data
     * 
     * @example $data = g::run("data.parse", $jsonString, "json");
     * @example $params = g::run("data.parse", "foo=bar&baz=qux", "query");
     * @example $rows = g::run("data.parse", $csvString, "csv");
     */
    "parse" => function ($data, $format = 'json', $options = array()) {
        switch ($format) {
            case 'json':
                $result = json_decode($data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    g::run("log.warning", "JSON parse error: " . json_last_error_msg());
                    return null;
                }
                return $result;

            case 'query':
            case 'querystring':
                parse_str($data, $result);
                return $result;

            case 'csv':
                $delimiter = isset($options['delimiter']) ? $options['delimiter'] : ',';
                $enclosure = isset($options['enclosure']) ? $options['enclosure'] : '"';
                $escape = isset($options['escape']) ? $options['escape'] : '\\';

                $lines = explode("\n", trim($data));
                $result = array();

                // First line as headers if specified
                $hasHeaders = isset($options['headers']) ? $options['headers'] : true;
                $headers = null;

                foreach ($lines as $i => $line) {
                    if (empty(trim($line))) {
                        continue;
                    }

                    $row = str_getcsv($line, $delimiter, $enclosure, $escape);

                    if ($hasHeaders && $i === 0) {
                        $headers = $row;
                    } else {
                        if ($headers) {
                            $row = array_combine($headers, $row);
                        }
                        $result[] = $row;
                    }
                }

                return $result;

            case 'xml':
                if (function_exists('simplexml_load_string')) {
                    $xml = @simplexml_load_string($data);
                    if ($xml === false) {
                        g::run("log.warning", "XML parse error");
                        return null;
                    }
                    return json_decode(json_encode($xml), true);
                }
                g::run("log.warning", "XML extension not available");
                return null;

            default:
                g::run("log.warning", "Unknown parse format: $format");
                return null;
        }
    },

    /**
     * Format date/time
     * 
     * Converts date strings or timestamps into formatted strings.
     * Handles various input formats and outputs in specified format.
     * 
     * @param mixed $date Date string, timestamp, or DateTime object
     * @param string $format Output format (PHP date format)
     * @param string $default Default value if date is invalid
     * @return string Formatted date string
     * 
     * @example g::run("data.formatDate", "2025-10-10", "M d, Y"); // "Oct 10, 2025"
     * @example g::run("data.formatDate", time(), "Y-m-d H:i:s");
     * @example g::run("data.formatDate", "invalid", "Y-m-d", "N/A"); // "N/A"
     */
    "formatDate" => function ($date, $format = 'Y-m-d H:i:s', $default = '') {
        if (empty($date)) {
            return $default;
        }

        // If it's already a timestamp
        if (is_numeric($date)) {
            return date($format, (int)$date);
        }

        // If it's a DateTime object
        if ($date instanceof DateTime) {
            return $date->format($format);
        }

        // Try to parse string
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            g::run("log.debug", "Could not parse date: $date");
            return $default;
        }

        return date($format, $timestamp);
    },

    /**
     * Get relative time (e.g., "2 hours ago", "in 3 days")
     * 
     * Converts dates to human-readable relative format.
     * 
     * @param mixed $date Date string or timestamp
     * @param bool $detailed Whether to show detailed time (default: false)
     * @return string Relative time string
     * 
     * @example g::run("data.relativeTime", "2025-10-09"); // "1 day ago"
     * @example g::run("data.relativeTime", time() - 3600); // "1 hour ago"
     */
    "relativeTime" => function ($date, $detailed = false) {
        if (empty($date)) {
            return '';
        }

        // Convert to timestamp
        if (is_numeric($date)) {
            $timestamp = (int)$date;
        } else {
            $timestamp = strtotime($date);
            if ($timestamp === false) {
                return '';
            }
        }

        $diff = time() - $timestamp;
        $isPast = $diff >= 0;
        $diff = abs($diff);

        // Time units
        $units = array(
            31536000 => array('year', 'years'),
            2592000 => array('month', 'months'),
            604800 => array('week', 'weeks'),
            86400 => array('day', 'days'),
            3600 => array('hour', 'hours'),
            60 => array('minute', 'minutes'),
            1 => array('second', 'seconds')
        );

        foreach ($units as $seconds => $names) {
            $count = floor($diff / $seconds);
            if ($count >= 1) {
                $unit = ($count == 1) ? $names[0] : $names[1];
                $timeStr = $count . ' ' . $unit;

                if ($detailed && $count < 10) {
                    $remainder = $diff % $seconds;
                    $nextUnit = null;
                    $nextCount = 0;

                    foreach ($units as $nextSeconds => $nextNames) {
                        if ($nextSeconds < $seconds) {
                            $nextCount = floor($remainder / $nextSeconds);
                            if ($nextCount >= 1) {
                                $nextUnit = ($nextCount == 1) ? $nextNames[0] : $nextNames[1];
                                break;
                            }
                        }
                    }

                    if ($nextUnit) {
                        $timeStr .= ' ' . $nextCount . ' ' . $nextUnit;
                    }
                }

                return $isPast ? $timeStr . ' ago' : 'in ' . $timeStr;
            }
        }

        return 'just now';
    },

));

// ============================================================================
// PHASE 10: TEMPLATE ENGINE (PROGRESSIVE ENHANCEMENT)
// ============================================================================

g::def("tpl", array(

    /**
     * Load template file
     * 
     * Loads HTML template from file system with path resolution.
     * Supports multiple template directories and caching.
     * 
     * @param string $name Template name (e.g., "home", "user/profile")
     * @param array $options Load options (paths, cache, etc.)
     * @return string|false Template content or false on failure
     * 
     * @example $html = g::run("tpl.load", "home");
     * @example $html = g::run("tpl.load", "user/profile", array("cache" => true));
     */
    "load" => function ($name, $options = array()) {
        $defaults = array(
            "paths" => array(
                DATA_FOLDER . 'templates',
                UI_FOLDER . 'tmpls',
                WORK_DIR . 'templates'
            ),
            "extension" => ".html",
            "cache" => false
        );

        $config = array_merge($defaults, $options);

        // Check cache first
        if ($config['cache']) {
            $cached = g::get("tpl.cache.$name");
            if ($cached !== null) {
                g::run("log.debug", "Template loaded from cache: $name");
                return $cached;
            }
        }

        // Clean up template name
        $name = trim($name, '/');
        $filename = $name . $config['extension'];

        // Try each path
        foreach ($config['paths'] as $path) {
            $fullPath = $path . '/' . $filename;

            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);

                if ($content === false) {
                    g::run("log.error", "Could not read template: $fullPath");
                    continue;
                }

                g::run("log.debug", "Template loaded: $fullPath");

                // Cache if requested
                if ($config['cache']) {
                    g::set("tpl.cache.$name", $content);
                }

                return $content;
            }
        }

        g::run("log.warning", "Template not found: $name (tried " . count($config['paths']) . " paths)");
        return false;
    },

    /**
     * Parse template HTML
     * 
     * Extracts data-g-* attributes from HTML templates.
     * Template authors can add data-a, data-b, data-c markers
     * and matching comments (<!--a-->, <!--b-->, etc.) for nested elements.
     * 
     * @param string $html HTML template content
     * @return array Parsed template structure
     * 
     * @example 
     * <div data-g-if="show" data-a>...</div><!--a-->
     * <div data-g-for="item in items" data-b>...</div><!--b-->
     * <div data-g-load="auth/login" data-c></div><!--c-->
     * <div data-g-load="user/profile" data-g-with="currentUser" data-d></div><!--d-->
     */
    "parse" => function ($html) {
        $elements = array();

        // Find all elements with data-g-* attributes
        $pattern = '/<([a-z][a-z0-9]*)\s+([^>]*data-g-[^>]*)>/i';

        if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($matches as $match) {
                $fullTag = $match[0][0];
                $tagName = $match[1][0];
                $attributes = $match[2][0];
                $offset = $match[0][1];

                // Extract data-g-* attributes
                $dataAttrs = array();

                if (preg_match_all('/data-g-([a-z]+)="([^"]*)"/i', $attributes, $attrMatches, PREG_SET_ORDER)) {
                    foreach ($attrMatches as $attrMatch) {
                        $attrName = $attrMatch[1];
                        $attrValue = $attrMatch[2];
                        $dataAttrs[$attrName] = $attrValue;
                    }
                }

                // Look for single-letter marker at the END: data-a, data-b, data-c, etc.
                // Must be at the end (just before >) for simplicity and consistency
                $marker = null;
                if (preg_match('/data-([a-fh-z])\s*>$/i', $fullTag, $markerMatch)) {
                    $marker = $markerMatch[1];
                }

                $elements[] = array(
                    'tag' => $tagName,
                    'offset' => $offset,
                    'length' => strlen($fullTag),
                    'full' => $fullTag,
                    'attrs' => $dataAttrs,
                    'marker' => $marker // Single letter: a, b, c, etc.
                );
            }
        }

        // Mark which elements are nested inside others
        // We'll calculate closing positions to determine nesting
        foreach ($elements as $i => $elem) {
            $marker = $elem['marker'];
            if ($marker) {
                // Find closing position using marker
                $closeComment = '<!--' . $marker . '-->';
                $closePos = strpos($html, $closeComment, $elem['offset']);
                if ($closePos !== false) {
                    $elements[$i]['closePos'] = $closePos;
                }
            }
        }

        // Mark nested elements (elements inside other elements)
        foreach ($elements as $i => $elem) {
            $isNested = false;

            if (isset($elem['closePos'])) {
                foreach ($elements as $j => $other) {
                    if ($i !== $j && isset($other['closePos'])) {
                        // Is elem inside other?
                        if ($elem['offset'] > $other['offset'] && $elem['closePos'] < $other['closePos']) {
                            $isNested = true;
                            break;
                        }
                    }
                }
            }

            $elements[$i]['isNested'] = $isNested;
        }

        g::run("log.debug", "Parsed " . count($elements) . " template elements");

        return array(
            'html' => $html,
            'elements' => $elements
        );
    },

    /**
     * Render template with data
     * 
     * Processes data-g-* attributes and renders HTML with provided data.
     * 
     * Supported attributes:
     * - data-g-bind: Bind text content (sanitized)
     * - data-g-html: Bind raw HTML content
     * - data-g-if: Conditional rendering with expressions:
     *   * Truthy: data-g-if="user" (checks if value exists and is truthy)
     *   * Negation: data-g-if="!user" (checks if value is falsy)
     *   * Equality: data-g-if="status:active" (checks if status equals "active")
     *   * Boolean: data-g-if="active:true" or data-g-if="disabled:false"
     *   * Number: data-g-if="count:0" (type-aware comparison)
     * - data-g-for: Loop rendering (item in items)
     * - data-g-load: Load partial templates
     * - data-g-with: Pass data context to partials
     * - data-g-attr: Bind attributes (name:path,name2:path2)
     * - data-g-helper: Execute custom helper functions
     * 
     * Progressive enhancement: HTML is valid and shows defaults,
     * but gets enhanced with actual data server-side.
     * 
     * @param string $html HTML template
     * @param array $data Data to bind to template
     * @param array $options Render options
     * @return string Rendered HTML
     * 
     * @example $output = g::run("tpl.render", $html, array(
     *     "user" => array("name" => "John", "email" => "john@example.com"),
     *     "posts" => array(...)
     * ));
     */
    "render" => function ($html, $data = array(), $options = array()) {
        $defaults = array(
            'sanitize' => true,
            'helpers' => true
        );

        $config = array_merge($defaults, $options);

        // First pass: Simple {{variable}} replacement for backwards compatibility
        $html = preg_replace_callback('/\{\{([^}]+)\}\}/', function ($matches) use ($data) {
            $path = trim($matches[1]);
            $value = g::run("tpl._getValue", $path, $data);

            if ($value === null) {
                return $matches[0]; // Keep original if not found
            }

            // Return raw value without escaping for backwards compatibility
            return $value;
        }, $html);

        // Parse template
        $parsed = g::run("tpl.parse", $html);
        $html = $parsed['html'];
        $elements = $parsed['elements'];

        // Filter: Skip elements nested inside data-g-for loops
        // They'll be processed during loop iteration's recursive render
        // But DO process elements nested in data-g-if (conditionals don't iterate)
        $processableElements = array();
        foreach ($elements as $elem) {
            $skipThis = false;

            if (isset($elem['isNested']) && $elem['isNested'] && isset($elem['closePos'])) {
                // Check if nested inside a FOR loop
                foreach ($elements as $parent) {
                    if (isset($parent['attrs']['for']) && isset($parent['closePos'])) {
                        if ($elem['offset'] > $parent['offset'] && $elem['closePos'] < $parent['closePos']) {
                            $skipThis = true;
                            break;
                        }
                    }
                }
            }

            if (!$skipThis) {
                $processableElements[] = $elem;
            }
        }

        // Sort by offset (DESCENDING) to process from end to beginning
        usort($processableElements, function ($a, $b) {
            return $b['offset'] - $a['offset'];
        });

        foreach ($processableElements as $element) {
            $attrs = $element['attrs'];
            $replacement = null;

            // Process data-g-if (conditional rendering)
            if (isset($attrs['if'])) {
                $condition = $attrs['if'];
                $result = g::run("tpl._evaluateCondition", $condition, $data);

                if (!$result) {
                    // Remove this element completely
                    $html = g::run("tpl._removeElement", $html, $element);
                    continue;
                }
            }

            // Process data-g-for (loop rendering)
            if (isset($attrs['for'])) {
                $html = g::run("tpl._processFor", $html, $element, $data);
                continue;
            }

            // Process data-g-load (load partial template)
            if (isset($attrs['load'])) {
                $html = g::run("tpl._processLoad", $html, $element, $data, $config);
                continue;
            }

            // Process data-g-bind (text content binding)
            if (isset($attrs['bind'])) {
                $path = $attrs['bind'];
                $value = g::run("tpl._getValue", $path, $data);

                if ($value !== null) {
                    if ($config['sanitize']) {
                        $value = g::run("data.sanitize", $value, "html");
                    }

                    $html = g::run("tpl._setContent", $html, $element, $value);
                }
            }

            // Process data-g-html (raw HTML binding)
            if (isset($attrs['html'])) {
                $path = $attrs['html'];
                $value = g::run("tpl._getValue", $path, $data);

                if ($value !== null) {
                    $html = g::run("tpl._setContent", $html, $element, $value);
                }
            }

            // Process data-g-attr (attribute binding)
            if (isset($attrs['attr'])) {
                $attrBindings = $attrs['attr'];
                // Format: "href:user.url,title:user.name"
                $html = g::run("tpl._setAttributes", $html, $element, $attrBindings, $data);
            }

            // Process data-g-helper (custom helper function)
            if (isset($attrs['helper']) && $config['helpers']) {
                $helperName = $attrs['helper'];
                $html = g::run("tpl._executeHelper", $html, $element, $helperName, $data);
            }
        }

        // Clean up: Remove all data-g-* attributes from rendered HTML
        $html = preg_replace('/\s+data-g-[a-z]+="[^"]*"/i', '', $html);

        // Clean up: Remove marker attributes (data-a, data-b, etc.)
        $html = preg_replace('/\s+data-[a-z](?=[\s>])/i', '', $html);

        // Clean up: Remove marker comments (<!--a-->, <!--b-->, etc.)
        $html = preg_replace('/<!--[a-z]-->/i', '', $html);

        return $html;
    },

    /**
     * Render view template
     * 
     * Loads and renders the template for a specific view.
     * Checks view config for custom template, falls back to ui/index.html.
     * 
     * @param string $viewName View name from config (e.g., "Index", "Admin")
     * @param array $data Data to bind to template
     * @param array $options Render options
     * @return string|false Rendered HTML or false on failure
     * 
     * @example echo g::run("tpl.renderView", "Admin", array("user" => $user));
     * @example echo g::run("tpl.renderView", "Index", $data, array("template" => "custom"));
     */
    "renderView" => function ($viewName, $data = array(), $options = array()) {
        $config = g::get("config");
        $views = isset($config["views"]) ? $config["views"] : array();

        // Get view config
        $viewConfig = isset($views[$viewName]) ? $views[$viewName] : null;

        // ============================================================
        // VALIDATION: Check for unhandled route segments or query params
        // ============================================================
        $request = g::get("request");

        // Check if view allows additional segments (like /blog/post-slug)
        $allowSegments = isset($options['allowSegments']) ? $options['allowSegments'] : false;

        // Check if view allows query parameters (like /docs;section;topic)
        $allowParams = isset($options['allowParams']) ? $options['allowParams'] : false;

        // Check for unexpected route segments (path segments after the matched route)
        if (!$allowSegments && $request && isset($request['route_segments']) && !empty($request['route_segments'])) {
            // Unexpected segments found - this is a 404
            http_response_code(404);

            if (g::has("clone.NotFound")) {
                $lang = isset($data['lang']) ? $data['lang'] : g::run("route.detectLanguage");
                $bits = isset($data['bits']) ? $data['bits'] : array();
                return g::run("clone.NotFound", $bits, $lang, $request['path']);
            }

            echo "404 - Page Not Found";
            exit;
        }

        // Check for unexpected query parameters (from semicolon-delimited URL)
        if (!$allowParams && $request && isset($request['query']) && !empty($request['query'])) {
            // Unexpected query params found - this is a 404
            http_response_code(404);

            if (g::has("clone.NotFound")) {
                $lang = isset($data['lang']) ? $data['lang'] : g::run("route.detectLanguage");
                $bits = isset($data['bits']) ? $data['bits'] : array();
                return g::run("clone.NotFound", $bits, $lang, $request['path']);
            }

            echo "404 - Page Not Found";
            exit;
        }
        // ============================================================

        // Determine template name
        $templateName = null;

        // 1. Check options override
        if (isset($options['template'])) {
            $templateName = $options['template'];
        }
        // 2. Check view config for custom template
        else if ($viewConfig && isset($viewConfig['template'])) {
            $templateName = $viewConfig['template'];
        }
        // 3. Fall back to index
        else {
            $templateName = 'index';
        }

        // Load template
        $html = g::run("tpl.load", $templateName, array(
            "paths" => array(
                UI_FOLDER,
                DATA_FOLDER . 'templates',
                UI_FOLDER . 'tmpls'
            )
        ));

        if ($html === false) {
            g::run("log.error", "Template not found for view: $viewName (template: $templateName)");
            return false;
        }

        // Inject base href if not already present - always use current request URL
        $baseUrl = g::run("core.getBaseUrl");
        if (stripos($html, '<base') === false && stripos($html, '<head') !== false) {
            $baseTag = '<base href="' . htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '">';
            $html = preg_replace('/(<head[^>]*>)/i', '$1' . "\n    " . $baseTag, $html, 1);
        }

        // Add meta information to data for template to use
        if (!isset($data['meta'])) {
            $data['meta'] = array();
        }

        // Add base URL to meta
        $data['meta']['base_url'] = $baseUrl;

        // Add canonical URL to meta (auto-generate from current URL)
        if (!isset($data['meta']['canonical_url'])) {
            $request = g::get("request");
            if ($request) {
                $data['meta']['canonical_url'] = $baseUrl . ltrim($request['path'], '/');
            }
        }

        // Template can now use {{meta.base_url}}, {{meta.canonical_url}}, etc.

        // Render with data
        $rendered = g::run("tpl.render", $html, $data, $options);

        // Check if this is an AJAX request from client-side router
        // If so, return only the content columns (feed + details) for SPA navigation
        $isAjax = g::get("response.is_ajax");
        if ($isAjax) {
            // Parse the rendered HTML to extract just the middle and right columns
            $doc = new DOMDocument();
            @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $rendered);

            $result = '';

            // Extract middle column (feed)
            $middleColumn = $doc->getElementById('feed');
            if (!$middleColumn) {
                // Try alternative selector
                $xpath = new DOMXPath($doc);
                $nodes = $xpath->query("//*[contains(@class, 'middle-column')]");
                if ($nodes->length > 0) {
                    $middleColumn = $nodes->item(0);
                }
            }

            // Extract right column (details)  
            $rightColumn = $doc->getElementById('details');
            if (!$rightColumn) {
                $xpath = new DOMXPath($doc);
                $nodes = $xpath->query("//*[contains(@class, 'right-column')]");
                if ($nodes->length > 0) {
                    $rightColumn = $nodes->item(0);
                }
            }

            // Build partial HTML response
            if ($middleColumn) {
                $result .= $doc->saveHTML($middleColumn);
            }
            if ($rightColumn) {
                $result .= $doc->saveHTML($rightColumn);
            }

            // If we successfully extracted columns, return them
            // Otherwise fall back to full HTML
            if (!empty($result)) {
                return $result;
            }
        }

        return $rendered;
    },

    /**
     * Compile template
     * 
     * Pre-processes template for faster repeated rendering.
     * Extracts structure and creates optimized render function.
     * 
     * @param string $html Template HTML
     * @return array Compiled template data
     * 
     * @example $compiled = g::run("tpl.compile", $html);
     * @example $output = g::run("tpl.render", $compiled['html'], $data);
     */
    "compile" => function ($html) {
        $parsed = g::run("tpl.parse", $html);

        $compiled = array(
            'html' => $html,
            'elements' => $parsed['elements'],
            'compiled_at' => time(),
            'stats' => array(
                'element_count' => count($parsed['elements']),
                'has_conditionals' => false,
                'has_loops' => false,
                'has_bindings' => false
            )
        );

        // Analyze template for optimization hints
        foreach ($parsed['elements'] as $element) {
            if (isset($element['attrs']['if'])) {
                $compiled['stats']['has_conditionals'] = true;
            }
            if (isset($element['attrs']['for'])) {
                $compiled['stats']['has_loops'] = true;
            }
            if (isset($element['attrs']['bind']) || isset($element['attrs']['html'])) {
                $compiled['stats']['has_bindings'] = true;
            }
        }

        g::run("log.debug", "Template compiled with " . $compiled['stats']['element_count'] . " elements");

        return $compiled;
    },

    /**
     * Register template helper
     * 
     * Registers custom helper function for use in templates.
     * Helpers can format data, generate HTML, etc.
     * 
     * @param string $name Helper name
     * @param callable $callback Helper function
     * @return void
     * 
     * @example g::run("tpl.helper", "formatDate", function($date) {
     *     return date('M d, Y', strtotime($date));
     * });
     * 
     * @example g::run("tpl.helper", "money", function($amount) {
     *     return '$' . number_format($amount, 2);
     * });
     */
    "helper" => function ($name, $callback) {
        if (!is_callable($callback)) {
            g::run("log.error", "Template helper must be callable: $name");
            return;
        }

        $helpers = g::get("tpl.helpers");
        if (!is_array($helpers)) {
            $helpers = array();
        }

        $helpers[$name] = $callback;
        g::set("tpl.helpers", $helpers);

        g::run("log.debug", "Registered template helper: $name");
    },

    // ========================================================================
    // INTERNAL HELPER FUNCTIONS
    // ========================================================================

    /**
     * Evaluate conditional expression (internal)
     * 
     * Supports:
     * - Negation: !user, !logged_in
     * - Equality: status:active, role:admin, count:0
     * - Truthy: user, logged_in
     * 
     * @param string $condition Condition expression
     * @param array $data Template data
     * @return bool True if condition passes
     */
    "_evaluateCondition" => function ($condition, $data) {
        // Check for negation operator
        $negated = false;
        if (strpos($condition, '!') === 0) {
            $negated = true;
            $condition = substr($condition, 1);
        }

        // Check for comparison operator (key:value)
        if (strpos($condition, ':') !== false) {
            list($path, $expected) = explode(':', $condition, 2);
            $value = g::run("tpl._getValue", $path, $data);

            // Type-aware comparison
            if ($expected === 'true') $expected = true;
            else if ($expected === 'false') $expected = false;
            else if ($expected === 'null') $expected = null;
            else if (is_numeric($expected)) $expected = $expected + 0; // Convert to int/float

            $result = ($value == $expected);
            return $negated ? !$result : $result;
        }

        // Simple truthy check
        $value = g::run("tpl._getValue", $condition, $data);
        $result = !empty($value);

        return $negated ? !$result : $result;
    },

    /**
     * Get value from data by path (internal)
     */
    "_getValue" => function ($path, $data) {
        $parts = explode('.', $path);
        $current = $data;

        foreach ($parts as $part) {
            if (is_array($current) && isset($current[$part])) {
                $current = $current[$part];
            } else if (is_object($current) && isset($current->$part)) {
                $current = $current->$part;
            } else {
                return null;
            }
        }

        return $current;
    },

    /**
     * Set content inside element (internal)
     */
    "_setContent" => function ($html, $element, $content) {
        // Find MATCHING closing tag using marker
        $tagName = $element['tag'];
        $marker = isset($element['marker']) ? $element['marker'] : null;
        $startPos = $element['offset'] + $element['length'];

        $endPos = g::run("tpl._findClosingTag", $html, $tagName, $startPos, $marker);

        if ($endPos === false) {
            // Self-closing or no closing tag
            return $html;
        }

        // Replace content between tags
        $before = substr($html, 0, $startPos);
        $after = substr($html, $endPos);

        return $before . $content . $after;
    },

    /**
     * Remove element completely (internal)
     */
    "_removeElement" => function ($html, $element) {
        $tagName = $element['tag'];
        $marker = isset($element['marker']) ? $element['marker'] : null;
        $startPos = $element['offset'];
        $closeTag = '</' . $tagName . '>';

        // Find MATCHING closing tag using marker
        $searchStart = $startPos + $element['length'];
        $endPos = g::run("tpl._findClosingTag", $html, $tagName, $searchStart, $marker);

        if ($endPos === false) {
            // Remove just the opening tag
            $before = substr($html, 0, $startPos);
            $after = substr($html, $startPos + $element['length']);
            return $before . $after;
        }

        // Remove entire element (including marker comment)
        $before = substr($html, 0, $startPos);
        $markerComment = $marker ? '<!--' . $marker . '-->' : '';
        $after = substr($html, $endPos + strlen($closeTag) + strlen($markerComment));

        return $before . $after;
    },

    /**
     * Find matching closing tag using unique marker (internal)
     * 
     * Looks for a closing tag with format: </tagname><!--marker-->
     * This is a simple, reliable way to handle nested tags.
     */
    "_findClosingTag" => function ($html, $tagName, $startPos, $marker) {
        $closeTag = '</' . $tagName . '><!--' . $marker . '-->';
        $pos = strpos($html, $closeTag, $startPos);

        if ($pos !== false) {
            return $pos;
        }

        // Fallback: if no marker found, use simple close tag
        // (for non-nested cases or already processed elements)
        $simpleClose = '</' . $tagName . '>';
        return strpos($html, $simpleClose, $startPos);
    },

    /**
     * Process data-g-for loop (internal)
     */
    "_processFor" => function ($html, $element, $data) {
        // Parse for syntax: "item in items" or "post in posts"
        $forExpr = $element['attrs']['for'];

        if (!preg_match('/(\w+)\s+in\s+([\w\.]+)/', $forExpr, $matches)) {
            g::run("log.warning", "Invalid data-g-for syntax: $forExpr");
            return $html;
        }

        $itemName = $matches[1];
        $arrayPath = $matches[2];

        // Get array data
        $array = g::run("tpl._getValue", $arrayPath, $data);

        if (!is_array($array) || empty($array)) {
            // Remove the loop element
            return g::run("tpl._removeElement", $html, $element);
        }

        // Extract element template
        $tagName = $element['tag'];
        $marker = isset($element['marker']) ? $element['marker'] : null;
        $startPos = $element['offset'];
        $closeTag = '</' . $tagName . '>';

        // Find MATCHING closing tag using marker
        $searchStart = $startPos + $element['length'];
        $endPos = g::run("tpl._findClosingTag", $html, $tagName, $searchStart, $marker);

        if ($endPos === false) {
            return $html;
        }

        $markerComment = $marker ? '<!--' . $marker . '-->' : '';
        $templateStart = $startPos;
        $templateEnd = $endPos + strlen($closeTag) + strlen($markerComment);
        $template = substr($html, $templateStart, $templateEnd - $templateStart);

        // Render for each item
        $output = '';
        foreach ($array as $index => $item) {
            // Create context with item data
            $itemData = array_merge($data, array(
                $itemName => $item,
                '_index' => $index,
                '_first' => ($index === 0),
                '_last' => ($index === count($array) - 1)
            ));

            // Render this iteration
            // Remove ONLY the outer data-g-for and marker (keep nested ones intact)
            $itemHtml = $template;

            // Remove the first occurrence of data-g-for (the outer one)
            $itemHtml = preg_replace('/data-g-for="' . preg_quote($forExpr, '/') . '"\s*/', '', $itemHtml, 1);

            // Remove the marker attribute and comment if present
            if ($marker) {
                // Remove data-X attribute at end: " data-a>" becomes ">"
                $itemHtml = preg_replace('/\s*data-' . preg_quote($marker, '/') . '\s*>/', '>', $itemHtml, 1);
                $itemHtml = str_replace($markerComment, '', $itemHtml);
            }

            $rendered = g::run("tpl.render", $itemHtml, $itemData, array('sanitize' => true, 'helpers' => false));
            $output .= $rendered;
        }

        // Replace original element with rendered loop
        $before = substr($html, 0, $templateStart);
        $after = substr($html, $templateEnd);

        return $before . $output . $after;
    },

    /**
     * Process data-g-load (load partial template) (internal)
     * 
     * Loads a partial template file and renders it with current data context.
     * Supports passing data from parent scope or specific data path.
     * 
     * @example <div data-g-load="auth/login" data-a></div><!--a-->
     * @example <div data-g-load="user/profile" data-g-with="currentUser" data-b></div><!--b-->
     */
    "_processLoad" => function ($html, $element, $data, $config) {
        $partialName = $element['attrs']['load'];

        // Check if data-g-with attribute specifies a data path
        $contextData = $data;
        if (isset($element['attrs']['with'])) {
            $dataPath = $element['attrs']['with'];
            $specificData = g::run("tpl._getValue", $dataPath, $data);

            if ($specificData !== null) {
                // Merge specific data with parent data (specific data takes precedence)
                if (is_array($specificData)) {
                    $contextData = array_merge($data, $specificData);
                } else {
                    // If it's not an array, wrap it in 'item' key
                    $contextData = array_merge($data, array('item' => $specificData));
                }
            }
        }

        // Load the partial template
        $partialHtml = g::run("tpl.load", $partialName, array(
            "paths" => array(
                UI_FOLDER . 'partials',
                UI_FOLDER . 'tmpls',
                DATA_FOLDER . 'templates',
                WORK_DIR . 'templates'
            ),
            "cache" => false // Don't cache partials during development
        ));

        if ($partialHtml === false) {
            g::run("log.warning", "Partial template not found: $partialName");
            // Keep the original element or show error
            $errorMsg = "<!-- Template not found: $partialName -->";
            return g::run("tpl._replaceElement", $html, $element, $errorMsg);
        }

        // Render the partial with context data
        $rendered = g::run("tpl.render", $partialHtml, $contextData, array(
            'sanitize' => isset($config['sanitize']) ? $config['sanitize'] : true,
            'helpers' => isset($config['helpers']) ? $config['helpers'] : true
        ));

        // Replace the element with rendered partial
        return g::run("tpl._replaceElement", $html, $element, $rendered);
    },

    /**
     * Replace element with new content (internal)
     * 
     * Similar to _removeElement but replaces with new content instead of removing.
     */
    "_replaceElement" => function ($html, $element, $newContent) {
        $tagName = $element['tag'];
        $marker = isset($element['marker']) ? $element['marker'] : null;
        $startPos = $element['offset'];
        $closeTag = '</' . $tagName . '>';

        // Find MATCHING closing tag using marker
        $searchStart = $startPos + $element['length'];
        $endPos = g::run("tpl._findClosingTag", $html, $tagName, $searchStart, $marker);

        if ($endPos === false) {
            // Replace just the opening tag
            $before = substr($html, 0, $startPos);
            $after = substr($html, $startPos + $element['length']);
            return $before . $newContent . $after;
        }

        // Replace entire element (including marker comment)
        $before = substr($html, 0, $startPos);
        $markerComment = $marker ? '<!--' . $marker . '-->' : '';
        $after = substr($html, $endPos + strlen($closeTag) + strlen($markerComment));

        return $before . $newContent . $after;
    },

    /**
     * Set attributes on element (internal)
     */
    "_setAttributes" => function ($html, $element, $attrBindings, $data) {
        // Parse attr bindings: "href:user.url,title:user.name"
        $bindings = explode(',', $attrBindings);
        $newTag = $element['full'];

        foreach ($bindings as $binding) {
            $parts = explode(':', trim($binding));
            if (count($parts) !== 2) {
                continue;
            }

            $attrName = trim($parts[0]);
            $valuePath = trim($parts[1]);
            $value = g::run("tpl._getValue", $valuePath, $data);

            if ($value !== null) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

                // Check if attribute already exists
                if (preg_match('/' . $attrName . '="[^"]*"/', $newTag)) {
                    // Replace existing
                    $newTag = preg_replace('/' . $attrName . '="[^"]*"/', $attrName . '="' . $value . '"', $newTag);
                } else {
                    // Add new attribute
                    $newTag = str_replace('>', ' ' . $attrName . '="' . $value . '">', $newTag);
                }
            }
        }

        // Replace in HTML
        $html = substr_replace($html, $newTag, $element['offset'], $element['length']);

        return $html;
    },

    /**
     * Execute helper function (internal)
     */
    "_executeHelper" => function ($html, $element, $helperName, $data) {
        $helpers = g::get("tpl.helpers");

        if (!$helpers || !isset($helpers[$helperName])) {
            g::run("log.warning", "Template helper not found: $helperName");
            return $html;
        }

        $helper = $helpers[$helperName];

        // Get element content
        $tagName = $element['tag'];
        $marker = isset($element['marker']) ? $element['marker'] : null;
        $startPos = $element['offset'] + $element['length'];

        // Find MATCHING closing tag using marker
        $endPos = g::run("tpl._findClosingTag", $html, $tagName, $startPos, $marker);

        if ($endPos === false) {
            return $html;
        }

        $content = substr($html, $startPos, $endPos - $startPos);

        // Execute helper
        try {
            $result = call_user_func($helper, $content, $data, $element);

            if ($result !== null) {
                $html = g::run("tpl._setContent", $html, $element, $result);
            }
        } catch (Exception $e) {
            g::run("log.error", "Helper execution failed: $helperName - " . $e->getMessage());
        }

        return $html;
    },

));

// ============================================================================
// AUTO-INITIALIZE
// ============================================================================

if (!defined('GENES_NO_AUTO_INIT')) {
    g::run("core.init");

    // Auto-log performance for each request
    $logFile = LOG_FILE; // Capture constant in local scope
    register_shutdown_function(function () use ($logFile) {
        // Get performance metrics
        $perfData = g::run("log.performance");

        if ($perfData && isset($perfData['execution_time_ms'])) {
            $duration = $perfData['execution_time_ms'];
            $memory = $perfData['peak_memory_kb'];

            // Get request info if available
            $request = g::get("request");
            $path = $request && isset($request['path']) ? $request['path'] : 'CLI';
            $method = $request && isset($request['method']) ? $request['method'] : 'CLI';

            // Log directly
            $timestamp = date('Y-m-d H:i:s.u');
            $logMessage = "Request: $method $path | Time: {$duration}ms | Memory: {$memory}KB";
            $logEntry = "[$timestamp] [PERF] $logMessage\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
        }
    });
}

// END OF PHASE 0 + PHASE 1 + PHASE 2 + PHASE 3 + PHASE 4 + PHASE 5 + PHASE 6 + PHASE 7 + PHASE 8
// ============================================================================
