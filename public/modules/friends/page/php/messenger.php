<?php

// BEGIN - INITIAL CONFIG ---------------------------

session_start();

header('Content-Type: text/html; charset=utf-8');
ini_set("default_charset", "UTF-8");
mb_internal_encoding("UTF-8");

date_default_timezone_set("UTC");

// END - INITIAL CONFIG -----------------------------


// BEGIN - INITIAL SECURITY SCREEN ------------------

switch (true):

    case ($_SERVER['REQUEST_METHOD'] !== "GET"):

        die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));

        break;
endswitch;

// END - INITIAL SECURITY SCREEN --------------------


// BEGIN - REQUESTING INITIAL DEPENDENCIES ----------

define('load_env.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/load_env.php");

define('db_connect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/db_connect.php");

// END - REQUESTING INITIAL DEPENDENCIES ------------


// BEGIN - RETRIEVE PERSONAL CHATS ------------------

$sql =
    "SELECT
        `chats`.`id`,
        `chats`.`sender`,
        `chats`.`recipient`,
        @correspondent:= 
        IF(`chats`.`sender` = ?, `chats`.`recipient`, `chats`.`sender`) AS `correspondent`,

        CONCAT_WS(' ', `users`.`firstname`, `users`.`lastname`) AS `correspondent_fullname`
    
    FROM `chats`
    LEFT JOIN `users` ON (`users`.`id` = IF(`chats`.`sender` = ?, `chats`.`recipient`, `chats`.`sender`) AND `users`.`enabled` = 1)
    
    WHERE (`chats`.`sender` = ? OR `chats`.`recipient` = ?) AND `chats`.`enabled` = 1";

// --------------------------------------------------

$stmt = $mysqli->prepare($sql);
$stmt->bind_param(
    "ssss",
    $_SESSION['id'],
    $_SESSION['id'],
    $_SESSION['id'],
    $_SESSION['id'],
);

if ($stmt->execute()):

    $result = $stmt->get_result();
    $temp = [];

    while ($row = $result->fetch_assoc()):

        $temp[] = $row;
    endwhile;

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => $temp
    ];

    unset($temp);
else:

    $output = [
        'success' => 0,
        'message' => 'Ooops! Something went wrong...',
        'data' => null
    ];
endif;

mysqli_stmt_close($stmt);

// END - RETRIEVE PERSONAL CHATS --------------------


// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

define('db_disconnect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------

echo json_encode($output);
