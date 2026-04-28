<?php

function convertToHours($value) {
    if (stripos($value, "day") !== false) return 24;
    return intval($value);
}

function convertTo24Hour($hour, $ampm) {
    $hour = intval($hour);

    if ($ampm === "AM") {
        if ($hour == 12) $hour = 0;
    } else {
        if ($hour != 12) $hour += 12;
    }

    return sprintf("%02d:00:00", $hour);
}

function convertStoredTo24($stored_time) {
    if (empty($stored_time)) return "00:00:00";

    $parts = explode(" ", $stored_time);

    if (count($parts) !== 2) {
        return "00:00:00";
    }

    list($t, $ampm) = $parts;
    return convertTo24Hour($t, $ampm);
}

function checkSlotOverlap($conn, $slot, $date, $start_time_user, $hours_user) {

    $hours_user = intval($hours_user);

    // Convert "4 PM" to "16:00:00"
    $start_time_user_24 = convertStoredTo24($start_time_user);

    // User end time
    $user_end_24 = date("H:i:s", strtotime($start_time_user_24) + ($hours_user * 3600));

    // Fetch existing bookings for this slot + date
    $sql = "SELECT start_time, hours FROM slots WHERE slot_number=? AND booking_date=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $slot, $date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $db_start_24 = convertStoredTo24($row['start_time']);
        $db_end_24   = date("H:i:s", strtotime($db_start_24) + ($row['hours'] * 3600));

        // Overlap Condition
        if ($start_time_user_24 < $db_end_24 && $user_end_24 > $db_start_24) {
            return true; // clash found
        }
    }

    return false;
}
?>
