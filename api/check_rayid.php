<?php

// session start
session_start();

// CORS headers for actual requests
header("Access-Control-Allow-Origin: *"); // Replace with your actual domain
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");  // Methods allowed
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Headers allowed
header("Access-Control-Allow-Credentials: true"); // If you need to allow cookies

// Cloudflare ray id
// $rayid = $_POST['rayid'];

$input = json_decode(file_get_contents("php://input"), true);
$rayid = $input['rayid'];


// Check Cloudflare Ray Id
function checkValueInCSV($filename, $valueToCheck) {
    // Open the CSV file for reading
    if (($handle = fopen($filename, 'r')) !== false) {
        // Loop through each row in the CSV file
        while (($row = fgetcsv($handle)) !== false) {
            // Check if the value is in the current row
            if (in_array($valueToCheck, $row)) {
                $_SESSION['isDownload'] = '1';
                fclose($handle);
                return true; // Value found
            }
        }
        fclose($handle);
    }
    return false; // Value not found
}

$isRayId = checkValueInCSV('visitors.csv', $rayid);
$mainDomain = $_SERVER['HTTP_HOST']; // Extracts the domain name, like "domain.com"

if ($isRayId) {
    echo 10;
    // header("Location: https://$mainDomain");
    exit;
}

?>