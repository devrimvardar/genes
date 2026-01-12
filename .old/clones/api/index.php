<?php
require "../../api/genes.php";

g::def(
    "clone",
    array(
        "Index" => function () {
            g::run("ui.LoadViewHtml");
        },
        "Query" => function () {
            $post = g::get("post");
            $murl = g::get("op.meta.url.clean");
            if ($murl === "todo-login") {
                g::run("mods.pass.MemberAPI.Login");
            } else if ($murl === "todo-register") {
                g::run("mods.pass.MemberAPI.Register");
            } else if ($murl === "todo-logout") {
                g::run("mods.pass.MemberAPI.Logout");
            } else if ($murl === "todo-add") {
                if (g::get("op.meta.user.type") === "guest") {
                    return false;
                }

                $user = g::get("op.meta.user.alias");
                $cfg_base = g::get("config.base");
                $cfg_base[$user][] = array("ty" => $post["ty"], "ta" => $post["ta"]);
                g::run("tools.UpdateConfigFiles", "base", $cfg_base);
            } else if ($murl === "todo-edit") {
                if (g::get("op.meta.user.type") === "guest") {
                    return false;
                }

                $user = g::get("op.meta.user.alias");
                $cfg_base = g::get("config.base");
                if (!empty($post["ok"])) {
                    $cfg_base[$user][$post["id"] - 1]["ok"] = $post["ok"];
                } else {
                    $cfg_base[$user][$post["id"] - 1] = array("ty" => $post["ty"], "ta" => $post["ta"]);
                }
                g::run("tools.UpdateConfigFiles", "base", $cfg_base);
            } else if ($murl === "todo-del") {
                if (g::get("op.meta.user.type") === "guest") {
                    return false;
                }

                $user = g::get("op.meta.user.alias");
                $cfg_base = g::get("config.base");
                unset($cfg_base[$user][$post["id"] - 1]);
                $cfg_base[$user] = array_values($cfg_base[$user]);
                g::run("tools.UpdateConfigFiles", "base", $cfg_base);
            } else if ($murl === "ping") {
                if (g::get("op.meta.user.type") !== "guest") {
                    $user = g::get("op.meta.user.alias");
                    $cfg_base = g::get("config.base");
                    $data = (!empty($cfg_base[$user])) ? $cfg_base[$user] : array();
                    g::set("op.meta.base", $data);
                }
            }
        }
    )
);
g::run("core.Render");
