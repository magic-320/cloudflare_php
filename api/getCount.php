<?php

// In your PHP file (getData.php), add this at the top:
header("Access-Control-Allow-Origin: *"); // Or specify the exact origin
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Set content type to JSON
header('Content-Type: application/json');

$filename = './download_count.txt'; // Change to your file path

$content = file_get_contents($filename);

if ($content === false) {
    echo "Error reading the file.";
} else {
    echo $content;
}

?>