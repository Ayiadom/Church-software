<?php
include 'db.php';

// Handle Create (INSERT) for Tithes and Welfare payments
if (isset($_POST['create_payment'])) {
    $member_id = $_POST['member_id'];
    $amount_tithe = $_POST['amount_tithe'];
    $amount_welfare = $_POST['amount_welfare'];
    $payment_date = $_POST['payment_date'];

    $payment_success = false;
    $sms_success = false;

    // Check if Tithe or Welfare is selected and insert respective records
    if ($amount_tithe > 0) {
        // Insert Tithe Payment
        $sql_tithe = "INSERT INTO tithes_welfare (member_id, amount, payment_type, payment_date) 
                      VALUES ('$member_id', '$amount_tithe', 'Tithe', '$payment_date')";
        $payment_success = $conn->query($sql_tithe);
        
        // Send SMS Notification for Tithe payment if it was successful
        if ($payment_success) {
            $sms_success = sendSMSNotification($member_id, $amount_tithe, 'Tithe', $payment_date);
        }
    }

    if ($amount_welfare > 0) {
        // Insert Welfare Payment
        $sql_welfare = "INSERT INTO tithes_welfare (member_id, amount, payment_type, payment_date) 
                        VALUES ('$member_id', '$amount_welfare', 'Welfare', '$payment_date')";
        $payment_success = $conn->query($sql_welfare);
        
        // Send SMS Notification for Welfare payment if it was successful
        if ($payment_success) {
            $sms_success = sendSMSNotification($member_id, $amount_welfare, 'Welfare', $payment_date);
        }
    }

    // Output success or failure message
    if ($payment_success && $sms_success) {
        echo "Payment recorded and SMS sent successfully!";
    } else {
        echo "Error occurred. Please try again.";
    }
}

// Function to send SMS via Infobip API
function sendSMSNotification($member_id, $amount, $payment_type, $payment_date) {
    global $conn;

    // Fetch member's phone number from the database
    $sql = "SELECT phonenumber FROM members WHERE memID = '$member_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $phone_number = $row['phonenumber'];
        
        // Compose the SMS message
        $message = "Dear member, you have successfully paid $payment_type of GHS $amount on $payment_date. Thank you for your contribution.";
        
        // Send SMS
        return sendSMS($phone_number, $message);
    } else {
        echo "Member not found.";
        return false;
    }
}

// Function to send SMS via Infobip API
function sendSMS($phoneNumber, $message) {
    $api_url = "https://api.infobip.com/sms/2/text/advanced";  // API URL for sending SMS
    $api_key = "c03c588bf450ea745a7b108e965bbb57-6985ea6b-fcdb-4cb4-b114-cc4c77b76ffb"; // Your API Key

    // SMS payload
    $data = [
        "messages" => [
            [
                "from" => "YourSenderID", // Replace with your sender ID
                "to" => $phoneNumber,
                "text" => $message
            ]
        ]
    ];

    // cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: App $api_key", // Authorization header with API key
        "Content-Type: application/json"
    ]);

    // Execute the cURL request and get the response
    $response = curl_exec($ch);

    // Check for errors
    if(curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        curl_close($ch);
        return false; // Return false if SMS failed
    }

    curl_close($ch);
    return true; // Return true if SMS sent successfully
}

// Fetch all members for the member dropdown
$members_sql = "SELECT memID, firstname, lastname, phonenumber FROM members";
$members_result = $conn->query($members_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tithes and Welfare Payments</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Tithes and Welfare Payments</h1>

    <form method="POST">
        <!-- Select Member -->
        <label for="member_id">Member</label>
        <select name="member_id" id="member_id" required>
            <option value="" disabled selected>Select Member</option>
            <?php
            while ($member = $members_result->fetch_assoc()) {
                echo "<option value='{$member['memID']}'>{$member['firstname']} {$member['lastname']}</option>";
            }
            ?>
        </select>

        <!-- Amount for Tithe -->
        <label for="amount_tithe">Tithe Amount</label>
        <input type="number" name="amount_tithe" id="amount_tithe" placeholder="Tithe Amount" value="0" min="0">

        <!-- Amount for Welfare -->
        <label for="amount_welfare">Welfare Amount</label>
        <input type="number" name="amount_welfare" id="amount_welfare" placeholder="Welfare Amount" value="0" min="0">

        <!-- Date Picker for Payment Date -->
        <label for="payment_date">Payment Date</label>
        <input type="date" name="payment_date" id="payment_date" required>

        <!-- Submit Button to Record Payment -->
        <button type="submit" name="create_payment">Record Payment</button>
    </form>

    <h2>Payment Records</h2>
    <table>
        <tr>
            <th>Member</th>
            <th>Phone Number</th>
            <th>Amount</th>
            <th>Payment Type</th>
            <th>Payment Date</th>
        </tr>
        <?php
        // Fetch all payment records to display
        $sql = "SELECT tw.id, m.firstname, m.lastname, m.phonenumber, tw.amount, tw.payment_type, tw.payment_date 
                FROM tithes_welfare tw 
                JOIN members m ON tw.member_id = m.memID";
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['firstname']} {$row['lastname']}</td>
                    <td>{$row['phonenumber']}</td>
                    <td>{$row['amount']}</td>
                    <td>{$row['payment_type']}</td>
                    <td>{$row['payment_date']}</td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>

<?php $conn->close(); ?>
