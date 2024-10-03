<?php

// session start
session_start();

// CORS headers for actual requests
header("Access-Control-Allow-Origin: *"); // Replace with your actual domain
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");  // Methods allowed
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Headers allowed
header("Access-Control-Allow-Credentials: true"); // If you need to allow cookies

// Set content type to JSON
header('Content-Type: application/json');


$isDark = $_POST['mode'];


if ($isDark == 'true') {
	$_SESSION['darkmode'] = '1';
	echo 1;
} else {
	$_SESSION['darkmode'] = '0';
	echo 0;
}


?>