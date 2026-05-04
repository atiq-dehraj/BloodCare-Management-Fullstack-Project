<?php
session_start();
require_once 'db_connect.php';
require_once 'mailer.php'; // Add this line!

// Security: Only System Admins can do this
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized Access.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $delete_id = $_POST['delete_id'];
    $admin_id = $_SESSION['user_id'];

    // Double-check they aren't trying to delete themselves via inspect element tricks
    if ($delete_id == $admin_id) {
        die("Critical Error: You cannot delete your own admin account.");
    }

    $conn->begin_transaction();

    try {
        // 1. Get the user's name, EMAIL, and role FIRST so we can put it in the log and send the email
        // THE FIX: Added 'email' to the SELECT statement
        $stmt1 = $conn->prepare("SELECT full_name, email, role FROM users WHERE user_id = ?");
        $stmt1->bind_param("i", $delete_id);
        $stmt1->execute();
        $result = $stmt1->get_result();
        if($result->num_rows === 0) throw new Exception("User not found.");
        $user_info = $result->fetch_assoc();
        $stmt1->close();

        // 2. Delete the user
        $stmt2 = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt2->bind_param("i", $delete_id);
        $stmt2->execute();
        $stmt2->close();

        // 3. Log the deletion
        $action_desc = "Permanently deleted " . strtoupper($user_info['role']) . " account: " . $user_info['full_name'] . " (ID: $delete_id).";
        $stmt3 = $conn->prepare("INSERT INTO system_logs (action_description, user_id) VALUES (?, ?)");
        $stmt3->bind_param("si", $action_desc, $admin_id);
        $stmt3->execute();
        $stmt3->close();

        $conn->commit();
        
        // --- SEND DELETION EMAIL ---
        $user_email = $user_info['email'];
        $user_name = $user_info['full_name'];
        $subject = "BloodCare Account Deactivated";
        $html_body = "
            <h2>Account Deactivation Notice</h2>
            <p>Dear $user_name,</p>
            <p>This is an automated notification that your BloodCare portal account has been permanently deactivated by a System Administrator.</p>
            <p>If you believe this was an error, please contact the IT department immediately.</p>
            <br>
            <p>Regards,</p>
            <p><em>The BloodCare IT Team</em></p>
        ";
        
        // Fire the email engine!
        sendBloodCareEmail($user_email, $user_name, $subject, $html_body);
        // ---------------------------

        echo "<script>alert('User has been deleted and notified via email.'); window.location.href='../admin_portal/admin_dashboard.php';</script>";

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        // Foreign Key Safety Net
        if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
             echo "<script>alert('ACTION BLOCKED: This user has active data (donations, blood bags, or requests) linked to their account. They cannot be deleted until their historical data is removed.'); window.location.href='../admin_portal/admin_dashboard.php';</script>";
        } else {
             echo "<script>alert('Database Error: Could not delete user.'); window.location.href='../admin_portal/admin_dashboard.php';</script>";
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('" . $e->getMessage() . "'); window.location.href='../admin_portal/admin_dashboard.php';</script>";
    }
}
$conn->close();
?>