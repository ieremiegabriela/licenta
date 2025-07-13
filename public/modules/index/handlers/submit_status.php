<?php

// BEGIN - INITIAL CONFIG ---------------------------

session_start();

header('Content-Type: text/html; charset=utf-8');
ini_set("default_charset", "UTF-8");
mb_internal_encoding("UTF-8");

date_default_timezone_set("UTC");

define("helper_functions.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/helpers/php/helper_functions.php");

// END - INITIAL CONFIG -----------------------------


// BEGIN - INITIAL SECURITY SCREEN ------------------

switch (true):
    case (!isset($_SESSION['authenticated'])):
    case (isset($_SESSION['authenticated']) && !$_SESSION['authenticated']):
    case ($_SERVER['REQUEST_METHOD'] !== "POST"):
    case (!isset($_POST['type'])):
    case (!in_array($_POST['type'], ['safe', 'danger'], true)):
        die(http_response_code(404));
        break;
endswitch;

// END - INITIAL SECURITY SCREEN --------------------


// BEGIN - FILTER INPUT -----------------------------

$input = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$input['user'] = (int)$_SESSION['id'];

// END - FILTER INPUT -------------------------------


// BEGIN - REQUESTING INITIAL DEPENDENCIES ----------

define('load_env.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/load_env.php");

define('db_connect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/db_connect.php");

// END - REQUESTING INITIAL DEPENDENCIES ------------


// BEGIN - SUBMIT STATUS ----------------------------

$sql =
    "INSERT INTO `status`
    (
        `status`.`type`,
        `status`.`added_by`,
        `status`.`added_on`,
        `status`.`enabled`
    )

    VALUES
    (
        ?,
        ?,
        CURRENT_TIMESTAMP(),
        1
    )";

// --------------------------------------------------

$output = [
    'success' => 0,
    'message' => 'Ooops! Something went wrong...',
    'data' => null
];

// --------------------------------------------------

$params = [
    $input['type'],
    $input['user']
];
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("si", ...$params);

if ($stmt->execute()):
    if (!$mysqli->affected_rows) die(json_encode($output));

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => null
    ];
else: die(json_encode($output));
endif;

mysqli_stmt_close($stmt);

// END - SUBMIT STATUS ------------------------------


// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

define('db_disconnect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------


die(json_encode($output));
