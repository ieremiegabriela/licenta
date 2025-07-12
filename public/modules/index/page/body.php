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
    <div class="container d-flex mt-3 px-2" style="height: calc(27% - 1.6rem);">
        <div class="container col-lg-10 p-2 px-0 bg-light border border-2 border-primary-subtle shadow custom-border-radius">
            <!-- Feed -->
            <div class="card-list overflow-auto custom-border-radius p-1 h-100 d-flex justify-content-center">
                <div class="row w-100">
                    <!-- Main Control Column -->
                    <div class="col-md-6 mb-4">
                        <!-- Main Action Buttons -->
                        <div class="d-grid gap-3">
                            <button class="btn btn-safe py-2 custom-border-radius">
                                <i class="bi bi-shield-check"></i> I'm Safe
                                <small class="d-block mt-1">Mark yourself as safe and well</small>
                            </button>
                            <button class="btn btn-unsafe py-2 custom-border-radius">
                                <i class="bi bi-exclamation-triangle"></i> Need Help
                                <small class="d-block mt-1">Alert others if you're in danger</small>
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
                    <div class="col-md-6">
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
            <!-- Feed -->
        </div>
    </div>

    <!-- Feed Section -->
    <div class="container d-flex mt-2 px-2" style="height: calc(73% - 0.5rem);">
        <div class="container col-lg-10 p-0 bg-light border border-2 border-primary-subtle shadow custom-border-radius">
            <!-- Title -->
            <div class="px-2 pt-3 d-flex flex-row justify-content-between">
                <h1 class="fw-bold m-0 pb-1 text-dark">Feed</h1>
            </div>

            <!-- Feed -->
            <div class="card-list overflow-auto custom-border-radius p-1" style="height: calc(100% - 4.9rem);">
            </div>
            <!-- Feed -->
        </div>
    </div>
</body>

<?php
// BEGIN - REQUESTING FINAL DEPENDENCIES ------------

if (!defined('db_disconnect.php')) define('db_disconnect.php', true);
require("{$_SERVER['DOCUMENT_ROOT']}/config/db_disconnect.php");

// END - REQUESTING FINAL DEPENDENCIES --------------
?>