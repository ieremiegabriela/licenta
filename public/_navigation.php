<?php

// BEGIN - INITIAL SECURITY SCREEN ------------------

if (!defined('_navigation.php')):

    die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
endif;

// END - INITIAL SECURITY SCREEN --------------------

?>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top custom-rounded-bottom shadow">
    <div class="container-fluid">
        <a class="navbar-brand d-flex justify-content-center align-items center" href="#">
            <img class="nav-logo img-fluid mx-1" src="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/assets/img/logo-inverted.png"; ?>" alt="#">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a id="home" class="nav-link" aria-current="page" href="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/index.php"; ?>"><i class="fa-solid fa-house"></i>&nbsp;Home</a>
                </li>
                <li class="nav-item">
                    <a id="friends" class="nav-link" aria-current="page" href="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/friends.php"; ?>"><i class="fa-solid fa-user-group"></i>&nbsp;Friends</a>
                </li>
                <li class="nav-item">
                    <a id="messages" class="nav-link" aria-current="page" href="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/messenger.php"; ?>"><i class="fa-solid fa-envelope"></i>&nbsp;Messages</a>
                </li>
                <li class="nav-item">
                    <a id="settings" class="nav-link" aria-current="page" href="#"><i class="fa-solid fa-gear"></i>&nbsp;Settings</a>
                </li>
                <li class="nav-item">
                    <a id="logout" class="nav-link" aria-current="page" href="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/modules/login/handlers/logout.php"; ?>"><i class="fa-solid fa-arrow-right-from-bracket"></i>&nbsp;Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>