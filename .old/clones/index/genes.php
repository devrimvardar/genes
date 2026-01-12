<?php
/*!
 * genes.php v2024.09.27
 * (c) 2024 NodOnce OÜ
 * All rights reserved.
 */

// genes is a closure, an array of anonymous functions.
// which can be set or changed any time. anywhere.
// all is set to a private related arrays..
// and that can be called via a single g function
// wrapped inside a simple class.
// ::set sets a value
// ::get gets that value
// ::del deletes the key
// ::def defines a callable function
// ::run runs that callable function
// ::key removes the key of the callable function
// ::log prints the value

class g
{
    // defined static variable used inside
    private static $app = array();
    // defined static variable used inside
    private static $fns = array();
    // these are basic functions to manipulate or use the variables
    public static function set($key, $value)
    {
        $ref = &self::find($key);
        $ref = $value;
    }
    public static function get($key)
    {
        return self::find($key);
    }
    public static function del($key)
    {
        self::find($key, true);
    }
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
    }
    public static function kill($key)
    {
        self::find($key, true, true);
    }
    public static function log($key)
    {
        if ($key === 0) {
            print_r(self::$app);
        } else if ($key === 1) {
            print_r(self::$fns);
        } else {
            $val = self::find($key);
            if (is_array($val)) {
                print_r($val);
            } else {
                echo $val;
            }
        }
    }
    // Private functions
    private static function &find($key, $remove = false, $fns_mode = false)
    {
        if ($fns_mode) {
            $ref = &static::$fns;
        } else {
            $ref = &static::$app;
        }

        if (strpos($key, ".") > -1) {
            $ps = explode('.', $key);
            $c = count($ps);
            foreach ($ps as $part) {
                $c--;
                if ($remove && $c === 0) {
                    unset($ref[$part]);
                } else {
                    $ref = &$ref[$part];
                }
            }
            return $ref;
        } else {
            if ($remove) {
                unset($ref[$key]);
            } else {
                return $ref[$key];
            }
        }
    }
}

//* CONSTANTS ****************************************************************/
//. Some basic constant settings to begin with .............................../
defined('V') or define('V', DIRECTORY_SEPARATOR);
defined('__DIR__') or define('__DIR__', dirname(__FILE__));
defined('C') or define('C', __DIR__);

defined('GENES_ROOT_FOLDER') or define('GENES_ROOT_FOLDER', dirname(C) . V);
defined('GENES_API_FOLDER') or define('GENES_API_FOLDER', GENES_ROOT_FOLDER . "api" . V);
defined('GENES_CORE_FOLDER') or define('GENES_CORE_FOLDER', GENES_API_FOLDER . "core" . V);
defined('GENES_MODS_FOLDER') or define('GENES_MODS_FOLDER', GENES_API_FOLDER . "mods" . V); // folder: mod folder genes includes during runtime

defined('GENES_UI_FOLDER') or define('GENES_UI_FOLDER', GENES_ROOT_FOLDER . "ui" . V); // folder: frontend for mods theme folder genes includes during runtime
defined('GENES_UI_TMPLS_FOLDER') or define('GENES_UI_TMPLS_FOLDER', GENES_UI_FOLDER . "tmpls" . V . "base" . V); // folder: frontend for mods theme folder genes includes during runtime
defined('GENES_UI_HTML') or define('GENES_UI_HTML', GENES_UI_TMPLS_FOLDER . "root.html"); // root frontend html for mods theme genes includes during runtime

defined('CLONE_FOLDER') or define('CLONE_FOLDER', getcwd() . V);
defined('CLONE_CACHE_FOLDER') or define('CLONE_CACHE_FOLDER', CLONE_FOLDER . "cache" . V); // folder: clone's cached outputs, reachable via url
defined('CLONE_UI_FOLDER') or define('CLONE_UI_FOLDER', CLONE_FOLDER . "ui" . V); // folder: clone's ui related files, css, js, img
defined('CLONE_UI_HTML') or define('CLONE_UI_HTML', CLONE_UI_FOLDER . "index.html"); // clone frontend html for a genes clone includes during runtime

defined('CLONE_DATA_FOLDER') or define('CLONE_DATA_FOLDER', CLONE_FOLDER . "data" . V); // folder: clone's logs / lang files, not reachable via url
defined('CLONE_LOG_FILE') or define('CLONE_LOG_FILE', CLONE_DATA_FOLDER . "sys.log"); // system log file name
defined('CLONE_CONFIG_FILE') or define('CLONE_CONFIG_FILE', CLONE_DATA_FOLDER . "config.json"); // file: default configuration file.
defined('CLONE_VIEWS_FILE') or define('CLONE_VIEWS_FILE', ""); // file: configuration file part for view and querystring details.
defined('CLONE_MODS_FILE') or define('CLONE_MODS_FILE', ""); // file: configuration file part for view and querystring details.
defined('CLONE_TMPLS_FILE') or define('CLONE_TMPLS_FILE', ""); // file: configuration file part for template related settings.
defined('CLONE_BITS_FILE') or define('CLONE_BITS_FILE', ""); // file: configuration file part for static data.
defined('CLONE_BASE_FILE') or define('CLONE_BASE_FILE', ""); // file: configuration file part for static database.

defined('GENES_UI_URL') or define('GENES_UI_URL', "https://ui.genes.one/"); // default :: https://ui.genes.one/
defined('GENES_CDN_URL') or define('GENES_CDN_URL', "https://cdn.genes.one/"); // default :: https://cdn.genes.one/
defined('CLONE_UI_URL') or define('CLONE_UI_URL', ""); // folder: clone's ui related url file path, css, js, img

