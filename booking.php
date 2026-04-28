<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "car_parking_system");
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}


require('vendor/autoload.php');
require "slots_utils.php";
require('fpdf/fpdf.php');

use Razorpay\Api\Api;

function generatePDFAndSave($data, $booking_id) {

    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,'Parking Booking Receipt',0,1,'C');

    $pdf->Ln(10);

    $pdf->SetFont('Arial','',12);

    $pdf->Cell(60,10,'Owner Name:',0,0);
    $pdf->Cell(100,10,$data['owner_name'],0,1);

    $pdf->Cell(60,10,'Vehicle Name:',0,0);
    $pdf->Cell(100,10,$data['vehicle_name'],0,1);

    $pdf->Cell(60,10,'Vehicle Number:',0,0);
    $pdf->Cell(100,10,$data['vehicle_number'],0,1);

    $pdf->Cell(60,10,'Mobile:',0,0);
    $pdf->Cell(100,10,$data['mobile_number'],0,1);

    $pdf->Cell(60,10,'Slot:',0,0);
    $pdf->Cell(100,10,$data['slot'],0,1);

    $pdf->Cell(60,10,'Date:',0,0);
    $pdf->Cell(100,10,$data['booking_date'],0,1);

    $pdf->Cell(60,10,'Start Time:',0,0);
    $pdf->Cell(100,10,$data['parking_start_time'],0,1);

    $pdf->Cell(60,10,'Hours:',0,0);
    $pdf->Cell(100,10,$data['hours'],0,1);

    $pdf->Cell(60,10,'Total:',0,0);
    $pdf->Cell(100,10,"Rs. ".$data['total_price'],0,1);

    $pdf->Ln(10);
    $pdf->Cell(0,10,"Booking ID: $booking_id",0,1);

    if (!file_exists('receipts')) {
        mkdir('receipts', 0777, true);
    }

    $file_path = "receipts/".$booking_id.".pdf";
    $pdf->Output('F', $file_path);

    return $file_path;
}

// Escape helper
function s($conn, $v) {
    return mysqli_real_escape_string($conn, trim($v));
}

// Razorpay keys
$api_key    = "rzp_test_RfxFeD0vDnMU5J";
$api_secret = "0UPxlyDTVqSPZqW0UJPWsOLP";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $owner_name     = s($conn, $_POST['owner_name']);
    $vehicle_name   = s($conn, $_POST['vehicle_name']);
    $vehicle_number = strtoupper(s($conn, $_POST['vehicle_number']));
    $booking_date   = s($conn, $_POST['booking_date']);
    $mobile_number  = s($conn, $_POST['mobile_number']);
    $hours          = intval($_POST['hours']);
    $payment_method = s($conn, $_POST['payment_method']);
    $slot           = s($conn, $_POST['slot']);
    $start_time     = s($conn, $_POST['start_time']);
    $start_ampm     = s($conn, $_POST['start_ampm']);

    $parking_start_time = trim($start_time . ' ' . $start_ampm);
    $start_time_24 = date("H:i", strtotime($parking_start_time)); // ✅ FIX

    // ✅ VALIDATION
    if($owner_name=="" || $vehicle_name=="" || $vehicle_number=="" || 
       $booking_date=="" || $mobile_number=="" || $hours <= 0 || 
       $slot=="" || $start_time=="" || $start_ampm=="") 
    {
        header("Location: booking_start.php?error=missing");
        exit();
    }

    if(!preg_match('/^[0-9]{10}$/', $mobile_number)){
        header("Location: booking_start.php?error=invalid_mobile");
        exit();
    }

    if (!preg_match("/^UK[0-9]{2}-[A-Z]{1,3}-[0-9]{1,4}$/", $vehicle_number)) {
        header("Location: booking_start.php?error=invalid_vehicle");
        exit();
    }

    // ✅ SLOT CHECK
    if (checkSlotOverlap($conn, $slot, $booking_date, $start_time_24, $hours)) {
        header("Location: booking_start.php?error=busy");
        exit();
    }

    $total_price = $hours * 200;

    // ✅ START TRANSACTION
    $conn->begin_transaction();

    try {

        // ✅ PAYMENT CHECK
        if($payment_method == "Online Payment") {

            $payment_id = $_POST['razorpay_payment_id'] ?? '';
            $order_id   = $_POST['razorpay_order_id'] ?? '';
            $signature  = $_POST['razorpay_signature'] ?? '';

            if($payment_id=="" || $order_id=="" || $signature==""){
                throw new Exception("Payment Failed");
            }

            $api = new Api($api_key, $api_secret);
            $api->utility->verifyPaymentSignature([
                "razorpay_order_id"   => $order_id,
                "razorpay_payment_id" => $payment_id,
                "razorpay_signature"  => $signature
            ]);

            $sql = "INSERT INTO bookings_info 
                (owner_name, vehicle_name, vehicle_number, booking_date, mobile_number, hours, total_price, payment_method, slot, parking_start_time, payment_id, order_id, signature)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssiissssss",
                $owner_name, $vehicle_name, $vehicle_number, $booking_date,
                $mobile_number, $hours, $total_price, $payment_method,
                $slot, $parking_start_time, $payment_id, $order_id, $signature
            );

        } else {

            $sql = "INSERT INTO bookings_info 
                (owner_name, vehicle_name, vehicle_number, booking_date, mobile_number, hours, total_price, payment_method, slot, parking_start_time)
                VALUES (?,?,?,?,?,?,?,?,?,?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssiisss",
                $owner_name, $vehicle_name, $vehicle_number, $booking_date,
                $mobile_number, $hours, $total_price, $payment_method,
                $slot, $parking_start_time
            );
        }

        // ✅ INSERT BOOKING
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        // ✅ INSERT SLOT
        $insertSlot = $conn->prepare("
            INSERT INTO slots 
            (slot_number, booking_date, start_time, car_number, status, hours) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $slot_status = 'busy';

        $insertSlot->bind_param(
            "sssssi", 
            $slot,
            $booking_date,
            $start_time_24,
            $vehicle_number,
            $slot_status,
            $hours
        );

        if (!$insertSlot->execute()) {
            die("hello");
            throw new Exception($insertSlot->error);
        }

       
        // ✅ COMMIT
      
$conn->commit();

// Booking ID
//$booking_id = "PK" . time();
$booking_id = $insertSlot->insert_id;

// PDF data
$pdfData = [
    "owner_name" => $owner_name,
    "vehicle_name" => $vehicle_name,
    "vehicle_number" => $vehicle_number,
    "mobile_number" => $mobile_number,
    "slot" => $slot,
    "booking_date" => $booking_date,
    "parking_start_time" => $parking_start_time,
    "hours" => $hours,
    "total_price" => $total_price
];

// ✅ PDF generate (ONLY ONCE)
$pdf_path = generatePDFAndSave($pdfData, $booking_id);

// ✅ SESSION SAVE
$_SESSION['pdf'] = $pdf_path;
$_SESSION['booking_id'] = $booking_id;

// ✅ REDIRECT
header("Location: success.php");
exit();
      

    } catch (Exception $e) {

        // ❌ ROLLBACK
        $conn->rollback();

        header("Location: booking_start.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>