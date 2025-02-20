<?php
// Include Guzzle Client and database connection
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
require 'vendor/autoload.php';

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

// Function to send SMS via Guzzle
function sendSMS($phoneNumber, $message) {
    $api_url = "https://sms-gate.azurewebsites.net/api/send-sms";  // API URL for sending SMS
    $api_key = "2zKlyHTyOEykoPRojwvvIrJscyUse539yPGZLzbscriF6ficHgR0wTe7ZmLuyjvh"; // API Key
    $client_id = "F923B201"; // Client ID
    $sender_id = "WesleyMeth"; // Sender ID

    // Initialize Guzzle client
    $client = new Client();

    // Set headers for the request
    $headers = [
        'Content-Type' => 'application/json',
        'x-api-key' => $api_key
    ];

    // Prepare the SMS payload
    $body = json_encode([
        'clientId' => $client_id,
        'senderId' => $sender_id,
        'phone' => $phoneNumber,
        'message' => $message
    ]);

    try {
        // Send SMS via POST request asynchronously
        $request = new \GuzzleHttp\Psr7\Request('POST', $api_url, $headers, $body);
        $response = $client->sendAsync($request)->wait();

        // Get the response body and output it for debugging
        echo "Response: " . $response->getBody() . "\n";
        
        // Check if the response status code is 200 (OK)
        if ($response->getStatusCode() == 200) {
            return true; // Return true if SMS was sent successfully
        } else {
            echo "Failed to send SMS. Status code: " . $response->getStatusCode();
            return false;
        }
        
    } catch (RequestException $e) {
        // Handle errors
        echo "Request failed: " . $e->getMessage() . "\n";
        
        // Check if there is a response body with error details
        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $errorBody = (string) $response->getBody();
            echo "Error Response: " . $errorBody . "\n";  // Echo error response for debugging
        }
        return false;
    }
}

// Function to send SMS Notification to the member
function sendSMSNotification($member_id, $amount, $payment_type, $payment_date) {
    global $conn;

    // Fetch member's phone number from the database
    $sql = "SELECT phonenumber, firstname, lastname FROM members WHERE memID = '$member_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $phone_number = $row['phonenumber'];
        $first_name = $row['firstname'];
        $last_name = $row['lastname'];
        
        // Compose the SMS message
        $message = "Dear $first_name $last_name, you have successfully paid $payment_type of GHS $amount on $payment_date. Thank you for your contribution.";

        // Send SMS
        return sendSMS($phone_number, $message);
    } else {
        echo "Member not found.";
        return false;
    }
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
        <p>
        <label for="amount_tithe">Tithe Amount</label>
        </p>
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
        <!--
    <h2>Recent Payment Records</h2>
    <table>
        <tr>
            <th>Member</th>
            <th>Phone Number</th>
            <th>Amount</th>
            <th>Payment Type</th>
            <th>Payment Date</th>
        </tr> -->
       // <?php
        // Fetch the 6 most recent payment records

        
        //$sql = "SELECT tw.id, m.firstname, m.lastname, m.phonenumber, tw.amount, tw.payment_type, tw.payment_date 
          //      FROM tithes_welfare tw 
            //    JOIN members m ON tw.member_id = m.memID
              //  ORDER BY tw.payment_date DESC LIMIT 6";
       // $result = $conn->query($sql);
        
      //  while ($row = $result->fetch_assoc()) {
        //    echo "<tr>
          //          <td>{$row['firstname']} {$row['lastname']}</td>
              //      <td>{$row['phonenumber']}</td>
            //        <td>{$row['amount']}</td>
               //     <td>{$row['payment_type']}</td>
             //       <td>{$row['payment_date']}</td>
             //     </tr>";
      //  } 
     //   ?>
    // </table>
</body>
</html>

<?php $conn->close(); ?>
