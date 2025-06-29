<?php

// BEGIN - INITIAL CONFIG ---------------------------

session_start();

header('Content-Type: text/html; charset=utf-8');
ini_set("default_charset", "UTF-8");
mb_internal_encoding("UTF-8");

date_default_timezone_set("UTC");

define("helper_functions.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}helpers/php/helper_functions.php");

// END - INITIAL CONFIG -----------------------------


// BEGIN - INITIAL SECURITY SCREEN ------------------

switch (true):
    case (!isset($_SESSION['authenticated'])):
    case (isset($_SESSION['authenticated']) && !$_SESSION['authenticated']):
    case ($_SERVER['REQUEST_METHOD'] !== "POST"):
    case (!isset($_POST['userId'])):
        die(http_response_code(404));
        break;
endswitch;

// END - INITIAL SECURITY SCREEN --------------------


// BEGIN - FILTER INPUT -----------------------------

$input = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$input['user'] = (int)$_SESSION['id'];
$input['userId'] = (int)$input['userId'];

// END - FILTER INPUT -------------------------------


// BEGIN - REQUESTING INITIAL DEPENDENCIES ----------

define('load_env.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/load_env.php");

define('db_connect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/db_connect.php");

// END - REQUESTING INITIAL DEPENDENCIES ------------


// BEGIN - REVOKE FRIEND REQUEST --------------------

$sql =
    "UPDATE `friends`
    INNER JOIN `users` ON (`users`.`id` = `friends`.`sender` AND
                            `users`.`enabled` = 1)
    INNER JOIN `users` AS `a_users` ON (`a_users`.`id` = `friends`.`recipient` AND
                                        `a_users`.`enabled` = 1)

    SET `friends`.`enabled` = 0

    WHERE `friends`.`enabled` = 1
    AND `friends`.`accepted` = 1
    AND ? IN (`friends`.`sender`, `friends`.`recipient`)
    AND ? IN (`friends`.`sender`, `friends`.`recipient`)

    ORDER BY `friends`.`id` DESC
    LIMIT 1";

// --------------------------------------------------

$output = [
    'success' => 0,
    'message' => 'Ooops! Something went wrong...',
    'data' => null
];

// --------------------------------------------------

$params = [
    $input['user'],
    $input['userId'],
];
$types = str_repeat("i", sizeof($params));

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()):
    if (!(int)$stmt->affected_rows) die(json_encode($output));

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => null
    ];
else: die(json_encode($output));
endif;

mysqli_stmt_close($stmt);

// END - REVOKE FRIEND REQUEST ----------------------


// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

define('db_disconnect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------


die(json_encode($output));
