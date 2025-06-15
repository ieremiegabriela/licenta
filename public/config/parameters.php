<?php

// BEGIN - INITIAL SECURITY SCREEN ------------------

if (!defined('parameters.php')):

    die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
endif;

// END - INITIAL SECURITY SCREEN --------------------


// BEING - PARAMETER DEFINITIONS --------------------

if (!defined('DB_HOST')) define('DB_HOST', 'mysql');
if (!defined('DB_USERNAME')) define('DB_USERNAME', 'root');
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', 'hWrbEe0DKwdRyve');
if (!defined('DB_NAME')) define('DB_NAME', 'licenta');

// END - PARAMETER DEFINITIONS ----------------------