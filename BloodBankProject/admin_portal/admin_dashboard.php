<?php
session_start();
require_once '../backend/db_connect.php';

// Security: Only System Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login_system/login.html");
    exit();
}

// Fetch all users in the system
$users_sql = "SELECT user_id, full_name, email, role, status FROM users ORDER BY user_id DESC";
$users_result = $conn->query($users_sql);

// Fetch system logs
$logs_sql = "SELECT log_id, action_description, log_date FROM system_logs ORDER BY log_date DESC LIMIT 50";
$logs_result = $conn->query($logs_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Admin | BloodCare</title>
    <link rel="stylesheet" href="../staff_portal/staff.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* A little extra CSS for the delete button */
        .btn-danger { background-color: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.85em; }
        .btn-danger:hover { background-color: #c0392b; }
    </style>
</head>
<body>
    <nav class="dashboard-nav" style="background-color: #34495e;">
        <div class="logo"><i class="fas fa-server"></i> System Administration</div>
        <div class="user-info">
            <span style="color: white;">Super Admin</span>
            <a href="../backend/logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="dashboard-layout">
        <aside class="sidebar" style="background-color: #2c3e50;">
            <ul id="menu">
                <li class="active" data-target="manage-users-view"><i class="fas fa-users-cog"></i> Manage Users</li>
                <li data-target="add-user-view"><i class="fas fa-user-plus"></i> Add New User</li>
                <li data-target="logs-view"><i class="fas fa-terminal"></i> System Logs</li>
            </ul>
        </aside>

        <main class="main-content">
            
            <section id="manage-users-view" class="view-section active">
                <h2>Platform User Management</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email Account</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($users_result->num_rows > 0) {
                            while($row = $users_result->fetch_assoc()) {
                                $role_color = '#95a5a6';
                                if($row['role'] == 'admin') $role_color = '#e74c3c';
                                if($row['role'] == 'manager') $role_color = '#8e44ad';
                                if($row['role'] == 'hospital') $role_color = '#3498db';
                                if($row['role'] == 'staff') $role_color = '#2ecc71';
                                if($row['role'] == 'donor') $role_color = '#f39c12';

                                echo "<tr>";
                                echo "<td>" . $row['user_id'] . "</td>";
                                echo "<td><strong>" . htmlspecialchars($row['full_name']) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td><span style='background-color: {$role_color}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; text-transform: uppercase;'>" . htmlspecialchars($row['role']) . "</span></td>";
                                
                                // The Delete Button (Disables if trying to delete yourself!)
                                if ($row['user_id'] == $_SESSION['user_id']) {
                                    echo "<td><button class='btn-danger' disabled style='opacity: 0.5;'>Cannot Delete Self</button></td>";
                                } else {
                                    echo "<td>
                                            <form action='../backend/delete_user.php' method='POST' style='margin:0;' onsubmit='return confirm(\"Are you sure you want to permanently delete this user?\");'>
                                                <input type='hidden' name='delete_id' value='" . $row['user_id'] . "'>
                                                <button type='submit' class='btn-danger'>Delete User</button>
                                            </form>
                                          </td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No users found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

            <section id="add-user-view" class="view-section hidden">
                <h2>Register Internal Staff</h2>
                <div class="form-card" style="max-width: 600px;">
                    <form action="../backend/add_user_process.php" method="POST">
                        <div class="input-group" style="margin-bottom: 15px;">
                            <label>Full Name *</label>
                            <input type="text" name="new_name" required style="width: 100%; padding: 8px;">
                        </div>
                        <div class="input-group" style="margin-bottom: 15px;">
                            <label>Email Address *</label>
                            <input type="email" name="new_email" required style="width: 100%; padding: 8px;">
                        </div>
                        <div class="input-group" style="margin-bottom: 15px;">
                            <label>Temporary Password *</label>
                            <input type="password" name="new_password" required style="width: 100%; padding: 8px;">
                        </div>
                        <div class="input-group" style="margin-bottom: 20px;">
                            <label>Assign Role *</label>
                            <select name="new_role" required style="width: 100%; padding: 8px;">
                                <option value="" disabled selected>Select Role</option>
                                <option value="staff">Healthcare Staff</option>
                                <option value="manager">Blood Bank Manager</option>
                                <option value="admin">System Administrator</option>
                            </select>
                        </div>
                        <button type="submit" class="primary-btn" style="background-color: #2ecc71;">Create Account</button>
                    </form>
                </div>
            </section>

            <section id="logs-view" class="view-section hidden">
                <h2>System Activity Logs</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Timestamp</th>
                            <th>Action Performed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($logs_result->num_rows > 0) {
                            while($log = $logs_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>#" . $log['log_id'] . "</td>";
                                echo "<td>" . date("M d, Y H:i:s", strtotime($log['log_date'])) . "</td>";
                                echo "<td>" . htmlspecialchars($log['action_description']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No system logs recorded yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

        </main>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const menuItems = document.querySelectorAll('.sidebar li');
            const sections = document.querySelectorAll('.view-section');

            sections.forEach(sec => {
                if (sec.id !== 'manage-users-view') sec.style.display = 'none';
                else sec.style.display = 'block';
            });

            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    menuItems.forEach(li => li.classList.remove('active'));
                    this.classList.add('active');

                    sections.forEach(sec => sec.style.display = 'none');
                    
                    const targetId = this.getAttribute('data-target');
                    if (document.getElementById(targetId)) {
                        document.getElementById(targetId).style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>
</html>