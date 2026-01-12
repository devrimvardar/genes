<?php !file_exists("genes.php") && ($ch = curl_init("http://cdn.genes.one/genes.php.gz")) && curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false]) && file_put_contents("genes.php", gzdecode(curl_exec($ch))) && curl_close($ch); require "genes.php";

g::def("clone", array(
    "Index" => function () {
        $loops = array(
            "main" => array("items", "s=public;t=content;o=date-za")
        );
        g::run("core.Query", $loops);
        CloneProcessQueryResult();
        g::run("ui.LoadViewHtml");
    },
    "Query" => function () {
        $murl = g::get("op.meta.url");
        $args = $murl["args"];

        $post = g::get("post");
        $ut = g::get("op.meta.user.type");
        if (!empty($murl["bare"]) && !empty($murl["match"]) && $murl["match"] === "swh") {
            StripeWebhook();
        } else if (!empty($args) && !empty($args["work"]) && $ut !== "guest") {
            if ($args["work"] === "add") {
                // work = add
                if (!empty($post)) {
                    $row_data = array(
                        "g_state" => "public",
                        "g_type" => "content",
                        "g_hash" => g::run("tools.DTS", 7),
                        "g_alias" => g::run("tools.SafeUrl", $post["title"]),
                        "g_link" => $post["link"],
                        "g_name" => $post["title"],
                        "g_blurb" => $post["summary"],
                        "g_media" => (!empty($post["image"])) ? g::run("tools.JE", array("image" => array($post["image"]))) : null,
                        "tsu" => g::run("tools.DTS", 5),
                    );

                    $event_data = array(
                        "g_type" => "item_create",
                        "g_hash" => $row_data["g_hash"],
                        "g_key" => $row_data["g_alias"],
                        "g_bits" => g::run("tools.JE", $post),
                    );

                    $sql_rows = array(
                        array("insert", "items", $row_data), // insert item
                        array("insert", "events", $event_data), // insert event
                    );

                    g::run("db.Prepare", $sql_rows);
                    g::set("op.meta.url.state", "success");
                }
            } else if ($args["work"] === "edit") {
                // work = edit
                if (!empty($post)) {
                    $hash = $post["hash"];
                    $uh = g::get("op.meta.user.hash");
                    $row_data = array(
                        "g_state" => "public",
                        "g_type" => "content",
                        "g_alias" => g::run("tools.SafeUrl", $post["title"]),
                        "g_link" => $post["link"],
                        "g_name" => $post["title"],
                        "g_blurb" => $post["summary"],
                        "g_media" => (!empty($post["image"])) ? g::run("tools.JE", array("image" => array($post["image"]))) : null,
                        "tsu" => g::run("tools.DTS", 5),
                    );

                    $event_data = array(
                        "g_type" => "item_update",
                        "g_hash" => $hash,
                        "g_key" => $row_data["g_alias"],
                        "g_bits" => g::run("tools.JE", $post),
                    );

                    $sql_rows = array(
                        array("update", "items", $row_data, "g_hash='$hash' AND uhc = '$uh'"), // update item
                        array("insert", "events", $event_data), // insert event
                    );

                    g::run("db.Prepare", $sql_rows);
                    g::set("op.meta.url.state", "success");
                }
            } else if ($args["work"] === "delete") {
                // work = delete
                if (!empty($post)) {
                    $hash = $post["hash"];
                    $uh = g::get("op.meta.user.hash");
                    $event_data = array(
                        "g_type" => "item_delete",
                        "g_hash" => $hash
                    );
                    $sql_rows = array(
                        array("delete", "items", "g_hash='$hash' AND uhc = '$uh'"), // delete item
                        array("insert", "events", $event_data), // insert delete event
                    );

                    g::run("db.Prepare", $sql_rows);
                    g::set("op.meta.url.state", "success");
                }
            } else if ($args["work"] === "fav") {
                // work = fav/unfav
                if (!empty($post)) {
                    $hash = $post["hash"];
                    $uh = g::get("op.meta.user.hash");
                    $epf = g::run("db.Get", array("id, g_bits", "events", "(g_hash='$uh' AND g_type='person_favs')"));
                    if ($epf["total"] == 1) {
                        $bits = g::run("tools.JD", $epf["list"][0]["g_bits"]);
                        $id = $epf["list"][0]["id"];
                        if (in_array($hash, $bits)) {
                            $bits = array_values(array_diff($bits, [$hash]));
                            g::set("op.meta.url.state", "nact");
                        } else {
                            $bits[] = $hash;
                            g::set("op.meta.url.state", "act");
                        }
                        $event_data = array(
                            "g_bits" => g::run("tools.JE", $bits)
                        );
                        $sql_rows = array(
                            array("update", "events", $event_data, "id='$id'"), // removed
                        );
                    } else {
                        $event_data = array(
                            "g_type" => "person_favs",
                            "g_hash" => $uh,
                            "g_bits" => g::run("tools.JE", array($hash))
                        );
                        $sql_rows = array(
                            array("insert", "events", $event_data), // insert delete event
                        );
                        g::set("op.meta.url.state", "act");
                    }
                    g::run("db.Prepare", $sql_rows);
                }
            }
        } else if (!empty($args) && empty($args["work"])) {
            if (!empty($args["u"]) && $args["u"] === "favs") {
                CloneGetFavs();
                CloneProcessQueryResult();
            } else {
                g::run("core.Query");
                CloneProcessQueryResult();
            }
            g::run("ui.LoadViewHtml");
        }
    },
    "Login" => function () {
        $post = g::get("post");
        $user = g::run("core.SessionGet", "op.meta.user");
        if (empty($user)) {
            if ($post["uid"] === FireBaseVerifyIdToken($post["token"])) {
                if ($post["emailvf"] == true) {
                    // DB actions
                    // Set posted user
                    $user = $post;
                    unset($user["emailvf"]);
                    unset($user["token"]);
                    $user["type"] = array("user");
                    $user["login_date"] = g::run("tools.Now");
                    $email = $user["email"];

                    // first get to see if user exists
                    $ue = g::run("db.Get", array("*", "persons", "((g_email='$email'))"));
                    if ($ue["total"] == 0) {
                        // if not, insert user to db
                        $row_data = array(
                            "g_state" => "active",
                            "g_type" => "member",
                            "g_pwd" => $post["uid"],
                            "g_hash" => g::run("tools.DTS", 7),
                            "g_alias" => g::run("tools.SafeUrl", $post["name"]),
                            "g_email" => "$email",
                            "tsu" => g::run("tools.DTS", 5),
                        );

                        $event_data = array(
                            "g_type" => "person_create",
                            "g_hash" => $row_data["g_hash"],
                            "g_key" => $email,
                            "g_bits" => g::run("tools.JE", $user),
                        );

                        $sql_rows = array(
                            array("insert", "persons", $row_data), // insert user
                            array("insert", "events", $event_data), // insert event
                        );

                        g::run("db.Prepare", $sql_rows);
                        $user["hash"] = $row_data["g_hash"];
                    } else {
                        // if yes, recall user
                        $db_user = $ue["list"][0];
                        $user["hash"] = $db_user["g_hash"];

                        // log login
                        $event_data = array(
                            "g_type" => "person_login",
                            "g_hash" => $user["hash"],
                            "g_key" => $email,
                        );
                        $sql_rows = array(
                            array("insert", "events", $event_data), // insert event
                        );
                        g::run("db.Prepare", $sql_rows);
                    }
                } else {
                    g::run("tools.Say", array("Error", "Email not verified."), 5);
                }
            }
        }
        if (!empty($user)) {
            g::run("core.SessionSet", "op.meta.user", $user);
            g::set("op.meta.user", $user);
        }
    },
    "Logout" => function () {
        g::run("core.SessionEnd");
        g::run("tools.Redirect", "./");
    }
));

