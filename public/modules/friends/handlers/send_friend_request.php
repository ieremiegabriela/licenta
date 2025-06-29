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


// BEGIN - CHECK FRIEND REQUEST ELIGIBILITY ---------

$sql =
    "SELECT
        `users`.`id`
    
    FROM `users`
    LEFT JOIN `friends` ON (`users`.`id` IN (`friends`.`sender`, `friends`.`recipient`) AND
                            ? IN (`friends`.`sender`, `friends`.`recipient`) AND
                            `friends`.`enabled` = 1)
    
    WHERE (`friends`.`sender` <=> NULL OR `friends`.`recipient` <=> NULL)
    AND `users`.`enabled` = 1";

// --------------------------------------------------

$output = [
    'success' => 0,
    'message' => 'Ooops! Something went wrong...',
    'data' => null
];

// --------------------------------------------------

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $input['user']);

if ($stmt->execute()):

    $result = $stmt->get_result();

    $temp = [];
    while ($row = $result->fetch_assoc()):
        $temp[] = (int)$row['id'];
    endwhile;

    if (!in_array($input['userId'], $temp, true)) die(json_encode($output));

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => null
    ];

    unset($temp);
else: die(json_encode($output));
endif;

mysqli_stmt_close($stmt);

// END - CHECK FRIEND REQUEST ELIGIBILITY -----------


// BEGIN - RECORD FRIEND REQUEST --------------------

$sql =
    "INSERT INTO `friends`
    (
        `friends`.`sender`,
        `friends`.`recipient`,
        `friends`.`accepted`,
        `friends`.`added_on`,
        `friends`.`enabled`
    )
    
    VALUES
    (
        ?,
        ?,
        0,
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
    $input['user'],
    $input['userId']
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

// END - RECORD FRIEND REQUEST ----------------------


// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

define('db_disconnect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------


die(json_encode($output));
