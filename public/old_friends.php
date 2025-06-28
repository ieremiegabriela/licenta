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
        die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
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
    </script>
    <script type="text/javascript" src="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/helpers/js/helper_functions.js"; ?>"></script>
    <script type="text/javascript" src="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/modules/friends/view.js"; ?>"></script>
</head>

<?php
// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

define('db_disconnect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------
?>