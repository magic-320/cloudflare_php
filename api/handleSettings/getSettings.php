<?php

// CORS headers for actual requests
header("Access-Control-Allow-Origin: *"); // Replace with your actual domain
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");  // Methods allowed
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Headers allowed
header("Access-Control-Allow-Credentials: true"); // If you need to allow cookies

// Set content type to JSON
header('Content-Type: application/json');

// Load JSON settings file
$settingsFile = '../settings.json'; // Path to the settings file
$settingsFileStr = file_get_contents($settingsFile);
// $settings = json_decode($settingsFileStr, false);


echo $settingsFileStr;

?>