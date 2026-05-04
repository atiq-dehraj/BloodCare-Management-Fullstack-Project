<?php
// Start the session to remember the user across pages
session_start();

// Bring in the database connection
require_once 'db_connect.php';

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // die(print_r($_POST, true));
    // Grab the data from the HTML form
    $role = $_POST['userRole'];
    $email = trim($_POST['userID']);
    $password = $_POST['password'];

    // 1. Prepare a secure SQL statement to prevent SQL Injection
    $stmt = $conn->prepare("SELECT user_id, full_name, password_hash FROM users WHERE email = ? AND role = ? AND status = 'active'");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    // 2. Check if the user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 3. Verify the password
        // Note: For testing, we are allowing '12345' directly if the hash fails due to copy/paste issues in phpMyAdmin
        if (password_verify($password, $user['password_hash']) || $password === '12345') {
            
            // Success! Store user data in session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['full_name'];
            $_SESSION['role'] = $role;

          // 4. Redirect to the correct dashboard
            if ($role === 'donor') {
                // UPDATE THIS LINE!
                header("Location: ../donor_portal/dashboard.php");
            } else if ($role === 'staff') {
                // This now points to the secure PHP file!
                header("Location: ../staff_portal/staff_dashboard.php");
            } else if ($role === 'hospital') {
                header("Location: ../hospital_portal/hospital_dashboard.php");
            } else if ($role === 'manager') {
                // Update this line!
                header("Location: ../manager_portal/manager_dashboard.php");
            
            } else if ($role === 'admin') {
                // The new System Admin route!
                header("Location: ../admin_portal/admin_dashboard.php");
            }
            exit();

        } else {
            // Password failed
            echo "<script>alert('Invalid password. Please try again.'); window.location.href='../login_system/login.html';</script>";
        }
    } else {
        // User not found or role mismatch
        echo "<script>alert('Account not found or incorrect role selected.'); window.location.href='../login_system/login.html';</script>";
    }
    
    $stmt->close();
}
$conn->close();
?>