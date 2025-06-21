<?php

session_start();

switch (true):
    case ($_SERVER['REQUEST_METHOD'] !== "POST"):
    case (!isset($_POST['mailTo'])):
    case (!isset($_POST['locationOrigin'])):
        die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
        break;
endswitch;

$input = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// --------------------------------------------------

header('Content-Type: text/html; charset=utf-8');
ini_set("default_charset", "UTF-8");
mb_internal_encoding("UTF-8");

date_default_timezone_set("UTC");

define("helper_functions.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}helpers/php/helper_functions.php");

// --------------------------------------------------


// BEGIN - REQUESTING INITIAL DEPENDENCIES ----------

define('parameters.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/parameters.php");

define('db_connect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/db_connect.php");

// END - REQUESTING INITIAL DEPENDENCIES ------------


// BEGIN - DB TRANSACTION ---------------------------

$sql =
    "SELECT 
        CONCAT_WS(' ', `users`.`firstname`, `users`.`lastname`) AS `full_name`
        
    FROM `users`
    WHERE `users`.`enabled` = '1'
    AND `users`.`email` = ?
    LIMIT 1";

// --------------------------------------------------

$output = [
    'success' => 0,
    'message' => 'Ooops! Something went wrong...',
    'data' => null
];

$stmt = $mysqli->prepare($sql);
if (!$stmt) die(json_encode($output));

$stmt->bind_param("s", $input['mailTo']);
if ($stmt->execute()):

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!(int)$result->num_rows) die(json_encode($output));
    // if (!(int)$result->num_rows) $row['full_name'] = "X";

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => [
            'fullName' => $row['full_name']
        ]
    ];
else: die(json_encode($output));
endif;

mysqli_stmt_close($stmt);

// END - DB TRANSACTION -----------------------------

$input = [
    'mailTo' => $input['mailTo'],
    'emailSubject' => "Welcome to Marked as Safe",
    'htmlBodyPath' => "{$_SERVER['DOCUMENT_ROOT']}helpers/html/welcome_mail.html",
    'replacements' => [
        "[FULL_NAME]" => $output['data']['fullName'],
        "[LOCATION_ORIGIN]" => $input['locationOrigin'],
    ]
];

$output = sendEmail(
    $input['mailTo'],
    $input['emailSubject'],
    $input['htmlBodyPath'],
    $input['replacements']
);

// --------------------------------------------------

die(json_encode($output));
