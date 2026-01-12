<?php
/**
 * Genes Framework Official Website
 * genes.one
 * 
 * Marketing site for both AI services and human developers
 * Demonstrates Genes building itself!
 * 
 * Created by Devrim Vardar
 * https://devrimvardar.com | @DevrimVardar
 */

require_once '../genes.php';

// Define views
g::def("clone", array(
    
    "Index" => function ($bits, $lang, $path) {
        echo g::run("tpl.renderView", "Index", array(
            "bits" => $bits,
            "lang" => $lang
        ));
    },
    
    "Docs" => function ($bits, $lang, $path) {
        echo g::run("tpl.renderView", "Docs", array(
            "bits" => $bits,
            "lang" => $lang
        ));
    },
    
    "Examples" => function ($bits, $lang, $path) {
        echo g::run("tpl.renderView", "Examples", array(
            "bits" => $bits,
            "lang" => $lang
        ));
    },
    
    "Download" => function ($bits, $lang, $path) {
        echo g::run("tpl.renderView", "Download", array(
            "bits" => $bits,
            "lang" => $lang
        ));
    }
    
));

g::run("route.handle");
