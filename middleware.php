<?php

// session start
session_start();

// Load JSON settings file
$settingsFile = './api/settings.json'; // Path to the settings file
$settingsFileStr = file_get_contents($settingsFile);
$settings = json_decode($settingsFileStr, false);

$pageStatus = $settings->PageStatus;
$version = $settings->Version->Enabled;


// Get the main domain dynamically
$mainDomain = $_SERVER['HTTP_HOST']; // Extracts the domain name, like "domain.com"


if ($pageStatus == 'ON') {
	if (!isset($_SESSION['isDownload']) || $_SESSION['isDownload'] != '1') {
		header("Location: https://$mainDomain/$version");
	}
}


?>