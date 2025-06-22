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


// BEGIN - IDENTIFY CORRESPONDENT -------------------

$sql =
    "SELECT 
        `pseudo`.`status`,
        `pseudo`.`correspondent`,
        `pseudo`.`status_classes`,
        `pseudo`.`timestamp`,
        CONCAT_WS(' ', `users`.`firstname`, `users`.`lastname`) AS `name`,
        CASE
            WHEN `pseudo`.`status` = 'Accepted' THEN 'Remove'
            WHEN `pseudo`.`sender` = ? AND `pseudo`.`status` = 'Pending' THEN 'Revoke'
            WHEN `pseudo`.`recipient` = ? AND `pseudo`.`status` = 'Pending' THEN 'Accept'
        END AS `actions`,
        CASE
            WHEN `pseudo`.`status` = 'Accepted' THEN 'remove fa-solid fa-user-minus text-dark'
            WHEN `pseudo`.`sender` = ? AND `pseudo`.`status` = 'Pending' THEN 'revoke fa-solid fa-xmark text-dark'
            WHEN `pseudo`.`recipient` = ? AND `pseudo`.`status` = 'Pending' THEN 'accept fa-solid fa-check text-dark'
        END AS `actions_classes`
        
    FROM (
        SELECT
            `friends`.`sender`,
            `friends`.`recipient`,
            UNIX_TIMESTAMP(`friends`.`added_on`) AS `timestamp`,
            IF(`friends`.`accepted` = 1, 'Accepted', 'Pending') AS `status`,
            IF(`friends`.`accepted` = 1, 'fa-regular fa-circle-check', 'fa-regular fa-clock') AS `status_classes`,
            IF(`friends`.`sender` = ?, `friends`.`recipient`, `friends`.`sender`) AS `correspondent`

        FROM `friends`
        WHERE `friends`.`enabled` = 1
        AND ? IN (`friends`.`sender`, `friends`.`recipient`)) AS `pseudo`
    LEFT JOIN `users` ON `users`.`id` = `pseudo`.`correspondent`";

// --------------------------------------------------

$params = array_fill(0, 6, $_SESSION['id']);
$type = str_repeat('i', sizeof($params));
$stmt = $mysqli->prepare($sql);
$stmt->bind_param($type, ...$params);

if ($stmt->execute()):

    $result = $stmt->get_result();

    $temp = [];
    $count = 0;
    while ($row = $result->fetch_assoc()):

        $wClass = $row['status'] === "Accepted" ? "w-25" : "w-50";

        $temp[] = [
            'picture' => "<img class=\"me-1\" src=\"assets/img/user.png\" alt=\"#\" height=\"40\">",
            'name' => "<span>{$row['name']}</span>",
            'status' => "<span class=\"me-1\">{$row['status']}</span><i class=\"{$row['status_classes']}\"></i>",
            'actions' => "<button class=\"$wClass m-1 mybtn bg-white border border-2 border-primary-subtle\" data-bs-toggle=\"tooltip\" data-bs-title=\"{$row['actions']}\"><i class=\"{$row['actions_classes']}\"></i></button>",
            'timestamp' => (int)$row['timestamp']
        ];

        $temp[$count]['actions'] .= $row['status'] === "Accepted" ? "<button class=\"w-25 m-1 mybtn bg-white border border-2 border-primary-subtle\" data-bs-toggle=\"tooltip\" data-bs-title=\"Send Message\"><i class=\"fa-solid fa-comment text-dark\"></i></button>" : (string)null;

        $count++;
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

// END - IDENTIFY CORRESPONDENT ---------------------


// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

define('db_disconnect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------

die(json_encode($output));
