<?php

// BEGIN - INITIAL SECURITY CHECK -------------------

session_start();

define("helper_functions.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/helpers/php/helper_functions.php");

switch (true):
    case (!isset($_SESSION['authenticated'])):
    case (isset($_SESSION['authenticated']) && !$_SESSION['authenticated']):
        die(header("Location: /login.php"));
        break;
    case ($_SERVER['REQUEST_METHOD'] !== "GET"):
    case (!isset($_GET['userId'])):
    case (!(int)$_GET['userId']):
        die(http_response_code(404));
        break;
endswitch;

$input = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$input['user'] = (int)$_SESSION['id'];

// END - INITIAL SECURITY CHECK ---------------------


// BEGIN - INITIAL CONFIG & DEPENDENCIES ------------

define("config.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/config.php");

define('load_env.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/load_env.php");

define('db_connect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/db_connect.php");

// END - INITIAL CONFIG & DEPENDENCIES --------------


// BEGIN - AUTHORIZE ACTION -------------------------

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "{$_SESSION['DOCKER_ORIGIN']}/modules/messenger/handlers/authorize_action.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'userId' => $input['userId'],
    'action' => "send-message"
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID={$_COOKIE['PHPSESSID']}; path=/");
session_write_close();

// Capture the response
$response = curl_exec($ch);
curl_close($ch);

$jsonObj = (array)json_decode($response, true);
if (!$jsonObj['success']) die(http_response_code(404));

// END - AUTHORIZE ACTION ---------------------------


// BEGIN - CHECK IF CONVERSATION EXISTS -------------

$sql =
    "SELECT `chats`.`id` FROM `chats`

    WHERE `chats`.`enabled`
    AND ? IN (`chats`.`sender`, `chats`.`recipient`)
    AND ? IN (`chats`.`sender`, `chats`.`recipient`)
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
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $temp = ['chatId' => null];
    if ($result->num_rows) $temp = ['chatId' => (int)$row['id']];

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => $temp
    ];
    unset($temp);
else: die(http_response_code(404));
endif;

mysqli_stmt_close($stmt);

// END - CHECK IF CONVERSATION EXISTS ---------------


// BEGIN - CREATE CHAT RECORD IF REQUIRED -----------

if (!$output['data']['chatId']):
    $sql =
        "INSERT INTO `chats`
        (
            `chats`.`sender`,
            `chats`.`recipient`,
            `chats`.`added_on`,
            `chats`.`enabled`
        )
        
        VALUES
        (
            ?,
            ?,
            CURRENT_TIMESTAMP(),
            1
        )";

    // ----------------------------------------------

    $params = [
        $input['user'],
        $input['userId'],
    ];
    $types = str_repeat("i", sizeof($params));
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()):
        if (!$stmt->affected_rows):
            die(http_response_code(404));
        endif;
    else: die(http_response_code(404));
    endif;

    mysqli_stmt_close($stmt);
endif;

// END - CREATE CHAT RECORD IF REQUIRED -------------


// BEGIN - CREATE CHAT TABLE IF REQUIRED ------------

if (!$output['data']['chatId'] && $mysqli->insert_id):
    $output['data']['chatId'] = $mysqli->insert_id;

    $sql =
        "CREATE TABLE `chat_{$output['data']['chatId']}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `sender` int(11) NOT NULL,
            `recipient` int(11) NOT NULL,
            `message` text NOT NULL,
            `seen` bit(1) NOT NULL DEFAULT b'0',
            `added_on` timestamp NOT NULL DEFAULT current_timestamp(),
            `enabled` bit(1) NOT NULL DEFAULT b'0',
            PRIMARY KEY (`id`)
        )";

    // ----------------------------------------------

    if (!$mysqli->query($sql)):
        die(http_response_code(404));
    endif;
endif;

// END - CREATE CHAT RECORD IF REQUIRED -------------


die(header("Location: /chat.php?id={$output['data']['chatId']}"));
