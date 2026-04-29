<?php
header('Content-Type: application/json');

require('vendor/autoload.php'); // Razorpay SDK

use Razorpay\Api\Api;

// 🔴 YAHAN APNI KEYS DAALO
$key = "rzp_test_RfxFeD0vDnMU5J";
$secret = "YOUR_SECRET_KEY";

$api = new Api($key, $secret);

$amount = $_POST['amount'] ?? 0;

if ($amount <= 0) {
    echo json_encode(["error" => "Invalid amount"]);
    exit;
}

try {
    $order = $api->order->create([
        'receipt' => 'order_rcptid_11',
        'amount' => $amount * 100,
        'currency' => 'INR'
    ]);

    echo json_encode([
        "order_id" => $order['id'],
        "amount" => $order['amount']
    ]);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}