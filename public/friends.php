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

<body class="h-100 pt-5">
    <!-- Overlay DIV -->
    <div class="overlay fixed-top vw-100 vh-100 d-flex justify-content-center align-items-center">
        <img class="img-fluid" style="scale: 0.25;" src="assets/img/loading.gif" alt="#">
    </div>

    <!-- Navigation -->
    <?php
    define("_navigation.php", true);
    require("{$_SERVER['DOCUMENT_ROOT']}_navigation.php");
    ?>

    <!-- Page Content -->
    <div class="container d-flex mt-3 px-2" style="height: calc(100% - 1.6rem);">
        <div class="container col-lg-10 p-0 bg-light border border-2 border-primary-subtle shadow custom-border-radius">
            <!-- Title -->
            <div class="px-2 pt-3 d-flex flex-row justify-content-between">
                <h1 class="fw-bold m-0 pb-1 text-dark">Friends</h1>
                <h1 class="fw-bold m-0 pb-1 text-dark">
                    <a href="#"><i class="fa-solid fa-people-arrows text-info"></i></i></a>
                </h1>
            </div>

            <!-- Friends -->
            <div class="card-list overflow-auto custom-border-radius p-1" style="height: calc(100% - 4.9rem);">
                <table id="friendsTable" class="display">
                    <thead>
                        <tr>
                            <th colspan="2">Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <!-- Friends -->
        </div>
    </div>
</body>

</html>

<?php
// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

define('db_disconnect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------
?>