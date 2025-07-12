<?php

// BEGIN - REQUESTING INITIAL DEPENDENCIES ----------

if (!defined('load_env.php')) define('load_env.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/load_env.php");

if (!defined('db_connect.php')) define('db_connect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/db_connect.php");

// END - REQUESTING INITIAL DEPENDENCIES ------------

?>

<body class="h-100 pt-5">
    <!-- Overlay DIV -->
    <div class="overlay fixed-top vw-100 vh-100 d-flex justify-content-center align-items-center">
        <img class="img-fluid" style="scale: 0.25;" src="/assets/img/loading.gif" alt="#">
    </div>

    <!-- Navigation -->
    <?php
    if (!defined("_navigation.php")) define("_navigation.php", true);
    require("{$_SERVER['DOCUMENT_ROOT']}/_navigation.php");
    ?>

    <!-- Control Section -->
    <div class="container d-flex mt-3 px-2" style="height: calc(100% - 1.6rem);">
        <div class="container col-lg-10 p-2 px-0 bg-light border border-2 border-primary-subtle shadow custom-border-radius">
            <!-- Stats & Controls -->
            <div class="card-list overflow-auto custom-border-radius p-1 h-100 d-flex justify-content-center">
                <div class="position-relative d-flex flex-column h-100">
                    <div class="d-flex flex-row justify-content-center">
                        <div class="row w-100">
                            <!-- Main Control Column -->
                            <div class="col-md-4 mb-2">
                                <!-- Main Action Buttons -->
                                <div class="d-grid gap-2">
                                    <button class="btn btn-safe btn-lg py-2 custom-border-radius">
                                        <i class="fa-solid fa-shield-halved"></i> I'm Safe
                                    </button>
                                    <button class="btn btn-unsafe btn-lg py-2 custom-border-radius">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Need Help
                                    </button>
                                    <!-- Stat Card 2 -->
                                    <div class="status-card card border-warning border-top-0 border-end-0 border-bottom-0 border-4">
                                        <div class="card-body text-center">
                                            <p class="mb-0"><strong>Last marked:</strong> <span id="lastMarkTime">12/07/2023 14:30</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- General Stats Column -->
                            <div class="col-md-8">
                                <div class="row g-2">
                                    <!-- Stat Card 1 -->
                                    <div class="col-md-4">
                                        <div class="status-card card h-100 border-success border-top-0 border-end-0 border-bottom-0 border-4">
                                            <div class="card-body text-center px-2">
                                                <h2 class="text-success">248</h2>
                                                <p class="mb-0">Marked as Safe in last 24 hours</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stat Card 2 -->
                                    <div class="col-md-4">
                                        <div class="status-card card h-100 border-warning border-top-0 border-end-0 border-bottom-0 border-4">
                                            <div class="card-body text-center px-2">
                                                <h2 class="text-warning">12</h2>
                                                <p class="mb-0">Requests for help today</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stat Card 3 -->
                                    <div class="col-md-4">
                                        <div class="status-card card h-100 border-primary border-top-0 border-end-0 border-bottom-0 border-4">
                                            <div class="card-body text-center px-2">
                                                <h2 class="text-primary">1,842</h2>
                                                <p class="mb-0">Total safe marks this week</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-grow-1 align-items-center justify-content-center">
                        <img class="img-fluid opacity-55" src="/assets/img/logo.png" alt="#" style="scale: 0.5;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<?php
// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

if (!defined('db_disconnect.php')) define('db_disconnect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------
?>