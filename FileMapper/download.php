<?php
// Whitelist of allowed files and their download names
$allowedFiles = [
  // Filename of the source => filename you want to save file as
    'report_q1.pdf' => 'Q1_Report_2025.pdf',
    'manual_v2.pdf' => 'User_Manual_v2.pdf',
    'data_export.csv' => 'Exported_Data_June.csv'
];

// Get the requested file from the GET parameter
$requestedFile = $_GET['file'] ?? '';

// Check if the file is in the allowed list
if (array_key_exists($requestedFile, $allowedFiles)) {
    $filePath = __DIR__ . '/files/' . $requestedFile; // Adjust path as needed
    $downloadName = $allowedFiles[$requestedFile];

    // Check if the file exists on disk
    if (file_exists($filePath)) {
        // Set headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        ob_clean();
        flush();
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        echo 'File not found.';
    }
} else {
    http_response_code(403);
    echo 'Unauthorized file request.';
}


/* Example usage
https://yourdomain.com/download.php?file=report_q1.pdf
USE THE INCLUDED .htaccess file to give fancy URLS
https://yourdomain.com/download/report_q1.pdf (this will only ever be the file you link to, the file it downloads as can have whatever characters are required)
*/
?>
