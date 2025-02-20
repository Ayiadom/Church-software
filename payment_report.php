<?php
include 'db.php';

$report_type = $_GET['report_type'] ?? '';
$filter = $_GET['filter'] ?? '';
$duration = $_GET['duration'] ?? '';

// Prepare the SQL query based on the selected report type and duration
if ($report_type === 'tithes' || $report_type === 'welfare') {
    $sql = "SELECT 
                t.paymentID, 
                m.firstname, 
                m.lastname, 
                t.amount, 
                t.payment_type, 
                t.payment_date 
            FROM tithes_welfare t
            INNER JOIN members m ON t.member_id = m.memID
            WHERE t.payment_type = ? ";

    // Check if a duration filter is selected (e.g., last 1 month, 2 months, etc.)
    if ($duration) {
        $date_filter = date('Y-m-d', strtotime("-$duration months"));
        $sql .= "AND t.payment_date >= ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $report_type, $date_filter);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $report_type);
    }

    $stmt->execute();
    $payments_result = $stmt->get_result();

    // Calculate the total amount for the selected report and duration
    $total_sql = "SELECT SUM(amount) AS total_amount FROM tithes_welfare WHERE payment_type = ? ";
    if ($duration) {
        $total_sql .= "AND payment_date >= ?";
        $total_stmt = $conn->prepare($total_sql);
        $total_stmt->bind_param("ss", $report_type, $date_filter);
    } else {
        $total_stmt = $conn->prepare($total_sql);
        $total_stmt->bind_param("s", $report_type);
    }
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_amount = $total_row['total_amount'] ?? 0;
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
            <option value="tithes">Tithes</option>
            <option value="welfare">Welfare</option>
        </select>
        
        <!-- Date picker for filtering by date -->
        <input type="date" name="filter" required>
        
        <!-- Duration selection for 1 to 24 months -->
        <select name="duration">
            <option value="">Select Duration (Optional)</option>
            <?php for ($i = 1; $i <= 24; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?> Month<?php echo $i > 1 ? 's' : ''; ?></option>
            <?php endfor; ?>
        </select>
        
        <button type="submit">View Report</button>
    </form>

    <?php if (($report_type === 'tithes' || $report_type === 'welfare') && isset($payments_result)): ?>
        <h2><?php echo ucfirst($report_type); ?> Made on <?php echo htmlspecialchars($filter); ?></h2>
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
            <h3>Total <?php echo ucfirst($report_type); ?> Collected: GHS <?php echo number_format($total_amount, 2); ?></h3>
        <?php else: ?>
            <p>No <?php echo $report_type; ?> found for the specified date or duration.</p>
        <?php endif; ?>
    <?php endif; ?>

</body>
</html>

<?php
if (isset($stmt)) {
    $stmt->close();
}
if (isset($total_stmt)) {
    $total_stmt->close();
}
$conn->close();
?>
