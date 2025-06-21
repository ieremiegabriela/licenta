<?php

session_start();

// Authentication check
switch (true):
    case (!isset($_GET['authToken'])):
    case ($_GET['authToken'] !== $_SESSION['authToken']):
    case ($_SERVER['REQUEST_METHOD'] !== "GET"):
    case (!isset($_GET['input'])):

        die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
endswitch;

// --------------------------------------------------

$input = (array)json_decode($_GET['input'], true);
$input = filter_var_array($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// --------------------------------------------------

switch (true):
    case (!isset($input['id'])):

        die(header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found"));
endswitch;

// --------------------------------------------------

// Set error reporting level
error_reporting(0);

// Set headers for SSE
header('Connection: keep-alive');
header("Content-Type: text/event-stream; charset=UTF-8");
header("X-Accel-Buffering: no");
header("Cache-Control: no-cache");

ini_set("default_charset", "UTF-8");
mb_internal_encoding("UTF-8");

// Set timezone
date_default_timezone_set("UTC");

// --------------------------------------------------

// Previous data tracking
$prevData = null;

// Infinite loop for SSE
while (true) {
    $output = json_encode([
        'time' => date(DateTime::ATOM),
        'authenticated' => (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) ? 1 : 0,
    ]);

    // Send a ping event every second
    echo "event: ping\n";
    echo "data: {$output}\n\n";

    // Capture dynamic content from the included file
    ob_start();
    require("{$_SERVER['DOCUMENT_ROOT']}modules/chat/page/body.php");
    $outputBuffer = ob_get_clean();

    // JSON encode the output
    $output = json_encode([
        'success' => !empty($outputBuffer) ? 1 : 0,
        'data'    =>  $outputBuffer ?: null
    ]);

    // Only send updates when data has changed
    if ($prevData !== $output):
        echo "event: update\n";
        echo "data: {$output}\n\n";
        $prevData = $output;
    endif;

    // Flush output buffer if necessary
    if (!empty($outputBuffer)):
        unset($outputBuffer);
        ob_end_flush();
    endif;

    flush();

    // Stop execution if connection is aborted
    if (connection_aborted()) die;

    // sleep(1);
    usleep(100000);
}
