<?php

if (!defined("config.php")):

    die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
endif;

// --------------------------------------------------

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: text/html; charset=utf-8');

ini_set("default_charset", "UTF-8");
mb_internal_encoding("UTF-8");

date_default_timezone_set("UTC");
