<?php

// In your PHP file (getData.php), add this at the top:
header("Access-Control-Allow-Origin: *"); // Or specify the exact origin
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Set content type to JSON
header('Content-Type: application/json');

// Path to the CSV file
$csvFile = 'visitors.csv';

// Check if the file exists
if (!file_exists($csvFile) || !is_readable($csvFile)) {
    die('File not found or not readable.');
}

// Open the CSV file for reading
if (($handle = fopen($csvFile, 'r')) !== false) {
    // Read the header line (optional)
    // $header = fgetcsv($handle);

    $header = ["ip", "os", "country_code", "date", "rayid", "browser", "isDownload", "version"];
    
    // Initialize an array to hold the data
    $data = [];
    
    // Read each line of the CSV file
    while (($row = fgetcsv($handle)) !== false) {
        $data[] = array_combine($header, $row);
    }

    // Close the file
    fclose($handle);
    
    // Output the data (you can manipulate it as needed)
    print_r(json_encode($data));
} else {
    echo "Error opening the file.";
}

?>