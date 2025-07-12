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
    case (!isset($_GET['id'])):
    case (!(int)$_GET['id']):
        die(http_response_code(404));
        break;
endswitch;

$input = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// END - INITIAL SECURITY CHECK ---------------------


// BEGIN - INITIAL CONFIG & DEPENDENCIES ------------

define("config.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/config.php");

define('load_env.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/load_env.php");

define('db_connect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/db_connect.php");

// END - INITIAL CONFIG & DEPENDENCIES --------------


// BEGIN - ADDITIONAL SECURITY CHECK ----------------

$sql =
    "SELECT * FROM `chats`
    
    WHERE `chats`.`id` = ?
    AND ? IN (`chats`.`sender`, `chats`.`recipient`)
    
    LIMIT 1";

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
    if (!$result->num_rows) die(http_response_code(404));

    $output = [
        'success' => 1,
        'message' => 'Success!',
        'data' => null
    ];
else: die(http_response_code(404));
endif;

mysqli_stmt_close($stmt);

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

    <link rel="icon" type="image/x-icon" href="/assets/img/favicon.ico">

    <!-- -------------------------------------------------- -->

    <?php
    define("_libs.php", true);
    require_once("{$_SERVER['DOCUMENT_ROOT']}/_libs.php");
    ?>

    <!-- -------------------------------------------------- -->

    <link rel="stylesheet" href="/helpers/css/custom.css">

    <script type="text/javascript">
        localStorage.setItem("authToken", "<?php echo $_SESSION['authToken'] ?>");
        const input = JSON.stringify(<?php echo json_encode($input); ?>);
        const id = <?php echo (int)$input['id']; ?>;
    </script>
    <script type="text/javascript" src="/helpers/js/helper_functions.js"></script>
    <script type="text/javascript" src="/modules/chat/view.js"></script>
    <script type="text/javascript" src="/modules/chat/page/js/body.js"></script>
</head>

<?php
require_once("{$_SERVER['DOCUMENT_ROOT']}/modules/chat/page/body.php");
?>

</html>