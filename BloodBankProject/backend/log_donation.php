<?php
session_start();
require_once 'db_connect.php';

// Security Check: Only Staff can log donations
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    die("Unauthorized Access.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Grab POST Data
    $donor_id = $_POST['donorId'];
    $blood_type = $_POST['bagBloodType'];
    $volume = $_POST['volume'];
    $unit_id = $_POST['storageUnit'];
    $staff_id = $_SESSION['user_id']; // Captured from the logged-in user

    // 2. Generate System Data
    $bag_id = "BAG-" . strtoupper(uniqid()); // e.g., BAG-64A3F...
    $donation_date = date("Y-m-d");
    $expiration_date = date("Y-m-d", strtotime("+42 days")); // Blood expires in 42 days

    // 3. Begin Database Transaction
    // A transaction ensures that if one insert fails, both fail, preventing orphaned data.
    $conn->begin_transaction();

    try {
        // Step A: Insert into Blood Bags table
        $stmt1 = $conn->prepare("INSERT INTO blood_bags (bag_id, blood_type, expiration_date, status, unit_id) VALUES (?, ?, ?, 'Available', ?)");
        $stmt1->bind_param("ssss", $bag_id, $blood_type, $expiration_date, $unit_id);
        $stmt1->execute();

        // Step B: Insert into Donations table
        $stmt2 = $conn->prepare("INSERT INTO donations (donor_id, bag_id, staff_id, donation_date, volume_ml) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("isisi", $donor_id, $bag_id, $staff_id, $donation_date, $volume);
        $stmt2->execute();

        // If both succeed, commit the changes to the database
        $conn->commit();
        
        echo "<script>alert('Success! Donation Logged.\\nBag ID Generated: $bag_id'); window.location.href='../staff_portal/staff_dashboard.php';</script>";

    } catch (mysqli_sql_exception $exception) {
        // If anything fails (like a bad Donor ID), rollback the changes
        $conn->rollback();
        // Gently handle the foreign key constraint error if the Donor ID doesn't exist
        if(strpos($exception->getMessage(), 'FOREIGN KEY (`donor_id`)') !== false) {
             echo "<script>alert('Error: Donor ID ($donor_id) does not exist in the system. Please register the donor first.'); window.location.href='../staff_portal/staff_dashboard.php';</script>";
        } else {
             echo "<script>alert('Database Error: " . $exception->getMessage() . "'); window.location.href='../staff_portal/staff_dashboard.php';</script>";
        }
    }

    $stmt1->close();
    $stmt2->close();
}
$conn->close();
?>