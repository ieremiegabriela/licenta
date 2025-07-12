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
    case (!isset($_POST['emailLogin'])):
    case (!isset($_POST['passwordLogin'])):

        die(http_response_code(404));

        break;
endswitch;

$input = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// END - INITIAL SECURITY SCREEN --------------------


// BEGIN - REQUESTING INITIAL DEPENDENCIES ----------

define('load_env.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/load_env.php");

define('db_connect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/db_connect.php");

// END - REQUESTING INITIAL DEPENDENCIES ------------


// BEGIN - CHECK THE UNIQUENESS OF THE EMAIL --------

$sql =
    "SELECT 
        `users`.`id`,
        `users`.`password`
    
    FROM `users`
    
    WHERE `users`.`email` = ? AND `users`.`enabled` = 1
    LIMIT 1";

// --------------------------------------------------

$stmt = $mysqli->prepare($sql);
$stmt->bind_param(
    "s",
    $input['emailLogin']
);

if ($stmt->execute()):

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => null
    ];

    if ((int)mysqli_num_rows($result)):
        $output['data'] = [
            'authenticated' => password_verify($input['passwordLogin'], $row['password']),
            'id' => password_verify($input['passwordLogin'], $row['password']) ? $row['id'] : null
        ];
    endif;
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


// BEGIN - PARSE THE OUTPUT -------------------------

switch (true):

    case ($output['success'] === 0):
    case (!$output['data']):
    case (!$output['data']['authenticated']):

        $_SESSION['mismatchedCredentials'] = true;

        die(header("Location: /login.php"));

        break;

    case ($output['data']['authenticated']):

        $_SESSION['authenticated'] = (int)$output['data']['authenticated'];
        $_SESSION['id'] = $output['data']['id'];
        $_SESSION['authToken'] = createAuthToken();

        die(header("Location: /index.php"));

        break;
endswitch;

// END - PARSE THE OUTPUT ---------------------------
