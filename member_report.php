<?php
include 'db.php';

$sql = "SELECT * FROM members";
$result = $conn->query($sql);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Member Report</title>
</head>
<body>
    <h1>Member Report</h1>";

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Member ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Day Born</th>
                <th>Phone Number</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['memID'] . "</td>
                <td>" . $row['firstname'] . "</td>
                <td>" . $row['lastname'] . "</td>
                <td>" . $row['dayborn'] . "</td>
                <td>" . $row['phonenumber'] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No members found.";
}

echo "</body>
</html>";

$conn->close();
?>
