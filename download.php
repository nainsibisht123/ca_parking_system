<?php
$file = $_GET['file'] ?? '';

if(file_exists($file)) {

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="booking_receipt.pdf"');
    header('Content-Length: ' . filesize($file));

    readfile($file);
    exit();

} else {
    echo "File not found!";
}
?>