<?php
require "../../api/genes.php";

g::def(
    "clone",
    array(
        "Index" => function () {
            g::run("ui.LoadViewHtml");
        }
    )
);
g::run("core.Render");
