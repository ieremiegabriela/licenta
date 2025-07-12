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
        die(http_response_code(404));
        break;
endswitch;

// END - INITIAL SECURITY SCREEN --------------------


// BEGIN - FILTER INPUT -----------------------------

$input = (array)json_decode(file_get_contents("php://input"), true);
$input = filter_var_array($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// END - FILTER INPUT -------------------------------


// BEGIN - ADDITIONAL SECURITY SCREEN ---------------

switch (true):
    case (!isset($input['id'])):
    case (!isset($input['message'])):
        die(http_response_code(404));
        break;
endswitch;

$input['sender'] = (int)$_SESSION['id'];
$input['id'] = (int)$input['id'];

// END - ADDITIONAL SECURITY SCREEN -----------------


// BEGIN - REQUESTING INITIAL DEPENDENCIES ----------

define('load_env.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/load_env.php");

define('db_connect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/db_connect.php");

// END - REQUESTING INITIAL DEPENDENCIES ------------


// BEGIN - IDENTIFY CORRESPONDENT -------------------

$sql =
    "SELECT
        IF(`chats`.`sender` = ?, `chats`.`recipient`, `chats`.`sender`) AS `correspondent`

    FROM `chats`
    WHERE `chats`.`id` = ?
    AND ? IN (`chats`.`sender`, `chats`.`recipient`)";

// --------------------------------------------------

$params = [
    $input['sender'],
    $input['id'],
    $input['sender'],
];
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("iii", ...$params);

if ($stmt->execute()):

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $input['recipient'] = $row['correspondent'];

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => $row
    ];
else:

    $output = [
        'success' => 0,
        'message' => 'Ooops! Something went wrong...',
        'data' => null
    ];
endif;

mysqli_stmt_close($stmt);

// END - IDENTIFY CORRESPONDENT ---------------------


// BEGIN - INSERT MESSAGE ---------------------------

$sql =
    "INSERT INTO `chat_{$input['id']}`
    (
        `chat_{$input['id']}`.`sender`,
        `chat_{$input['id']}`.`recipient`,
        `chat_{$input['id']}`.`message`,
        `chat_{$input['id']}`.`seen`,
        `chat_{$input['id']}`.`added_on`,
        `chat_{$input['id']}`.`enabled`
    )
    
    VALUES
    (
        ?,
        ?,
        ?,
        0,
        CURRENT_TIMESTAMP(),
        1
    )";

// --------------------------------------------------

$params = [
    $input['sender'],
    $input['recipient'],
    $input['message'],
];
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("iis", ...$params);

if ($stmt->execute()):

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => [
            'insertedRows' => mysqli_affected_rows($mysqli)
        ]
    ];
else:

    $output = [
        'success' => 0,
        'message' => 'Ooops! Something went wrong...',
        'data' => null
    ];
endif;

mysqli_stmt_close($stmt);

// END - INSERT MESSAGE -----------------------------


// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

define('db_disconnect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------

die(json_encode($output));
