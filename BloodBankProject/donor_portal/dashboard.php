<?php
session_start();

// PREVENT BROWSER CACHING (The Ghost Session Fix)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once '../backend/db_connect.php';
// ... rest of the code

// 1. SECURITY BOUNCER: Only logged-in donors allowed!
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: ../login_system/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch the Donor's Profile Info
$stmt1 = $conn->prepare("SELECT donor_id, name, blood_type FROM donors WHERE user_id = ?");
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$donor_result = $stmt1->get_result();
$donor = $donor_result->fetch_assoc();
$donor_id = $donor['donor_id'];
$stmt1->close();

// 3. Fetch their total donations count (FIXED: Query the 'donations' table)
$stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM donations WHERE donor_id = ?");
$stmt2->bind_param("i", $donor_id);
$stmt2->execute();
$count_result = $stmt2->get_result();
$total_donations = $count_result->fetch_assoc()['total'];
$stmt2->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard | BloodCare</title>
    <link rel="stylesheet" href="donor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="dashboard-nav">
        <div class="logo"><i class="fas fa-heartbeat"></i> BloodCare Portal</div>
        <a href="../backend/logout.php" class="logout-btn">Logout</a>
    </nav>

    <div class="dashboard-layout">
        <aside class="sidebar">
           <ul>
                <li class="active" data-target="dashboard-view"><i class="fas fa-home"></i> Dashboard</li>
                <li data-target="history-view"><i class="fas fa-history"></i> Donation History</li>
                <li data-target="profile-view"><i class="fas fa-user-cog"></i> Profile Settings</li>
            </ul>
        </aside>

        <main class="main-content">
            
            <section id="dashboard-view" class="view-section active">
                <header class="welcome-banner">
                    <h1>Welcome back, <span><?php echo htmlspecialchars($donor['name']); ?></span>!</h1>
                    <p style="font-size: 1.2em; color: #e74c3c; font-weight: bold; margin-top: 5px;">Your Official Donor ID: #<?php echo htmlspecialchars($donor['donor_id']); ?></p>
                    <p>Your blood type is precious. Thank you for your continued support.</p>
                </header>

                <div class="stats-grid">
                    <div class="stat-box">
                        <h3>Blood Type</h3>
                        <div class="stat-value highlight"><?php echo htmlspecialchars($donor['blood_type']); ?></div>
                    </div>
                    <div class="stat-box">
                        <h3>Total Donations</h3>
                        <div class="stat-value"><?php echo $total_donations; ?></div>
                    </div>
                    <div class="stat-box">
                        <h3>Status</h3>
                        <div class="stat-value" style="color: #2ecc71;">Active</div>
                    </div>
                </div>

                <div class="action-section" style="text-align: center;">
                    <h2>Your Official Medical Records</h2>
                    <p style="margin-bottom: 15px;">You can download your certified haematology report and screening results here. A copy will also be emailed to you.</p>
                    
                    <a href="../backend/generate_report.php" class="primary-btn" style="display: inline-block; background-color: #34495e; margin-bottom: 30px; text-decoration: none;">
                        <i class="fas fa-file-pdf"></i> Download Medical Report
                    </a>

                    <hr style="border: 1px solid #eee; margin-bottom: 20px;">

                    <h2>Ready to donate again?</h2>
                    <p>Based on your profile, you are currently <strong>eligible</strong> to donate.</p>
                    <button class="primary-btn" onclick="alert('Booking feature coming soon!')">Schedule Next Donation</button>
                </div>
            </section>

            <section id="history-view" class="view-section hidden">
                <h2>Your Donation History</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Bag ID</th>
                                <th>Blood Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // FIXED: Join donations and blood_bags to correctly link the donor to the bag
                            $stmt3 = $conn->prepare("
                                SELECT b.bag_id, b.blood_type, b.status 
                                FROM blood_bags b
                                JOIN donations d ON b.bag_id = d.bag_id
                                WHERE d.donor_id = ? 
                                ORDER BY d.donation_date DESC
                            ");
                            $stmt3->bind_param("i", $donor_id);
                            $stmt3->execute();
                            $history = $stmt3->get_result();

                            if ($history->num_rows > 0) {
                                while($row = $history->fetch_assoc()) {
                                    $status_color = ($row['status'] === 'Dispatched') ? '#2ecc71' : '#f39c12';
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['bag_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['blood_type']) . "</td>";
                                    echo "<td><span style='font-weight:bold; color: {$status_color};'>" . htmlspecialchars($row['status']) . "</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' style='text-align: center; padding: 20px;'>No donations on record yet. Schedule your first visit today!</td></tr>";
                            }
                            $stmt3->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="profile-view" class="view-section hidden">
                <h2>Profile Settings</h2>
                <p>Contact the hospital administrator to update your profile information.</p>
            </section>

        </main>
    </div>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const menuItems = document.querySelectorAll('.sidebar li');
            const sections = document.querySelectorAll('.view-section');

            sections.forEach(sec => {
                if (sec.id !== 'dashboard-view') sec.style.display = 'none';
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