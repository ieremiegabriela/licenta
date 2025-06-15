<?php

// BEGIN - INITIAL SECURITY SCREEN ------------------

if (!defined('db_connect.php')):

    die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
endif;

// END - INITIAL SECURITY SCREEN --------------------


// BEGIN - DB CONNECTION ----------------------------

$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_errno):

    die("Failed to connect to MySQL: $mysqli->connect_error");
endif;

$mysqli->set_charset("utf8mb4");

// END - DB CONNECTION ------------------------------