g::def("core", array(
    "Init" => function () {
        g::run("core.PrepConfig");
        g::run("core.SetEnvironment");
        g::run("core.FigureState");
        g::run("core.IncludeMods");
        g::run("core.ProcessRules");
        if (g::run("core.CheckPermissions")) {
            g::run("db.ConnectIfAvailable");
        }
        g::run("core.EmbedBits");
    },
    "Render" => function () {
        if (g::get("op.meta.user.not_allowed") !== true) {
            g::run("core.TriggerFunctions");
        }

        g::run("core.RenderOutput");

        $t = g::run("tools.Now");
        $p = g::run("tools.Performance");
        g::run("tools.Log", "$t|$p");
    },
    "PrepConfig" => function () {
        // THE REST
        // This will be turned into a base.json config file.
        $config = array( // Config data :: (optional) conf.json...
            "paths" => array(
                "genes_api_folder" => GENES_API_FOLDER,
                "genes_core_folder" => GENES_CORE_FOLDER,
                "genes_mods_folder" => GENES_MODS_FOLDER,
                "genes_ui_folder" => GENES_UI_FOLDER,
                "genes_ui_tmpls_folder" => GENES_UI_TMPLS_FOLDER,
                "genes_ui_html" => GENES_UI_HTML,
                "clone_folder" => CLONE_FOLDER,
                "clone_cache_folder" => CLONE_CACHE_FOLDER,
                "clone_ui_folder" => CLONE_UI_FOLDER,
                "clone_ui_html" => CLONE_UI_HTML,
                "clone_data_folder" => CLONE_DATA_FOLDER,
                "clone_log_file" => CLONE_LOG_FILE,
                "clone_config_file" => CLONE_CONFIG_FILE,
                "clone_views_file" => CLONE_VIEWS_FILE,
                "clone_mods_file" => CLONE_MODS_FILE,
                "clone_tmpls_file" => CLONE_TMPLS_FILE,
                "clone_bits_file" => CLONE_BITS_FILE,
                "clone_base_file" => CLONE_BASE_FILE,
            ),
            "urls" => array(
                "genes_ui" => GENES_UI_URL,
                "genes_cdn" => GENES_CDN_URL,
                "clone_ui" => CLONE_UI_URL,
            ),
            "settings" => array(
                "allow_setup" => 0, // after setup is completed disable the setup path
                "reset_pwd" => 0, // any time if you want to reset to hardcoded admin password
                "allow_cors" => 0, // cross domain queries needed?
                "api_render_html" => 1, // do you want to render html server-side?
                "api_serve_html" => 0, // do you want api calls to serve rendered html?
                "api_serve_tmpl" => 0, // do you want api calls to serve raw tmpl?
                "api_serve_data" => 0, // do you want api calls to serve data?
                "ui_render_html" => 0, // do you want ui to to render html?
                "ui_handle_urls" => 0, // do you want ui to handle urls?
                "ui_parse_urls" => 0, // do you want ui to parse urls and process routing?
                "msg_level" => 4, // outputting msgs importance, if higher or equal will be said.
                "log_level" => 1, // logging msgs importance, if higher or equal will be logged.
                "output_types" => array("json" => true), // output types (.extensions) allowed
                "cache_renders" => 0, // cache rendered outputs
                "compress_renders" => 0, // compress rendered outputs
                "cache_compress_assets" => 0, // cache and compress used assets like css and js files
                "langs" => array("en"), // first option is the default language file chosen for the clone, can be anything but options can be found here: https://en.wikipedia.org/wiki/List_of_tz_database_time_zones
                "user_types" => array("guest", "user", "admin"), // user types
                "user_states" => array("active", "inactive"), // user states
                "timezone" => "Europe/Tallinn", // time zone of the clone, options found here: https://en.wikipedia.org/wiki/List_of_tz_database_time_zones
                "date_time_format" => "Y-m-d H:i:s.u", // time format including milliseconds
            ),
            "clone" => array(
                "salt" => "", // gHash_generateRandomKey(), salting communications
                "secret_salt" => "", // gHash_generateRandomKey(), salting secret
                "user_salt" => "", // gHash_generateRandomKey(), salting password
                "hash" => "", // used as hash_clone, in db records when clone creates data
                "alias" => "", // gHash_generateRandomKey(16, false), key and hash separates this clone from others.
                "name" => "", // gHash_generateRandomKey(16, false), key and hash separates this clone from others.
                "contact" => "", //
                "secret" => "", // salted secret key, like a password for this clone
            ),
            "admin" => array(
                "hash" => "", // used as hash_user, in db records when clone creates data
                "alias" => "admin", // you can change, but pass needs to be regenerated
                "email" => "", //
                "open_pass" => "", // genes admin password, when written not encoded and reset_pwd is true, salted, encoded and set again.
                "pass" => "", // genes admin password, when written not encoded and reset_pwd is true, salted, encoded and set again.
            ),
            "users" => array(),
            "db" => array(
                "conns" => array(
                    "default" => array(
                        "type" => "mysql", // mysql, sqlite, dbaas or else..
                        "path" => "", // if sqlite enter direct path
                        "name" => "",
                        "user" => "",
                        "pass" => "",
                        "charset" => "utf8",
                    ),
                ),
                "tables" => array(
                    "clones" => array("default", "g_clones"),
                    "persons" => array("default", "g_persons"),
                    "items" => array("default", "g_items"),
                    "labels" => array("default", "g_labels"),
                    "events" => array("default", "g_events"),
                ),
            ),
            "views" => array( // Views data :: (optional) view.json...
                // first come first serve..
                // genes queries, custom paths leading to function or file name
                "Index" => array(
                    "urls" => array("en" => "index"),
                    "bits" => array("title" => array("en" => "Genes Clone Index")),
                ),
            ),
            "mods" => array(
                "pass" => array(),
                "pane" => array(),
            ), // Mods give clones extra powers without messing their structure...
            "tmpls" => array(), // Template configuration :: (optional) tmpl.json...
            "bits" => array( // Data with i18n :: (optional) data.json...
                // genes labels, things that i18nized but not changed according to page
                "clone_title" => array("en" => "A Genes Clone"), // decide a unique shortname for the clone
                "lang_en" => array("en" => "English"),
            ),
            "base" => array(), // Base is necessary if you are using a flat-file db...
            "rules" => array(
                "no" => array(),
            ), // Rules are for permissions...
            "checks" => array(), // Checks will be used so there are no unnecessary queries made to db or other heavy functions...
            "env" => array(), // If the clone runs at any other environment, is clone url_key exists here use its config.
        );
        g::set("config", $config);
        g::set("op", array( // Output data
            "meta" => array(),
            "data" => array(),
            "tmpl" => "",
            "html" => "",
        ));
        g::set("msgs", array());
        g::set("void", array(
            "time" => microtime(true),
            "mem" => memory_get_usage(),
        ));
    },
    "SetEnvironment" => function () {
        error_reporting(E_ALL);
        setlocale(LC_CTYPE, 'en_US.utf8');
        g::run("core.SessionStart");
        g::run("core.GetServerUrl");
        g::run("core.CreateSetConfigFilesFolders");
        date_default_timezone_set(g::get("config.settings.timezone"));
    },
    "FigureState" => function () {
        g::set("op.meta.url.base", g::get("clone.url"));
        g::set("post", g::run("tools.CleanData", $_POST));
        g::set("files", g::run("tools.CleanData", $_FILES));

        $clone_hash = g::get("config.clone.hash");
        if (empty($_SESSION[$clone_hash])) {
            $_SESSION[$clone_hash] = array();
        }
        g::set("session", g::run("tools.CleanData", $_SESSION[$clone_hash]));

        if (!empty($_COOKIE["genes_$clone_hash"])) {
            g::set("cookie", g::run("tools.CleanData", g::run("tools.JD", $_COOKIE["genes_$clone_hash"])));
        }

        g::run("core.SessionGetSet");
        g::run("core.ParseUrlQuery");

        g::set("op.meta.user", g::run("core.CheckLogin"));
    },
    "IncludeMods" => function () {
        $mods = g::get("config.mods");
        $mod_folder = g::get("config.paths.genes_mods_folder");
        $any_mod = false;
        // include mod files
        foreach ($mods as $mod_name => $mod_config) {
            if ($mod_config !== false) {
                if (empty(g::get("mods.$mod_name"))) {
                    if (!empty($mod_config["path"])) {
                        $mod_folder = g::get("config.paths." . $mod_config["path"]);
                    }
                    // IF CLONE HAS A CONFIG IT SHOULD BE
                    // WRITTEN IN THE CONFIG
                    // CHECKED HERE
                    // BECAUSE MODS MODIFY ROOT.HTML AND ADMIN DASHBOARD
                    // BUT VIEWS MODIFY INDEX.HTML AND INDEX.PHP
                    require $mod_folder . "genes.$mod_name.php";
                }
                $areKeysSet = g::get("config.checks." . $mod_name . "_keys_set");
                if ($areKeysSet != 1) {
                    $v = g::get("void.$mod_name.views");
                    $b = g::get("void.$mod_name.bits");
                    $t = g::get("void.$mod_name.tmpls");
                    $o = g::get("void.$mod_name.opts");
                    $r = g::get("void.$mod_name.rules");
                    g::run("core.WriteModViewsLabelsTmplsOptsRules", $mod_name, $v, $b, $t, $o, $r);
                    g::set("config.checks." . $mod_name . "_keys_set", 1);
                    $any_mod = true;
                }
                g::set("op.meta.mods.$mod_name", 1);
            }
        }
        if ($any_mod) {
            $config_mod_update = g::get("config.mods");
            $config_rules_update = g::get("config.rules");
            $config_checks = g::get("config.checks");

            $cuki = g::get("clone.uki");
            $config_paths = g::get("config.env.$cuki.paths");
            $config_path = $config_paths["clone_config_file"];
            $config_data = g::run("tools.ReadFileJD", $config_path);

            $config_data["paths"] = $config_paths;
            $config_data["mods"] = $config_mod_update;
            $config_data["rules"] = $config_rules_update;
            $config_data["checks"] = $config_checks;

            $config_data["paths"]["clone_views_file"] = "";
            $config_data["paths"]["clone_tmpls_file"] = "";
            $config_data["paths"]["clone_bits_file"] = "";
            $config_data["paths"]["clone_base_file"] = "";
            g::run("tools.UpdateConfigComplete", $config_data, false);
        }
    },
    "RulesArrayDepth" => function ($rules, $lang, $user_type) {
        $user_types = g::get("config.settings.user_types");
        $langs = g::get("config.settings.langs");
        if (is_array($rules)) {
            foreach ($rules as $key => $val) {
                $tmp_a = $tmp_b = array();
                $any_1 = (!empty($val["any"]["any"])) ? $val["any"]["any"] : array();
                $any_2 = (!empty($val[$lang]["any"])) ? $val[$lang]["any"] : array();

                $any_4 = (!empty($val["any"])) ? $val["any"] : array();
                $lang_1 = (!empty($val[$lang])) ? $val[$lang] : array();

                if (is_array($user_type)) {
                    foreach ($user_type as $i => $ut) {
                        $any_3 = (!empty($val[$ut]["any"])) ? $val[$ut]["any"] : array();
                        $lang_2 = (!empty($val[$lang][$ut])) ? $val[$lang][$ut] : array();
                        $user_type_1 = (!empty($val[$ut])) ? $val[$ut] : array();
                        $user_type_2 = (!empty($val[$ut][$lang])) ? $val[$ut][$lang] : array();
                        $tmp_a[$ut] =  array_merge($any_3, $lang_2, $user_type_1, $user_type_2);
                    }
                    $tmp_b = $tmp_a[$ut];
                    foreach ($tmp_a as $ko => $arro) {
                        $tmp_b = array_intersect($arro, $tmp_b);
                    }
                    $rules[$key] = array_merge($any_1, $any_2, $any_4, $lang_1, $tmp_b);
                } else {
                    $any_3 = (!empty($val[$user_type]["any"])) ? $val[$user_type]["any"] : array();
                    $lang_2 = (!empty($val[$lang][$user_type])) ? $val[$lang][$user_type] : array();
                    $user_type_1 = (!empty($val[$user_type])) ? $val[$user_type] : array();
                    $user_type_2 = (!empty($val[$user_type][$lang])) ? $val[$user_type][$lang] : array();

                    $rules[$key] = array_merge($any_1, $any_2, $any_3, $any_4, $lang_1, $lang_2, $user_type_1, $user_type_2);
                }
            }
        }
        return $rules;
    },
    "ProcessRules" => function () {
        g::run("core.DecideViewLang");
        $op_meta = g::get("op.meta");
        $op_meta_url = $op_meta["url"];
        $lang = $op_meta_url["lang"];
        $view = $op_meta_url["view"];

        $op_meta_user = $op_meta["user"];
        $user_type = $op_meta_user["type"];

        $rules = g::get("config.rules");
        $rules = g::run("core.RulesArrayDepth", $rules, $lang, $user_type);
        g::set("op.meta.rules", $rules);
    },
    "CheckPermissions" => function () {
        $meta = g::get("op.meta");
        $rules = $meta["rules"];
        $url_refer = $meta["url"]["refer"];
        $url_base = $meta["url"]["base"];
        $url_request = $meta["url"]["request"];
        $user_type = $meta["user"]["type"];
        $url_output = $meta["url"]["output"];
        $url_view = $meta["url"]["view"];
        $url_bare = $meta["url"]["bare"];
        $url_lang = $meta["url"]["lang"];
        $no = (!empty($rules["no"])) ? $rules["no"] : array();
        if (
            in_array($url_base, $no) ||
            in_array($url_bare, $no) ||
            in_array($url_view, $no)
        ) {
            return g::run("core.NotAllowed");
        } else {
            /*
                $currl = $url_base . $url_request;
                g::run("core.SessionSet", "entry", $url_refer);
                $entry = g::run("core.SessionGet", "entry");
                $redir = g::run("core.SessionGet", "redir");
                error_log("CheckPermissions= $user_type :: $url_refer :: $currl || Session ID=" . session_id());
                error_log("Entry= $entry || Redir= $redir");
            */
            return true;
        }
    },
    "CheckLogin" => function () {
        $user = array("type" => "guest");
        $su = g::get("op.meta.user");
        if (!empty($su)) {
            $user = $su;
        }
        $user["login_ip"] = g::run("core.GetUserIP");
        return $user;
    },
    "GetUserIP" => function () {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if ($ip === "::1") {
            $ip = "127.0.0.1";
        }
        return $ip;
    },
    "NotAllowed" => function () {
        g::set("op.meta.user.not_allowed", true);
        $murl = g::get("op.meta.url");
        $na = $murl["base"] . $murl["bare"];
        g::run("core.SessionSet", "redir", $na);
        g::run("core.DecideUserRedirection");
        return false;
    },
    "DecideUserRedirection" => function ($is_login = false) {
        $user = g::get("op.meta.user");
        $is_login = ($user["type"] !== "guest") ? true : false;
        $una = g::get("op.meta.user.not_allowed");
        $murl = g::get("op.meta.url");
        $refer = $murl["refer"];
        if ($is_login) {
            // user is logged in
            $redirect_to = "index";
            $url_after_login = g::get("config.mods.pass.opts.url_after_login");
            if (!empty($url_after_login)) {
                $redirect_to = $url_after_login;
            }
            g::run("tools.Say", "user_logged_in");
        } else {
            // user is not allowed / logged out
            $redirect_to = "login";
            $url_after_logout = g::get("config.mods.pass.opts.url_after_logout");
            if (!empty($url_after_logout)) {
                $redirect_to = $url_after_logout;
            }
            g::run("tools.Say", "user_not_logged_in");
        }
        if ($redirect_to === "index") {
            $redirect_to = $murl["base"];
        }

        $na = $murl["base"] . $murl["bare"];
        $redirecter = g::run("core.SessionGet", "redir");

        // removed is_login from below line
        if (!empty($redirecter) && $redirecter != $na) {
            $redirect_to = $redirecter;
        }

        //g::run("tools.Say", "islogin:".json_encode($is_login),1);
        //g::run("tools.Say", "tools-redirect-na:".$na,1);
        //g::run("tools.Say", "tools-redirect-session:".$redirecter,1);
        //g::run("tools.Say", "tools-redirect-triggered: $redirect_to",1);

        g::run("tools.Redirect", $redirect_to);

        if (!empty($redirecter) && $redirecter !== $redirect_to) {
            g::run("core.SessionSet", "redir", $redirecter);
        } else {
            g::run("core.SessionSet", "redir", null);
        }
    },
    "TriggerFunctions" => function () {
        $found_view = g::get("op.meta.url.view");
        if ($found_view && is_callable(g::ret("clone.$found_view"))) {
            g::run("clone.$found_view");
        } elseif ($found_view && is_array(g::get("config.mods"))) {
            $mods = g::get("config.mods");
            foreach ($mods as $mod_name => $mod_config) {
                if (is_callable(g::ret("mods.$mod_name.$found_view"))) {
                    g::run("mods.$mod_name.$found_view");
                    break;
                }
            }
        } else {
            $found_view = "Query";
            if ($found_view && is_callable(g::ret("clone.$found_view"))) {
                g::run("clone.$found_view");
            } else {
                g::run("core.Query");
            }
        }

        g::set("op.meta.url.call", $found_view);
    },
    "RenderOutput" => function () {
        g::set("op.meta.config", g::run("core.CreateMetaConfigClasses"));

        $op = g::get("op");
        $op_type = $op["meta"]["url"]["output"];

        $op_redirect = (!empty($op["meta"]["redirect"])) ? $op["meta"]["redirect"] : "";
        if (!empty($op_redirect)) {
            if (!empty($op["meta"]["msgs"])) {
                g::run("core.SessionSet", "op.meta.msgs", $op["meta"]["msgs"]);
            }
            if ($op_type !== "json") {
                g::run("tools.RedirectNow", $op_redirect);
            } else {
                g::run("core.SessionSet", "op.meta.msgs", "");
            }
        } else {
            g::run("core.SessionSet", "op.meta.msgs", "");
            //$ut = g::get("op.meta.user.type");
            //if ($ut === "guest") {
            // g::run("core.SessionEnd");
            //}
        }

        $api_render_html = g::get("config.settings.api_render_html");
        $api_serve_tmpl = g::get("config.settings.api_serve_tmpl");
        $api_serve_html = g::get("config.settings.api_serve_html");
        $api_serve_data = g::get("config.settings.api_serve_data");

        //ob_start("g_compress");
        ob_start();
        //* HEADERS *******************************************************************/
        //header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        //header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        //header("Cache-Control: no-store, no-cache, must-revalidate");
        //header("Cache-Control: post-check=0, pre-check=0", false);
        //header("Pragma: no-cache");
        if ($op_type === "json") {
            header('Content-type:application/json;charset=utf-8');
            if (!$api_serve_html) {
                unset($op["html"]);
            } else {
                $op["html"] = g::run("ui.ProcessTags", $op["tmpl"]);
            }
            if (!$api_serve_tmpl) {
                unset($op["tmpl"]);
            }
            if (!$api_serve_data) {
                unset($op["bits"]);
                unset($op["data"]);
            }
            echo g::run("tools.JE", $op);
        } elseif ($op_type === "txt") {
            header("Content-Type: text/plain;charset=utf-8");
            echo $op["txt"];
        } else {
            if ($api_render_html) {
                header("Content-Type: text/html;charset=utf-8");
                echo g::run("ui.ProcessTags", $op["tmpl"]);
            }
        }
        $op = ob_get_clean();
        echo $op;
    },
    "CreateMetaConfigClasses" => function () {
        $classes = "";
        $config = g::get("config.settings");
        /*
        "api_render_html": 1,
        "api_serve_html": 1,
        "api_serve_tmpl": 0,
        "api_serve_data": 0,
        "ui_render_html": 0,
        "ui_handle_urls": 0,
        "ui_parse_urls": 0,
         */
        if ($config["api_render_html"]) {
            $classes .= " data-api-rh";
        }
        if ($config["api_serve_html"]) {
            $classes .= " data-api-sh";
        }
        if ($config["api_serve_tmpl"]) {
            $classes .= " data-api-st";
        }
        if ($config["api_serve_data"]) {
            $classes .= " data-api-sd";
        }
        if ($config["ui_render_html"]) {
            $classes .= " data-ui-rh";
        }
        if ($config["ui_handle_urls"]) {
            $classes .= " data-ui-hu";
        }
        if ($config["ui_parse_urls"]) {
            $classes .= " data-ui-pu";
        }
        return trim($classes);
    },
    "CreateSetConfigFilesFolders" => function () {
        g::run("tools.CreateFolder", g::get("config.paths.clone_cache_folder"));
        g::run("tools.CreateFolder", g::get("config.paths.clone_data_folder"));
        g::run("tools.CreateFolder", g::get("config.paths.clone_ui_folder"));

        $htaccess = g::get("config.paths.clone_folder") . ".htaccess";
        g::run("tools.CreateHtaccess", $htaccess);

        $indexhtml = g::get("config.paths.clone_ui_folder") . "index.html";
        g::run("tools.CreateUIIndexHtml", $indexhtml);

        $cuki = g::get("clone.uki");
        g::run("config.env.$cuki", array());

        // do not write config paths to config.
        // plus this path comes from the config CONSTANT.
        g::run("tools.CreateSetConfig", "config", g::get("config.paths.clone_config_file"));

        // if there are specific customizations for this environment, merge them.
        if (!empty(g::get("config.env.$cuki"))) {
            $live_config = g::get("config");
            $env_config = g::get("config.env.$cuki");
            $active_config = g::run("tools.ArrayMergeRecurseProper", $live_config, $env_config);
            g::set("config", $active_config);
        }
        g::run("tools.CreateSetConfig", "config.views", g::get("config.paths.clone_views_file"));
        g::run("tools.CreateSetConfig", "config.mods", g::get("config.paths.clone_mods_file"));
        g::run("tools.CreateSetConfig", "config.tmpls", g::get("config.paths.clone_tmpls_file"));
        g::run("tools.CreateSetConfig", "config.bits", g::get("config.paths.clone_bits_file"));
        g::run("tools.CreateSetConfig", "config.base", g::get("config.paths.clone_base_file"));
    },
    "ParseUrlQuery" => function () {
        // Get complete query
        $gq = g::get("get");
        if (empty($gq)) {
            g::set("get", "");
        }
        $query_complete = trim($gq);

        if (strpos($query_complete, ";bot") > -1) {
            $query_complete = str_replace(";bot", "", $query_complete);
            g::set("op.meta.bot", 1);
        }

        if (strpos($query_complete, ";ping") > -1) {
            $query_complete = str_replace(";ping", "", $query_complete);
            g::set("op.meta.ping", 1);
        }

        // Select a default language
        $bcl = g::get("config.settings.langs");
        $clone_lang = $bcl[0];

        // Set default index page and view path for clone
        $clone_index = $query_path = "index";

        if ($query_complete == ".json") {
            header("Location: $clone_index.json");
            die;
        }

        // Set other variables defaults
        $query_complete_clean = $query_bare = $query_folders = $query_actual = $query_match
            = $query_args = $query_output = $path_view
            = $query_css = $path_gqls = $path_bits = null;

        if (strpos($query_complete, ".json") > -1) {
            $query_complete_clean = str_replace(".json", "", $query_complete);
            $query_complete_clean = str_replace("index", "", $query_complete_clean);
        } else {
            $query_complete_clean = str_replace("index", "", $query_complete);
        }

        $clone_base = g::get("op.meta.url.base");

        g::set("op.meta.clone", g::run("core.SetCloneInfo"));
        g::set("op.meta.clone.index", $clone_index);

        // THE OUTPUT > META > URL DETAILS
        g::set("op.meta.url", array(
            "base" => $clone_base,
            "request" => $query_complete, //$this->str_url_safe($uc);
            "clean" => $query_complete_clean, //$this->str_url_safe($uc);
            "bare" => $query_bare, //$uac;
            "args" => $query_args, //$ua;
            "folder" => $query_folders, //$uf;
            "match" => $query_match, //$this->str_url_safe($um);
            "output" => $query_output, //$this->str_url_safe($uo);
        ));

        // IS THERE A REFERER
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        // THERE IS NO QUERY STRING...
        if (empty($query_complete)) {
            $lv = g::get("config.views.Index.urls." . $clone_lang);
            $ld = g::get("config.bits.url.Index." . $clone_lang);
            if (!empty($lv)) {
                if (is_array($lv)) {
                    $clone_index = $lv[0];
                } else {
                    $clone_index = $lv;
                }
            } elseif (!empty($ld)) {
                if (is_array($ld)) {
                    $clone_index = $ld[0];
                } else {
                    $clone_index = $ld;
                }
            }
            $query_actual = $query_complete = $query_bare = $clone_index;
        }
        // THERE IS A QUERY STRING
        else {
            $query_bare = $query_actual = $query_complete;
            // THERE IS A SLASH.. LETS DECIDE FOLDER STRUCTURE.
            // IF there is no = or ; before it..
            if (strpos($query_complete, "/") !== false) {
                $qArr = explode('/', trim($query_complete));
                if (strpos($qArr[0], ";") === false && strpos($qArr[0], "=") === false) {
                    $ca = count($qArr);
                    $currf = &$query_folders;
                    for ($i = 0; $i + 1 < $ca; $i++) {
                        $query_folders[] = $qArr[$i];
                    }
                    $query_bare = $query_actual = $qArr[$ca - 1];
                }
            }
            // THERE IS A DOT.. LETS DECIDE OUTPUT TYPE.
            // IF it is defined in config
            if (strpos($query_bare, ".") !== false) {
                $ot = g::get("config.settings.output_types");
                if (count($ot) > 0) {
                    foreach ($ot as $key => $yeah) {
                        $qb = trim($query_bare);
                        if (strlen($qb) - strlen(".$key") == strrpos($qb, ".$key")) {
                            $query_output = $key;
                            $query_bare = $query_actual = str_replace(".$key", "", $qb);
                        }
                    }
                }
            }

            // IS THERE A REDIRECT ~
            if (strpos($query_bare, "~") !== false) {
                $qr = explode("~", $query_bare);
                $query_bare_new = $qr[0];
                if (count($qr) == 2) {
                    $referer = $qr[1];
                } else {
                    $referer = str_replace("$query_bare_new~", "", $query_bare);
                }
                $query_bare = $query_bare_new;
            }

            // THERE IS ; AND = SO THERE ARE ARGS HERE!
            if (strpos($query_bare, ";") !== false || strpos($query_bare, "=") !== false) {
                $query_args = g::run("core.GQLParse", $query_bare);
                reset($query_args);
                $query_actual = key($query_args);
            } else {
                $query_match = $query_bare;
            }
        }

        $meta_url = array(
            "base" => $clone_base,
            "request" => $query_complete, //$this->str_url_safe($uc);
            "clean" => $query_complete_clean, //$this->str_url_safe($uc);
            "bare" => $query_bare, //$uac;
            "args" => $query_args, //$ua;
            "folder" => $query_folders, //$uf;
            "match" => $query_match, //$this->str_url_safe($um);
            "output" => $query_output, //$this->str_url_safe($uo);
            "refer" => $referer,
            //"redirecter" => $redirecter,
        );

        $uqx = g::get("clone.uqx");
        if (!empty($uqx)) {
            $meta_url["exts"] = $uqx;
        }

        $config_urls = g::get("config.urls");
        if (empty($config_urls["clone_ui"])) {
            $config_urls["clone_ui"] = $clone_base . "ui/";
        }
        $meta_url = array_merge($meta_url, $config_urls);
        g::set("op.meta.url", $meta_url);
        //print_r($meta_url);
        //die;
    },
    "GetServerUrl" => function () {
        $server_request_uri = rawurldecode($_SERVER['REQUEST_URI']);
        //echo "1. server_request_uri: " . $server_request_uri . "\n";
        $server_query_string = rawurldecode($_SERVER['QUERY_STRING']);

        // output type detection
        $output = false;
        if ((strpos($server_request_uri, ".php") === false && strpos($server_request_uri, ".") > -1) ||
            (strpos($server_request_uri, ".php") > -1 && substr_count($server_request_uri, '.') > 1)
        ) {
            $output = current(array_reverse(explode('.', $server_request_uri)));
            $server_request_uri = str_replace(".$output", "", $server_request_uri);
            $server_query_string = str_replace(".$output", "", $server_query_string);
        }
        // bot detection is genes built-in analytics module's requirement
        $is_bot = false;
        if (strpos($server_query_string, ";bot") > -1) {
            $is_bot = true;
            $server_request_uri = str_replace(";bot", "", $server_request_uri);
            $server_query_string = str_replace(";bot", "", $server_query_string);
        }
        $server_query_extras = "";

        if (strpos($server_request_uri, "index.php?") > -1) {
            // genes urls may have index.php? if the server has no url_rewrite
            $server_request_uri = str_replace("index.php?", "", $server_request_uri);
            // then, we should remove the querystring part, we have left the true base url
            $server_request_uri = str_replace($server_query_string, "", $server_request_uri);

            //echo "3. $server_request_uri\n";
            //echo "3,5. $server_query_string\n";

            //$server_query_string cleanup any external query parts
            if (strpos($server_query_string, "?") > -1) {
                $sqsr = explode("?", $server_query_string);
                $server_query_extras = str_replace($sqsr[0] . "?", "", $server_query_string);
                $server_query_string = $sqsr[0];
            }
        } else if (strpos($server_request_uri, "?") > -1) {
            //echo "3. $server_request_uri\n";
            //echo "3,5. $server_query_string\n";

            $sqsr = explode("?", $server_request_uri);
            $server_query_extras = str_replace($sqsr[0] . "?", "", $server_request_uri);
            $server_request_uri = $sqsr[0];

            // then, we should remove the querystring part, we have left the true base url
            $server_request_uri = str_replace($server_query_string, "", $server_request_uri);
        } else if (strpos($server_request_uri, "&") > -1) {
            //echo "3. $server_request_uri\n";
            //echo "3,5. $server_query_string\n";

            $sqsr = explode("&", $server_request_uri);
            $server_query_extras = str_replace($sqsr[0] . "&", "", $server_request_uri);
            $server_request_uri = $sqsr[0];

            $sqsr = explode("&", $server_query_string);
            $server_query_string = $sqsr[0];

            // then, we should remove the querystring part, we have left the true base url
            $server_request_uri = str_replace($server_query_string, "", $server_request_uri);
        } else {
            // then, we should remove the querystring part, we have left the true base url
            // but we should replace the last occurrence, otherwise it just destroy the domain.
            $last_occurrence_pos = strrpos($server_request_uri, $server_query_string);
            if ($last_occurrence_pos !== false) {
                $server_request_uri = substr_replace($server_request_uri, '', $last_occurrence_pos, strlen($server_query_string));
            }
        }

        // protocol :: http or https
        if (!empty($_SERVER["REQUEST_SCHEME"])) {
            $protocol = $_SERVER["REQUEST_SCHEME"];
        } else {
            $s = empty($_SERVER["HTTPS"]) ? '' : (($_SERVER["HTTPS"] == "on") ? "s" : "");
            $protocol = strtolower(explode("/", $_SERVER["SERVER_PROTOCOL"])[0]) . $s;
            $protocol = g::run("tools.StrLeft", strtolower($_SERVER["SERVER_PROTOCOL"]), "/") . $s;
        }
        // port :: does it run on another port
        $port = ($_SERVER["SERVER_PORT"] == "80" || $_SERVER["SERVER_PORT"] == "443") ? "" : (":" . $_SERVER["SERVER_PORT"]);
        // domain :: main url part
        $domain = $_SERVER['SERVER_NAME'];

        // collect the clone_url
        $clone_url = "$protocol://$domain$port$server_request_uri";

        // add output to the clean server_query_string
        if ($output !== false) {
            $server_query_string .= ".$output";
        }

        // clone query will be passed to be processed
        $clone_query = $server_query_string;
        $clone_query_extras = $server_query_extras;
        //echo "4. $clone_url\n";
        //echo "5. $clone_query\n";
        //echo "6. $clone_query_extras\n";
        /*
        // $server_request_uri = rawurldecode(str_replace("?", "&", $_SERVER['REQUEST_URI']));
        $server_request_uri = rawurldecode(str_replace(array("?", "&"), array(";", ";"), $_SERVER['REQUEST_URI']));
        // $server_query_string = rawurldecode(str_replace("?", "&", $_SERVER['QUERY_STRING']));
        $server_query_string = rawurldecode(str_replace(array("?", "&"), array(";", ";"), $_SERVER['QUERY_STRING']));

        echo $_SERVER['REQUEST_URI'] . "\n" . "$server_request_uri\n" . $_SERVER['QUERY_STRING'] . "\n" . "$server_query_string\n";

        // [server_request_uri] => /something_app/pair=google;token;code=4/0AfgeXvvutfu4_MQ4eG0bxbPGFt6qZTYA1PRPk97QytooXlTmyR33p21kjbkSNJPA8Fwz8Q;scope=email profile https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/business.manage openid;authuser=2;hd=walksineurope.com;prompt=none
        // [server_query_string] => pair=google;token
        $clone_url_path = $server_request_uri;
        $clone_query = $server_query_string;
        $clone_url_path = str_replace("index.php;", "", $clone_url_path);
        $clone_url_path = str_replace("index.php", "", $clone_url_path);
        $clone_query = str_replace("index.php;", "", $clone_query);
        $clone_query = str_replace("index.php", "", $clone_query);
        if (!empty($server_query_string)) {
            if (strpos($clone_url_path, "/;") > -1) {
                if (strpos($clone_query, "/;") > -1) {
                    $qarr = explode("$clone_query", $clone_url_path);
                } else if (strpos($clone_query, ";") > -1) {
                    $qarr = explode("/$clone_query", $clone_url_path);
                } else {
                    $qarr = explode("$clone_query", $clone_url_path);
                }
            } else {
                $qarr = explode("/$clone_query", $clone_url_path);
            }
            $clone_url_path = $qarr[0];
            $clone_query = $clone_query . $qarr[1];
        } else if (strpos($clone_url_path, "/;") > -1) {
            $qarr = explode("/;", $clone_url_path);
            $clone_url_path = $qarr[0];
            $clone_query = $clone_query . $qarr[1];
        }
        $clone_query = ltrim($clone_query, ';');
        $clone_query = rtrim($clone_query, ';');
        $clone_url_path = rtrim($clone_url_path, ';');

        print_r(array("server_request_uri" => $server_request_uri, "server_query_string" => $server_query_string, "clone_query" => $clone_query, "clone_url_path" => $clone_url_path));
        */
        // Cleanup clone_url and convert to url key so that it can be rendered on multiple domains
        $clone_url = rtrim($clone_url, '&');
        $clone_url = rtrim($clone_url, '/');
        $clone_url = "$clone_url/";
        g::set("clone.url", $clone_url);
        $clone_url_key = str_replace(array("https://", "http://", ".", ":", "/"), array("", "", "-", "-", "_"), $clone_url);
        $clone_url_key = substr($clone_url_key, 0, -1);

        g::set("clone.uki", $clone_url_key);
        g::set("clone.uqs", $clone_query);
        g::set("clone.uqx", $clone_query_extras);

        if ($clone_query === "403.shtml") {
            die;
        }
        //print_r(g::get("clone"));
        if ($is_bot) {
            $clone_query .= ";bot";
        }
        g::set("get", g::run("tools.CleanQS", $clone_query));
        //echo "GET! >>>> " . g::get("get") . "\n";
        //die;
    },
    "SetCloneInfo" => function () {
        $clone = array("type" => "default");
        $sc = g::run("core.SessionGet", "clone");
        if (!empty($sc)) {
            $clone = $sc;
        }
        return $clone;
    },
    "PathTranslateFindViewSetLang" => function ($paths) {
        // FIRST COME FIRST SERVER
        // TRIES TO FIND THE FIRST SUITABLE
        // FIRST TRIES TO MATCH EQUAL
        // THEN TRIES TO MATCH IF THE BEGINNING IS SAME
        $views = g::get("config.views");
        $langs = g::get("config.settings.langs");
        g::set("op.meta.url.lang", $langs[0]);

        $mods = g::get("config.mods");
        if (!empty($mods)) {
            foreach ($mods as $mod_name => $mod_config) {
                if ($mod_config !== false) {
                    if (!empty($mod_config["views"])) {
                        $mod_views = $mod_config["views"];
                        $nv = array_merge($views, $mod_views);
                        $views = $nv;
                    }
                }
            }
        }

        foreach ($views as $key => $details) {
            $urls = $details["urls"];
            foreach ($langs as $lang) {
                $url_lang = false;
                $is_url_array = false;
                if (!empty($urls[$lang])) {
                    $url_lang = $urls[$lang];
                    if (is_array($urls[$lang])) {
                        $is_url_array = true;
                    }
                }

                if ($url_lang !== false) {
                    foreach ($paths as $path) {
                        //echo "$path\n";
                        if ($is_url_array === false) {
                            if ($url_lang === $path) {
                                // found.
                                g::set("op.meta.url.lang", $lang);
                                g::set("op.meta.url.vurl", $url_lang);
                                return $key;
                            } elseif (strpos($path, $url_lang) === 0) {
                                // found.
                                g::set("op.meta.url.lang", $lang);
                                g::set("op.meta.url.vurl", $url_lang);
                                return $key;
                            }
                        } else {
                            if (in_array($path, $url_lang)) {
                                // found
                                g::set("op.meta.url.lang", $lang);
                                g::set("op.meta.url.vurl", $path);
                                return $key;
                            } else {
                                foreach ($url_lang as $urlp) {
                                    if (strpos($path, $urlp) === 0) {
                                        // found.
                                        g::set("op.meta.url.lang", $lang);
                                        g::set("op.meta.url.vurl", $urlp);
                                        return $key;
                                    } else {
                                        g::set("op.meta.url.vurl", "query");
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return "";
    },
    "GQLParse" => function ($gql) {
        $gqls = array();
        if (strpos($gql, ";") !== false) {
            $qArr = explode(';', trim($gql));
            foreach ($qArr as $argbits) {
                if (strpos($argbits, "=") !== false) {
                    $qArrb = explode('=', trim($argbits));
                    if (strpos($qArrb[1], ",") !== false) {
                        $gqls[$qArrb[0]] = explode(",", $qArrb[1]);
                    } else {
                        $gqls[$qArrb[0]] = $qArrb[1];
                    }
                } else {
                    $gqls[trim($argbits)] = 1;
                }
            }
        } elseif (strpos($gql, "=") !== false) {
            $qArr = explode('=', trim($gql));
            $gqls[$qArr[0]] = $qArr[1];
        }
        return $gqls;
    },
    "GQLtoSQL" => function ($cid, $table_name, $args) {
        $sql = array();
        $sql_array = array();

        $sql_query = $sql_vals = null;

        /*
        Alphanumeric :    a b c d e f g h i j k l m n o p q r s t u v w x y z A B C D E F G H I J K L M N O P Q R S T U V W X Y Z 0 1 2 3 4 5 6 7 8 9
        Unreserved :    - _ . ~
        Reserved :    ! * ' ( ) ; : @ & = + $ , / ? % # [ ]
        Source : https://developers.google.com/maps/documentation/urls/url-encoding
        FILTERS
        /185 --> match=185 (id)
        /title-of-the-content --> match=title-of-the-content (safe_title)
        /qWxm --> match=qWxm (short_url)
         * Other than that a url can have
        (;) | (=) | (,) | (-) | (_) | (~)
            * And other than "match", url can be queried with
            "type" | "tag" | "find" | "date" | "user" | "sort" | "list" | "page"
            "price" | "location" ???
            "stat" (like|view) ???
            args are separated with ;
            plus (+) is not allowed but if necessary ~ is available, eg: 2017~2020
            dash (-) is also used in safe url so can only be used in the beginning of word eg: -metal
            /arg=this,that || query this or that
            /arg=this_that || query this and that
            /arg=this,those,-that || query this or those but not that
            /arg=2d || query in last 2 days
            /arg=3m || query in last 3 months
            /arg=1y || query in last 1 year
            /price=100-- || price less than 100
            /price=100++ || price greater than 100
            /type=products;tag=mittens,hats;date=3m;price=50--;list=20
            || list 20 mitten or hat products made in the last 3 months, price under 50
            type=bundle,content;
            tag=crochet,yarn,-bulky;
            find=mitten,-glove;
            date=-30d;
            user=-birdy99;
            price=20TL-50USD;
            location=TR,CA,US;
            sort=day-desc,title-asc;
            pattern-list-20.pdf
            list=50;
            page=2
            stat=like-top,view-bottom
            additionally....
            act=edit|update|delete|new
            id=518
            type=contents;act=edit;id=65

        p, n, o, g, f, s, t, i, u
         */
        $sql_query = $sql_query_raw = "SELECT * FROM $table_name WHERE cid=$cid ";
        $sql_vals = array();

        // s for state
        if (!empty($args["s"])) {
            if ($args["s"] !== "any") {
                $sql_query .= "AND (g_state=?)";
                $sql_vals = array_merge($sql_vals, array($args["s"]));
            }
        } else {
            $sql_query .= "AND (g_state=?)";
            $sql_vals = array_merge($sql_vals, array("public"));
        }

        // t for type
        if (!empty($args["t"])) {
            $sql_query .= "AND (g_type=?)";
            $sql_vals = array_merge($sql_vals, array($args["t"]));
        }

        // u for user
        if (!empty($args["u"])) {
            $sql_query .= "AND (uhc=?)";
            $sql_vals = array_merge($sql_vals, array($args["u"]));
        }

        // i for match (id | hash | alias | name)
        if (!empty($args["i"])) {
            if ($table_name === "g_items") {
                // $sql_query .= "AND (id=? OR g_hash=? OR g_alias=? OR g_name=?)";
                // $sql_vals = array_merge($sql_vals, array($args["i"], $args["i"], $args["i"], $args["i"]));
                if (is_numeric($args["i"])) {
                    // The value is numeric, so include 'id' in the query
                    $sql_query .= " AND (id=? OR g_hash=? OR g_alias=? OR g_name=?)";
                    $sql_vals = array_merge($sql_vals, array($args["i"], $args["i"], $args["i"], $args["i"]));
                } else {
                    // The value is not numeric, so exclude 'id' from the query
                    $sql_query .= " AND (g_hash=? OR g_alias=? OR g_name=?)";
                    $sql_vals = array_merge($sql_vals, array($args["i"], $args["i"], $args["i"]));
                }
            } else {
                $sql_query .= "AND (id=? OR g_hash=? OR g_alias=?)";
                $sql_vals = array_merge($sql_vals, array($args["i"], $args["i"], $args["i"]));
            }
        }

        // f for free text find (alias | name | blurb | text | bits | key | value | void | labels)
        if (!empty($args["f"]) || !empty($args["find"])) {
            $args["f"] = (!empty($args["f"])) ? $args["f"] : $args["find"];
            if ($table_name === "g_items") {
                $sql_query .= "AND (g_alias LIKE(?) OR g_name LIKE(?) OR g_blurb LIKE(?) OR g_text LIKE(?) OR g_bits LIKE(?))";
                $sql_vals = array_merge($sql_vals, array("%" . $args["f"] . "%", "%" . $args["f"] . "%", "%" . $args["f"] . "%", "%" . $args["f"] . "%", "%" . $args["f"] . "%"));
            } else if ($table_name === "g_events") {
                $sql_query .= "AND (g_key LIKE(?) OR g_value LIKE(?) OR g_void LIKE(?) OR g_bits LIKE(?))";
                $sql_vals = array_merge($sql_vals, array("%" . $args["f"] . "%", "%" . $args["f"] . "%", "%" . $args["f"] . "%", "%" . $args["f"] . "%"));
            } else if ($table_name === "g_labels") {
                $sql_query .= "AND (g_key LIKE(?) OR g_value LIKE(?) OR g_bits LIKE(?))";
                $sql_vals = array_merge($sql_vals, array("%" . $args["f"] . "%", "%" . $args["f"] . "%", "%" . $args["f"] . "%"));
            } else {
                $sql_query .= "AND (g_alias LIKE(?) OR g_blurb LIKE(?) OR g_text LIKE(?) OR g_bits LIKE(?) OR g_labels LIKE(?))";
                $sql_vals = array_merge($sql_vals, array("%" . $args["f"] . "%", "%" . $args["f"] . "%", "%" . $args["f"] . "%", "%" . $args["f"] . "%", "%" . $args["f"] . "%"));
            }
        }

        // g for genes tags
        // ~ has tag a or b
        // , has tag a and b
        // ,- has tag a but not b
        if (!empty($args["g"]) || !empty($args["tag"])) {
            $args["g"] = (!empty($args["g"])) ? $args["g"] : $args["tag"];
            $tagand = false;
            if (is_string($args["g"]) && strpos($args["g"], "~") > -1) {
                $args["g"] = explode("~", $args["g"]);
            } else if (is_array($args["g"])) {
                $tagand = true;
            }
            if ($table_name === "g_persons") {
                $sub_label = '$.person_labels';
                $tag_label = $args["g"][0];
                if (!empty($tag_label)) {
                    $sub_label = '$.' . $tag_label;
                }
                $sql_query .= "AND JSON_CONTAINS(`g_labels`, ?, '$sub_label')=1";
            } else if ($table_name === "g_events") {
                $sql_query .= "AND JSON_CONTAINS(`g_labels`, ?, '$.event_labels')=1";
            } else {
                if (is_array($args["g"])) {
                    $sql_query .= " AND (";
                    for ($i = 0; $i < count($args["g"]); $i++) {
                        if ($tagand === true) {
                            if ($args["g"][$i][0] === '-') {
                                $args["g"][$i] = substr($args["g"][$i], 1);
                                $sql_query .= "JSON_CONTAINS(`g_labels`, ?, '$.item_labels')=0 AND ";
                            } else {
                                $sql_query .= "JSON_CONTAINS(`g_labels`, ?, '$.item_labels')=1 AND ";
                            }
                        } else {
                            $sql_query .= "JSON_CONTAINS(`g_labels`, ?, '$.item_labels')=1 OR ";
                        }
                    }
                    $sql_query = substr($sql_query, 0, -4) . ")";
                } else {
                    $sql_query .= "AND JSON_CONTAINS(`g_labels`, ?, '$.item_labels')=1";
                }
            }
            $tags = (!is_array($args["g"])) ? explode(",", $args["g"]) : $args["g"];
            $tags = array_map(function ($item) {
                return g::run("tools.JE", $item);
            }, $tags);
            $sql_vals = array_merge($sql_vals, $tags);
            // print_r($sql_query);echo "\n";print_r($sql_vals);
        }

        // o for order by (random | az / za | date | update | sdate | edate | type | state | name | email | alias | hash | key | context)
        if (!empty($args["o"])) {
            if (strpos($args["o"], "-") > -1) {
                $ss = explode("-", $args["o"]);
            }
            if ($args["o"] === "random") {
                $sql_query .= " ORDER BY rand()";
            } else {
                $sort = ($ss[1] == "az") ? "asc" : "desc";
                $col = "id";
                if ($ss[0] == "date") {
                    $col = "tsc";
                } else if ($ss[0] == "update") {
                    $col = "tsu";
                } else if ($ss[0] == "sdate") {
                    $col = "tss";
                } else if ($ss[0] == "edate") {
                    $col = "tse";
                } else if ($ss[0] == "type") {
                    $col = "g_type";
                } else if ($ss[0] == "state") {
                    $col = "g_state";
                } else if ($ss[0] == "name") {
                    $col = "g_name";
                } else if ($ss[0] == "email") {
                    $col = "g_email";
                } else if ($ss[0] == "alias") {
                    $col = "g_alias";
                } else if ($ss[0] == "hash") {
                    $col = "g_hash";
                } else if ($ss[0] == "key") {
                    $col = "g_key";
                } else if ($ss[0] == "context") {
                    $col = "g_context";
                } else {
                    $col = "id";
                }
                $sql_query .= " ORDER BY " . $col . " " . $sort;
            }
        } else {
            $sql_query .= " ORDER BY id desc";
        }

        // n for n items to get 
        if (empty($args["n"])) {
            $args["n"] = 50;
        }

        if (!empty($args["n"])) {
            $sql_query .= " LIMIT " . $args["n"];
        }

        // p for page number
        if (empty($args["p"])) {
            $args["p"] = 1;
        }

        if (!empty($args["p"])) {
            $start = ($args["p"] - 1) * $args["n"];
            $sql_query .= " OFFSET " . $start;
        }

        if (!empty($sql_query) && $sql_query !== $sql_query_raw) {
            $sql_array = array($sql_query, $sql_vals, $args["n"], $args["p"]);
            //print_r($sql_array);die;
            return $sql_array;
        } else {
            return false;
        }
    },
    "CleanQueryLinks" => function ($list, $edit = null, $add = null) {
        $murl = g::get("op.meta.url");
        $args = $murl["args"];
        $lang = $murl["lang"];
        $list_curr = $murl["bare"];
        if (!empty($edit)) {
            $edit = $edit[$lang];
            $add = $add[$lang];

            if (!empty($args["edit"])) {
                if (!empty($args["hash"])) {
                    $hash = $args["hash"];
                    $list_curr = str_replace($edit, $list[$lang], $murl["bare"]);
                    $list_curr = str_replace(";hash=$hash", "", $list_curr);
                } else if (!empty($args["eid"])) {
                    $eid = $args["eid"];
                    $list_curr = str_replace($edit, $list[$lang], $murl["bare"]);
                    $list_curr = str_replace(";eid=$eid", "", $list_curr);
                }
            } elseif (!empty($args["add"])) {
                $list_curr = str_replace($add, $list[$lang], $murl["bare"]);
            }

            $links = array(
                "list" => $list_curr,
                "edit" => str_replace($list[$lang], $edit, $list_curr),
                "add" => str_replace($list[$lang], $add, $list_curr),
            );
        } else {
            $links = array(
                "list" => $list_curr
            );
        }
        return $links;
    },
    "CleanSideQueryLinks" => function ($list, $edit, $add) {
        $murl = g::get("op.meta.url");
        $base = $murl["base"];
        $lang = $murl["lang"];
        $edit = $edit[$lang];
        $add = $add[$lang];

        $links = array(
            "list" => $list[$lang],
            "edit" => $edit,
            "add" => $add,
        );

        return $links;
    },
    "Query" => function ($loops = array(), $return = false) {
        $config = g::get("config");
        $dataset = array();
        $dataset_sql_array = array();
        $executing_query = array();

        $cid = g::run("db.GetCloneId");

        if ($cid !== false) {
            $args = g::get("op.meta.url.args");

            if (empty($loops) || (!empty($loops) && empty($loops["main"]))) {
                $table = "items";
                $db_table = $config["db"]["tables"][$table];
                $conn = $db_table[0];
                $table_name = $db_table[1];

                $match = g::get("op.meta.url.match");

                if (!empty($args)) {
                    // args are set now must be converted to sql queries to select from db
                    $dataset_sql_array["main"] = g::run("core.GQLtoSQL", $cid, $table_name, $args);
                } else if (!empty($match)) {
                    // args (match is an arg) are set now must be converted to sql queries to select from db
                    $dataset_sql_array["main"] = g::run("core.GQLtoSQL", $cid, $table_name, array("i" => $match));
                } else if (empty($args)) {
                    $args = array();
                    if (empty($args["o"])) {
                        $args["o"] = "date-za";
                    }
                    $dataset_sql_array["main"] = g::run("core.GQLtoSQL", $cid, $table_name, $args);
                }
                $executing_query[$conn] = $dataset_sql_array;
            }

            if (!empty($loops)) {
                foreach ($loops as $name => $query_pack) {
                    $table = $query_pack[0];
                    $db_table = $config["db"]["tables"][$table];
                    $conn = $db_table[0];
                    $table_name = $db_table[1];
                    $query_bare = $query_pack[1];
                    $args_query = g::run("core.GQLParse", $query_bare);

                    if ($name === "main") {
                        $args = (is_array($args)) ? $args : array();
                        $args_c = array_merge($args_query, $args);
                    } else {
                        $args_c = $args_query;
                    }

                    $dataset_sql_array[$name] = g::run("core.GQLtoSQL", $cid, $table_name, $args_c);
                    if ($name === "main") {
                        if (!empty($args["o"]) && strpos($args["o"], "-") > -1) {
                            $ss = explode("-", $args["o"]);
                            $sort = ($ss[1] == "az") ? "asc" : "desc";
                            g::set("op.meta.url.sort." . $ss[0], "sort_" . $ss[1]);
                        }
                        $dataset_sql_array[$name][] = true;
                    }
                }
            }
            $executing_query = array_merge($executing_query, array($conn => $dataset_sql_array));
            $dataset = g::run("db.Execute", $executing_query);
        }
        $view = g::get("op.meta.url.view");
        $call = g::get("op.meta.url.call");
        $base = g::get("op.meta.url.base");
        if (empty($view) && empty($dataset)) {
            g::run("tools.Say", "Your query returned nothing.", 5);
            g::run("tools.Redirect", $base);
        } else {
            if ($return === false) {
                g::set("op.data", $dataset);
            } else {
                return $dataset;
            }
        }
    },
    "Check" => function ($key) {
        $value = null;
        $keyExists = false;
        if (strpos($key, ".") > -1) {
            $ps = explode('.', $key);
            $value = &$g;
            foreach ($ps as $part) {
                $value = &$value[$part];
            }
            if (isset($value)) {
                $keyExists = true;
            }
        } else {
            $value = &$g[$key];
            if (isset($g[$key])) {
                $keyExists = true;
            }
        }
        if ($keyExists) {
            if (is_callable($value)) {
                return "function";
            } elseif (is_array($value)) {
                return "array";
            } else {
                return "string";
            }
        }
        return false;
    },
    "WriteModViewsLabelsTmplsOptsRules" => function ($mod_name, $v, $b, $t, $o, $r) {
        if (is_array($v) && !empty($v)) {
            $views = g::get("config.mods.$mod_name.views");
            $nv = $v;
            if (!empty($views)) {
                $nv = array_merge($views, $v);
            }
            g::set("config.mods.$mod_name.views", $nv);
        }
        if (is_array($b) && !empty($b)) {
            $bits = g::get("config.mods.$mod_name.bits");
            $nb = $b;
            if (!empty($bits)) {
                $nb = array_merge($bits, $b);
            }
            g::set("config.mods.$mod_name.bits", $nb);
        }
        if (is_array($t) && !empty($t)) {
            $tmpls = g::get("config.mods.$mod_name.tmpls");
            $nt = $t;
            if (!empty($tmpls)) {
                $nt = array_merge($tmpls, $t);
            }
            g::set("config.mods.$mod_name.tmpls", $nt);
        }
        if (is_array($o) && !empty($o)) {
            $opts = g::get("config.mods.$mod_name.opts");
            $no = $o;
            if (!empty($opts)) {
                $no = array_merge($opts, $o);
            }
            g::set("config.mods.$mod_name.opts", $no);
        }
        if (is_array($r) && !empty($r)) {
            $rules = g::get("config.rules");
            $nr = $r;
            if (!empty($rules) || !empty($nr)) {
                $nr = array_merge_recursive($rules, $r);
            }
            g::set("config.rules", $nr);
        }
    },
    "EmbedBits" => function () {
        $op_meta_url = g::get("op.meta.url");
        $lang = $op_meta_url["lang"];
        $view = $op_meta_url["view"];
        // $call = $op_meta_url["call"];
        $bits = g::get("config.bits");
        $view_bits = g::get("config.views.$view.bits");

        $mods = g::get("config.mods");
        if (!empty($mods)) {
            foreach ($mods as $mod_name => $mod_config) {
                if ($mod_config !== false) {
                    if (!empty($mod_config["bits"])) {
                        $mod_bits = $mod_config["bits"];
                        $nv = array_merge($bits, $mod_bits);
                        $bits = $nv;
                    }
                }
            }
        }

        if (is_array($view_bits)) {
            $bits = array_merge($bits, $view_bits);
        }
        $bits = g::run("tools.BitsArrayDepth", $bits, $lang);
        g::set("op.bits", $bits);
    },
    "SessionStart" => function () {
        session_start();
        // WHEN SESSION STARTS THERE IS AN ID GENERATED
        // 
    },
    "SessionEnd" => function () {
        setcookie(session_id(), "", time() - 3600);
        // session_write_close();
        session_unset();
        session_destroy();
        $clone_hash = g::get("config.clone.hash");
        g::run("core.CookieDel", "genes_$clone_hash");
        g::set("cookie", null);
        g::set("session", null);
    },
    "SessionSetUser" => function ($value) {
        // ALL USER SESSIONS MUST BE SET WITH THIS
        // SO THAT WE CAN MANAGE EASIER.
        // 
        $clone_hash = g::get("config.clone.hash");
        $_SESSION[$clone_hash]["op.meta.user"] = $value;
        $session = g::get("session");
        $session["op.meta.user"] = $value;
        g::set("session", $session);
    },
    "SessionSet" => function ($key, $value) {
        $clone_hash = g::get("config.clone.hash");
        $_SESSION[$clone_hash][$key] = $value;
        $session = g::get("session");
        $session[$key] = $value;
        g::set("session", $session);
    },
    "SessionGet" => function ($key) {
        return g::get("session.$key");
    },
    "SessionGetSet" => function ($key = "") {
        $session = g::get("session");
        if (empty($key)) {
            foreach ($session as $key => $value) {
                g::set($key, $value);
            }
        } else {
            $value = $session[$key];
            g::set($key, $value);
        }
    },
    "CookieAdd" => function ($key, $value, $expires) {
        setcookie($key, $value, $expires);
    },
    "CookieSet" => function ($key, $value, $expires = 0, $name = "genes") {
        $clone_hash = g::get("config.clone.hash");
        $cookie = g::get("cookie");
        if (empty($cookie)) {
            $cookie = array();
        }
        $cookie[$key] = $value;
        g::set("cookie", $cookie);
        $contents = g::run("tools.JE", $cookie);

        if ($expires === "1year") {
            $expires = time() + (365 * 24 * 60 * 60);
        } else if ($expires === "1day") {
            $expires = time() + (24 * 60 * 60);
        } else if ($expires === "1hour") {
            $expires = time() + (60 * 60);
        }

        setcookie("$name" . "_" . "$clone_hash", $contents, $expires);
    },
    "CookieDel" => function ($key) {
        setcookie($key, null, time() - 3600);
    },
    "DecideViewLang" => function () {
        $url = g::get("op.meta.url");

        $g_query_call = $query_name = null;
        $query_paths = array();
        // Function Name could be "bare"
        // Function Name could be "folder[0]"
        // Function Name could be "array_keys(args)[0]"
        // Else if match=bare then filter contents (id=,short_url=,safe_url=)
        if (
            !empty($url["bare"]) ||
            ($url["bare"] === $url["match"] || $url["bare"] === g::get("op.meta.clone.index"))
        ) {
            $query_paths[] = $url["bare"];
        }

        if (!empty($url["folder"])) {
            $query_paths[] = $url["folder"][0];
        }

        if (!empty($url["args"])) {
            $fn = array_keys($url["args"]);
            $query_paths[] = $fn[0];
        }

        $found_view = g::run("core.PathTranslateFindViewSetLang", $query_paths);
        g::set("op.meta.url.view", $found_view);
        g::run("core.ConsiderBitsLinks");
    },
    "ConsiderBitsLinks" => function () {
        $bare = g::get("op.meta.url.bare");
        $bits = g::get("config.bits");
        if (!empty($bits["links"])) {
            $links = $bits["links"];
            foreach ($links as $key => $langs) {
                foreach ($langs as $lang => $path) {
                    if ($path === $bare) {
                        g::set("op.meta.url.lang", $lang);
                    }
                }
            }
        }
    },
    "ProcessURLImagesGet" => function ($urls, $folder = "") {
        $dataset = array();
        $errors = array();
        $extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (empty($folder)) {
            $folder = g::get("config.paths.clone_data_folder") . "uploads" . V;
            g::run("tools.CreateFolder", $folder);
        }

        $file_count = count($urls);

        for ($i = 0; $i < $file_count; $i++) {
            $cimg = $urls[$i];
            $fname = explode("/", $cimg);
            $fc = count($fname);
            $actual_file_name = $fname[$fc - 1];
            $local_path = $folder . $actual_file_name;
            g::run("tools.LoadPathSafe", $cimg, 'DOWNLOAD', array(), null, $local_path);
            return $actual_file_name;
        }
    },
    "ProcessUploads" => function ($files, $folder = "") {
        $dataset = array();
        $errors = array();
        $extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (empty($folder)) {
            $folder = g::get("config.paths.clone_data_folder") . "uploads" . V;
            g::run("tools.CreateFolder", $folder);
        }

        //if ($_SERVER["CONTENT_LENGTH"] > (int)(str_replace("M", "", ini_get("post_max_size")) * 1024 * 1024)) {
        //    $errors[] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        //    g::set("op.meta.msgs", $errors);
        //    return false;
        //}
        $file_names = $files["uploads"]["name"];
        $file_count = count($file_names);
        $file_types = $files["uploads"]["type"];
        $file_tmp_names = $files["uploads"]["tmp_name"];
        $file_errors = $files["uploads"]["error"];
        $file_sizes = $files["uploads"]["size"];

        for ($i = 0; $i < $file_count; $i++) {
            $file_tmp_name = $file_tmp_names[$i];
            $file_name = $file_names[$i];
            $file_type = $file_types[$i];
            $file_size = $file_sizes[$i];
            $file_error = $file_errors[$i];

            $tmp = explode('.', $file_name);
            $file_ext = strtolower(end($tmp));

            if (!in_array($file_ext, $extensions)) {
                $errors[$file_name][] = 'Extension not allowed: ' . $file_name . ' - ' . $file_type;
            }

            if ($file_size > (1024 * 1024 * 2)) {
                $errors[$file_name][] = 'File size exceeds limit: ' . $file_name . ' - ' . $file_size;
            }

            if ($file_error > 0) {
                $phpFileUploadErrors = array(
                    0 => 'There is no error, the file uploaded with success',
                    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                    3 => 'The uploaded file was only partially uploaded',
                    4 => 'No file was uploaded',
                    6 => 'Missing a temporary folder',
                    7 => 'Failed to write file to disk.',
                    8 => 'A PHP extension stopped the file upload.',
                );
                $error_type = $phpFileUploadErrors[$file_error];
                $errors[$file_name][] = "File upload error: $error_type | $file_name - $file_type - $file_size";
            }

            $upload_file = basename($file_name);
            $upload_file_path = $folder . $upload_file;

            if (empty($errors)) {
                move_uploaded_file($file_tmp_name, $upload_file_path);
            }
        }

        if ($errors) {
            g::set("op.meta.msgs", $errors);
        }
    },
    "ProcessUploadDeletes" => function ($files, $folder = "") {
        if (empty($folder)) {
            $folder = g::get("config.paths.clone_data_folder") . "uploads" . V;
        }

        if (!empty($files)) {
            if (is_array($files)) {
                foreach ($files as $key => $file) {
                    $filename = $folder . $file;
                    if (file_exists($filename)) {
                        unlink($filename);
                    } else {
                        g::run("tools.Say", "File not found, can not delete: $filename");
                    }
                }
            } else {
                $filename = $folder . $files;
                if (file_exists($filename)) {
                    unlink($filename);
                } else {
                    g::run("tools.Say", "File not found, can not delete: $filename");
                }
            }
        }
    },
    "ProcessUploadsAfterAdd" => function ($files, $hash, $rename_files = true, $format = "", $tmp_folder = "", $folder = "", $type = "") {
        $renames = array();
        if (empty($tmp_folder)) {
            $tmp_folder = g::get("config.paths.clone_data_folder") . "uploads" . V;
            g::run("tools.CreateFolder", $tmp_folder);
        }
        if (empty($folder)) {
            $folder = g::get("config.paths.clone_ui_folder") . "uploads" . V;
            if (!empty($type)) {
                $folder .= $type . V;
            }
            g::run("tools.CreateFolder", $folder);
        } else {
            if (!empty($type)) {
                $folder .= $type . V;
            }
        }

        if ($rename_files != true) {
            $renames = $files;
        } else {
            $fl = count($files);
            for ($i = 0; $i < $fl; $i++) {
                $file = $files[$i];
                $tmp = explode('.', $file);
                $file_ext = strtolower(end($tmp));
                if (empty($format)) {
                    $filename = explode(".$file_ext", $file);
                    $clean_file = g::run("tools.ToAscii", $filename[0]) . ".$file_ext";
                } else {
                    $n = sprintf("%02d", $i);
                    $clean_file = (!empty($hash)) ? "$format-$n-$hash.$file_ext" : "$format-$n.$file_ext";
                }
                rename($tmp_folder . $file, $folder . $clean_file);
                $renames[] = $clean_file;
            }
        }
        return $renames;
    },
    "ProcessUploadsAfterEdit" => function ($files, $old_files, $hash, $rename_files = true, $format = "", $tmp_folder = "", $folder = "", $type = "", $prev_type = "") {
        $renames = array();
        $rename_agains = array();
        if (empty($tmp_folder)) {
            $tmp_folder = g::get("config.paths.clone_data_folder") . "uploads" . V;
            g::run("tools.CreateFolder", $tmp_folder);
        }
        if (empty($folder)) {
            $folder = g::get("config.paths.clone_ui_folder") . "uploads" . V;
            if ($type !== $prev_type) {
                $prev_folder = $folder . V . $prev_type . V;
            }
            if (!empty($type)) {
                $folder .= $type . V;
            }
            g::run("tools.CreateFolder", $folder);
        } else {
            if ($type !== $prev_type) {
                $prev_folder = $folder . V . $prev_type . V;
            }
            if (!empty($type)) {
                $folder .= $type . V;
            }
        }

        foreach ($old_files as $i => $img) {
            if (!empty($type)) {
                $old_files[$i] = $type . "/" . $img;
            }
        }
        if ($files === $old_files) {
            foreach ($files as $i => $img) {
                $files[$i] = str_replace("$type/", "", $img);
            }
            return $files;
        } else {
            if ($rename_files != true) {
                $delete_files = array_diff($old_files, $files);
                g::run("core.ProcessUploadDeletes", $delete_files, $folder);
                return $files;
            } else {
                if ($type === $prev_type) {
                    $delete_files = array_diff($old_files, $files);
                    g::run("core.ProcessUploadDeletes", $delete_files, $folder);
                } else {
                    $delete_files = array_diff($files, $old_files);
                    g::run("core.ProcessUploadDeletes", $delete_files, $prev_folder);
                }
                $fl = count($files);
                if ($type === $prev_type) {
                    for ($i = 0; $i < $fl; $i++) {
                        if (!empty($type)) {
                            $file = str_replace("$type/", "", $files[$i]);
                        } else {
                            $file = $files[$i];
                        }
                        $tmp = explode('.', $file);
                        $file_ext = strtolower(end($tmp));
                        if (empty($format)) {
                            $filename = explode(".$file_ext", $file);
                            $clean_file = g::run("tools.ToAscii", $filename[0]) . ".$file_ext";
                        } else {
                            $n = sprintf("%02d", $i);
                            $clean_file = (!empty($hash)) ? "$format-$n-$hash.$file_ext" : "$format-$n.$file_ext";
                        }
                        $renames[] = $clean_file;
                        if (file_exists($folder . $file)) {
                            if (file_exists($folder . $clean_file)) {
                                $rename_agains[] = $clean_file;
                                $clean_file = "___" . $clean_file;
                            }
                            rename($folder . $file, $folder . $clean_file);
                        } else if (file_exists($tmp_folder . $file)) {
                            rename($tmp_folder . $file, $folder . $clean_file);
                        }
                    }
                    foreach ($rename_agains as $key => $ra) {
                        rename($folder . "___" . $ra, $folder . $ra);
                    }
                } else {
                    for ($i = 0; $i < $fl; $i++) {
                        $file = str_replace("$prev_type/", "", $files[$i]);
                        $tmp = explode('.', $file);
                        $file_ext = strtolower(end($tmp));
                        if (empty($format)) {
                            $filename = explode(".$file_ext", $file);
                            $clean_file = g::run("tools.ToAscii", $filename[0]) . ".$file_ext";
                        } else {
                            $n = sprintf("%02d", $i);
                            $clean_file = (!empty($hash)) ? "$format-$n-$hash.$file_ext" : "$format-$n.$file_ext";
                        }
                        $renames[] = $clean_file;
                        if (file_exists($prev_folder . $file)) {
                            rename($prev_folder . $file, $folder . $clean_file);
                        } else if (file_exists($tmp_folder . $file)) {
                            rename($tmp_folder . $file, $folder . $clean_file);
                        }
                    }
                }
            }
        }
        return $renames;
    },
    "PrepareUploadedImagesEdit" => function ($images, $folder = "", $type = "") {
        if (empty($folder)) {
            $folder = g::get("config.paths.clone_ui_folder") . "uploads" . V;
        }
        if (!empty($type)) {
            $folder .= $type . V;
        }

        $imgs = array();
        if (!empty($images)) {
            foreach ($images as $k => $img) {
                if (is_array($img)) {
                    $img_path = $folder . str_replace("$type/", "", $img[0]);
                    if (file_exists($img_path)) {
                        $img_name = str_replace("$type/", "", $img[0]);
                        if (!empty($type)) {
                            $img_name = "$type/$img_name";
                        }
                        $imgs[] = array(
                            $img_name,
                            $img[1],
                            $img[2],
                            mime_content_type($img_path),
                            filesize($img_path),
                        );
                    }
                } else {
                    $img_path = $folder . str_replace("$type/", "", $img);
                    if (file_exists($img_path)) {
                        $img_name = str_replace("$type/", "", $img);
                        if (!empty($type)) {
                            $img_name = "$type/$img_name";
                        }
                        $imgs[] = array(
                            $img_name,
                            null,
                            null,
                            mime_content_type($img_path),
                            filesize($img_path),
                        );
                    }
                }
            }
            return $imgs;
        } else {
            return $imgs;
        }
    }
));

g::set("crypt", array(
    "charset_cray" => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789[]{};:?.,!@#$%^&*()-_=+|",
    "charset_hash" => "abcdefghijkmnopqrstuvwxyz0123456789",
    "charset_alpha" => "abcdefghijklmnopqrstuvwxyz",
    "charset_lcase" => "abcdefghijklmnopqrstuvwxyz0123456789",
    "charset_mixed" => "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ",
    "hash_separator" => "l",
));

g::def("crypt", array(
    "MakeSaltySecret" => function ($string, $salt = "") {
        if (empty($salt)) {
            $salt = g::get("config.clone.secret_salt");
        }
        return md5($string . $salt);
    },
    "IsMD5" => function ($md5_str) {
        return strlen($md5_str) == 32 && ctype_xdigit($md5_str);
    },
    "GenerateRandomKey" => function ($len = 64, $type = "cray") {
        $randStringLen = $len;
        $charset = g::get("crypt.charset_$type");
        $randString = "";
        for ($i = 0; $i < $randStringLen; $i++) {
            $randString .= $charset[mt_rand(0, strlen($charset) - 1)];
        }

        return $randString;
    },
    "HashEndecode" => function ($in, $to_num = false, $passKey = null, $index = null) {
        // Safe Until 99991231235959
        // For date hashing use only.
        // Used to create app-wide date hashes
        // Encodes and Decodes if necessary
        // Generally will not be necessary
        if ($index === null) {
            $index = g::get("crypt.charset_hash");
        } elseif ($index === "alpha") {
            $index = g::get("crypt.charset_alpha");
        } elseif ($index === "lcase") {
            $index = g::get("crypt.charset_lcase");
        } elseif ($index === "mix") {
            $index = g::get("crypt.charset_mixed");
        } elseif ($index === "cray") {
            $index = g::get("crypt.charset_cray");
        }

        if ($passKey == null) {
            $passKey = "";
        }
        if ($passKey !== null) {
            for ($n = 0; $n < strlen($index); $n++) {
                $i[] = substr($index, $n, 1);
            }

            $passhash = hash('sha256', $passKey);
            $passhash = (strlen($passhash) < strlen($index)) ? hash('sha512', $passKey) : $passhash;

            for ($n = 0; $n < strlen($index); $n++) {
                $p[] = substr($passhash, $n, 1);
            }

            array_multisort($p, SORT_DESC, $i);
            $index = implode($i);
        }

        $base = strlen($index);

        if ($to_num) {
            // Digital number  <<--  alphabet letter code
            $in = strrev($in);
            $out = 0;
            $len = strlen($in) - 1;
            for ($t = 0; $t <= $len; $t++) {
                $bcpow = bcpow($base, $len - $t);
                $out = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
            }
            $out = sprintf('%F', $out);
            $out = substr($out, 0, strpos($out, '.'));
        } else {
            // Digital number  -->>  alphabet letter code
            $out = "";
            for ($t = floor(log($in, $base)); $t >= 0; $t--) {
                $bcp = bcpow($base, $t);
                $a = floor($in / $bcp) % $base;
                $out = $out . substr($index, $a, 1);
                $in = $in - ($a * $bcp);
            }
            $out = strrev($out); // reverse
        }

        return $out;
    },
    "MicroHash" => function ($micro_now) {
        $md_arr = explode(".", $micro_now);
        return g::run("crypt.HashEndecode", $md_arr[0]) . g::get("crypt.hash_separator") . g::run("crypt.HashEndecode", $md_arr[1]);
    },
    "HashMicro" => function ($micro_hash) {
        $md_arr = explode(g::get("crypt.hash_separator"), $micro_hash);
        return g::run("crypt.HashEndecode", $md_arr[0], true) . "." . sprintf('%06d', g::run("crypt.HashEndecode", $md_arr[1], true));
    },
    "MakeSaltyKey" => function () {
    },
    "GenerateKeys" => function () {
        $clone_created = g::run("tools.DTS", 3);

        $chars = g::get("crypt.charset_hash");
        $shuffled_chars = str_shuffle($chars);
        g::get("crypt.charset_hash", $shuffled_chars);

        $clone_hash = g::run("tools.DTS", 7);

        $clone_salt = g::run("crypt.GenerateRandomKey");
        $clone_secret_salt = g::run("crypt.GenerateRandomKey");
        $clone_user_salt = g::run("crypt.GenerateRandomKey");

        $clone_secret = g::run("crypt.GenerateRandomKey", 8, "mixed");
        $user_open_pass = g::run("crypt.GenerateRandomKey", 8, "mixed");

        $clone = array(
            "chars" => $shuffled_chars, // salting secret
            "salt" => $clone_salt, // salting secret
            "secret_salt" => $clone_secret_salt, // gHash_generateRandomKey(), salting secret
            "user_salt" => $clone_user_salt, // gHash_generateRandomKey(), salting password
            "hash" => $clone_hash, // used as hash_clone, in db records when clone creates data
            "alias" => g::run("crypt.GenerateRandomKey", 16, "lcase"), // key and hash separates this clone from others.
            "name" => "Genes Clone", //
            "contact" => "", //
            "open_secret" => $clone_secret, // unsalted secret
            "secret" => g::run("crypt.MakeSaltySecret", $clone_secret, $clone_secret_salt), // salted secret
            "clone_create" => $clone_created,
        );
        $admin = array(
            "hash" => $clone_hash, // used as hash_user, in db records when clone creates data
            "alias" => "admin", // you can change, but pass needs to be regenerated
            "email" => "", //
            "open_pass" => $user_open_pass, // genes admin password, when written not encoded and reset_pwd is true, salted, encoded and set again.
            "pass" => g::run("crypt.MakeSaltySecret", $user_open_pass, $clone_user_salt), // genes admin password, when written not encoded and reset_pwd is true, salted, encoded and set again.
        );
        // set keys to g.
        g::set("config.clone", $clone);
        g::set("config.admin", $admin);
    },
    "CreateCoreAdmin" => function () {
        $config_clone = g::get("config.clone");
        $clone_hash = $config_clone["hash"];
        $clone_user_salt = $config_clone["user_salt"];
        $user_open_pass = g::run("crypt.GenerateRandomKey", 8, "mixed");

        $admin = array(
            "hash" => $clone_hash, // used as hash_user, in db records when clone creates data
            "alias" => "admin", // you can change, but pass needs to be regenerated
            "email" => "", //
            "open_pass" => $user_open_pass, // genes admin password, when written not encoded and reset_pwd is true, salted, encoded and set again.
            "pass" => g::run("crypt.MakeSaltySecret", $user_open_pass, $clone_user_salt), // genes admin password, when written not encoded and reset_pwd is true, salted, encoded and set again.
        );
        g::run("tools.UpdateConfigFiles", "admin", $admin);
    },
    "BUE" => function ($str) {
        $pips = g::get("config.clone.chars");
        $pops = g::get("crypt.charset_hash");
        $ret_str = rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
        $ret_str = strtr($ret_str, $pips, $pops);
        return $ret_str;
    },
    "BUD" => function ($enc_str) {
        $pips = g::get("config.clone.chars");
        $pops = g::get("crypt.charset_hash");
        $enc_str = strtr($enc_str, $pops, $pips);
        return base64_decode(str_pad(strtr($enc_str, '-_', '+/'), strlen($enc_str) % 4, '=', STR_PAD_RIGHT));
    },
));


g::def("db", array(
    "ConnectIfAvailable" => function () {
        $db_conns = g::get("config.db.conns");
        foreach ($db_conns as $key => $value) {
            $db_key = $key;
            break;
        }
        if (!empty($db_conns[$db_key]["path"])) {
            g::run("db.Connect", $db_conns);
            g::set("op.meta.clone.db", 1);
        } else {
            $msg = "Default DB path is not given. Will not connect to DB.";
            g::run("tools.Say", $msg);
            g::set("op.meta.clone.db", 0);
        }
    },
    "IsConnected" => function () {
        $config_db_conns = g::get("config.db.conns");
        foreach ($config_db_conns as $conn_name => $details) {
            $conn = g::get("db.conns.$conn_name");
            if (empty($conn)) {
                return false;
            }
        }
        return true;
    },
    "Connect" => function ($db_conns) {
        foreach ($db_conns as $conn_name => $conn_details) {
            if ($conn_details["type"] == "mysql") {
                g::run("db.ConnectMySql", $conn_name, $conn_details);
            } elseif ($conn_details["type"] == "sqlite") {
                g::run("db.ConnectSQLite", $conn_name, $conn_details);
            } elseif ($conn_details["type"] == "mongodb") {
                g::run("db.ConnectMongoDB", $conn_name, $conn_details);
            }
        }
    },
    "ConnectMySql" => function ($key, $cd) {
        g::run("tools.Say", "Connected to MySQL: $key");

        // try connecting...
        try {
            if (!empty($cd["name"]) && !empty($cd["user"]) && !empty($cd["pass"])) {
                $dsn = "mysql:host=" . $cd["path"] . ";dbname=" . $cd["name"] . ";charset=" . $cd["charset"];
                $opt = array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                );
                $conn = new PDO($dsn, $cd["user"], $cd["pass"], $opt);
                $conn->exec('SET NAMES utf8mb4');
                g::set("db.conns.$key", $conn);
            } else {
                $msg = "DB name, user, pass information is not given can not connect to db.";
                g::run("tools.Say", $msg);
                echo $msg;
                die;
            }
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            g::run("tools.Say", $msg);
            echo $msg;
            die;
        }

        // if there is no settings create tables and enter defaults.
        // and then try connecting again...
        g::run("db.CheckDBStructure");
    },
    "ConnectSQLite" => function ($key, $cd) {
        g::run("tools.Say", "Connected to SQLite: $key");
    },
    "ConnectMongoDB" => function ($key, $cd) {
        g::run("tools.Say", "Connected to MongoDB: $key");

        // try connecting..
        try {
            if (!empty($cd["name"]) && !empty($cd["user"]) && !empty($cd["pass"])) {
                $conn = new MongoDB\Driver\Manager("mongodb://" . $cd["path"]);
                g::set("db.conns.$key", $conn);
            } else {
                $msg = "DB name, user, pass information is not given can not connect to db.";
                g::run("tools.Say", $msg);
                echo $msg;
                die;
            }
        } catch (MongoDBDriverExceptionException $e) {
            $msg = $e->getMessage();
            g::run("tools.Say", $msg);
            echo $msg;
            die;
        }
    },
    "Disconnect" => function () {
    },
    "InsertCloneHash" => function () {
        // insert clone
        $config_clone = g::get("config.clone");
        $clone_hash = $config_clone["hash"];
        $clone_alias = $config_clone["alias"];
        $clone_name = $config_clone["name"];
        $clone_table = g::get("config.db.tables.clones");
        $query_sqls = array();
        $query_sqls[$clone_table[0]]["create_clone"] = array("INSERT INTO " . $clone_table[1] . "(`g_hash`, `g_alias`, `g_name`) VALUES ('$clone_hash', '$clone_alias', '$clone_name')");
        // run query function with sql array
        g::run("db.Execute", $query_sqls);
    },
    "CreateMissingTables" => function () {
        $tables = g::get("config.db.tables");
        $query_sqls = array();
        $not_exists = array();

        foreach ($tables as $key => $info) {
            $table_conn = $info[0];
            $table_name = $info[1];
            if (empty($not_exists[$table_conn])) {
                $not_exists[$table_conn] = array();
            }
            $sql = "SELECT 1 FROM $table_name LIMIT 1";
            $val = array();
            $query_sqls[$table_conn][$key] = array($sql, $val);
        }
        $response = g::run("db.Execute", $query_sqls);
        if (!empty($response["error"])) {
            $errors = $response["error"];
            // if missing a table select this.
            foreach ($errors as $table_key => $error_msg) {
                g::run("db.CreateTables", $table_key);
            }
        }
    },
    "CheckDBStructure" => function ($create_missing = true) {
        $db_is_proper = g::get("config.checks.db_is_proper");
        // Admin thinks db is not proper
        if ($db_is_proper !== 1) {
            g::run("db.CreateMissingTables");
            $cid = g::run("db.GetCloneId");
            if ($cid === false) {
                // Clone info is not matched to db clone table
                g::run("db.InsertCloneHash");
                g::set("config.checks.db_is_proper", 1);
                g::run("core.SessionSet", "cid", null);
                g::run("db.CreateDefaultLabels");
            }
            g::run("tools.UpdateConfigCheck", "db_is_proper", 1);
        }
    },
    "CreateTables" => function ($table_key, $format = false) {
        // echo "$table_key\n";
        $table_sql = array(
            "clones" => array(
                "name" => "",
                "cols" => array(
                    "id" => array("INT", "PRIMARY", "AUTOINC", "NOT NULL"),
                    "g_state" => array("VARCHAR|255"),
                    "g_type" => array("VARCHAR|255"),
                    "g_hash" => array("VARCHAR|15", "NOT NULL"),
                    "g_alias" => array("VARCHAR|255"),
                    "g_name" => array("VARCHAR|255"),
                    "g_blurb" => array("VARCHAR|767"),
                    "g_text" => array("MEDIUMTEXT"),
                    "g_bits" => array("JSON"),
                    "g_labels" => array("JSON"),
                    "tsc" => array("TIMESTAMP|DEFAULT"),
                    "uhc" => array("VARCHAR|15"),
                    "tsu" => array("TIMESTAMP"),
                    "uhu" => array("VARCHAR|15"),
                    "del" => array("TINYINT"),
                ),
                "extras" => array(
                    array("unique" => "id"),
                    array("unique" => "g_hash"),
                    array("unique" => "g_alias"),
                ),
            ),
            "persons" => array(
                "name" => "",
                "cols" => array(
                    "id" => array("INT", "PRIMARY", "AUTOINC", "NOT NULL"),
                    "g_state" => array("VARCHAR|255"),
                    "g_type" => array("VARCHAR|255"),
                    "g_hash" => array("VARCHAR|15", "NOT NULL"),
                    "g_alias" => array("VARCHAR|255", "NULL"),
                    "g_email" => array("VARCHAR|255", "NOT NULL"),
                    "g_pwd" => array("VARCHAR|255"),
                    "g_blurb" => array("VARCHAR|767"),
                    "g_text" => array("MEDIUMTEXT"),
                    "g_bits" => array("JSON"),
                    "g_media" => array("JSON"),
                    "g_labels" => array("JSON"),
                    "cid" => array("INT"),
                    "tsc" => array("TIMESTAMP|DEFAULT"),
                    "uhc" => array("INT"),
                    "tsu" => array("TIMESTAMP"),
                    "uhu" => array("INT"),
                    "del" => array("TINYINT"),
                ),
                "extras" => array(
                    array("unique" => "id"),
                    array("unique" => "g_hash"),
                    array("unique" => array("clone_user_email" => array("g_email", "cid"))),
                    array("unique" => array("clone_user_alias" => array("g_alias", "cid"))),
                ),
            ),
            "items" => array(
                "name" => "",
                "cols" => array(
                    "id" => array("INT", "PRIMARY", "AUTOINC", "NOT NULL"),
                    "g_state" => array("VARCHAR|255"),
                    "g_type" => array("VARCHAR|255"),
                    "g_hash" => array("VARCHAR|15", "NOT NULL"),
                    "g_alias" => array("VARCHAR|255", "NOT NULL"),
                    "g_link" => array("VARCHAR|255"),
                    "g_name" => array("VARCHAR|255", "NOT NULL"),
                    "g_blurb" => array("VARCHAR|767"),
                    "g_text" => array("MEDIUMTEXT"),
                    "g_bits" => array("JSON"),
                    "g_media" => array("JSON"),
                    "g_labels" => array("JSON"),
                    "tss" => array("TIMESTAMP"),
                    "tse" => array("TIMESTAMP"),
                    "cid" => array("INT"),
                    "tsc" => array("TIMESTAMP|DEFAULT"),
                    "uhc" => array("VARCHAR|15"),
                    "tsu" => array("TIMESTAMP"),
                    "uhu" => array("VARCHAR|15"),
                    "del" => array("TINYINT"),
                ),
                "extras" => array(
                    array("unique" => "id"),
                    array("unique" => "g_hash"),
                    array("unique" => array("clone_item_alias" => array("g_alias", "cid"))),
                    array("unique" => array("clone_item_name" => array("g_name", "cid"))),
                ),
            ),
            "labels" => array(
                "name" => "",
                "cols" => array(
                    "id" => array("INT", "PRIMARY", "AUTOINC", "NOT NULL"),
                    "g_state" => array("VARCHAR|255"),
                    "g_type" => array("VARCHAR|255", "NOT NULL"),
                    "g_context" => array("VARCHAR|255"),
                    "g_hash" => array("VARCHAR|15", "NOT NULL"),
                    "g_key" => array("VARCHAR|255", "NOT NULL"),
                    "g_value" => array("VARCHAR|255"),
                    "g_bits" => array("JSON"),
                    "cid" => array("INT"),
                    "tsc" => array("TIMESTAMP|DEFAULT"),
                    "uhc" => array("VARCHAR|15"),
                    "tsu" => array("TIMESTAMP"),
                    "uhu" => array("VARCHAR|15"),
                    "del" => array("TINYINT"),
                ),
                "extras" => array(
                    array("unique" => "id"),
                    array("unique" => "g_hash"),
                    array("unique" => array("clone_type_label" => array("g_key", "g_type", "g_context", "cid"))),
                ),
            ),
            "events" => array(
                "name" => "",
                "cols" => array(
                    "id" => array("INT", "PRIMARY", "AUTOINC", "NOT NULL"),
                    "g_state" => array("VARCHAR|255"),
                    "g_type" => array("VARCHAR|255"),
                    "g_hash" => array("VARCHAR|15", "NOT NULL"),
                    "g_key" => array("VARCHAR|255"),
                    "g_value" => array("VARCHAR|767"),
                    "g_void" => array("VARCHAR|767"),
                    "g_bits" => array("JSON"),
                    "g_labels" => array("JSON"),
                    "cid" => array("INT"), // clone id
                    "lid" => array("INT"), // label id
                    "uid" => array("INT"), // user id
                    "iid" => array("INT"), // item id
                    "tss" => array("TIMESTAMP"), // timestamp start
                    "tse" => array("TIMESTAMP"), // timestamp end
                    "tsc" => array("TIMESTAMP|DEFAULT"), // timestamp create
                    "uhc" => array("VARCHAR|15"), // user hash create
                    "tsu" => array("TIMESTAMP"), // timestamp update
                    "uhu" => array("VARCHAR|15"), // user hash update
                    "del" => array("TINYINT"),
                ),
                "extras" => array(),
            ),
        );

        $query_sqls = array();

        if ($format) {
            $table_info = g::get("config.db.tables");
            foreach ($table_info as $key => $details) {
                $table_conn = $details[0];
                $db_type = g::get("config.db.conns.$table_conn.type");
                $table_name = $details[1];
                $table_sql[$key]["name"] = $table_name;
                $query_sqls[$table_conn]["delete_$table_name"] = array("DROP TABLE IF EXISTS $table_name");
                $query_sqls[$table_conn]["create_$table_name"] = array(g::run("db.GenerateCreateTablesSql", $db_type, $table_sql[$key]));
            }

            // insert clone
            $config_clone = g::get("config.clone");
            $clone_hash = $config_clone["hash"];
            $clone_alias = $config_clone["alias"];
            $clone_name = $config_clone["name"];
            $clone_table = g::get("config.db.tables.clones");
            $query_sqls[$clone_table[0]]["create_clone"] = array("INSERT INTO " . $clone_table[1] . "(`g_hash`, `g_alias`, `g_name`) VALUES ('$clone_hash', '$clone_alias', '$clone_name')");
            //print_r($query_sqls);die;
            // run query function with sql array
            g::run("db.Execute", $query_sqls);
        } else {
            $table_info = g::get("config.db.tables.$table_key");
            $table_conn = $table_info[0];
            $db_type = g::get("config.db.conns.$table_conn.type");
            $table_name = $table_info[1];
            $table_sql[$table_key]["name"] = $table_name;
            $query_sqls[$table_conn]["delete_$table_name"] = array("DROP TABLE IF EXISTS $table_name");
            $query_sqls[$table_conn]["create_$table_name"] = array(g::run("db.GenerateCreateTablesSql", $db_type, $table_sql[$table_key]));

            if ($table_key === "clones") {
                // insert clone
                $config_clone = g::get("config.clone");
                $clone_hash = $config_clone["hash"];
                $clone_alias = $config_clone["alias"];
                $clone_name = $config_clone["name"];
                $clone_table = g::get("config.db.tables.clones");
                $query_sqls[$clone_table[0]]["create_clone"] = array("INSERT INTO " . $clone_table[1] . "(`g_hash`, `g_alias`, `g_name`) VALUES ('$clone_hash', '$clone_alias', '$clone_name')");
            }
            //print_r($query_sqls);die;
            g::run("db.Execute", $query_sqls);
        }
    },
    "CreateDefaultLabels" => function () {
        // ENTER DEFAULT GENES LABELS
        $sql_rows = array(
            // basic two options
            array("insert_ine", "labels", array("g_state" => "system", "g_type" => "label_types", "g_context" => "clone", "g_key" => "label_types"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "system", "g_type" => "label_types", "g_context" => "clone", "g_key" => "label_states"), array("g_type", "g_context", "g_key")),
            // default label states :: draft, private, public
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "label_states", "g_context" => "label", "g_key" => "draft"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "label_states", "g_context" => "label", "g_key" => "private"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "label_states", "g_context" => "label", "g_key" => "public"), array("g_type", "g_context", "g_key")),
            // basic label types
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "label_types", "g_context" => "clone", "g_key" => "event_types"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "label_types", "g_context" => "clone", "g_key" => "person_types"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "label_types", "g_context" => "clone", "g_key" => "item_types"), array("g_type", "g_context", "g_key")),
            // basic label type :: states
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "label_types", "g_context" => "clone", "g_key" => "event_states"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "label_types", "g_context" => "clone", "g_key" => "person_states"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "label_types", "g_context" => "clone", "g_key" => "item_states"), array("g_type", "g_context", "g_key")),
            // basic label types :: labels
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "label_types", "g_context" => "clone", "g_key" => "event_labels"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "label_types", "g_context" => "clone", "g_key" => "person_labels"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "label_types", "g_context" => "clone", "g_key" => "item_labels"), array("g_type", "g_context", "g_key")),
            // default event states :: single, session, history
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_states", "g_context" => "event", "g_key" => "single"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_states", "g_context" => "event", "g_key" => "session"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_states", "g_context" => "event", "g_key" => "history"), array("g_type", "g_context", "g_key")),
            // default person states :: active, inactive
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "person_states", "g_context" => "person", "g_key" => "active"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "person_states", "g_context" => "person", "g_key" => "inactive"), array("g_type", "g_context", "g_key")),
            // default item states :: draft, public
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "item_states", "g_context" => "item", "g_key" => "draft"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "item_states", "g_context" => "item", "g_key" => "public"), array("g_type", "g_context", "g_key")),
            // default event types :: create, update, delete :: clone, label, person, item
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "clone_create"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "person_create"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "label_create"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "item_create"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "clone_update"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "person_update"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "label_update"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "item_update"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "clone_delete"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "person_delete"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "label_delete"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "item_delete"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "event_types", "g_context" => "event", "g_key" => "member_login"), array("g_type", "g_context", "g_key")),
            // default person types :: member, contact
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "person_types", "g_context" => "person", "g_key" => "member"), array("g_type", "g_context", "g_key")),
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "person_types", "g_context" => "person", "g_key" => "contact"), array("g_type", "g_context", "g_key")),
            // default item types :: content
            array("insert_ine", "labels", array("g_state" => "public", "g_type" => "item_types", "g_context" => "item", "g_key" => "content"), array("g_type", "g_context", "g_key")),
        );
        $db_result = g::run("db.Prepare", $sql_rows);
        return true;
    },
    "GenerateCreateTablesSql" => function ($db_type, $table_sql) {
        if ($db_type === "mysql") {
            $s = "`";
            $sqltemp = "";
            foreach ($table_sql as $key => $value) {
                if ($key == "name") {
                    $sqltemp .= "CREATE TABLE " . $s . $value . $s . " (";
                } elseif ($key == "cols") {
                    foreach ($value as $col => $colval) {
                        $sqltemp .= $s . $col . $s;
                        foreach ($colval as $coldets) {
                            $val = explode("|", $coldets);
                            $tempmore = "";
                            $temp = $val[0];
                            if (isset($val[1])) {
                                $tempmore = $val[1];
                            }
                            switch (strtolower($temp)) {
                                case "decimal":
                                    $sqltemp .= " " . $temp . "(" . $tempmore . ")";
                                    break;
                                case "varchar":
                                    $sqltemp .= " " . $temp . "(" . $tempmore . ")";
                                    break;
                                case "default":
                                    $sqltemp .= " " . $temp . " '" . $tempmore . "'";
                                    break;
                                case "tinyint":
                                    $sqltemp .= " tinyint DEFAULT 0";
                                    break;
                                case "int":
                                    $sqltemp .= " int";
                                    break;
                                case "bigint":
                                    $sqltemp .= " bigint";
                                    break;
                                case "primary":
                                    $sqltemp .= " PRIMARY KEY";
                                    break;
                                case "autoinc":
                                    $sqltemp .= " AUTO_INCREMENT";
                                    break;
                                case "date":
                                    $sqltemp .= " datetime";
                                    break;
                                case "timestamp":
                                    if ($tempmore == "UPDATE") {
                                        $sqltemp .= " TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                                    } else if ($tempmore == "DEFAULT") {
                                        $sqltemp .= " TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
                                    } else {
                                        $sqltemp .= " TIMESTAMP NULL";
                                    }
                                    break;
                                default:
                                    $sqltemp .= " " . $temp;
                                    break;
                            }
                        }
                        $sqltemp .= ", ";
                    }
                } elseif ($key == "extras") {
                    foreach ($value as $valArr) {
                        foreach ($valArr as $col => $colval) {
                            switch (strtolower($col)) {
                                case "primary_key":
                                    $sqltemp .= " PRIMARY KEY (" . $s . $colval . $s . "), ";
                                    break;
                                case "unique":
                                    if (is_array($colval)) {
                                        foreach ($colval as $colkey => $coldeets) {
                                            $sqltemp .= " CONSTRAINT $colkey UNIQUE(" . $s . implode("$s,$s", $coldeets) . $s . "), ";
                                        }
                                        // CONSTRAINT uni_clone_tag UNIQUE(alias, hash_clone)
                                    } else {
                                        $sqltemp .= " UNIQUE KEY (" . $s . $colval . $s . "), ";
                                    }
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                }
            }
            $sqltemp = substr($sqltemp, 0, -2);
            $sqltemp .= ") ENGINE=InnoDB AUTO_INCREMENT=1234 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            return $sqltemp;
        }
    },
    "PrepExecuteLinks" => function ($query_name, $total, $count, $get, $p) {
        $url = g::get("op.meta.url");
        //$list = g::get("config.mods.pane.views." . $url["view"] . ".urls." . $url["lang"]);
        $list = $url["vurl"];
        $args = g::get("op.meta.url.args");
        /*
            print_r($url);
            print_r($list);
            print_r($args);
            links/base : base view link
            links/curr : current loop link
            links/c : create/add item link
            links/r : read/view/edit item link
            links/u : update/edit item link
            links/d : delete item link
            links/next : next page link
            links/prev : prev page link
            links/more : get more same query
        */
        $url_args = array("s", "t", "i", "f", "g", "o", "n", "p"); // p, n, o, g, f, s, t, i
        $url_path = "";
        if (is_array($args)) {
            foreach ($args as $key => $value) {
                if (in_array($key, $url_args)) {
                    $url_path .= ";$key=$value";
                }
            }
        }

        $curr_links = array("base" => $list, "curr" => "$list$url_path");
        if (!empty($curr_links["base"])) {
            $curr_links["total"] = $total;
            $curr_links["count"] = $count;
            $curr_links["px"] = ceil($total / $get);
            $curr_links["pc"] = $p;
            $np = (($p + 1) > $curr_links["px"]) ? "" : $p + 1;
            $pp = (($p - 1) < 1) ? "" : $p - 1;

            if (strpos($curr_links["curr"], ";p=") > -1) {
                if (!empty($np)) {
                    $curr_links["pn"] = str_replace(";p=" . $curr_links["pc"], ";p=$np", $curr_links["curr"]);
                }
                if (!empty($pp)) {
                    $curr_links["pp"] = str_replace(";p=" . $curr_links["pc"], ";p=$pp", $curr_links["curr"]);
                }
            } else {
                if (!empty($np)) {
                    $curr_links["pn"] = $curr_links["curr"] . ";p=$np";
                }
                if (!empty($pp)) {
                    $curr_links["pp"] = $curr_links["curr"] . ";p=$pp";
                }
            }
            g::set("op.meta.links.$query_name", $curr_links);
        }
    },
    "Execute" => function ($query_sqls) {
        $result = array();
        foreach ($query_sqls as $table_conn => $sqls) {
            $conn = g::get("db.conns.$table_conn");
            if ($conn !== false) {
                $conn->beginTransaction();
                foreach ($sqls as $query_name => $sql_line) {
                    if (!empty($sql_line)) {
                        $sql = $sql_line[0];
                        $val = (!empty($sql_line[1])) ? $sql_line[1] : array();
                        $get = (!empty($sql_line[2])) ? $sql_line[2] : 50;
                        $p = (!empty($sql_line[3])) ? $sql_line[3] : 0;
                        $prepel = (isset($sql_line[4])) ? $sql_line[4] : false;

                        try {
                            if (strpos($sql, "INSERT") > -1 || strpos($sql, "UPDATE") > -1 || strpos($sql, "DELETE") > -1) {
                                // "prepare"
                                $pack = $conn->prepare($sql);
                                $pack->execute($val);
                                $result[$query_name]["last_id"] = $conn->lastInsertId();
                            } elseif (strpos($sql, "SELECT") > -1) {
                                $pack = $conn->prepare($sql);
                                $pack->execute($val);

                                try {
                                    $total_sql = explode("LIMIT", $sql);
                                    $real_sql = trim($total_sql[0]);
                                    $new_sql = "SELECT COUNT(*) FROM (" . $real_sql . ") REALSQL";
                                    $cpack = $conn->prepare($new_sql);
                                    $cpack->execute($val);
                                    $response["total"] = $cpack->fetchColumn();
                                } catch (Exception $e) {
                                    $msg = $e->errorInfo;
                                    g::run("tools.Say", g::run("tools.JE", $msg));
                                    $response["total"] = 0;
                                }

                                $response["count"] = $pack->rowCount();
                                $response["list"] = $pack->fetchAll(PDO::FETCH_ASSOC);
                                $result[$query_name] = $response;

                                if ($prepel) {
                                    g::run("db.PrepExecuteLinks", $query_name, $response["total"], $response["count"], $get, $p);
                                }
                            } else {
                                $pack = $conn->prepare($sql);
                                $pack->execute($val);
                            }
                            //g::run("tools.Say", "DB.Execute: " . $sql, 1);
                        } catch (PDOException $e) {
                            $error = $e->getMessage();
                            // var_dump($error);die;
                            $result["error"][$query_name] = $error;
                            // $result[$query_name]["msg"]["err"] = $error;
                            g::run("tools.Say", "There is an error with the db execution: $sql");
                            g::run("tools.Say", g::run("tools.JE", $result["error"]));
                        }
                    }
                }
                $conn->commit();
            }
        }
        /*
            $this->db_connect();

            $sql = "SELECT * FROM genes_settings";
            $val = array();

            $sql = "SELECT * FROM genes_settings WHERE id > ?";
            $val = array(30);

            $sql = "UPDATE genes_settings SET setting_description=? WHERE id = ?";
            $val = array("Adding a user...", 32);

            $sql = "SELECT * FROM genes_settings WHERE id > ?";
            $val = array(30);

            $sql = "INSERT INTO genes_settings (setting_type, setting_title, setting_key, setting_value) VALUES (?, ?, ?, ?)";
            $val = array("thunder_cats", "Thunder Cats", "tc", "liono");

            $sql = "SELECT * FROM genes_settings WHERE id > ?";
            $val = array(54);

            $sql = "DELETE FROM genes_settings WHERE id = ?";
            $val = array(55);

            $this->db_exec($sql, $val);
         */
        return $result;
    },
    "GetCloneId" => function () {
        $db_is_proper = g::get("config.checks.db_is_proper");
        // Admin thinks db is not proper
        if ($db_is_proper === 1) {
            $cid = g::run("core.SessionGet", "cid");
            if (empty($cid)) {
                $cid = false;
                $config = g::get("config");
                $clone_hash = $config["clone"]["hash"];
                $clone_table = $config["db"]["tables"]["clones"];
                $table_conn = $clone_table[0];
                $table_name = $clone_table[1];
                $query_sqls = array($clone_table[0] => array(array("SELECT id FROM " . $clone_table[1] . " WHERE `g_hash`='$clone_hash';")));
                $result = g::run("db.Execute", $query_sqls);
                if (!empty($result[0]) && $result[0]["count"] > 0) {
                    $cid = $result[0]["list"][0]["id"];
                    g::run("core.SessionSet", "cid", $cid);
                }

                return $cid;
            } else {
                return $cid;
            }
        }
        return false;
    },
    "Prepare" => function ($sql_rows, $is_genes_db = true) {
        $query_sqls = array();
        $cid = g::run("db.GetCloneId");
        $config = g::get("config");

        $meta = g::get("op.meta");
        if (!empty($meta["url"]["lang"])) {
            $lang = $meta["url"]["lang"]; // use later, not implemented yet.
        }
        $user = $meta["user"]; // use later, not implemented yet.

        foreach ($sql_rows as $key => $sql_details) {
            $action = $sql_details[0];
            $table_key = $sql_details[1];

            $table_info = $config["db"]["tables"][$table_key];
            $table_conn = $table_info[0];
            $table_name = $table_info[1];

            if (is_array($sql_details[2])) {
                $table_cols = array_keys($sql_details[2]);
                $table_vals = array_values($sql_details[2]);
                if ($action == "insert_ine" || $action == "insert") {
                    if ($is_genes_db) {
                        if (!in_array("g_hash", $table_cols)) {
                            $ts_hash = g::run("tools.DTS", 7);
                            $table_cols[] = "g_hash";
                            $table_vals[] = $ts_hash;
                        }
                        if (!in_array("uhc", $table_cols)) {
                            $table_cols[] = "uhc";
                            $table_vals[] = (!empty($user["hash"])) ? $user["hash"] : 0;
                        }
                    }
                }

                if ($action !== "update") {
                    if ($is_genes_db) {
                        $table_cols[] = "cid";
                        $table_vals[] = $cid;
                    }
                } else {
                    if (!in_array("uhu", $table_cols)) {
                        $table_cols[] = "uhu";
                        $table_vals[] = (!empty($user["hash"])) ? $user["hash"] : 0;
                    }
                }

                $table_cols_csv = implode("`,`", $table_cols);
                $table_ptr_csv = "";
                foreach ($table_cols as $col) {
                    $table_ptr_csv .= "?, ";
                }
                $table_ptr_csv = substr($table_ptr_csv, 0, -2);
            }

            if ($action === "insert_ine") {
                $table_vals_csv = "'";
                $tc = 0;
                foreach ($table_vals as $i => $val) {
                    if (strpos($val, "'") > -1) {
                        $val = str_replace("'", "\'", $val);
                    }
                    $tc++;
                    $table_vals_csv .= $val . "' as A$tc,'";
                }
                $table_vals_csv = substr($table_vals_csv, 0, -2);

                $table_ine_query = "";
                $table_ineq = $sql_details[2];
                $given = false;
                if (!empty($sql_details[3])) {
                    $table_ineq_keys = $sql_details[3];
                    foreach ($table_ineq_keys as $i => $tkey) {
                        $val = $table_ineq[$tkey];
                        if (strpos($val, "'") > -1) {
                            $val = str_replace("'", "\'", $val);
                        }
                        $table_ine_query .= "`$tkey`='$val' AND ";
                    }
                } else {
                    foreach ($table_ineq as $rkey => $val) {
                        if (strpos($val, "'") > -1) {
                            $val = str_replace("'", "\'", $val);
                        }
                        $table_ine_query .= "`$rkey`='$val' AND ";
                    }
                }

                if ($is_genes_db) {
                    $table_ine_query .= "cid=$cid";
                } else {
                    $table_ine_query .= "1=1";
                }

                $sql = "INSERT INTO $table_name (`$table_cols_csv`) ";
                $sql .= "SELECT * FROM (SELECT $table_vals_csv) AS tmp WHERE NOT EXISTS (";
                $sql .= "SELECT id FROM $table_name WHERE $table_ine_query";
                $sql .= ") LIMIT 1;";
                $val = array();
                $tmp_sql = array($sql, $val);
            } elseif ($action === "insert") {
                $sql = "INSERT INTO $table_name (`$table_cols_csv`) VALUES ($table_ptr_csv)";
                $val = $table_vals;
                $tmp_sql = array($sql, $val);
            } elseif ($action === "update") {
                if ($is_genes_db) {
                    $where = "cid=$cid";
                } else {
                    $where = "1=1";
                }
                if (!empty($sql_details[3])) {
                    $where .= " AND " . $sql_details[3];
                }

                $table_cols_ptr_csv = implode("=?,", $table_cols) . "=?";

                $sql = "UPDATE $table_name SET $table_cols_ptr_csv WHERE $where";
                $val = $table_vals;
                $tmp_sql = array($sql, $val);
            } elseif ($action === "delete") {
                if ($is_genes_db) {
                    $where = "cid=$cid";
                } else {
                    $where = "1=1";
                }
                if (!empty($sql_details[2])) {
                    $where .= " AND " . $sql_details[2];
                }

                $sql = "DELETE FROM $table_name WHERE $where";
                $tmp_sql = array($sql, array());
            }

            $query_sqls[$table_conn][$key] = $tmp_sql;
        }
        //print_r($query_sqls);die;
        return g::run("db.Execute", $query_sqls);
    },
    "Get" => function ($query, $is_genes_db = true) {
        // query = array($cols, $table, $where, $order, $limit)
        $response = array();
        $cols = $query[0];
        $table_key = $query[1];

        $config = g::get("config");
        $table_info = $config["db"]["tables"][$table_key];
        $table_conn = $table_info[0];
        $table_name = $table_info[1];

        $cid = g::run("db.GetCloneId");
        $where = ($is_genes_db) ? "$table_name.cid=$cid" : "1=1";
        $where .= (!empty($query[2])) ? " AND (" . $query[2] . ")" : "";
        $order = (!empty($query[3])) ? "ORDER BY " . $query[3] : "";
        $limit = (!empty($query[4])) ? $query[4] : "";
        $limits = (!empty($query[4])) ? "LIMIT " . $query[4] : "";

        // persons, g_hash
        $joins = "";
        if (!empty($query[5])) {
            $jt_name = $config["db"]["tables"][$query[5][0]][1];
            $jt_col = $query[5][1];
            $joins = "LEFT JOIN $jt_name ON $jt_name.$jt_col = $table_name.$jt_col";
        }

        $exec_query = array(
            $table_conn => array(
                "main" => array(
                    "SELECT $cols FROM $table_name $joins WHERE $where $order $limits", null, $limit, 0, false
                )
            )
        );
        $response = g::run("db.Execute", $exec_query);
        //print_r($exec_query);print_r($response);
        return $response["main"];
    },
    "GetQuery" => function ($sql) {
        $response = array();
        $conn = g::get("db.conns.default");
        if (empty($conn)) {
            return false;
        }

        try {
            $pack = $conn->prepare($sql);
            $pack->execute();
            $response["count"] = $pack->rowCount();
            $response["list"] = $pack->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $msg = $e->errorInfo;
            g::run("tools.Say", $msg[2], 5);
            $response["count"] = 0;
            $response["list"] = array();
        }
        return $response;
    },
    "GetWithSessionPostedQueryArgs" => function ($mixed_query) {
        /*
        $mixed_query = array(
        "cols" => "*",
        "table" => "old_users",
        "sort" => array("created_date" => "DESC"),
        "filter" => array(),
        "args" => $args,
        "post" => $post
        );
         */
        $args = $mixed_query["args"];
        $seq = (empty($args["seq"])) ? "" : $args["seq"];
        $post = $mixed_query["post"];
        $sort = $mixed_query["sort"];
        $filter = $mixed_query["filter"];

        $cols = $mixed_query["cols"];
        $table = $mixed_query["table"];

        $sort_val = $filter_val = "";
        $get_rows = 25;
        $page_val = 1;

        if (!empty($post)) {
            $sort_val = $post["sort"];
            if (!empty($sort_val)) {
                $sort = array();
                if (strpos($sort_val, ",") > -1) {
                    $psarr = explode(",", $sort_val);
                    foreach ($psarr as $psr) {
                        $psdata = explode("|", $psr);
                        $sort[$psdata[0]] = $psdata[1];
                    }
                } else {
                    $psdata = explode("|", $sort_val);
                    if (count($psdata) > 1) {
                        $sort[$psdata[0]] = strtoupper($psdata[1]);
                    }
                }
            }
            $filter_val = $post["filter"];
            if (!empty($filter_val)) {
                if (strpos($filter_val, ",") > -1) {
                    $psarr = explode(",", $filter_val);
                    foreach ($psarr as $psr) {
                        $psdata = explode("|", $psr);
                        $filter[$psdata[0]] = $psdata[1];
                    }
                } else {
                    $psdata = explode("|", $filter_val);
                    if (count($psdata) > 1) {
                        $filter[$psdata[0]] = $psdata[1];
                    }
                }
            }

            if (!empty($post["rows"])) {
                $get_rows = $post["rows"];
                $args["rows"] = $get_rows;
                g::run("core.SessionSet", "$seq-rows", $get_rows);
            }

            if (!empty($post["page"])) {
                $page_val = $post["page"];
                $args["start"] = ($page_val - 1) * $get_rows;
                g::run("core.SessionSet", "$seq-page", $page_val);
                g::run("core.SessionSet", "$seq-start", $args["start"]);
            }

            g::run("core.SessionSet", "$seq-sort_val", $sort_val);
            g::run("core.SessionSet", "$seq-sort", $sort);
            g::run("core.SessionSet", "$seq-filter_val", $filter_val);
            g::run("core.SessionSet", "$seq-filter", $filter);
        } else {
            if (empty($seq)) {
            }
        }

        $where = "";
        $order = "";

        if (!empty($seq)) {
            if (empty($post)) {
                $session_filter = g::run("core.SessionGet", "$seq-filter");
                $filter = (!empty($session_filter)) ? $session_filter : $filter;
                $session_sort = g::run("core.SessionGet", "$seq-sort");
                $sort = (!empty($session_sort)) ? $session_sort : $sort;
                $session_sort_val = g::run("core.SessionGet", "$seq-sort_val");
                $sort_val = (!empty($session_sort_val)) ? $session_sort_val : $sort_val;
                $session_filter_val = g::run("core.SessionGet", "$seq-filter_val");
                $filter_val = (!empty($session_filter_val)) ? $session_filter_val : $filter_val;

                $get_rows = g::run("core.SessionGet", "seq-rows");
                $page_val = g::run("core.SessionGet", "$seq-page");

                if (!empty($args["rows"])) {
                    $get_rows = $args["rows"];
                    g::run("core.SessionSet", "$seq-rows", $get_rows);
                }
            }
        }

        $filters = array();
        foreach ($filter as $key => $value) {
            if (strpos($value, "%") > -1) {
                $filters[] = "$key LIKE '$value'";
            } else {
                $filters[] = "$key='$value'";
            }
        }
        if (!empty($filters)) {
            $where .= implode(" AND ", $filters);
        }

        foreach ($sort as $key => $value) {
            if (!empty($order)) {
                $order .= ", $key $value ";
            } else {
                $order .= "$key $value ";
            }
        }

        $rows = (empty($args["rows"])) ? 25 : $args["rows"];
        $start = (empty($args["start"])) ? 0 : $args["start"];
        $page_val = (empty($args["page"])) ? $page_val : $args["page"];
        $limit = "$start, $rows";

        $dataset = g::run("db.Get", array($cols, $table, $where, $order, $limit));

        $total = $dataset["total"];
        $count = $dataset["count"];

        $current_page = ($start / $rows) + 1;
        $total_pages = ceil(($total / $rows));

        $dataset["current_page"] = $current_page;
        $dataset["total_pages"] = $total_pages;

        $dataset["seq"] = $seq;
        $dataset["rows"] = $rows;
        $dataset["start"] = $start;
        $dataset["sort_val"] = $sort_val;
        $dataset["filter_val"] = $filter_val;

        $dataset["prev_start"] = 0;
        $dataset["next_start"] = $start;

        if ($current_page == $total_pages) {
            $dataset["prev_start"] = $start - $rows;
        } else if ($start > 0) {
            $dataset["next_start"] = $start + $rows;
            $dataset["prev_start"] = $start - $rows;
        } else if ($start == 0) {
            $dataset["next_start"] = $start + $rows;
        }

        if ($dataset["prev_start"] < 0) {
            $dataset["prev_start"] = 0;
        }

        if (ceil($dataset["next_start"] / $dataset["rows"]) > $total_pages) {
            $dataset["next_start"] = $start;
        }

        return $dataset;
    }
));


g::def("tools", array(
    "ArrayMergeRecurseProper" => function ($array1, $array2) {
        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($array1[$key]) && is_array($array1[$key])) {
                $array1[$key] = g::run("tools.ArrayMergeRecurseProper", $array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }
        return $array1;
    },
    "BitsArrayDepth" => function ($bits, $lang) {
        foreach ($bits as $key => $val) {
            if (!empty($val[$lang])) {
                $bits[$key] = $bits[$key][$lang];
            } elseif (is_array($val)) {
                $bits[$key] = g::run("tools.BitsArrayDepth", $val, $lang);
            } else {
                return "***";
            }
        }
        return $bits;
    },
    "PR" => function ($sth) {
        print_r($sth);
    },
    "Now" => function ($uts = null) {
        if (is_null($uts)) {
            $uts = microtime(true);
        }
        $ts = floor($uts);
        $mss = round(($uts - $ts) * 1000);
        if ($mss < 10) {
            $mss = "00" . $mss;
        } elseif ($mss < 100) {
            $mss = "0" . $mss;
        }

        return date(preg_replace('`(?<!\\\\)u`', $mss, g::get("config.settings.date_time_format")), $ts);
    },
    "Performance" => function () {
        $t = sprintf('%0.6f', microtime(true) - g::get("void.time"));
        $unit = array('Bytes', 'KB', 'MB', 'GB', 'tb', 'pb');
        $mm = memory_get_usage() - g::get("void.mem");
        if ($mm < 0) {
            $mm = 0;
        }
        $m = @round($mm / pow(1024, ($i = floor(log($mm, 1024)))), 2) . ' ' . $unit[$i];
        return ($t . " sec | " . $m . " ram. | " . g::get("op.meta.url.request"));
    },
    "Say" => function ($what, $importance = 0) {
        // Message levels,
        // 0 -- Not important, debugging, informational
        // 1 -- Success
        // 3 -- Warning
        // 5 -- Error
        // 9 -- Dead
        $t = g::run("tools.Now");

        $cml = g::get("config.settings.msg_level");
        $cll = g::get("config.settings.log_level");
        if ($importance >= $cml) {
            $marr = array();
            $msgs = g::get("op.meta.msgs");
            if (!empty($msgs)) {
                $marr = $msgs;
            }
            if (is_array($what)) {
                //$marr[] = array($t, $importance, g::run("tools.JE", $what));
                $marr[] = array($t, $importance, $what);
            } else {
                $marr[] = array($t, $importance, g::run("tools.Translate", $what));
            }
            g::set("op.meta.msgs", $marr);
        }
        if ($importance >= $cll) {
            if (is_array($what)) {
                g::run("tools.Log", "$t|" . g::run("tools.JE", $what));
            } else {
                g::run("tools.Log", "$t|" . $what);
            }
        }
    },
    "Log" => function ($what) {
        $filename = g::get("config.paths.clone_log_file");
        g::run("tools.WriteFile", $what, $filename, true);
    },
    "Translate" => function ($what) {
        $tr9 = g::get("op.bits.$what");
        if (!empty($tr9)) {
            return $tr9;
        } else {
            return $what;
        }
    },
    "CreateFolder" => function ($folder, $mode = 0777) {
        return is_dir($folder) || mkdir($folder, $mode, true);
    },
    "CreateHtaccess" => function ($file_path) {
        if (!empty($file_path)) {
            if (!is_file($file_path)) {
                $htc = '';
                $htc .= '# BEGIN GENES' . "\n";
                $htc .= '# Header set X-Robots-Tag "noindex, nofollow"' . "\n\n";
                $htc .= '# Force simple error message for requests for non-existent or forbidden files.' . "\n";
                $htc .= 'ErrorDocument 403 "Sorry, access to this page is forbidden."' . "\n";
                $htc .= 'ErrorDocument 404 "The requested file was not found."' . "\n\n";
                $htc .= '<IfModule mod_rewrite.c>' . "\n\n";
                $htc .= '# Turn on URL rewriting' . "\n";
                $htc .= 'RewriteEngine On' . "\n";
                $htc .= 'Options +FollowSymLinks' . "\n";
                $htc .= 'Options -Indexes' . "\n\n";
                $htc .= '# Protect hidden files from being viewed' . "\n";
                $htc .= '<FilesMatch "\.(htaccess|htpasswd|ini|log|sh|inc|bak)$">' . "\n";
                $htc .= 'Order Allow,Deny' . "\n";
                $htc .= 'Deny from all' . "\n";
                $htc .= '</FilesMatch>' . "\n\n";
                $htc .= '# Allow system php files to work' . "\n";
                $htc .= '<Files ~ "(index)\.(php|html)$">' . "\n";
                $htc .= 'Order Deny,Allow' . "\n";
                $htc .= 'Allow From All' . "\n";
                $htc .= '</Files>' . "\n\n";
                $htc .= '# FORCE HTTPS REDIRECT' . "\n";
                $htc .= '# Redirect http/https www to https non-www' . "\n";
                $htc .= 'RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]' . "\n";
                $htc .= 'RewriteRule ^(.*)$ https://%1/$1 [R=301,L]' . "\n";
                $htc .= '# Redirect http non-www to https non-www' . "\n";
                $htc .= 'RewriteCond %{HTTP_HOST} !^www\. [NC]' . "\n";
                $htc .= 'RewriteCond %{HTTPS} off' . "\n";
                $htc .= 'RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]' . "\n\n";
                $htc .= '# PREVENT DATA FOLDER REACH' . "\n";
                $htc .= 'RewriteRule ^data/ - [F]' . "\n\n";
                $htc .= '# :: IF STRONGER SERVER-SIDE :: API ONLY / API RENDERED UI' . "\n";
                $htc .= '# :: Unmark the lines below to handled everything by th API' . "\n";
                $htc .= '# :: Allow any files or directories that exist to be displayed directly other go to index.php' . "\n";
                $htc .= 'RewriteCond %{REQUEST_FILENAME} !\.(gif|jpe?g|png|js|css|swf|ico|txt|pdf|xml|eot|svg|ttf|woff|woff2|mp3|zip|webmanifest)$' . "\n";
                $htc .= 'RewriteCond %{REQUEST_URI} !^(.)$' . "\n";
                $htc .= 'RewriteCond %{REQUEST_URI} !(index.php)$' . "\n";
                $htc .= 'RewriteRule ^(.*) index.php?$1 [L,NC]' . "\n\n";
                $htc .= '# :: IF STRONGER CLIENT-SIDE :: COMPLETE SEPARATION OF API & UI' . "\n";
                $htc .= '# :: Still json requests must be handled by the API' . "\n";
                $htc .= '#RewriteCond %{QUERY_STRING} ^$' . "\n";
                $htc .= '#RewriteRule ^(.*\.json)$ index.php?$1 [L,NC]' . "\n";
                $htc .= '# :: Everything else is handled by the UI' . "\n";
                $htc .= '#RewriteCond %{QUERY_STRING} ^$' . "\n";
                $htc .= '# :: Allow any files or directories that exist to be displayed directly other go to index.html' . "\n";
                $htc .= '#RewriteCond %{REQUEST_FILENAME} !\.(gif|jpe?g|png|js|css|swf|ico|txt|pdf|xml|eot|svg|ttf|woff|woff2|mp3|zip|webmanifest)$' . "\n";
                $htc .= '#RewriteCond %{REQUEST_URI} !^(.)$' . "\n";
                $htc .= '#RewriteCond %{REQUEST_URI} !(ui/index.html)$ [NC]' . "\n";
                $htc .= '#RewriteRule ^(.*)$ ui/index.html?$1 [L,NC]' . "\n\n";
                $htc .= '</IfModule>' . "\n";
                $htc .= '# END GENES';

                g::run("tools.WriteFile", $htc, $file_path);
            }
        }
    },
    "CreateUIIndexHtml" => function ($file_path) {
        if (!empty($file_path)) {
            if (!is_file($file_path)) {
                $html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8" /><title>{{bits.title}}</title></head><body><h3>Hello, world.</h3><h4>Genes setup complete.</h4><h5>Your <a href="{{meta.url.base}}">new clone</a> is ready to use.</h5><span>Thanks, Have Fun!<br><a href="https://genes.one" target="_blank">Genes</a></span></body></html>';
                g::run("tools.WriteFile", $html, $file_path);
            }
        }
    },
    "CreateSetConfig" => function ($data_path, $file_path) {
        if (!empty($file_path)) {
            if (!is_file($file_path)) {
                if ($data_path === "config") {
                    g::run("crypt.GenerateKeys");
                    $config = g::get("config");
                    $cuki = g::get("clone.uki");
                    $config["env"][$cuki]["paths"] = $config["paths"];
                    unset($config["paths"]);
                    g::run("tools.CreateFileJE", $file_path, $config);
                    g::set("config", $config);
                } else {
                    g::run("tools.CreateFileJE", $file_path, g::get($data_path));
                }
            } else {
                if ($data_path === "config") {
                    $config_file = g::run("tools.ReadFileJD", $file_path);
                    if ($config_file === false) {
                        // HERE
                        g::run("tools.Log", "There is an error with the config.json file. Please fix the problem and check again.", 5);
                        die("There is an error with the config.json file. Please fix the problem and check again.");
                    }
                    $cuki = g::get("clone.uki");
                    $config_changed = false;

                    if (empty($config_file["clone"]) || empty($config_file["admin"])) {
                        g::run("crypt.GenerateKeys");
                        $tmp_config = g::get("config");
                        $config_file["clone"] = $tmp_config["clone"];
                        $config_file["admin"] = $tmp_config["admin"];
                        $config_changed = true;
                    }

                    if (empty($config_file["env"][$cuki])) {
                        $config = g::get("config");
                        $config_file["env"][$cuki]["paths"] = $config["paths"];
                        $config_changed = true;
                    }
                    if ($config_changed) {
                        g::run("tools.CreateFileJE", $file_path, $config_file);
                    }
                    g::set($data_path, $config_file);
                } else {
                    g::set($data_path, g::run("tools.ReadFileJD", $file_path));
                }
            }
        }
    },
    "UpdateConfigCheck" => function ($path, $value) {
        $config_path = g::get("config.paths.clone_config_file");
        $config_data = g::run("tools.ReadFileJD", $config_path);

        if (empty($config_data["checks"])) {
            $config_data["checks"] = array();
        }

        $config_data["checks"]["$path"] = $value;
        g::run("tools.CreateFileJE", $config_path, $config_data);
    },
    "UpdateConfigFiles" => function ($path, $value, $env = false) {
        $config_file = g::get("config.paths.clone_config_file");
        $config_data = g::run("tools.ReadFileJD", $config_file);

        $cuki = g::get("clone.uki");
        $config_data["paths"] = $config_data["env"][$cuki]["paths"];

        $clone_views_file = $config_data["paths"]["clone_views_file"];
        $clone_mods_file = $config_data["paths"]["clone_mods_file"];
        $clone_tmpls_file = $config_data["paths"]["clone_tmpls_file"];
        $clone_bits_file = $config_data["paths"]["clone_bits_file"];
        $clone_base_file = $config_data["paths"]["clone_base_file"];

        $edit_path = "";

        if (strpos($path, "views.") === 0 && !empty($clone_views_file)) {
            $config_data["views"] = g::run("tools.ReadFileJD", $clone_views_file);
            $config_file = $clone_views_file;
            $edit_path = "views";
        } else if (strpos($path, "mods.") === 0 && !empty($clone_mods_file)) {
            $config_data["mods"] = g::run("tools.ReadFileJD", $clone_mods_file);
            $config_file = $clone_mods_file;
            $edit_path = "mods";
        } else if (strpos($path, "tmpls.") === 0 && !empty($clone_tmpls_file)) {
            $config_data["tmpls"] = g::run("tools.ReadFileJD", $clone_tmpls_file);
            $config_file = $clone_tmpls_file;
            $edit_path = "tmpls";
        } else if (strpos($path, "bits.") === 0 && !empty($clone_bits_file)) {
            $config_data["bits"] = g::run("tools.ReadFileJD", $clone_bits_file);
            $config_file = $clone_bits_file;
            $edit_path = "bits";
        } else if (strpos($path, "base.") === 0 && !empty($clone_base_file)) {
            $config_data["base"] = g::run("tools.ReadFileJD", $clone_base_file);
            $config_file = $clone_base_file;
            $edit_path = "base";
        }

        unset($config_data["paths"]);
        g::set("void.config", $config_data);
        if ($env) {
            g::set("void.config.env.$cuki.$path", $value);
        } else {
            g::set("void.config.$path", $value);
        }

        if (!empty($edit_path)) {
            $changed_config_path = g::get("void.config.$edit_path");
        } else {
            $changed_config_path = g::get("void.config");
        }

        g::run("tools.CreateFileJE", $config_file, $changed_config_path);
        g::set("void.config", null);
    },
    "GetConfigComplete" => function () {
        $config_file = g::get("config.paths.clone_config_file");
        $config_data = g::run("tools.ReadFileJD", $config_file);

        $cuki = g::get("clone.uki");
        $config_data["paths"] = $config_data["env"][$cuki]["paths"];

        $clone_views_file = $config_data["paths"]["clone_views_file"];
        $clone_mods_file = $config_data["paths"]["clone_mods_file"];
        $clone_tmpls_file = $config_data["paths"]["clone_tmpls_file"];
        $clone_bits_file = $config_data["paths"]["clone_bits_file"];
        $clone_base_file = $config_data["paths"]["clone_base_file"];

        if (!empty($clone_views_file)) {
            $config_data["views"] = g::run("tools.ReadFileJD", $clone_views_file);
        } else if (!empty($clone_mods_file)) {
            $config_data["mods"] = g::run("tools.ReadFileJD", $clone_mods_file);
        } else if (!empty($clone_tmpls_file)) {
            $config_data["tmpls"] = g::run("tools.ReadFileJD", $clone_tmpls_file);
        } else if (!empty($clone_bits_file)) {
            $config_data["bits"] = g::run("tools.ReadFileJD", $clone_bits_file);
        } else if (!empty($clone_base_file)) {
            $config_data["base"] = g::run("tools.ReadFileJD", $clone_base_file);
        }

        unset($config_data["paths"]);
        return $config_data;
    },
    "UpdateConfigComplete" => function ($posted_config, $is_it_json = true) {
        if (!empty($posted_config)) {
            $config_path = g::get("config.paths.clone_config_file");
            if ($is_it_json) {
                $config_data = g::run("tools.DJD", $posted_config);
            } else {
                $config_data = $posted_config;
            }

            $cuki = g::get("clone.uki");
            $config_data["paths"] = $config_data["env"][$cuki]["paths"];
            $files_data = array();

            $clone_views_file = $config_data["paths"]["clone_views_file"];
            if (!empty($clone_views_file)) {
                $files_data[] = array($clone_views_file, $config_data["views"]);
                $config_data["views"] = array();
            }
            $clone_mods_file = $config_data["paths"]["clone_mods_file"];
            if (!empty($clone_mods_file)) {
                $files_data[] = array($clone_mods_file, $config_data["mods"]);
                $config_data["mods"] = array();
            }
            $clone_tmpls_file = $config_data["paths"]["clone_tmpls_file"];
            if (!empty($clone_tmpls_file)) {
                $files_data[] = array($clone_tmpls_file, $config_data["tmpls"]);
                $config_data["tmpls"] = array();
            }
            $clone_bits_file = $config_data["paths"]["clone_bits_file"];
            if (!empty($clone_bits_file)) {
                $files_data[] = array($clone_bits_file, $config_data["bits"]);
                $config_data["bits"] = array();
            }
            $clone_base_file = $config_data["paths"]["clone_base_file"];
            if (!empty($clone_base_file)) {
                $files_data[] = array($clone_base_file, $config_data["base"]);
                $config_data["base"] = array();
            }

            unset($config_data["paths"]);
            $files_data[] = array($config_path, $config_data);

            $fl = count($files_data);
            for ($i = 0; $i < $fl; $i++) {
                g::run("tools.CreateFileJE", $files_data[$i][0], $files_data[$i][1]);
            }
        }
    },
    "WriteFile" => function ($str, $filename, $append = false) {
        if (!empty($filename)) {
            if (@file_exists($filename) && $append) {
                $fh = fopen($filename, "a") or die(g::run("tools.Say", "error|file-exists-cant-open-file-to-append", 5));
            } else {
                $fh = fopen($filename, "w") or die(g::run("tools.Say", "error|cant-open-file-to-write", 5));
            }
            fwrite($fh, "$str\n") or die(g::run("tools.Say", "error|cant-write-to-file", 5));
            fclose($fh);
        } else {
            g::run("tools.Say", "error|filename-empty-string-not-written|$filename|$str");
        }
    },
    "ReadFileJD" => function ($file_path) {
        $file_contents = g::run("tools.LoadPathSafe", $file_path);
        $file_json_to_array = g::run("tools.JD", $file_contents);
        return (is_array($file_json_to_array)) ? $file_json_to_array : false;
    },
    "XMLtoArray" => function ($xml) {
        $previous_value = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXml($xml);
        libxml_use_internal_errors($previous_value);
        if (libxml_get_errors()) {
            return [];
        }
        return g::run("tools.DOMtoArray", $dom);
    },
    "DOMtoArray" => function ($root) {
        $result = array();

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if (in_array($child->nodeType, [XML_TEXT_NODE, XML_CDATA_SECTION_NODE])) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1
                        ? $result['_value']
                        : $result;
                }
            }
            $groups = array();
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = g::run("tools.DOMtoArray", $child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = g::run("tools.DOMtoArray", $child);
                }
            }
        }
        return $result;
    },
    "ReadFileXML" => function ($file_path) {
        $file_contents = g::run("tools.LoadPathSafe", $file_path);
        $file_xml_to_array = g::run("tools.XMLtoArray", $file_contents);
        return (is_array($file_xml_to_array)) ? $file_xml_to_array : false;
    },
    "LoadPathSafe" => function ($url, $method = 'GET', $data = array(), $auth = null, $headers = null, $local_path = null) {
        $isUrl = $isLocal = $callCurl = $callFopen = $fopenExists = $curlExists = $fopenUrlExists = false;

        if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false) {
            $isUrl = true;
        } else {
            $isLocal = true;
        }
        if (function_exists('curl_init')) {
            $curlExists = true;
        }

        if (function_exists('fopen') === true) {
            $fopenExists = true;
            if (ini_get('allow_url_fopen') === true) {
                $fopenUrlExists = true;
            }
        }

        if ($isUrl) {
            if ($curlExists) {
                $callCurl = true;
            } elseif ($fopenExists && $fopenUrlExists) {
                $callFopen = true;
            }
        } elseif ($isLocal) {
            if ($fopenExists) {
                $callFopen = true;
            } elseif ($curlExists) {
                $url = 'file:///' . realpath($url);
                $callCurl = true;
            }
        }
        if ($callCurl) {
            if ($method == 'GET') {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
                curl_setopt($ch, CURLOPT_URL, $url);
                // 2019-10-16, added to fix ipdata get data ssl issue
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $data);
                }
                $result = curl_exec($ch);
                /*
                $info = curl_getinfo($ch);
                print_r($result);
                print_r($info);
                */
                curl_close($ch);
            } else if ($method == 'DOWNLOAD') {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
                curl_setopt($ch, CURLOPT_URL, $url);

                // 2019-10-16, added to fix ipdata get data ssl issue
                // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $data = curl_exec($ch);
                curl_close($ch);
                $result = file_put_contents($local_path, $data);
            } else {
                // $postdata = http_build_query($data);
                //open connection
                $ch = curl_init();
                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_URL, $url);
                if (!empty($auth)) {
                    curl_setopt($ch, CURLOPT_USERPWD, "$auth");
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                }
                if (!empty($headers)) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }
                //curl_setopt($ch, CURLOPT_POST, count($data));
                if (is_array($data) && !empty($data)) {
                    curl_setopt($ch, CURLOPT_POST, count($data));
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                /*
                $info = curl_getinfo($ch);
                print_r($result);
                print_r($info);
                */
                curl_close($ch);
            }
        } elseif ($callFopen) {
            if ($method == 'GET') {
                $result = file_get_contents($url);
            } else if ($method == 'DOWNLOAD') {
                $result = file_put_contents($local_path, file_get_contents($url));
            } else {
                $postdata = http_build_query($data);
                $opts = array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => 'Content-type: application/x-www-form-urlencoded',
                        'content' => $postdata,
                    ),
                );
                $context = stream_context_create($opts);
                $result = @file_get_contents($url, false, $context);
            }
        } else {
            $result = "";
        }
        return $result;
    },
    "LoadFileSimple" => function ($file) {
        if (is_file($file)) {
            ob_start();
            require $file;
            return ob_get_clean();
        } else {
            g::run("tools.Say", "error|file-not-found|$file");
        }
    },
    "CreateFileJE" => function ($file_path, $contents) {
        g::run("tools.WriteFile", g::run("tools.JE", $contents), $file_path);
    },
    "StrLeft" => function ($s1, $s2) {
        return substr($s1, 0, strpos($s1, $s2));
    },
    "Redirect" => function ($url = "") {
        if (empty($url)) {
            $url = g::get("op.meta.url.refer");
        }
        // g::run("tools.CaptureRedirect", $url);
        if (!empty($url)) {
            g::set("op.meta.redirect", $url);
            g::del("op.meta.url.refer");
        }
    },
    "RedirectNow" => function ($url) {
        // g::run("tools.CaptureRedirect", $url);
        if (!headers_sent()) {
            if (preg_match('/(?i)msie [1-9]/', $_SERVER['HTTP_USER_AGENT'])) {
                header('Refresh:0;url=' . urldecode($url));
            } else {
                $blnReplace = true;
                $intHRC = 302;
                header('Refresh:0;url=' . urldecode($url));
                //header('Location: ' . urldecode($url), $blnReplace, $intHRC);
            }
            exit;
            die;
        } else {
            echo '<script type="text/javascript">';
            echo 'window.location.href="' . $url . '";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
            echo '</noscript>';
            exit;
            die;
        }
    },
    "CaptureRedirect" => function ($url) {
        $murl = g::get("op.meta.url");
        $ut = g::get("op.meta.user.type");
        $refer = $murl["refer"];
        $currl = $murl["base"] . $murl["request"];
        $output = $murl["output"];
        if ($output !== "json") {
            // g::run("core.SessionSet", "redir", $currl);
            error_log("User Type : '$ut'");
            error_log("Redirected from referer : '$refer'");
            error_log("Redirected from current url: '$currl'");
            error_log("Redirected to url: '$url'");
            error_log("Session ID: " . session_id());
        }
    },
    "HeaderSet" => function ($key, $value) {
        header("$key: $value");
    },
    "HeaderGet" => function ($key) {
        $headers = array();
        if (!function_exists('getallheaders')) {
            foreach ($_SERVER as $name => $value) {
                /* RFC2616 (HTTP/1.1) defines header fields as case-insensitive entities. */
                if (strtolower(substr($name, 0, 5)) == 'http_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        } else {
            $headers = getallheaders();
        }
        return $headers; //[$key];
    },
    "MDArrayKeys" => function ($arr) {
        foreach ($arr as $k => $v) {
            $keys[] = $k;
            if (is_array($arr[$k])) {
                $keys = array_merge($keys, g::run("tools.MDArrayKeys", $arr[$k]));
            }
        }
        return $keys;
    },
    "MaxArrayValByKey" => function ($array, $key_search) {
        $currentMax = null;
        foreach ($array as $arr) {
            foreach ($arr as $key => $value) {
                if ($key == $key_search && ($value >= $currentMax)) {
                    $currentMax = $value;
                }
            }
        }

        return $currentMax;
    },
    "ArrayDiffAssocRecursive" => function ($array1, $array2) {
        $difference = array();
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if ((!isset($array2[$key]) || !is_array($array2[$key])) && !empty($value)) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = g::run("tools.ArrayDiffAssocRecursive", $value, $array2[$key]);
                    if (!empty($new_diff))
                        $difference[$key] = $new_diff;
                }
            } else if ((!array_key_exists($key, $array2) || $array2[$key] !== $value) && !empty($value)) {
                $difference[$key] = $value;
            }
        }
        return $difference;
    },
    "JE" => function ($arr, $clean = false) {
        if ($clean) {
            return json_encode(g::run("tools.CleanData", $arr));
        }
        return json_encode($arr);
    },
    "JD" => function ($json_str, $dirty = false) {
        if ($dirty) {
            return g::run("tools.DirtData", json_decode($json_str, true));
        }
        return json_decode($json_str, true);
    },
    "CleanData" => function ($arr_or_str) {
        if (is_array($arr_or_str)) {
            foreach ($arr_or_str as $key => $value) {
                $arr_or_str[$key] = g::run("tools.CleanData", $value);
            }
        } else {
            if ($arr_or_str !== null) {
                $arr_or_str = str_replace(
                    array('>', '<', "'", '"', "\n", "\r", "\r\n", "\t", "  "),
                    array("&gt;", "&lt;", "&#039;", "&quot;", "", "", "", "", ""),
                    $arr_or_str
                );
            }
        }
        return $arr_or_str;
    },
    "DirtData" => function ($arr_or_str) {
        if (is_array($arr_or_str)) {
            foreach ($arr_or_str as $key => $value) {
                $arr_or_str[$key] = g::run("tools.DirtData", $value);
            }
        } else {
            $arr_or_str = str_replace(
                array('\"', "&gt;", "&lt;", "&quot;", "&#039;", "&nbsp;"),
                array('"', '>', '<', '"', "'", ' '),
                $arr_or_str
            );
        }
        return $arr_or_str;
    },
    "JEC" => function ($arr) {
        return g::run("tools.CleanData", json_encode($arr));
    },
    "JDD" => function ($json_str, $dirty = false) {
        return g::run("tools.DirtData", json_decode($json_str, true));
    },
    "DJD" => function ($json_str, $dirty = false) {
        return json_decode(g::run("tools.DirtData", $json_str), true);
    },
    "CleanQS" => function ($str) {
        // Remove out Non "Letters"
        $str = str_replace("&", ";", $str);
        $str = preg_replace('/[^\\pL\d\.,:;_\-+=\/ ]+/u', '', $str);
        return $str;
    },
    "SafeUrl" => function ($text, $wutf = false) {
        // First convert html entities back
        $text = str_replace(
            array("&amp;", "&gt;", "&lt;", "&#039;", "&quot;"),
            array("&", ">", "<", "'", "\""),
            $text
        );
        //--echo "1- $text\n";
        // Swap out Non "Letters" with a -
        $text = preg_replace('/[^\\pL\d\.;_+=,\/]+/u', '-', $text);
        //--echo "2- $text\n";
        // Trim out extra -'s
        $text = trim($text, '-');
        //--echo "3- $text\n";
        // Convert letters that we have left to the closest ASCII representation
        //setlocale(LC_ALL, 'en_US.utf8');
        //$text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = g::run("tools.RemoveAccents", $text);
        //--echo "4- $text\n";
        // Make text lowercase
        $text = strtolower($text);
        //--echo "5- $text\n";
        // exit here for proper genes path......................
        // Strip out anything we haven't been able to convert
        $text = preg_replace('/[^-\w\.;+\/]+/', '', $text);
        //--echo "6- $text\n";
        $text = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '-', $text);
        //--echo "7- $text\n";
        $text = preg_replace("/[\/_|+ -]+/", "-", $text);
        //--echo "8- $text\n";
        if (substr($text, -1) == "-") {
            $text = rtrim($text, "-");
            //--echo "9- $text\n";
        }
        //--echo "0- $text\n";
        return $text;
    },
    "ToAscii" => function ($str, $replace = array(), $delimiter = '-') {
        if (!empty($replace)) {
            $str = str_replace((array) $replace, ' ', $str);
        }
        $str = str_replace(array("&#039;", "&quot;", "&lt;", "&gt;"), "", $str);

        $clean = urldecode($str);
        $clean = g::run("tools.RemoveAccents", $clean);
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $clean);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim(substr($clean, 0, 128), '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
        if (substr($clean, -1) == "-") {
            $clean = rtrim($clean, "-");
        }
        return $clean;
    },
    "RemoveAccents" => function ($str) {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        return str_replace($a, $b, $str);
    },
    "DTS" => function ($mode = 0, $timestamp = null) {

        $settings = g::get("config.settings");
        $time_zone = (!empty($settings["timezone"])) ? $settings["timezone"] : "Europe/Tallinn";
        $date_format = (!empty($settings["date_time_format"])) ? $settings["date_time_format"] : "Y-m-d H:i:s.u";

        // input timestamp / datetime

        // microtimestamp
        $mt = microtime();
        // $returns[] = $mt;
        // explode microtime
        $t = explode(" ", $mt);
        $ts = $t[1];
        $ms = $t[0];

        if (!empty($timestamp)) {
            $ts = $timestamp;
            $ms = 0;
        }

        // $mode = 0 :: timestamp return as seconds
        if ($mode == 0) {
            // 1589288061
            return $ts;
        }

        // $mode = 1 ::  timestamp return as milliseconds 3 digits
        $ts_dot_ms = sprintf("%0.3f", $ts + $ms);
        if ($mode == 1) {
            // 1589288096.181
            return $ts_dot_ms;
        }

        // $mode = 2 :: timestamp return as microseconds 6 digits
        $ts_dot_mcs = sprintf("%0.6f", $ts + $ms);
        if ($mode == 2) {
            // 1589288112.660112
            return $ts_dot_mcs;
        }

        // $mode = 3 ::  datetime return as bigint, utc, milliseconds 3 digits
        $ms3 = substr((string) $ms, 2, 3);
        $ms6 = substr((string) $ms, 2, 6);
        $dtn = new DateTime($time_zone);
        $dtn->setTimestamp($ts);
        $dtn->setTimeZone(new DateTimeZone('UTC'));
        $datetime = $dtn->format('YmdHis');
        if ($mode == 3) {
            // 20200512125526009
            return $datetime . $ms3;
        }

        // $mode = 4 :: datetime return as float, utc, microseconds 6 digits
        if ($mode == 4) {
            // 20200512125538.815640
            return $datetime . "." . $ms6;
        }

        // $mode = 5 :: datetime return human readable, utc, with seconds
        $datetimef = DateTime::createFromFormat('YmdHis', $datetime, new DateTimeZone('UTC'));
        $dthr = $datetimef->format('Y-m-d H:i:s');

        if ($mode == 5) {
            // 2020-05-12 12:55:55
            return $dthr;
        }

        // $mode = 6 :: datetime return human readable, utc/timezoned, with milliseconds 3 digits
        if (!empty($time_zone)) {
            $datetimef->setTimeZone(new DateTimeZone($time_zone));
        }
        $add_ms = false;
        if (empty($date_format)) {
            $date_format = 'Y-m-d H:i:s';
        } else {
            if (strpos($date_format, ".u") > -1) {
                $date_format = str_replace(".u", "", $date_format);
                $add_ms = true;
            }
        }
        $dthrtz = $datetimef->format($date_format);
        if ($add_ms) {
            $dthrtz .= "." . $ms3;
        }
        if ($mode == 6) {
            // 2020-05-12 15:56:23.006
            return $dthrtz;
        }

        // $mode = 7 :: hashed datetime from float, utc, microseconds 6 digits
        if ($mode == 7) {
            // pv7e0hppxl7xgv
            return g::run("crypt.MicroHash", $datetime . "." . $ms6);
        }
        return false;
    },
    "ExitWithOpDataResponse" => function () {
        $op = g::get("op");
        $op_type = $op["meta"]["url"]["output"];

        if ($op_type === "json") {
            header('Content-type:application/json;charset=utf-8');
            echo g::run("tools.JE", $op["data"]);
        }
        exit;
    },
    "ExitWithOpMetaResponse" => function () {
        $op = g::get("op");
        $op_type = $op["meta"]["url"]["output"];

        if ($op_type === "json") {
            header('Content-type:application/json;charset=utf-8');
            $op = array("meta" => $op["meta"]);
            echo g::run("tools.JE", $op);
        }
        exit;
        die;
    },
    "PackHTML" => function ($html) {
        // Remove whitespace characters (space, tab, newline) outside HTML tags
        $html = preg_replace('/\s+/', ' ', $html);

        // Remove whitespace around HTML tags
        $html = preg_replace('/\s*<\s*(\/?[^>]+)\s*>/', '<$1>', $html);

        return $html;
    },
    "WP" => array(
        "ConvertWPXMLtoJSON" => function ($xml_path, $json_path) {
            g::run("tools.WriteFile", g::run("tools.JE", g::run("tools.ReadFileXML", $xml_path)), $json_path);
        },
        "ImportCategories" => function ($json_path) {
            $wp_data = g::run("tools.ReadFileJD", $json_path);
            $categories = $wp_data["rss"]["channel"]["wp:category"];
            $c = count($categories);
            //$o = 0;
            $clean_categories = array();
            for ($i = 0; $i < $c; $i++) {
                //if ($o === 50) {break;exit;return false;}
                $row = $categories[$i];
                $clean_categories[] = $row;
                //$o++;
            }
            return $clean_categories;
        },
        "ImportAttachments" => function ($json_path, $allowed_keys = array()) {
            if (empty($allowed_keys)) {
                $allowed_keys = array('title', 'wp:post_date', 'wp:post_parent', 'wp:attachment_url');
            }
            $wp_data = g::run("tools.ReadFileJD", $json_path);
            $items = $wp_data["rss"]["channel"]["item"];
            $c = count($items);
            //$o = 0;
            $attachments = array();
            for ($i = 0; $i < $c; $i++) {
                //if ($o === 50) {break;exit;return false;}
                $row = $items[$i];
                if ($row["wp:post_type"] === "attachment") {
                    //$o++;
                    $attachments[] = array_intersect_key($row, array_flip($allowed_keys));
                }
            }
            return $attachments;
        },
        "ImportItems" => function ($json_path, $allowed_keys = array()) {
            if (empty($allowed_keys)) {
                $allowed_keys = array('title', 'link', 'dc:creator', 'wp:post_id', 'wp:post_date', 'wp:post_name', 'wp:status', 'category');
            }
            $wp_data = g::run("tools.ReadFileJD", $json_path);
            $items = $wp_data["rss"]["channel"]["item"];
            $c = count($items);
            //$o = 0;
            $clean_items = array();
            for ($i = 0; $i < $c; $i++) {
                //if ($o === 50) {break;exit;return false;}
                $row = $items[$i];
                if ($row["wp:post_type"] === "post" && $row["wp:status"] === "publish") {
                    //$o++;
                    $clean_row = array_intersect_key($row, array_flip($allowed_keys));

                    $crc = $clean_row["category"];
                    if (!empty($crc["@attributes"]["nicename"])) {
                        $clean_row["category"] = g::run("tools.JE", array($crc["@attributes"]["nicename"]));
                    } else {
                        $crcc = array();
                        foreach ($crc as $a => $cc) {
                            $crcc[] = $cc["@attributes"]["nicename"];
                        }
                        $clean_row["category"] = g::run("tools.JE", $crcc);
                    }

                    $clean_items[] = $clean_row;
                }
            }
            //die("$o items exists.");
            return $clean_items;
        },
        "CreateLabelsMediaBundleJson" => function ($json_path, $json_labels_media_path) {
            $categories = g::run("tools.WP.ImportCategories", $json_path);
            $attachments = g::run("tools.WP.ImportAttachments", $json_path);

            $lc = array();
            foreach ($categories as $x => $cat) {
                $lc[$cat["wp:category_nicename"]] = array("parent" => $cat["wp:category_parent"], "value" => $cat["wp:cat_name"]);
            }
            $la = array();
            foreach ($attachments as $x => $att) {
                $la[$att["wp:post_parent"]] = array("url" => $att["wp:attachment_url"], "title" => $att["title"]);
            }

            $gla = array("labels" => $lc, "media" => $la);
            g::run("tools.CreateFileJE", $json_labels_media_path, $gla);
            echo "Done.";
        }
    )
));


