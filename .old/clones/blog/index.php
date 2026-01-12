<?php
require "../../api/genes.php";

g::def(
    "clone",
    array(
        "Index" => function () {
            $murl = g::get("op.meta.url");
            if (!in_array($murl["bare"], array("about", "contact", "hakkinda", "iletisim"))) {
                $loops = array(
                    "main" => array("items", "s=public;t=content;o=date-za"),
                    "sidebar" => array("items", "s=public;t=content;o=date-za;n=3;g=sidebar"),
                );
                g::run("clone.Query", $loops);
            }
            g::run("ui.LoadViewHtml");
        },
        "Query" => function ($loops = array()) {
            $murl = g::get("op.meta.url");
            $margs = $murl["args"];
            $lang = $murl["lang"];
            if (!empty($loops)) {
                $loops["main"][1] .= ";g=$lang,-sidebar";
                $loops["sidebar"][1] .= ",$lang";
            } else if (!empty($margs["n"]) || !empty($margs["p"])) {
                $loops = array(
                    "main" => array("items", "s=public;t=content;o=date-za;g=$lang,-sidebar"),
                    "sidebar" => array("items", "s=public;t=content;o=date-za;n=3;g=sidebar,$lang"),
                );
            }
            g::run("core.Query", $loops);
            CloneProcessData();
            g::run("ui.LoadViewHtml");
        }
    )
);
g::run("core.Render");

function CloneProcessData()
{
    $op_meta_url_base = g::get("op.meta.url.base");
    $op_meta_url_match = g::get("op.meta.url.match");
    $op_data = g::get("op.data");
    foreach ($op_data as $key => $value) {
        $op_data_loop = $value;
        $count = $op_data_loop["count"];
        $list = $op_data_loop["list"];

        $is_single = false;
        if ($count === 1 && $list[0]["g_alias"] === $op_meta_url_match) {
            $is_single = true;
        }

        for ($i = 0; $i < $count; $i++) {
            $list_item = $list[$i];
            $list_item = CloneParseItems($list_item, $op_meta_url_base, $is_single);
            if ($is_single && !empty($list_item["lang"])) {
                g::set("op.meta.url.lang", $list_item["lang"]);
                g::run("core.EmbedBits");
                $list[$i] = $list_item;
            } else {
                $list[$i] = $list_item;
                /*
                if ($list_item["lang"] === $lang) {
                    $list[$i] = $list_item;
                } else {
                    unset($list[$i]);
                }
                */
            }
        }
        if ($is_single) {
            $op_data[$key]["mode"] = "single";
        } else {
            $op_data[$key]["mode"] = "loop";
        }
        $op_data[$key]["list"] = $list;
    }
    g::set("op.data", $op_data);
}

function CloneParseItems($list_item, $op_meta_url_base, $is_single)
{
    $new_item = array(
        "g_hash" => $list_item["g_hash"],
        "g_alias" => $list_item["g_alias"],
        "g_name" => $list_item["g_name"],
        "g_blurb" => $list_item["g_blurb"],
        "tss" => $list_item["tss"],
        "with_img" => ""
    );

    if ($is_single) {
        $new_item["g_text"] = $list_item["g_text"];

        g::set("op.bits.title", $new_item["g_name"]);
        g::set("op.bits.description", $new_item["g_blurb"]);
    }

    $list_item["g_media"] = g::run("tools.JDD", $list_item["g_media"]);
    $new_item["g_bits"] = g::run("tools.JDD", $list_item["g_bits"]);
    $list_item["g_labels"] = g::run("tools.JDD", $list_item["g_labels"]);

    if (!empty($list_item["g_labels"]["item_labels"])) {
        $list_item["g_labels"] = $list_item["g_labels"]["item_labels"];

        $langs = g::get("config.settings.langs");
        $labels = $list_item["g_labels"];
        foreach ($labels as $i => $label) {
            if (in_array($label, $langs)) {
                unset($list_item["g_labels"][$i]);
                $new_item["lang"] = $label;
            }
        }
        $new_item["tags_class"] = implode(" ", $list_item["g_labels"]);
    }

    if (!empty($list_item["g_media"]["image"])) {
        $new_item["featured_img"] = $op_meta_url_base . "ui/uploads/" . $list_item["g_type"] . "/" . $list_item["g_media"]["image"][0][0];
        $new_item["featured_img_encoded"] = urlencode($new_item["featured_img"]);
        $new_item["with_img"] = "with_img";
    }
    $new_item["url"] = $op_meta_url_base . $list_item["g_alias"];
    $new_item["url_encoded"] = urlencode($new_item["url"]);

    $labels_str = implode('","', $list_item["g_labels"]);
    $labels_db = g::run("db.Get", array("g_key AS 'value', g_value AS 'text'", "labels", "g_type='item_labels' AND g_key IN (\"$labels_str\")"));
    if ($labels_db["total"] > 0) {
        $new_item["tags"] = $labels_db["list"];
    } else {
        $new_item["tags"] = array();
    }
    return $new_item;
}
