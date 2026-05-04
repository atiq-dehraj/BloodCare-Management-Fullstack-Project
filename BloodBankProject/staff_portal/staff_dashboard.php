<?php
// Start the session to access the login data
session_start();

require_once '../backend/db_connect.php';
// Check if the user is logged in AND if their role is actually 'staff'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    // If they aren't logged in as staff, kick them back to the login page
    header("Location: ../login_system/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
...


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | BloodCare</title>
    <link rel="stylesheet" href="staff.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="dashboard-nav">
        <div class="logo"><i class="fas fa-user-nurse"></i> Staff Portal</div>
        <div class="user-info">
            <span>Logged in as: Staff-042</span>
            <a href="../backend/logout.php" class="logout-btn">Logout</a>
            <!-- <a href="../login_system/login.html" class="logout-btn">Logout</a> -->
        </div>
    </nav>

    <div class="dashboard-layout">
        <aside class="sidebar">
            <ul id="menu">
                <li class="active" data-target="overview"><i class="fas fa-chart-line"></i> Overview</li>
                <li data-target="log-donation"><i class="fas fa-syringe"></i> Log Donation</li>
                <li data-target="storage-units"><i class="fas fa-snowflake"></i> Storage Units</li>
                <li data-target="pending-requests"><i class="fas fa-file-medical-alt"></i> Fulfill Requests</li>
            </ul>
        </aside>

        <main class="main-content">
            
            <section id="overview" class="view-section active">
                <h2>Shift Overview</h2>
                <div class="stats-grid">
                    <div class="stat-box">
                        <h3>Donations Today</h3>
                        <div class="stat-value">14</div>
                    </div>
                    <div class="stat-box alert">
                        <h3>Temp Alerts</h3>
                        <div class="stat-value">0</div>
                    </div>
                    <div class="stat-box">
                        <h3>Pending Requests</h3>
                        <div class="stat-value">2</div>
                    </div>
                </div>
            </section>

            <section id="log-donation" class="view-section hidden">
                <h2>Log New Blood Donation</h2>
                <div class="form-card">
                    <form id="donationForm" action="../backend/log_donation.php" method="POST">
                        <div class="form-row">
                            <div class="input-group">
                                <label>Donor ID (Integer) *</label>
                                <input type="number" id="donorId" name="donorId" required placeholder="e.g., 1">
                            </div>
                            <div class="input-group">
                                <label>Blood Type *</label>
                                <select id="bagBloodType" name="bagBloodType" required>
                                    <option value="" disabled selected>Verify Type</option>
                                    <option value="A+">A+</option><option value="A-">A-</option>
                                    <option value="B+">B+</option><option value="B-">B-</option>
                                    <option value="O+">O+</option><option value="O-">O-</option>
                                    <option value="AB+">AB+</option><option value="AB-">AB-</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="input-group">
                                <label>Volume (ml) *</label>
                               <input type="number" id="volume" name="volume" required value="450" min="100" max="600">
                            </div>
                            <div class="input-group">
                                <label>Assign Storage Unit *</label>
                                <select id="storageUnit" name="storageUnit" required>
                                    <option value="" disabled selected>Select Fridge/Freezer</option>
                                    <option value="Fridge-A">Fridge-A (Safe Temp)</option>
                                    <option value="Fridge-B">Fridge-B (Safe Temp)</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="primary-btn">Save & Generate Bag ID</button>
                    </form>
                </div>
            </section>

            <section id="storage-units" class="view-section hidden">
                <h2>Manage Storage Units</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Unit ID</th>
                            <th>Location</th>
                            <th>Current Temp</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Fridge-A</td>
                            <td>Main Lab</td>
                            <td>4°C</td>
                            <td><span class="badge safe">Optimal</span></td>
                            <td><button class="action-btn" onclick="updateTemp('Fridge-A')">Update Temp</button></td>
                        </tr>
                        <tr>
                            <td>Fridge-B</td>
                            <td>Surgical Wing</td>
                            <td>8°C</td>
                            <td><span class="badge warning">Warning</span></td>
                            <td><button class="action-btn" onclick="updateTemp('Fridge-B')">Update Temp</button></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section id="pending-requests" class="view-section hidden">
                <h2>Pending Hospital Requests</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Hospital</th>
                            <th>Blood Type</th>
                            <th>Urgency</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query the database for Pending requests, joining with hospitals table
                        $sql = "SELECT r.request_id, h.name AS hospital_name, r.blood_type_required, r.urgency 
                                FROM requests r
                                JOIN hospitals h ON r.hospital_id = h.hospital_id
                                WHERE r.status = 'Pending'
                                ORDER BY 
                                    CASE r.urgency 
                                        WHEN 'Critical' THEN 1 
                                        WHEN 'Urgent' THEN 2 
                                        ELSE 3 
                                    END ASC";
                        
                        $result = $conn->query($sql);

                        // Check if there are any requests
                        if ($result->num_rows > 0) {
                            // Loop through each request and create an HTML table row
                            while($row = $result->fetch_assoc()) {
                                // Determine badge color class based on urgency
                                $badge_class = 'routine'; // default
                                if ($row['urgency'] === 'Critical') $badge_class = 'critical';
                                if ($row['urgency'] === 'Urgent') $badge_class = 'warning';

                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['request_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['hospital_name']) . "</td>";
                                echo "<td><span style='font-weight: bold; color: #e74c3c;'>" . htmlspecialchars($row['blood_type_required']) . "</span></td>";
                                echo "<td><span class='badge {$badge_class}'>" . htmlspecialchars($row['urgency']) . "</span></td>";
                                // A form button to trigger the dispatch process
                                echo "<td>
                                        <form action='../backend/process_fulfillment.php' method='POST' style='margin:0;'>
                                            <input type='hidden' name='request_id' value='" . $row['request_id'] . "'>
                                            <input type='hidden' name='blood_type' value='" . $row['blood_type_required'] . "'>
                                            <button type='submit' class='action-btn assign' style='background-color: #3498db; color: white;'>Assign Bag</button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            // What to show if the database is empty
                            echo "<tr><td colspan='5' style='text-align: center; padding: 20px; color: #7f8c8d;'>No pending requests. Inventory is secure.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

        </main>
    </div>
    <script src="staff.js"></script>
</body>
</html>