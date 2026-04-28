<?php

function convertToHours($value) {
    if (stripos($value, "day") !== false) return 24;
    return intval($value); // "4 Hours" → 4
}

function convertTo24Hour($time, $ampm) {
    $time = intval($time);

    if ($ampm === "AM") {
        if ($time == 12) $time = 0;    // 12 AM → 00
    } else {
        if ($time != 12) $time += 12;  // 1 PM → 13
    }

    return sprintf("%02d:00", $time);
}

function convertStoredTo24($stored_time) {
    // stored format: "2 PM", "11 AM"
    list($t, $ampm) = explode(" ", $stored_time);

    return convertTo24Hour($t, $ampm);
}

function checkSlotOverlap($conn, $slot, $date, $start_time_user_24, $hours_user) {

    // User booking end time
    $user_end = date("H:i", strtotime("+$hours_user hour", strtotime($start_time_user_24)));

    // Fetch existing bookings for that slot & date
    $sql = "SELECT start_time, hours FROM slots WHERE slot_number=? AND booking_date=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $slot, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        // Convert stored "2 PM" → "14:00"
        $db_start_24 = convertStoredTo24($row['start_time']);
        $db_end_24 = date("H:i", strtotime("+{$row['hours']} hour", strtotime($db_start_24)));

        // Overlap condition
        if ($start_time_user_24 < $db_end_24 && $user_end > $db_start_24) {
            return true;  // OVERLAP
        }
    }

    return false; // NO overlap
}
?>
