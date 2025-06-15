<?php

// BEGIN - INITIAL SECURITY SCREEN ------------------

if (!defined('helper_functions.php')):

    die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
endif;

// END - INITIAL SECURITY SCREEN --------------------


// BEGIN - ADDITIONAL SESSION CONFIG ----------------

$_SESSION['LOCATION_ORIGIN'] = getLocationOrigin();

// END - ADDITIONAL SESSION CONFIG ------------------


function getLocationOrigin() {

    return (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]";
}

function createAuthToken() {

    return sha1(md5(mt_rand(0, mt_getrandmax())));
}