g::run("core.Render");

function FireBaseVerifyIdToken($idToken)
{
    // YOU NEED TO INPUT YOUR OWN FIREBASE API-KEY FOR THIS TO WORK
    // IT IS VERY EASY AND VERY QUICK TO GET AND FREE
    $apiKey = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
    $url = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . $apiKey;
    $data = json_encode(['idToken' => $idToken]);
    $headers = ['Content-Type: application/json'];
    // send request
    $response = g::run("tools.LoadPathSafe", $url, "POST", $data, null, $headers);
    // parse response
    $responseData = json_decode($response, true);
    if (isset($responseData['users'])) {
        $uid = $responseData['users'][0]['localId'];
        return $uid;
    } else {
        g::run("tools.Say", array("Error", "Invalid Token"), 5);
    }
}

function CloneProcessQueryResult()
{
    $op_meta_url_base = g::get("op.meta.url.base");
    $uho = g::get("op.meta.user.hash");
    $op_data = g::get("op.data");

    $epf = g::run("db.Get", array("id, g_bits", "events", "(g_hash='$uho' AND g_type='person_favs')"));
    $bits = array();
    if ($epf["total"] == 1) {
        $bits = g::run("tools.JD", $epf["list"][0]["g_bits"]);
    }

    foreach ($op_data as $key => $value) {
        $op_data_loop = $value;
        $count = $op_data_loop["count"];
        $list = $op_data_loop["list"];

        $uhcs = array();

        for ($i = 0; $i < $count; $i++) {
            $list_item = $list[$i];
            $list[$i] = array(
                "g_link" => $list_item["g_link"],
                "g_hash" => $list_item["g_hash"],
                "g_alias" => $list_item["g_alias"],
                "g_name" => $list_item["g_name"],
                "g_blurb" => $list_item["g_blurb"],
                "tsc" => $list_item["tsc"],
                "uhc" => $list_item["uhc"],
                "with_img" => "",
                "editable" => ($uho === $list_item["uhc"]) ? 1 : 0,
                "fav" => (in_array($list_item["g_hash"], $bits)) ? "act" : ""
            );

            $uh = $list_item["uhc"];
            if (empty($uhcs[$list_item["uhc"]])) {
                $list[$i]["user"] = $uhcs[$list_item["uhc"]] = g::run("db.Get", array("g_alias", "persons", "((g_hash='$uh'))"))["list"][0]["g_alias"];
            } else {
                $list[$i]["user"] = $uhcs[$list_item["uhc"]];
            }
            $list_item["g_media"] = g::run("tools.JDD", $list_item["g_media"]);
            if (!empty($list_item["g_media"]["image"])) {
                $list[$i]["featured_img"] = $list_item["g_media"]["image"][0];
                $list[$i]["with_img"] = "with_img";
            }
        }
        $op_data[$key]["mode"] = "loop";
        $op_data[$key]["list"] = $list;
    }
    g::set("op.data", $op_data);
}

