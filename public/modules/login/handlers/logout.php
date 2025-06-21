<?php

// BEGIN - INITIAL CONFIG ---------------------------

session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: text/html; charset=utf-8');

ini_set("default_charset", "UTF-8");
mb_internal_encoding("UTF-8");

date_default_timezone_set("UTC");

define("helper_functions.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}helpers/php/helper_functions.php");

// END - INITIAL CONFIG -----------------------------

$locationOrigin = $_SESSION['LOCATION_ORIGIN'];

setcookie(session_name(), "", time() - 3600, "/");
session_unset();
session_destroy();
session_write_close();

die(header("Location: $locationOrigin/login.php"));
