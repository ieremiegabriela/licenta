<?php

// BEGIN - INITIAL SECURITY SCREEN ------------------

if (!defined('db_disconnect.php')):

    die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
endif;


// END - INITIAL SECURITY SCREEN --------------------


// BEGIN - DB CLOSE CONNECTION ----------------------

$mysqli->close();

// END - DB CLOSE CONNECTION ------------------------
