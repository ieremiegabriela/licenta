<?php

// BEGIN - SESSION CHECK ----------------------------

session_start();

define("helper_functions.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/helpers/php/helper_functions.php");

switch (true):
	case (!isset($_SESSION['authenticated'])):
	case (isset($_SESSION['authenticated']) && !$_SESSION['authenticated']):

		die(header("Location: {$_SESSION['LOCATION_ORIGIN']}/login.php"));
		break;
endswitch;

// END - SESSION CHECK ------------------------------


// BEGIN - INITIAL CONFIG ---------------------------

define("config.php", true);
require_once("{$_SERVER['DOCUMENT_ROOT']}/config/config.php");

// END - INITIAL CONFIG -----------------------------

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta name="theme-color" content="#ea931a">
	<meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
	<meta http-equiv="pragma" content="no-cache">
	<meta http-equiv="expires" content="0">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>Document</title>

	<link rel="icon" type="image/x-icon" href="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/assets/img/favicon.ico"; ?>">

	<!-- -------------------------------------------------- -->

	<?php
	define("_libs.php", true);
	require_once("{$_SERVER['DOCUMENT_ROOT']}/_libs.php");
	?>

	<!-- -------------------------------------------------- -->

	<link rel="stylesheet" href="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/helpers/css/custom.css"; ?>">

	<script type="text/javascript" src="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/helpers/js/helper_functions.js"; ?>"></script>
	<script type="text/javascript" src="<?php echo "{$_SESSION['LOCATION_ORIGIN']}/modules/index/index.js"; ?>"></script>
	<style>
		html,
		body {
			margin: 0;
			padding: 0;
			height: 100%;
		}
	</style>
</head>

<body class="pt-5">
	<!-- Navigation -->
	<?php
	define("_navigation.php", true);
	require_once("{$_SERVER['DOCUMENT_ROOT']}/_navigation.php");
	?>

	<!-- Page Content -->
	<div class="container"></div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>

</html>