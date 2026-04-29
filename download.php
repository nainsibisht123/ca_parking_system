<?php
session_start();

$file = $_GET['file'] ?? '';

if($file == '') {
    die("No file specified");
}

$file_path = __DIR__ . "/" . $file;

if(file_exists($file_path)) {

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="booking_receipt.pdf"');
    header('Content-Length: ' . filesize($file_path));

    readfile($file_path);
    exit();

} else {
    echo "File not found!";
}
?>