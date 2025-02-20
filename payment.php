<?php
include 'db.php';

$sql = "SELECT 
            t.paymentID, 
            m.firstname, 
            m.lastname, 
            t.amount, 
            t.payment_type, 
            t.payment_date 
        FROM tithes_welfare t
        INNER JOIN members m ON t.member_id = m.memID
        ORDER BY t.payment_date DESC";

$result = $conn->query($sql);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Payment Report</title>
</head>
<body>
    <h1>Payment Report</h1>";

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Payment ID</th>
                <th>Member Name</th>
                <th>Amount</th>
                <th>Payment Type</th>
                <th>Payment Date</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['paymentID'] . "</td>
                <td>" . $row['firstname'] . " " . $row['lastname'] . "</td>
                <td>" . $row['amount'] . "</td>
                <td>" . $row['payment_type'] . "</td>
                <td>" . $row['payment_date'] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No payments found.";
}

echo "</body>
</html>";

$conn->close();
?>
