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
    case (!isset($_POST['searchedVal'])):
    case (!isset($_POST['limitOne'])):
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
require_once("{$_SERVER['DOCUMENT_ROOT']}config/load_env.php");

define('db_connect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/db_connect.php");

// END - REQUESTING INITIAL DEPENDENCIES ------------


// BEGIN - USER SEARCH ------------------------------

$sql = [];
$sql[] =
    "SELECT `pseudo`.`correspondent` AS `id`,
        CONCAT_WS(' ', `users`.`firstname`, `users`.`lastname`) AS `user_fullname`
    FROM
        (SELECT IF(`friends`.`sender` = ?, `friends`.`recipient`, `friends`.`sender`) AS `correspondent`
        FROM `friends`
        WHERE `friends`.`enabled` = 1
            AND `friends`.`accepted` = 1
            AND ? IN (`friends`.`sender`,
                    `friends`.`recipient`)) AS `pseudo`
    INNER JOIN `users` ON (`users`.`id` = `pseudo`.`correspondent`
                        AND `users`.`enabled` = 1)
    HAVING `user_fullname` LIKE ?
    ORDER BY `user_fullname` ASC";

$sql[] = (int)$input['limitOne'] ? "LIMIT 1" : (string)null;
$sql = implode(chr(32), $sql);

// --------------------------------------------------

$params = array_fill(0, 2, $input['user']);
$types = str_repeat("i", sizeof($params));

$params[] = "%{$input['searchedVal']}%";
$types .= "s";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()):

    $result = $stmt->get_result();

    $temp = [];
    while ($row = $result->fetch_assoc()):
        $temp[] =
            "<div class=\"card border-0 my-1\">
                <div class=\"card border-2 text-reset z-0 shadow-sm custom-border-radius\">
                    <div class=\"card-body py-1\">
                        <div class=\"row\">
                            <div class=\"d-flex justify-content-between px-0 align-items-center\">
                                <div style=\"width: 70px;\"><img src=\"assets/img/user.png\" alt=\"#\" class=\"img-fluid\"></div>
                                <h5 class=\"ms-2 me-auto mb-0\">{$row['user_fullname']}</h5>
                                <button class=\"w-25 h-75 m-2 mybtn bg-white border border-2 border-primary-subtle message-friend-btn\" data-bs-toggle=\"tooltip\" data-bs-title=\"Send Message\" data-id=\"{$row['id']}\"><i class=\"fa-solid fa-comment text-dark scale-plus-25\"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
    endwhile;

    // No results
    if ($result->num_rows === 0):
        $temp[] =
            "<div class=\"card border-0 my-1\" data-id=\"1\">
                <div class=\"card border-2 text-reset z-0 shadow-sm custom-border-radius\">
                    <div class=\"card-body py-1\">
                        <div class=\"row\">
                            <div class=\"text-center\">
                                <h5 class=\"ms-2 me-auto mb-0\" style=\"font-style: italic;\">No results...</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
    endif;

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => implode((string)null, $temp)
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

// END - USER SEARCH --------------------------------


// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

define('db_disconnect.php', true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------

die(json_encode($output));
