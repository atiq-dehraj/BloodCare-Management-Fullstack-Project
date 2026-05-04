<?php
session_start();
require_once '../backend/db_connect.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    header("Location: ../login_system/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the Hospital's ID and Name
$stmt = $conn->prepare("SELECT hospital_id, name FROM hospitals WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$hospital_result = $stmt->get_result();
$hospital_data = $hospital_result->fetch_assoc();
$hospital_id = $hospital_data['hospital_id'];
$hospital_name = $hospital_data['name'];
$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Portal | BloodCare</title>
    <link rel="stylesheet" href="hospital.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="dashboard-nav">
        <div class="logo"><i class="fas fa-hospital"></i> Hospital Administrator Portal</div>
        <div class="user-info">
            <span><?php echo htmlspecialchars($hospital_name); ?></span>
           <a href="../backend/logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="dashboard-layout">
        <aside class="sidebar">
            <ul id="menu">
                <li class="active" data-target="overview"><i class="fas fa-chart-pie"></i> Overview</li>
                <li data-target="submit-request"><i class="fas fa-paper-plane"></i> Submit Request</li>
                <li data-target="track-requests"><i class="fas fa-truck-medical"></i> Track Requests</li>
            </ul>
        </aside>

        <main class="main-content">
            
            <section id="overview" class="view-section active">
                <h2>Hospital Dashboard</h2>
                <div class="stats-grid">
                    <div class="stat-box">
                        <h3>Active Requests</h3>
                        <div class="stat-value">3</div>
                    </div>
                    <div class="stat-box highlight-blue">
                        <h3>Fulfilled This Month</h3>
                        <div class="stat-value">12</div>
                    </div>
                </div>
            </section>

            <section id="submit-request" class="view-section hidden">
                <h2>Submit Blood Request</h2>
                <p style="margin-bottom: 20px; color: #666;">Please provide accurate details to ensure rapid fulfillment of your request.</p>
                <div class="form-card">
                     
                   <form id="requestForm" action="../backend/request_blood.php" method="POST">
                        <div class="form-row">
                            <div class="input-group">
                                <label>Required Blood Type *</label>
                                <select name="bloodType" id="reqBloodType" required>
                                    <option value="">Select Type</option>
                                    <option value="A+">A+</option><option value="A-">A-</option>
                                    <option value="B+">B+</option><option value="B-">B-</option>
                                    <option value="O+">O+</option><option value="O-">O-</option>
                                    <option value="AB+">AB+</option><option value="AB-">AB-</option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Urgency Level *</label>
                                <select name="urgency" id="reqUrgency" required>
                                    <option value="">Select Urgency</option>
                                    <option value="Routine">Routine (24-48 Hours)</option>
                                    <option value="Urgent">Urgent (2-6 Hours)</option>
                                    <option value="Critical">Critical (Immediate)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="input-group">
                                <label>Delivery Location (Ward/Dept) *</label>
                                <input type="text" name="location" id="reqLocation" required placeholder="e.g., ER Trauma, Ward 3">
                            </div>
                            <div class="input-group">
                                <label>Contact Person (Receiving Doctor/Nurse) *</label>
                                <input type="text" name="contactPerson" id="reqContact" required placeholder="Dr. Smith / Nurse Jane">
                            </div>
                        </div>
                        <button type="submit" class="primary-btn" style="background-color: #e74c3c;">Submit Request to Blood Bank</button>
                    </form>
                </div>
            </section>

            <section id="track-requests" class="view-section hidden">
                <h2>Track Active Requests</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Blood Type</th>
                            <th>Urgency</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="requestTableBody">
                        <?php
                        // Fetch all requests made by THIS specific hospital
                        $stmt2 = $conn->prepare("SELECT request_id, blood_type_required, urgency, delivery_location, status FROM requests WHERE hospital_id = ? ORDER BY request_id DESC");
                        $stmt2->bind_param("i", $hospital_id);
                        $stmt2->execute();
                        $requests_result = $stmt2->get_result();

                        if ($requests_result->num_rows > 0) {
                            while($row = $requests_result->fetch_assoc()) {
                                // Set badge colors based on urgency
                                $urgency_class = 'routine';
                                if ($row['urgency'] === 'Critical') $urgency_class = 'critical';
                                if ($row['urgency'] === 'Urgent') $urgency_class = 'warning';

                                // Set badge colors based on status
                                $status_color = ($row['status'] === 'Fulfilled') ? 'background-color: #2ecc71; color: white;' : 'background-color: #f39c12; color: white;';

                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['request_id']) . "</td>";
                                echo "<td><strong>" . htmlspecialchars($row['blood_type_required']) . "</strong></td>";
                                echo "<td><span class='badge {$urgency_class}'>" . htmlspecialchars($row['urgency']) . "</span></td>";
                                echo "<td>" . htmlspecialchars($row['delivery_location']) . "</td>";
                                echo "<td><span style='padding: 5px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; {$status_color}'>" . htmlspecialchars($row['status']) . "</span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align: center; padding: 20px;'>No requests found.</td></tr>";
                        }
                        $stmt2->close();
                        ?>
                    </tbody>
                </table>
            </section>

        </main>
    </div>
    <script src="hospital.js"></script>
</body>
</html>