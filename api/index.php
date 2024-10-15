<?php

// Cloudflare ray id
$rayid = $_REQUEST['rayid'];
// Country Code
$countrycode = $_REQUEST['countrycode'];
// get Version
$version = $_REQUEST['version'];
// Browser name & version
$browser = getBrowser();

// Get the visitor's IP, OS, and Country
$userIP = $_SERVER['REMOTE_ADDR'];
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


// get browser name & version
function getBrowser()
{
    // Get the user agent string
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Initialize the browser name and version variables
    $browser = "Unknown Browser";
    $version = "";

    // Check for Microsoft Edge (Chromium-based and Legacy Edge)
    if (strpos($user_agent, 'Edg') !== false) {
        $browser = "Microsoft Edge";
        preg_match("/Edg\/([0-9\.]+)/", $user_agent, $matches);
        $version = $matches[1] ?? '';
    // Check for Opera
    } elseif (strpos($user_agent, 'OPR') !== false || strpos($user_agent, 'Opera') !== false) {
        $browser = "Opera";
        preg_match("/OPR\/([0-9\.]+)/", $user_agent, $matches);
        $version = $matches[1] ?? '';
    // Check for Google Chrome (after checking for Opera and Edge)
    } elseif (strpos($user_agent, 'Chrome') !== false) {
        $browser = "Google Chrome";
        preg_match("/Chrome\/([0-9\.]+)/", $user_agent, $matches);
        $version = $matches[1] ?? '';
    // Check for Mozilla Firefox
    } elseif (strpos($user_agent, 'Firefox') !== false) {
        $browser = "Mozilla Firefox";
        preg_match("/Firefox\/([0-9\.]+)/", $user_agent, $matches);
        $version = $matches[1] ?? '';
    // Check for Safari (excluding Chrome and Edge)
    } elseif (strpos($user_agent, 'Safari') !== false && strpos($user_agent, 'Chrome') === false && strpos($user_agent, 'Edg') === false) {
        $browser = "Safari";
        preg_match("/Version\/([0-9\.]+)/", $user_agent, $matches);
        $version = $matches[1] ?? '';
    }

    // Output the detected browser and version
    return $browser . ' ' . $version;
}


// Function to log visitor details to CSV
function logVisitor($ip, $os, $country, $rayid, $browser, $version) {
    $csvFile = 'visitors.csv';

    // Check if the file exists
    if (file_exists($csvFile)) {
        $file = fopen($csvFile, 'r');
        
        // Loop through the file to check if the IP already exists
        // while (($data = fgetcsv($file)) !== FALSE) {
        //     if ($data[0] === $ip) {
        //         fclose($file); // Close the file if IP is found
        //         return; // Skip logging if IP already exists
        //     }
        // }
        // fclose($file); // Close the file after reading
    }

    // If the IP does not exist, append the new data
    $file = fopen($csvFile, 'a');
    fputcsv($file, [$ip, $os, $country, date('Y-m-d H:i:s'), $rayid, $browser, "Visitor", $version]);
    fclose($file);
}


// save the user's info when the user visit the domain
logVisitor($userIP, $userOS, $countrycode, $rayid, $browser, $version);

?>
