<?php

session_start();

switch (true):
    case ($_SERVER['REQUEST_METHOD'] !== "POST"):

        die(http_response_code(404));
endswitch;

// --------------------------------------------------

header('Content-Type: text/html; charset=utf-8');
ini_set("default_charset", "UTF-8");
mb_internal_encoding("UTF-8");

date_default_timezone_set("UTC");

define("helper_functions.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}helpers/php/helper_functions.php");

// --------------------------------------------------

$_SESSION['authToken'] = createAuthToken();

$output = [
    'success' => 1,
    'message' => 'Success!',
    'data' => $_SESSION['authToken']
];

die(json_encode($output));
