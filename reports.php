<?php
include 'db.php';

$report_type = $_GET['report_type'] ?? '';
$filter = $_GET['filter'] ?? '';

if ($report_type === 'members') {
    // Fetch members born on a specific day
    $sql = "SELECT * FROM members WHERE dayborn = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filter);
    $stmt->execute();
    $members_result = $stmt->get_result();
} elseif ($report_type === 'payments') {
    // Fetch payments made on a specific date
    $sql = "SELECT 
                t.paymentID, 
                m.firstname, 
                m.lastname, 
                t.amount, 
                t.payment_type, 
                t.payment_date 
            FROM tithes_welfare t
            INNER JOIN members m ON t.member_id = m.memID
            WHERE t.payment_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filter);
    $stmt->execute();
    $payments_result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        h1 {
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 20px;
        }
        input, button, select {
            padding: 10px;
            margin: 5px;
            width: 200px;
            box-sizing: border-box;
        }
        button {
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Reports Dashboard</h1>

    <form method="GET">
        <select name="report_type" required>
            <option value="">Select Report</option>
            <option value="members">Members Born on Specific Day</option>
            <option value="payments">Payments Made on Specific Date</option>
        </select>
        <input type="text" name="filter" placeholder="Enter Day (e.g., Monday) or Date (YYYY-MM-DD)" required>
        <button type="submit">View Report</button>
    </form>

    <?php if ($report_type === 'members' && isset($members_result)): ?>
        <h2>Members Born on <?php echo htmlspecialchars($filter); ?></h2>
        <?php if ($members_result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Member ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Day Born</th>
                    <th>Phone Number</th>
                </tr>
                <?php while ($row = $members_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['memID']; ?></td>
                        <td><?php echo $row['firstname']; ?></td>
                        <td><?php echo $row['lastname']; ?></td>
                        <td><?php echo $row['dayborn']; ?></td>
                        <td><?php echo $row['phonenumber']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No members found for the specified day.</p>
        <?php endif; ?>
    <?php elseif ($report_type === 'payments' && isset($payments_result)): ?>
        <h2>Payments Made on <?php echo htmlspecialchars($filter); ?></h2>
        <?php if ($payments_result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Payment ID</th>
                    <th>Member Name</th>
                    <th>Amount</th>
                    <th>Payment Type</th>
                    <th>Payment Date</th>
                </tr>
                <?php while ($row = $payments_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['paymentID']; ?></td>
                        <td><?php echo $row['firstname'] . " " . $row['lastname']; ?></td>
                        <td><?php echo $row['amount']; ?></td>
                        <td><?php echo $row['payment_type']; ?></td>
                        <td><?php echo $row['payment_date']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No payments found for the specified date.</p>
        <?php endif; ?>
    <?php endif; ?>

</body>
</html>

<?php
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>