function CloneGetFavs()
{
    $uh = g::get("op.meta.user.hash");
    $epf = g::run("db.Get", array("g_bits", "events", "(g_hash='$uh' AND g_type='person_favs')"));
    if ($epf["total"] == 1) {
        $bits = g::run("tools.JD", $epf["list"][0]["g_bits"]);
        $vals = implode("','", $bits);
        $loop = g::run("db.Get", array("*", "items", "g_hash IN ('$vals')"));
        g::set("op.data.main", $loop);
    }
}

function StripeWebhook()
{
    // This is from the Stripe Dashboard
    // YOU NEED TO GET YOUR OWN STRIPE SECRET KEY FROM THEIR DASHBOARD
    $secretKey = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

    // Retrieve the request's body and parse it as JSON
    $input = @file_get_contents("php://input");
    $event_json = json_decode($input, true);
    // For extra security, you can verify the event by fetching it from Stripe
    $event_id = $event_json["id"];
    $url = "https://api.stripe.com/v1/events/" . $event_id;
    $headers = ["Authorization: Bearer $secretKey"];
    // send request
    $response = g::run("tools.LoadPathSafe", $url, "GET", $headers);
    // parse response
    $responseData = json_decode($response, true);
    error_log(g::run("tools.JE", $responseData));

    // Handle the event
    switch ($responseData["type"]) {
        case 'checkout.session.completed':
            $session = $responseData["data"]["object"]; // contains a Stripe Checkout Session
            // Then define and call a method to handle the successful checkout session.
            handleCheckoutSessionCompleted($session);
            break;
        default:
            http_response_code(400);
            exit();
    }

    http_response_code(200); // PHP 5.4 or greater
}

function handleCheckoutSessionCompleted($session)
{
    error_log(g::run("tools.JE", $session));
    // Use these to in the persons table, to update related user, 
    // add credits, whatever you like.
    /*
        $session["amount_total"];
        $session["currency"];
        $session["created"];
        $session["customer"];
        $session["email"];
        $session["name"];
        $session["payment_link"];
    */
}
