<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Car Parking Booking</title>
<link rel="stylesheet" href="booking.css">

<style>
input, select {
    padding: 8px;
    width: 100%;
    margin-bottom: 10px;
}
</style>
</head>
<body>
<?php
session_start();
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
}
?>

<!-- Success/Error messages -->
<?php if(isset($_GET['success'])) { ?>
    <div class="success-msg">✔ Booking Completed Successfully!</div>
<?php } ?>

<?php if(isset($_GET['error'])) { 
    $err = $_GET['error'];
    $msg = "";
    if($err == "missing") $msg = "Please fill all required fields.";
    if($err == "busy") $msg = "Selected slot is already booked.";
    if($err == "invalid_vehicle") $msg = "Invalid Vehicle Number Format!";
    if($err == "noslot") $msg = "Slot not found.";
    if($err == "db" || $err == "db_insert") $msg = "Database error. Try again.";
?>
    <div class="error-msg">✖ <?php echo $msg; ?></div>
<?php } ?>

<header>
  <h1>Car Parking Management System</h1>
  <nav>
    <ul>
      <li><a href="home.html">Home</a></li>
      <li><a href="booking_start.php">Booking</a></li>
      <li><a href="slot.html">Slots</a></li>
      <li><a href="about.html">About</a></li>
      <li><a href="contact.html">Contact</a></li>
    </ul>
  </nav>
</header>

<div class="booking-container">
    <div id="slot_status"></div>
  <h2>Book Your Parking Slot</h2>

  <form action="booking.php" method="POST" id="bookingForm">

    <!-- 🔵 Owner Name -->
    <label>Owner Name:</label>
    <input name="owner_name" type="text" required placeholder="Enter owner name"
oninput="this.value = this.value.replace(/[^A-Za-z ]/g, '');"
pattern="[A-Za-z ]+" title="Only letters allowed">

    <!-- 🔵 Vehicle Name -->
   <label>Vehicle Name:</label>
<input name="vehicle_name" type="text" list="vehicleBrands"
       required placeholder="Enter vehicle name"
       oninput="this.value = this.value.replace(/[^A-Za-z ]/g, '');"
       pattern="[A-Za-z ]+" title="Only letters allowed">
       <datalist id="vehicleBrands">
  <option value="Maruti Suzuki">
  <option value="Hyundai">
  <option value="Tata Motors">
  <option value="Mahindra">
  <option value="Kia">
  <option value="Toyota">
  <option value="Honda">
  <option value="MG Motor">
  <option value="Skoda">
  <option value="Volkswagen">
  <option value="Renault">
  <option value="Nissan">
  <option value="Ford">
  <option value="BMW">
  <option value="Mercedes-Benz">
  <option value="Audi">
  <option value="Jeep">
  <option value="Volvo">
  <option value="Jaguar">
  <option value="Land Rover">
  <option value="Lexus">
  <option value="Porsche">
  <option value="Mini">
  <option value="BYD">
  <option value="Citroen">
  <option value="Thar">

      </option>
</datalist>

<label>Vehicle Number:</label>
<input name="vehicle_number" id="vehicle_number" type="text" maxlength="13"
       required placeholder="UK07-AB-1234"
       oninput="this.value=this.value.toUpperCase().replace(/[^A-Z0-9-]/g,'');">


    <!-- 🔵 Booking Date -->
    <label>Booking Date:</label>
    <input name="booking_date" id="booking_date" type="date" required>
<label>Mobile Number</label>
<div style="display:flex;">
    <input type="text" name="mobile_number" id="mobile" maxlength="10"
placeholder="Enter Mobile Number" required
oninput="this.value = this.value.replace(/[^0-9]/g, '');"
pattern="[0-9]{10}" title="Enter valid 10 digit number">
</div>


    <!-- 🔵 Hours -->
    <label>Booking Duration (Hours):</label>
    <select name="hours" id="hours" required>
      <option value="">Select Hours</option>
      <option value="1">1 Hours</option>
      <option value="2">2 Hours</option>
      <option value="4">4 Hours</option>
      <option value="6">6 Hours</option>
      <option value="10">10 Hours</option>
      <option value="15">15 Hours</option>
      <option value="20">20 Hours</option>
      <option value="24">1 Day</option>
    </select>

    <!-- 🔵 Start Time -->
    <label>Parking Start Time:</label>
    <div style="display: flex; gap: 10px;">
        <input type="number" min="1" max="12" name="start_time" id="start_time"
               placeholder="Enter time" required style="flex:1;">
        <select id="start_ampm" name="start_ampm" required style="width:100px;">
            <option value="AM">AM</option>
            <option value="PM">PM</option>
        </select>
    </div>

    <div class="price-info" id="totalPrice">Parking Charges: ₹200 per hour</div>

    <!-- 🔵 Slot -->
    <label>Select Slot:</label>
    <select name="slot" id="slot" required>
      <option value="">--Select--</option>
      <?php for($i=1; $i<=30; $i++){ echo "<option value='$i'>$i</option>"; } ?>
    </select>

    <!-- 🔵 Payment -->
    <label>Payment Method:</label>
    <select name="payment_method" id="payment_method" required>
      <option value="">Select payment type</option>
      <option value="Cash">Cash</option>
      <option value="Online Payment">Online Payment</option>
    </select>

 <button type="submit" class="book-btn" id="bookBtn">Book Slot</button>
    <button type="button" class="cancel-btn" onclick="cancelBooking()">Cancel Booking</button>

  </form>
