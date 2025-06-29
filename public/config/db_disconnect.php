<?php

// BEGIN - INITIAL SECURITY SCREEN ------------------

if (!defined('db_disconnect.php')):

    die(http_response_code(404));
endif;


// END - INITIAL SECURITY SCREEN --------------------


// BEGIN - DB CLOSE CONNECTION ----------------------

$mysqli->close();

// END - DB CLOSE CONNECTION ------------------------
