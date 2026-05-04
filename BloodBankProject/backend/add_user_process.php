<?php
session_start();
require_once 'db_connect.php';
require_once 'mailer.php'; // Add this line!

// Security: Only System Admins can do this
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized Access.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['new_name']);
    $email = trim($_POST['new_email']);
    $raw_password = $_POST['new_password'];
    $role = $_POST['new_role'];
    $status = 'active';
    $admin_id = $_SESSION['user_id'];

    // Secure the password!
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

    $conn->begin_transaction();

    try {
        // 1. Insert the new user
        $stmt1 = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt1->bind_param("sssss", $name, $email, $hashed_password, $role, $status);
        $stmt1->execute();
        $new_user_id = $conn->insert_id;
        $stmt1->close();

        // 2. Log the action in the system logs
        $action_desc = "Created new $role account for $name (ID: $new_user_id).";
        $stmt2 = $conn->prepare("INSERT INTO system_logs (action_description, user_id) VALUES (?, ?)");
        $stmt2->bind_param("si", $action_desc, $admin_id);
        $stmt2->execute();
        $stmt2->close();

      
        $safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $conn->commit();
        
        // --- SEND WELCOME EMAIL ---
        $subject = "Welcome to BloodCare - Your Account Details";
        $html_body = "
            <h2>Welcome to the BloodCare Network, $name!</h2>
            <p>An administrator has created a <strong>$role</strong> account for you.</p>
            <p>You can now log in to the portal using the following credentials:</p>
            <ul>
                <li><strong>Email:</strong> $email</li>
                <li><strong>Temporary Password:</strong> $raw_password</li>
            </ul>
            <p>Please log in and speak to the System Administrator to update your password.</p>
            <br>
            <p>Thank you for helping us save lives,</p>
            <p><em>The BloodCare IT Team</em></p>
        ";
        
        // Fire the email engine!
        sendBloodCareEmail($email, $name, $subject, $html_body);
        // --------------------------

        $safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        echo "<script>alert('Success! $safe_name has been registered and an email has been sent.'); window.location.href='../admin_portal/admin_dashboard.php';</script>";

   } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        // Check if the email already exists
        if(strpos($e->getMessage(), 'Duplicate entry') !== false) {
             $safe_email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
             echo "<script>alert('Error: The email $safe_email is already in use.'); window.history.back();</script>";
        } else {
             echo "<script>alert('Database Error: Could not create user.'); window.history.back();</script>";
        }
    }
}
$conn->close();
?>