<?php
session_start();
require_once 'db_connect.php';

// Security Check: Only Hospitals can request blood
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    die("Unauthorized Access.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Grab the form data
    $blood_type = $_POST['bloodType'];
    $urgency = $_POST['urgency'];
    $location = trim($_POST['location']);
    $contact_person = trim($_POST['contactPerson']);
    $user_id = $_SESSION['user_id']; // Who is logged in?

    // 1. First, find the hospital_id linked to this user account
    $stmt1 = $conn->prepare("SELECT hospital_id FROM hospitals WHERE user_id = ?");
    $stmt1->bind_param("i", $user_id);
    $stmt1->execute();
    $result = $stmt1->get_result();
    
    if ($result->num_rows === 0) {
        die("Error: No hospital profile found for this user.");
    }
    
    $hospital = $result->fetch_assoc();
    $hospital_id = $hospital['hospital_id'];
    $stmt1->close();

    // 2. Generate a unique Request ID (e.g., REQ-7B29F)
    $request_id = "REQ-" . strtoupper(substr(uniqid(), -5));

    // 3. Insert the request into the database
    $stmt2 = $conn->prepare("INSERT INTO requests (request_id, hospital_id, blood_type_required, urgency, delivery_location, contact_person, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt2->bind_param("sissss", $request_id, $hospital_id, $blood_type, $urgency, $location, $contact_person);

    if ($stmt2->execute()) {
        echo "<script>alert('Request Submitted Successfully! Your Reference ID is: $request_id'); window.location.href='../hospital_portal/hospital_dashboard.php';</script>";
    } else {
        echo "<script>alert('Database Error: Could not submit request.'); window.history.back();</script>";
    }

    $stmt2->close();
}
$conn->close();
?>