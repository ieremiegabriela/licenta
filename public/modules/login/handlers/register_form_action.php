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

    case ($_SERVER['REQUEST_METHOD'] !== "POST"):
    case (!isset($_POST['firstnameRegister'])):
    case (!isset($_POST['lastnameRegister'])):
    case (!isset($_POST['emailRegister'])):
    case (!isset($_POST['passwordRegister'])):

        die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));

        break;
endswitch;

$input = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// END - INITIAL SECURITY SCREEN --------------------


// BEGIN - REQUESTING INITIAL DEPENDENCIES ----------

define('parameters.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/parameters.php");

define('db_connect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/db_connect.php");

// END - REQUESTING INITIAL DEPENDENCIES ------------


// BEGIN - CHECK THE UNIQUENESS OF THE EMAIL --------

$sql =
    "INSERT INTO `users`
    (
        `users`.`firstname`,
        `users`.`lastname`,
        `users`.`email`,
        `users`.`password`,
        `users`.`enabled`
    )

    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        1
    )";

// --------------------------------------------------

$params = [
    $input['firstnameRegister'],
    $input['lastnameRegister'],
    $input['emailRegister'],
    password_hash($input['passwordRegister'], PASSWORD_DEFAULT)
];
$types = str_repeat("s", sizeof($params));

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);

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

// END - CHECK THE UNIQUENESS OF THE EMAIL ----------


// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

define('db_disconnect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------

die(header("Location: /login.php"));