g::set("ui", array(
    "tmp_data" => array(),
));

g::def("ui", array(
    "LoadViewHtml" => function () {
        $bare = g::get("op.meta.url.bare");
        $mod = g::get("op.meta.url.mod");
        $view = g::get("op.meta.url.view");

        $genes_ui_html = g::get("config.paths.genes_ui_html");
        $clone_ui_html = g::get("config.paths.clone_ui_html");
        $clone_ui_folder = g::get("config.paths.clone_ui_folder");

        $cfg_mods = g::get("config.mods");
        $cfg_tmpls = g::get("config.tmpls");

        $tmpl = "";

        if (!empty($mod)) {
            if (!empty($cfg_mods[$mod])) {
                if (!empty($cfg_mods[$mod]["tmpls"]) && is_string($cfg_mods[$mod]["tmpls"])) {
                    $tmpl = $cfg_mods[$mod]["tmpls"];
                } else if (
                    !empty($cfg_mods[$mod]["tmpls"]) &&
                    is_array($cfg_mods[$mod]["tmpls"]) &&
                    !empty($cfg_mods[$mod]["tmpls"]["path"])
                ) {
                    $tmpl = g::get("config.paths." . $cfg_mods[$mod]["tmpls"]["path"]) . $cfg_mods[$mod]["tmpls"]["file"];
                } else if (!empty($cfg_mods[$mod]["tmpls"][$view]) && is_string($cfg_mods[$mod]["tmpls"][$view])) {
                    $tmpl = $cfg_mods[$mod]["tmpls"][$view];
                } else if (
                    !empty($cfg_mods[$mod]["tmpls"][$view]) &&
                    is_array($cfg_mods[$mod]["tmpls"][$view]) &&
                    !empty($cfg_mods[$mod]["tmpls"][$view]["path"])
                ) {
                    $tmpl = g::get("config.paths." . $cfg_mods[$mod]["tmpls"][$view]["path"]) . $cfg_mods[$mod]["tmpls"][$view]["file"];
                } else {
                    $tmpl = $genes_ui_html;
                }
            } else {
                $tmpl = $genes_ui_html;
            }
        } else if (!empty($cfg_tmpls[$view])) {
            $tmpl = $clone_ui_folder . $cfg_tmpls[$view];
        } else {
            $tmpl = $clone_ui_html;
        }

        if (!empty($tmpl) && is_file($tmpl)) {
            /*
            // void.cache.encoded.hash
            $vceh = g::run("crypt.BUE", $tmpl);
            // if it exists in session, get that;
            $tmpl_file = g::run("core.SessionGet", $vceh);
            if (empty($tmpl_file)) {
                $tmpl_file = g::run("tools.LoadFileSimple", $tmpl);
                $tmpl_file = g::run("tools.PackHTML", $tmpl_file);
                g::run("core.SessionSet", $vceh, $tmpl_file);
            } else {
                $tmpl_file = g::run("tools.DirtData", $tmpl_file);
            }
            */
            $tmpl_file = g::run("tools.LoadFileSimple", $tmpl);
            if (g::get("config.settings.compress_renders") != 0) {
                $tmpl_file = g::run("tools.PackHTML", $tmpl_file);
            }
            g::set("op.tmpl", $tmpl_file);
        } else {
            g::run("tools.Say", "error|tmpl-file-does-not-exist|$tmpl");
        }
    },
    "ProcessComments" => function ($html) {
        $rgx = '/<!--([a-zA-Z0-9]+)([^>][\s\w="@|:{}.,\;\-\/\\\']*?)>(.*?)[\s]*<!--\/\1-->/msi';

        if (preg_match_all($rgx, $html, $tags, PREG_SET_ORDER, 0)) {
            foreach ($tags as $tag) {
                $tn = trim($tag[1]); // tag_name
                $ta = trim($tag[2]); // tag_attributes
                $tc = trim($tag[3]); // tag_content

                $response = g::run("ui.ParseTagAttributes", $ta, $tc);
                $ta = (empty($response["ta"]) ? "" : " " . trim($response["ta"]));
                $tc = (empty($response["tc"]) ? $tc : trim($response["tc"]));

                $remove = (empty($response["remove"]) ? 0 : trim($response["remove"]));

                if ($remove > 0) {
                    $html = str_replace($tag[0], "", $html);
                } elseif ($response["tc"] === false) {
                    $tc = "";
                    $tn = "<$tn $ta>$tc</$tn>";
                    $html = str_replace($tag[0], $tn, $html);
                } else {
                    $tc = g::run("ui.ProcessComments", $tc); // tag_content
                    $tn = $tc;
                    $html = str_replace($tag[0], $tn, $html);
                }
            }
        }

        // genes key replacements
        $html = g::run("ui.ProcessKeys", $html);

        return trim($html);
    },
    "ProcessTags" => function ($html) {
        $rgx = '/<([a-zA-Z0-9]+)([^>][\s\w="#\[\]@|:{}.,\;\-\/\\\']*?)([a-zA-Z0-9._-]+)>(.*?)<\/\1>[\s]*<!--\3-->/msi';

        if (preg_match_all($rgx, $html, $tags, PREG_SET_ORDER, 0)) {
            foreach ($tags as $tag) {
                $tn = trim($tag[1]); // tag_name
                $ta = trim($tag[2]); // tag_attributes
                $tc = trim($tag[4]); // tag_content

                $response = g::run("ui.ParseTagAttributes", $ta, $tc);
                $ta = (empty($response["ta"]) ? "" : " " . trim($response["ta"]));
                $tc = (empty($response["tc"]) ? $tc : trim($response["tc"]));

                $remove = (empty($response["remove"]) ? 0 : trim($response["remove"]));

                if ($remove > 0) {
                    $html = str_replace($tag[0], "", $html);
                } elseif (empty($response["tc"])) {
                    $tc = "";
                    if ($tn === "del") {
                        $tn = $tc;
                    } else {
                        $tn = "<$tn$ta>$tc</$tn>";
                    }
                    $html = str_replace($tag[0], $tn, $html);
                } else {
                    $tc = g::run("ui.ProcessTags", $tc); // tag_content

                    if ($tn === "del") {
                        $tn = $tc;
                    } else {
                        $tn = "<$tn$ta>$tc</$tn>";
                    }

                    $html = str_replace($tag[0], $tn, $html);
                }
            }
        }
        // genes key replacements
        $html = g::run("ui.ProcessKeys", $html);
        return trim($html);
    },
    "ParseTagAttributes" => function ($ta, $tc) {
        // $rgx = '/(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?/msi';
        //$rgx = '/([^\s="\']+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?/msi';
        $rgx = '/([^\s=]+)\s*=\s*["\']([^"\']+)[\"\']/mi';
        $tag_attrs = array();
        $response = array();
        if (preg_match_all($rgx, $ta, $attrs, PREG_SET_ORDER, 0)) {

            foreach ($attrs as $attr) {
                $key = $attr[1];
                $val = $attr[2];

                // there may be a " with single numeric values, just in case..
                $val = str_replace('"', '', trim($val));

                $tag_attrs[$key] = $val;
            }
            if (!empty($tag_attrs["g"])) {
                $g_val = $tag_attrs["g"];
                unset($tag_attrs["g"]);
                $response = g::run("ui.ProcessGAttr", $g_val, $tag_attrs, $tc);
            }
        }

        return $response;
    },
    "ProcessGAttr" => function ($g_val, $tag_attrs, $tc) {
        $response = array(
            "ta" => "",
            "tc" => "",
        );
        $g_val_arr = explode(";", $g_val);
        /*
            // ;:.,-_a1|{} only allowed
            [0] => if:meta.rules.no{dnh:MemberActivate}
            [1] => if:meta.url.bare{is:activate}
            [2] => each:meta.msgs
            [3] => if:data.main.count{gt:1}
            [4] => use:genes_ui_tmpls_folder/root-guest.html
            [5] => attr:class{active}
        */
        foreach ($g_val_arr as $rule) {
            if (!empty($rule)) {
                $rule = (strpos($rule, "}") > -1) ? substr($rule, 0, strrpos($rule, '}')) : $rule;
                $rarr = explode("{", $rule);
                // print_r($rarr);
                // remove | append | prepend | replace
                // del, use, each, if, attr
                if ($rarr[0] === "del") {
                    $response["remove"] = 1;
                    return $response;
                } else {
                    $oarr = explode(":", $rarr[0]);
                    $operator = $oarr[0];
                    if (!in_array($operator, array("if", "each", "use", "attr"))) {
                        $response["remove"] = 1;
                        return $response;
                    }
                    $dataval = $oarr[1];

                    if ($operator === "if" || $operator === "each") {
                        $data = (empty($dataval) ? "" : g::get("op.$dataval"));
                        $arr = g::get("ui.tmp_data");
                        $sub_data = array();
                        if (!empty($arr)) {
                            if (empty($arr[$dataval])) {
                                if (strpos($dataval, ".") > -1) {
                                    $ps = explode('.', $dataval);
                                    $value = $arr;
                                    foreach ($ps as $part) {
                                        if (!empty($value[$part])) {
                                            $value = $value[$part];
                                        } else {
                                            $value = null;
                                        }
                                    }
                                    $sub_data = $data = $value;
                                } else {
                                    if (!empty($arr[$dataval])) {
                                        $data = $arr[$dataval];
                                    }
                                }
                            } else {
                                $data = $arr[$dataval];
                            }
                        }
                    }
                }
                // include whatever the file tmpl wants to use
                if ($operator === "use") {
                    $file_path = "clone_ui_folder";
                    if (strpos($dataval, "/") > -1) {
                        $path = explode("/", $dataval);
                        $file_path = $path[0];
                        $dataval = $path[1];
                    }
                    $file = g::get("config.paths.$file_path") . $dataval;
                    $tmpl = g::run("tools.LoadFileSimple", $file);
                    if (g::get("config.settings.compress_renders") != 0) {
                        $tmpl = g::run("tools.PackHTML", $tmpl);
                    }
                    $response["tc"] = g::run("ui.ProcessTags", $tmpl);
                    $tc = $tmpl;
                }
                if ($operator === "if") {
                    $response["tc"] = $tc;
                    $comparison =  (empty($rarr[1]) ? "" : $rarr[1]);
                    if (!empty($comparison)) {
                        $comp_arr = explode(":", $comparison);
                        $comp_op = $comp_arr[0];
                        $comp_val = $comp_arr[1];

                        if (($comp_op === "is" || $comp_op === "eq")) {
                            if (strpos($comp_val, ",") > -1) {
                                $comp_val_arr = explode(",", $comp_val);
                                if ($data === false && $comp_val != "false") {
                                    $response["remove"] = 1;
                                    return $response;
                                }
                                $is_found = 0;
                                foreach ($comp_val_arr as $comp_val) {
                                    if (($data == $comp_val)) {
                                        $is_found++;
                                    }
                                }
                                if (($is_found === 0)) {
                                    $response["remove"] = 1;
                                    return $response;
                                }
                            } else {
                                if (($data != $comp_val)) {
                                    $response["remove"] = 1;
                                    return $response;
                                }
                            }
                        } elseif (($comp_op === "has")) {
                            if (strpos($comp_val, ",") > -1) {
                                $comp_val_arr = explode(",", $comp_val);
                                if ($data === false && $comp_val != "false") {
                                    $response["remove"] = 1;
                                    return $response;
                                }
                                $is_found = 0;
                                foreach ($comp_val_arr as $comp_val) {
                                    if (in_array($comp_val, $data)) {
                                        $is_found++;
                                    }
                                }
                                if (($is_found === 0)) {
                                    $response["remove"] = 1;
                                    return $response;
                                }
                            } else {
                                if (is_array($data)) {
                                    if (!in_array($comp_val, $data)) {
                                        $response["remove"] = 1;
                                        return $response;
                                    }
                                } else {
                                    if (($data != $comp_val)) {
                                        $response["remove"] = 1;
                                        return $response;
                                    }
                                }
                            }
                        } elseif (($comp_op === "gt") && ($data <= $comp_val)) {
                            $response["remove"] = 1;
                            return $response;
                        } elseif (($comp_op === "lt") && ($data >= $comp_val)) {
                            $response["remove"] = 1;
                            return $response;
                        } elseif (($comp_op === "not")) {
                            if (strpos($comp_val, ",") > -1) {
                                $comp_val_arr = explode(",", $comp_val);
                                if ($data === false && $comp_val != "false") {
                                } else {
                                    $is_found = 0;
                                    foreach ($comp_val_arr as $comp_val) {
                                        if (($data == $comp_val)) {
                                            $is_found++;
                                        }
                                    }
                                    if (($is_found > 0)) {
                                        $response["remove"] = 1;
                                        return $response;
                                    }
                                }
                            } else {
                                if (($data == $comp_val)) {
                                    $response["remove"] = 1;
                                    return $response;
                                }
                            }
                        } elseif (($comp_op === "dnh")) {
                            // does not have
                            if (strpos($comp_val, ",") > -1) {
                                $comp_val_arr = explode(",", $comp_val);
                                if ($data === false && $comp_val != "false") {
                                    $response["remove"] = 1;
                                    return $response;
                                }
                                $is_found = 0;
                                foreach ($comp_val_arr as $comp_val) {
                                    if (!in_array($comp_val, $data)) {
                                        $is_found++;
                                    }
                                }
                                if (($is_found === 0)) {
                                    $response["remove"] = 1;
                                    return $response;
                                }
                            } else {
                                if (is_array($data)) {
                                    if (in_array($comp_val, $data)) {
                                        $response["remove"] = 1;
                                        return $response;
                                    }
                                } else {
                                    if (($data == $comp_val)) {
                                        $response["remove"] = 1;
                                        return $response;
                                    }
                                }
                            }
                        } elseif (($comp_op === "set") && $comp_val == "1") {
                            // value does exist
                            if (empty($data)) {
                                $response["remove"] = 1;
                                return $response;
                            }
                        } elseif (($comp_op === "set") && $comp_val == "0") {
                            // value does not exist
                            if (!empty($data)) {
                                $response["remove"] = 1;
                                return $response;
                            }
                        }
                    }
                }
                if ($operator === "each") {
                    $response["tc"] = "";
                    if (!empty($data)) {
                        $tmp_data = g::get("ui.tmp_data");
                        foreach ($data as $row) {
                            g::set("ui.tmp_data", $row);
                            $response["tc"] .= g::run("ui.ProcessTags", $tc);
                        }
                        g::set("ui.tmp_data", $tmp_data);
                    } else if (!empty($sub_data)) {
                        foreach ($sub_data as $row) {
                            g::set("ui.tmp_data", $row);
                            $response["tc"] .= g::run("ui.ProcessTags", $tc);
                        }
                        g::set("ui.tmp_data", $arr);
                    } else {
                        $response["tc"] = false;
                    }
                }
                if ($operator === "attr") {
                    $tag_attrs[$dataval] = $rarr[1];
                }
            }
        }
        // render tag attributes
        $ta = "";
        foreach ($tag_attrs as $key => $value) {
            $ta .= $key . '="' . $value . '" ';
        }
        $response["ta"] = $ta;
        // print_r($response);
        return $response;
    },
    "ProcessGAttribute" => function ($g_val, $tag_attrs, $tc) {
        $g_val_arr = explode("|", $g_val);

        $operator = $g_val_arr[0];
        $data = (empty($g_val_arr[1]) ? "" : g::get("op.$g_val_arr[1]"));
        $arr = g::get("ui.tmp_data");
        $sub_data = array();

        if (!empty($arr)) {
            if (empty($arr[$g_val_arr[1]])) {
                if (strpos($g_val_arr[1], ".") > -1) {
                    $ps = explode('.', $g_val_arr[1]);
                    $value = $arr;
                    foreach ($ps as $part) {
                        if (!empty($value[$part])) {
                            $value = $value[$part];
                        } else {
                            $value = null;
                        }
                    }
                    $sub_data = $data = $value;
                } else {
                    if (!empty($arr[$g_val_arr[1]])) {
                        $data = $arr[$g_val_arr[1]];
                    }
                }
            } else {
                $data = $arr[$g_val_arr[1]];
            }
        }

        $comparison = (empty($g_val_arr[2]) ? "" : $g_val_arr[2]);

        $response = array(
            "ta" => "",
            "tc" => "",
        );

        if ($operator === "remove") {
            $response["remove"] = 1;
            return $response;
        } elseif ($operator === "each") {
            if (!empty($data)) {
                $tmp_data = g::get("ui.tmp_data");
                foreach ($data as $row) {
                    g::set("ui.tmp_data", $row);
                    $response["tc"] .= g::run("ui.ProcessTags", $tc);
                }
                g::set("ui.tmp_data", $tmp_data);
            } else if (!empty($sub_data)) {
                foreach ($sub_data as $row) {
                    g::set("ui.tmp_data", $row);
                    $response["tc"] .= g::run("ui.ProcessTags", $tc);
                }
                g::set("ui.tmp_data", $arr);
            } else {
                $response["tc"] = false;
            }
        } elseif ($operator === "if") {
            if (!empty($comparison)) {
                $comp_arr = explode(":", $comparison);
                $comp_op = $comp_arr[0];
                $comp_val = $comp_arr[1];

                if (($comp_op === "is" || $comp_op === "eq")) {
                    if (strpos($comp_val, ",") > -1) {
                        $comp_val_arr = explode(",", $comp_val);
                        if ($data === false && $comp_val != "false") {
                            $response["remove"] = 1;
                            return $response;
                        }
                        $is_found = 0;
                        foreach ($comp_val_arr as $comp_val) {
                            if (($data == $comp_val)) {
                                $is_found++;
                            }
                        }
                        if (($is_found === 0)) {
                            $response["remove"] = 1;
                            return $response;
                        }
                    } else {
                        if (($data != $comp_val)) {
                            $response["remove"] = 1;
                            return $response;
                        }
                    }
                } elseif (($comp_op === "has")) {
                    if (strpos($comp_val, ",") > -1) {
                        $comp_val_arr = explode(",", $comp_val);
                        if ($data === false && $comp_val != "false") {
                            $response["remove"] = 1;
                            return $response;
                        }
                        $is_found = 0;
                        foreach ($comp_val_arr as $comp_val) {
                            if (in_array($comp_val, $data)) {
                                $is_found++;
                            }
                        }
                        if (($is_found === 0)) {
                            $response["remove"] = 1;
                            return $response;
                        }
                    } else {
                        if (is_array($data)) {
                            if (!in_array($comp_val, $data)) {
                                $response["remove"] = 1;
                                return $response;
                            }
                        } else {
                            if (($data != $comp_val)) {
                                $response["remove"] = 1;
                                return $response;
                            }
                        }
                    }
                } elseif (($comp_op === "gt") && ($data <= $comp_val)) {
                    $response["remove"] = 1;
                    return $response;
                } elseif (($comp_op === "lt") && ($data >= $comp_val)) {
                    $response["remove"] = 1;
                    return $response;
                } elseif (($comp_op === "not")) {
                    if (strpos($comp_val, ",") > -1) {
                        $comp_val_arr = explode(",", $comp_val);
                        if ($data === false && $comp_val != "false") {
                        } else {
                            $is_found = 0;
                            foreach ($comp_val_arr as $comp_val) {
                                if (($data == $comp_val)) {
                                    $is_found++;
                                }
                            }
                            if (($is_found > 0)) {
                                $response["remove"] = 1;
                                return $response;
                            }
                        }
                    } else {
                        if (($data == $comp_val)) {
                            $response["remove"] = 1;
                            return $response;
                        }
                    }
                } elseif (($comp_op === "dnh")) {
                    // does not have
                    if (strpos($comp_val, ",") > -1) {
                        $comp_val_arr = explode(",", $comp_val);
                        if ($data === false && $comp_val != "false") {
                            $response["remove"] = 1;
                            return $response;
                        }
                        $is_found = 0;
                        foreach ($comp_val_arr as $comp_val) {
                            if (!in_array($comp_val, $data)) {
                                $is_found++;
                            }
                        }
                        if (($is_found === 0)) {
                            $response["remove"] = 1;
                            return $response;
                        }
                    } else {
                        if (is_array($data)) {
                            if (in_array($comp_val, $data)) {
                                $response["remove"] = 1;
                                return $response;
                            }
                        } else {
                            if (($data == $comp_val)) {
                                $response["remove"] = 1;
                                return $response;
                            }
                        }
                    }
                } elseif (($comp_op === "set") && $comp_val == "1") {
                    // value does exist
                    if (empty($data)) {
                        $response["remove"] = 1;
                        return $response;
                    }
                } elseif (($comp_op === "set") && $comp_val == "0") {
                    // value does not exist
                    if (!empty($data)) {
                        $response["remove"] = 1;
                        return $response;
                    }
                }
            }
        } elseif ($operator === "use") {
            $file_path = "clone_ui_folder";
            if (!empty($g_val_arr[2])) {
                $file_path = $g_val_arr[2];
            }
            $file = g::get("config.paths.$file_path") . $g_val_arr[1];
            $tmpl = g::run("tools.LoadFileSimple", $file);
            $response["tc"] = g::run("ui.ProcessTags", $tmpl);
        } elseif ($operator === "attr") {
            $tag_attrs[$g_val_arr[2]] = $data;
        }

        $ta = "";
        foreach ($tag_attrs as $key => $value) {
            $ta .= $key . '="' . $value . '" ';
        }

        $response["ta"] = $ta;
        return $response;
    },
    "ProcessKeys" => function ($html) {
        $arr = g::get("ui.tmp_data");
        $re = '/{{(\w.*)}}/msiU';
        if (preg_match_all($re, $html, $keys, PREG_SET_ORDER, 0)) {
            foreach ($keys as $key) {
                $rk = $key[1];
                if (empty($arr)) {
                    $html = str_replace($key[0], g::get("op." . $rk), $html);
                } else {
                    if (strpos($rk, "op.") === 0) {
                        $html = str_replace($key[0], g::get($rk), $html);
                    } else {
                        if (is_array($arr)) {
                            // print_r($arr);
                            if (empty($arr[$rk])) {
                                if (strpos($rk, ".") > -1) {
                                    $ps = explode('.', $rk);
                                    $value = &$arr;
                                    foreach ($ps as $part) {
                                        if (is_array($value)) {
                                            $value = &$value[$part];
                                        } else {
                                            $value = "";
                                        }
                                    }
                                } else {
                                    $value = &$arr[$rk];
                                }
                            } else {
                                $value = &$arr[$rk];
                            }
                            // $html = str_replace($key[0], html_entity_decode($value, ENT_NOQUOTES, "UTF-8"), $html);
                            if (is_array($value)) {
                                $value = $value[0];
                            }
                            $html = str_replace($key[0], html_entity_decode($value, ENT_COMPAT, "UTF-8"), $html);
                        } else {
                            $html = str_replace($key[0], $arr, $html);
                        }
                    }
                }
            }
        }
        return trim($html);
    },
    "ProcessArgsRenders" => function () {
        $args = g::get("op.meta.url.args");

        if (!empty($args["m"])) {
            // m is defined -- request is made in meta only mode.
            // this whole render is expected to be meta only
            g::set("config.settings.api_serve_data", 0);
            g::set("config.settings.api_serve_html", 0);
            g::set("op.meta.url.args.q", array());
        } else {
            if (!empty($args["q"])) {
                // q is defined -- query data specifically defined query those.
                g::set("config.settings.api_serve_data", 0);
                g::set("config.settings.api_serve_html", 1);
                if (!is_array($args["q"])) {
                    $args["q"] = array($args["q"]);
                }
            } else {
                // q is not defined -- decided on api
                // m if not defined -- decided on api
                if (!empty($args["u"]) || !empty($args["r"]) || !empty($args["c"])) {
                    $args["q"] = array("nav", "main", "detail");
                } else {
                    $args["q"] = array("nav", "main");
                }
            }
            g::set("op.meta.url.args.q", $args["q"]);
        }
        // api: p, n, o, g, f, s, t, i (query filters)

        // api/ui: c, v, u, d, (crud modes)

        // q (=query:main,detail,nav), m (=meta), r (=redirect)

        // 

        // ok if d & q=main then it means it will redirect to something that load in main after function is done.
        // if u & q=main 
        // if c & q=main

        /*
            "slider" => array("items", "t=slider;o=tss-za;s=public;n=5;"),
            "sidebar" => array("items", "tag=patterns;sort=tss-desc;get=10"),
            "featured" => array("items", "tag=featured;sort=tss-desc;get=4"),
            "main" => array("items", "tag=index;sort=tss-desc;get=10"),

            links/base : base view link
            links/curr : current loop link
            links/c : create/add item link
            links/r : read/view/edit item link
            links/u : update/edit item link
            links/d : delete item link
            links/next : next page link
            links/prev : prev page link
            links/more : get more same query
        */
    }
));

g::run("core.Init");