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
        die(http_response_code(404));
        break;
endswitch;

// END - INITIAL SECURITY CHECK ---------------------


// BEGIN - INITIAL CONFIG & DEPENDENCIES ------------

define("config.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}config/config.php");

define('load_env.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}config/load_env.php");

define('db_connect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}config/db_connect.php");

// END - INITIAL CONFIG & DEPENDENCIES --------------

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
    require_once("{$_SERVER['DOCUMENT_ROOT']}_libs.php");
    ?>

    <!-- -------------------------------------------------- -->

    <link rel="stylesheet" href="/helpers/css/custom.css">

    <script type="text/javascript">
        localStorage.setItem("authToken", "<?php echo $_SESSION['authToken'] ?>");
        const input = null;
    </script>
    <script type="text/javascript" src="/helpers/js/helper_functions.js"></script>
    <script type="text/javascript" src="/modules/friends/view.js"></script>
    <script type="text/javascript" src="/modules/friends/page/js/body.js"></script>
</head>

<?php
require_once("{$_SERVER['DOCUMENT_ROOT']}modules/friends/page/body.php");
?>

</html>