</div>

<footer>
  © 2025 Car Parking Management System | All Rights Reserved
</footer>


<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
/* ------------------------- VEHICLE NUMBER STRICT CHECK --------------------------- */
function validateVehicleNumber() {
    let v = document.getElementById("vehicle_number").value;
    let pattern = /^[A-Z]{2}[0-9]{2}-[A-Z]{1,3}-[0-9]{1,4}$/; // Correct India format RJ14-AB-1234

    if (!pattern.test(v)) {
        alert("Invalid Vehicle Number!\nCorrect Format: RJ14-AB-1234");
        return false;
    }
    return true;
}
function addCountryCode() {
    let m = document.getElementById("mobile").value;

    if (m.length == 10) {
        m = "+91" + m;
    }

    document.getElementById("mobile").value = m;
}


/* ------------------------- SLOT CHECK ---------------------------- */
async function checkSlotAvailability() {
    const slot = document.getElementById("slot").value;
    const date = document.getElementById("booking_date").value;
    const start = document.getElementById("start_time").value;
    const hours = document.getElementById("hours").value;
    const ampm = document.getElementById("start_ampm").value;

    if (!slot || !date || !start || !hours) return;

    let formData = new FormData();
    formData.append("slot", slot);
    formData.append("booking_date", date);
    formData.append("start_time", start+" "+ampm);
    formData.append("hours", hours);

    let response = await fetch("check_slot.php", { method: "POST", body: formData });
    let result = await response.json();

    if (result.busy === false) {
        document.getElementById("payment_method").disabled = false;
        document.getElementById("slot_status").innerHTML =
            "<span style='color:green;font-weight:bold;'>Slot Available ✔</span>";
    } else {
        document.getElementById("payment_method").disabled = true;
        document.getElementById("slot_status").innerHTML =
            "<span style='color:red;font-weight:bold;'>Slot NOT Available ❌</span>";
        alert("This slot is already booked for this timing.");
    }
}

document.getElementById("slot").onchange = checkSlotAvailability;
document.getElementById("booking_date").onchange = checkSlotAvailability;
document.getElementById("start_time").onchange = checkSlotAvailability;
document.getElementById("hours").onchange = checkSlotAvailability;
document.getElementById("start_ampm").onchange = checkSlotAvailability;

/* ------------------------- PAYMENT + RAZORPAY ---------------------------- */
const form = document.getElementById('bookingForm');
const paymentSelect = document.getElementById('payment_method');
let razorpayDone = false;

paymentSelect.addEventListener("change", function () {
    if (paymentSelect.value !== "Online Payment" || razorpayDone) return;

    if (form.owner_name.value.trim() === "" || form.hours.value === "") {
        alert("Please fill owner name & hours first");
        paymentSelect.value = "";
        return;
    }

    let hours = parseInt(form.hours.value);
    let total = hours * 200;

    fetch("create_order.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "amount=" + total
    })
    .then(res => res.json())
    .then(order => {

        let options = {
            "key": "rzp_test_RfxFeD0vDnMU5J",
            "amount": order.amount,
            "currency": "INR",
            "order_id": order.order_id,
            "name": "Car Parking System",

            "handler": function (response) {
                razorpayDone = true;

                let fields = {
                    "razorpay_payment_id": response.razorpay_payment_id,
                    "razorpay_order_id": response.razorpay_order_id,
                    "razorpay_signature": response.razorpay_signature
                };

                for (let key in fields) {
                    let input = document.createElement("input");
                    input.type = "hidden";
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                }

                form.submit();
            }
        };

        let rzpObj = new Razorpay(options);
        rzpObj.open();
    });
});

/* --------------------------- BOOK BUTTON ------------------------------ */
bookBtn.addEventListener("click", function () {

    if (!validateVehicleNumber()) return;

    const method = paymentSelect.value;

    if (method === "Cash") {
        form.submit();
    }
    else if (method === "Online Payment") {
        if (razorpayDone) {
            form.submit();
        } else {
            alert("Please complete online payment first.");
        }
    }
    else {
        alert("Please select a payment method.");
    }
});

/* ------------------------- PRICE UPDATE --------------------------- */
document.getElementById('hours').addEventListener('change', () => {
    const h = parseInt(document.getElementById('hours').value);
    document.getElementById('totalPrice').innerHTML =
        (!isNaN(h)) ? `Total Parking Charges: ₹${h * 200}` : "Parking Charges: ₹200 per hour";
});

/* ------------------------- CANCEL --------------------------- */
function cancelBooking() {
    form.reset();
    document.getElementById('totalPrice').innerHTML = "Parking Charges: ₹200 per hour";
}
window.onload = function() {
    // Form reset on page load
    let form = document.getElementById("bookingForm");
    form.reset();

    // Mobile field ko blank rakho (user sirf 10 digit dale)
    document.getElementById("mobile").value = "";

    // Agar browser autofill kar bhi de, JS overwrite kar dega
    let inputs = form.querySelectorAll("input");
    inputs.forEach(input => {
        input.value = input.disabled ? input.value : ""; // disabled inputs jaise +91 untouched
    });
};
</script>

</body>
</html>
