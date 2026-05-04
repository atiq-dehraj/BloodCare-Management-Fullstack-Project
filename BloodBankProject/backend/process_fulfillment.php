<?php
session_start();
require_once 'db_connect.php';

// Security Check: Only Staff can dispatch blood
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    die("Unauthorized Access.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Grab the data sent by the hidden inputs in our Staff Dashboard table
    $request_id = $_POST['request_id'];
    $blood_type = $_POST['blood_type'];

    // 1. Search for the oldest available bag of the requested type
    // ORDER BY expiration_date ASC ensures we use the oldest blood first!
    $stmt1 = $conn->prepare("SELECT bag_id FROM blood_bags WHERE blood_type = ? AND status = 'Available' ORDER BY expiration_date ASC LIMIT 1");
    $stmt1->bind_param("s", $blood_type);
    $stmt1->execute();
    $result = $stmt1->get_result();

    // 2. Check if we actually have that blood type in stock
    // 2. Check if we actually have that blood type in stock
    if ($result->num_rows === 0) {
        $clean_type = htmlspecialchars($blood_type);
        echo "<script>
            alert('CRITICAL: Insufficient Inventory! No Available bags of type $clean_type found in storage.'); 
            window.location.replace('../staff_portal/staff_dashboard.php');
        </script>";
        $stmt1->close();
        $conn->close();
        exit(); // Force the script to stop running immediately
    }

    // We found a bag! Grab its ID.
    $bag = $result->fetch_assoc();
    $bag_id = $bag['bag_id'];
    $stmt1->close();

    // 3. Begin Transaction to update both tables safely
    $conn->begin_transaction();

    try {
        // Step A: Update the blood bag status to 'Dispatched'
        $stmt2 = $conn->prepare("UPDATE blood_bags SET status = 'Dispatched' WHERE bag_id = ?");
        $stmt2->bind_param("s", $bag_id);
        $stmt2->execute();

        // Step B: Update the hospital request status to 'Fulfilled'
        $stmt3 = $conn->prepare("UPDATE requests SET status = 'Fulfilled' WHERE request_id = ?");
        $stmt3->bind_param("s", $request_id);
        $stmt3->execute();

        // Commit the changes!
        $conn->commit();

        echo "<script>alert('Success! Bag $bag_id has been dispatched for Request $request_id.'); window.location.href='../staff_portal/staff_dashboard.php';</script>";

    } catch (mysqli_sql_exception $exception) {
        // Rollback if anything goes wrong
        $conn->rollback();
        echo "<script>alert('Database Error: Could not process fulfillment.'); window.location.href='../staff_portal/staff_dashboard.php';</script>";
    }

    if(isset($stmt2)) $stmt2->close();
    if(isset($stmt3)) $stmt3->close();
}
$conn->close();
?>