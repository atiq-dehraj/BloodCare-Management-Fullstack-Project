<?php
session_start();
require_once 'db_connect.php';
require_once 'mailer.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Grab the data using YOUR exact HTML name attributes
    $full_name = trim($_POST['donorName']);
    $email = trim($_POST['donorEmail']);
    $raw_password = $_POST['donorPassword'];
    $phone = trim($_POST['donorPhone']);
    $blood_type = $_POST['bloodType'];

    // 2. Hash the password securely
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
    $role = 'donor';
    $status = 'active';

    // 3. Start Database Transaction
    $conn->begin_transaction();

    try {
        // Step A: Create the Login Account in 'users' table
        $stmt1 = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)");
        $stmt1->bind_param("sssss", $full_name, $email, $hashed_password, $role, $status);
        $stmt1->execute();

        // Get the newly created user_id
        $new_user_id = $conn->insert_id;

        // Step B: Create Medical Profile in 'donors' table linked to user_id
        $stmt2 = $conn->prepare("INSERT INTO donors (name, phone, blood_type, user_id) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("sssi", $full_name, $phone, $blood_type, $new_user_id);
        $stmt2->execute();

        // Commit to database
        $conn->commit();

        // --- SEND DONOR WELCOME EMAIL ---
        $subject = "Welcome to BloodCare - Registration Successful";
        $html_body = "
            <h2>Welcome to the BloodCare Network, $full_name!</h2>
            <p>Thank you for registering as a blood donor. Your willingness to donate will save lives.</p>
            <p>You can now log in to the donor portal at any time to track your donations and download your medical reports.</p>
            <br>
            <p>Thank you for being a hero,</p>
            <p><em>The BloodCare Team</em></p>
        ";
        
        // Fire the email engine and check if it worked!
        $email_sent = sendBloodCareEmail($email, $full_name, $subject, $html_body);
        
        if ($email_sent) {
            echo "<script>alert('Registration Successful! Welcome Email has been sent.'); window.location.href='../login_system/login.html';</script>";
        } else {
            // If it fails, it will tell you!
            echo "<script>alert('Registration Successful, BUT the email failed to send. Check backend/mail_error_log.txt for details.'); window.location.href='../login_system/login.html';</script>";
        }
        // --------------------------------

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        
        if(strpos($exception->getMessage(), 'Duplicate entry') !== false) {
             echo "<script>alert('Error: This email address is already registered.'); window.history.back();</script>";
        } else {
             echo "<script>alert('System Error: Could not complete registration.'); window.history.back();</script>";
        }
    }

    if (isset($stmt1)) $stmt1->close();
    if (isset($stmt2)) $stmt2->close();
}
$conn->close();
?>