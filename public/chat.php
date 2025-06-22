<?php

// BEGIN - INITIAL SECURITY CHECK -------------------

session_start();

define("helper_functions.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}helpers/php/helper_functions.php");

switch (true):
    case (!isset($_SESSION['authenticated'])):
    case (isset($_SESSION['authenticated']) && !$_SESSION['authenticated']):

        die(header("Location: {$_SESSION['LOCATION_ORIGIN']}/login.php"));
        break;

    case ($_SERVER['REQUEST_METHOD'] !== "GET"):
    case (!(int)$_GET['id']):

        die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
        break;
endswitch;

$input = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// END - INITIAL SECURITY CHECK ---------------------


// BEGIN - INITIAL CONFIG & DEPENDENCIES ------------

define("config.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/config.php");

define('load_env.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}config/load_env.php");

define('db_connect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}config/db_connect.php");

// END - INITIAL CONFIG & DEPENDENCIES --------------


// BEGIN - ADDITIONAL SECURITY CHECK ----------------

$sql =
    "SELECT 
        COUNT(*) AS `count`
    
    FROM `chats`
    
    WHERE `chats`.`id` = ?
    AND ? IN (`chats`.`sender`, `chats`.`recipient`)";

// --------------------------------------------------

$params = [
    $input['id'],
    $_SESSION['id']
];
$types = str_repeat("i", sizeof($params));

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()):

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

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

    die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
endif;

mysqli_stmt_close($stmt);

// --------------------------------------------------

if (!$output['data']['count']):

    die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
endif;

// END - ADDITIONAL SECURITY CHECK ------------------

?>

<!DOCTYPE html>
<html class="h-100" lang="en">

<head>
    <meta name="theme-color" content="#ea931a">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Document</title>

    <link rel="icon" type="image/x-icon" href="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/assets/img/favicon.ico"; ?>">

    <!-- -------------------------------------------------- -->

    <?php
    define("_libs.php", true);
    require_once("{$_SERVER['DOCUMENT_ROOT']}_libs.php");
    ?>

    <!-- -------------------------------------------------- -->

    <link rel="stylesheet" href="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/helpers/css/custom.css"; ?>">

    <script type="text/javascript">
        localStorage.setItem("authToken", "<?php echo $_SESSION['authToken'] ?>");
        const input = JSON.stringify(<?php echo json_encode($input); ?>);
        const id = <?php echo (int)$input['id']; ?>;
    </script>
    <script type="text/javascript" src="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/helpers/js/helper_functions.js"; ?>"></script>
    <script type="text/javascript" src="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/modules/chat/view.js"; ?>"></script>
    <script type="text/javascript" src="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/modules/chat/page/js/body.js"; ?>"></script>
</head>

<?php
require_once("{$_SERVER['DOCUMENT_ROOT']}modules/chat/page/body.php");
?>

</html>