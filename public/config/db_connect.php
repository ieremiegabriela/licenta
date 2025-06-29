<?php

// BEGIN - SECURITY SCREEN --------------------------

switch (true):
    case (!defined('db_connect.php')):
    case (!isset($_ENV['DB_HOST'])):
    case (!isset($_ENV['DB_USERNAME'])):
    case (!isset($_ENV['DB_PASSWORD'])):
    case (!isset($_ENV['DB_NAME'])):
        die(http_response_code(404));
endswitch;

// END - SECURITY SCREEN ----------------------------


// BEGIN - DB CONNECTION ----------------------------

$mysqli = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD'],
    $_ENV['DB_NAME']
);

// Check connection
if ($mysqli->connect_errno):

    die("Failed to connect to MySQL: $mysqli->connect_error");
endif;

$mysqli->set_charset("utf8mb4");

// END - DB CONNECTION ------------------------------
