<?php
include 'db.php';

// Handle Create (INSERT)
if (isset($_POST['create'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $dayborn = $_POST['dayborn'];  // Now selecting from dropdown
    $phonenumber = $_POST['phonenumber'];
    
    $sql = "INSERT INTO members (firstname, lastname, dayborn, phonenumber) 
            VALUES ('$firstname', '$lastname', '$dayborn', '$phonenumber')";
    
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle Update (UPDATE)
if (isset($_POST['update'])) {
    $memID = $_POST['memID'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $dayborn = $_POST['dayborn'];  // Now selecting from dropdown
    $phonenumber = $_POST['phonenumber'];
    
    $sql = "UPDATE members 
            SET firstname='$firstname', lastname='$lastname', dayborn='$dayborn', phonenumber='$phonenumber' 
            WHERE memID=$memID";
    
    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle Delete (DELETE)
if (isset($_POST['delete'])) {
    $memID = $_POST['memID'];
    
    $sql = "DELETE FROM members WHERE memID=$memID";
    
    if ($conn->query($sql) === TRUE) {
        echo "Record deleted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Fetch all members for display
$sql = "SELECT * FROM members";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Members Management</h1>

    <!-- Button to navigate to view.php -->
    <a href="view.php">
        <button>View Members</button>
    </a>
    
    
    <form method="POST">
        <input type="hidden" name="memID" id="memID" value="">
        <input type="text" name="firstname" id="firstname" placeholder="First Name" required>
        <input type="text" name="lastname" id="lastname" placeholder="Last Name" required>

        <!-- Day Born Dropdown -->
        <select name="dayborn" id="dayborn" required>
            <option value="" disabled selected>Select Day of the Week</option>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
            <option value="Sunday">Sunday</option>
        </select>

        <input type="text" name="phonenumber" id="phonenumber" placeholder="Phone Number" required>
        
        <button type="submit" name="create">Create</button>
        <button type="submit" name="update">Update</button>
        <button type="submit" name="delete">Delete</button>
    </form>

    <h2>Member List</h2>
    <table>
        <tr>
            <th>MemID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Day Born</th>
            <th>Phone Number</th>
            <th>Action</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr id="row<?php echo $row['memID']; ?>">
                <td><?php echo $row['memID']; ?></td>
                <td><?php echo $row['firstname']; ?></td>
                <td><?php echo $row['lastname']; ?></td>
                <td><?php echo $row['dayborn']; ?></td>
                <td><?php echo $row['phonenumber']; ?></td>
                <td>
                    <button onclick="editMember(<?php echo $row['memID']; ?>)">Edit</button>
                    <button onclick="deleteMember(<?php echo $row['memID']; ?>)">Delete</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <script>
        function editMember(memID) {
            // Fetch the member data to fill the form
            let row = document.querySelector(`#row${memID}`);
            document.getElementById("memID").value = memID;
            document.getElementById("firstname").value = row.cells[1].textContent;
            document.getElementById("lastname").value = row.cells[2].textContent;
            document.getElementById("dayborn").value = row.cells[3].textContent; // Set dropdown value
            document.getElementById("phonenumber").value = row.cells[4].textContent;
        }

        function deleteMember(memID) {
            if (confirm("Are you sure you want to delete this member?")) {
                document.getElementById("memID").value = memID;
                document.querySelector('form').submit();
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
