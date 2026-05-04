<?php
session_start();
require_once '../backend/db_connect.php';

// Security: Only Managers allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: ../login_system/login.html");
    exit();
}

// 1. Get Total Available Inventory Count
$inventory_query = $conn->query("SELECT COUNT(*) as total FROM blood_bags WHERE status = 'Available'");
$total_bags = $inventory_query->fetch_assoc()['total'];

// 2. Get Total Fulfilled Requests
$requests_query = $conn->query("SELECT COUNT(*) as total FROM requests WHERE status = 'Fulfilled'");
$total_fulfilled = $requests_query->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | BloodCare</title>
    <link rel="stylesheet" href="../staff_portal/staff.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="dashboard-nav" style="background-color: #2c3e50;">
        <div class="logo"><i class="fas fa-chart-line"></i> Executive Analytics Portal</div>
        <div class="user-info">
            <span style="color: white;">Director Dashboard</span>
            <a href="../backend/logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="dashboard-layout">
        <aside class="sidebar">
            <ul id="menu">
                <li class="active"><i class="fas fa-chart-pie"></i> System Overview</li>
                <li onclick="alert('Staff management module coming soon!')"><i class="fas fa-users-cog"></i> Manage Staff</li>
            </ul>
        </aside>

        <main class="main-content">
            <section class="view-section active">
                <h2>System Overview</h2>
                
                <div class="stats-grid">
                    <div class="stat-box" style="border-left: 5px solid #3498db;">
                        <h3>Total Bags in Storage</h3>
                        <div class="stat-value"><?php echo $total_bags; ?></div>
                    </div>
                    <div class="stat-box" style="border-left: 5px solid #2ecc71;">
                        <h3>Life-Saving Requests Fulfilled</h3>
                        <div class="stat-value"><?php echo $total_fulfilled; ?></div>
                    </div>
                </div>

                <h3 style="margin-top: 30px; margin-bottom: 15px; color: #2c3e50;">Current Inventory Breakdown</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Blood Type</th>
                            <th>Units Available</th>
                            <th>Inventory Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Here is the magic SQL! GROUP BY lumps matching blood types together and counts them.
                        $sql = "SELECT blood_type, COUNT(*) as unit_count 
                                FROM blood_bags 
                                WHERE status = 'Available' 
                                GROUP BY blood_type 
                                ORDER BY unit_count DESC";
                        
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $count = $row['unit_count'];
                                // Logic for warning badges
                                if ($count <= 2) {
                                    $status_badge = "<span style='color: white; background-color: #e74c3c; padding: 4px 8px; border-radius: 4px;'>Critical Shortage</span>";
                                } else {
                                    $status_badge = "<span style='color: white; background-color: #2ecc71; padding: 4px 8px; border-radius: 4px;'>Healthy</span>";
                                }

                                echo "<tr>";
                                echo "<td><strong>" . htmlspecialchars($row['blood_type']) . "</strong></td>";
                                echo "<td>" . $count . " Bags</td>";
                                echo "<td>" . $status_badge . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' style='text-align: center; color: red; font-weight: bold;'>EMERGENCY: ZERO BLOOD IN STORAGE.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>