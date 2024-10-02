<?php

header('Access-Control-Allow-Origin: *'); // Adjust this to your React app's URL
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
// Set content type to JSON
header('Content-Type: application/json');

// Load JSON settings file
$settingsFile = '../settings.json'; // Path to the settings file
$settingsFileStr = file_get_contents($settingsFile);
// $settings = json_decode($settingsFileStr, false);


echo $settingsFileStr;

?>