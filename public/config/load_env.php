<?php

// BEGIN - INITIAL SECURITY SCREEN ------------------

if (!defined('load_env.php')):

    die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
endif;

// END - INITIAL SECURITY SCREEN --------------------


// BEING - LOAD ENVELOPE PARAMS ---------------------

use Dotenv\Dotenv;

require_once("{$_SERVER['DOCUMENT_ROOT']}/vendor/autoload.php");

$dotenv = Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
$dotenv->load();

// END - LOAD ENVELOPE PARAMS -----------------------