<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$servername = "localhost";
$username = "u655850112_site";
$password = "Q0jAJnA][";
$dbname = "u655850112_site";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, username, email, role, phone, Registration_time FROM users ORDER BY id ASC";
$result = $conn->query($sql);

$chartData = [];
$chartQuery = "SELECT DATE(Registration_time) as reg_date, COUNT(*) as count 
               FROM users 
               WHERE Registration_time >= CURDATE() - INTERVAL 9 DAY
               GROUP BY reg_date 
               ORDER BY reg_date";



$chartResult = $conn->query($chartQuery);
while ($row = $chartResult->fetch_assoc()) {
    $chartData[] = $row;
}

// Parents Tech Hub
$parentsResult1= $conn->query("SELECT COUNT(*) as total FROM parent_workshops")->fetch_assoc();
$parentsResult2 = $conn->query("SELECT COUNT(*) as total FROM tech_resources")->fetch_assoc();
$parentsResult3= $conn->query("SELECT COUNT(*) as total FROM forum_threads")->fetch_assoc();

// Women Legal and Safety Hub
$doctors = $conn->query("SELECT COUNT(*) as doctors FROM doctor_consultations")->fetch_assoc()['doctors'];
$lawyers = $conn->query("SELECT COUNT(*) as lawyers FROM legal_consultations")->fetch_assoc()['lawyers'];
$consultations = $conn->query("SELECT COUNT(*) as total FROM doctor_consultations")->fetch_assoc()['total'];
$cases = $conn->query("SELECT COUNT(*) as total FROM legal_consultations")->fetch_assoc()['total'];



// Seniors Support Hub
$storyCountResult = $conn->query("SELECT COUNT(*) AS total FROM senior_stories");
$storyCount = $storyCountResult->fetch_assoc()['total'];

$mentorship = $conn->query("SELECT COUNT(*) as total FROM mentorship_requests")
                   ->fetch_assoc()['total'];
$events = $conn->query("SELECT COUNT(*) as total FROM session_registrations")->fetch_assoc()['total'];

// Fetching consultation data
$consultationQuery = "SELECT consultations.id, users.username, consultations.topic, consultations.consultation_mode, consultations.preferred_datetime
                      FROM consultations
                      JOIN users ON consultations.id = users.id
                      ORDER BY consultations.id DESC";

$consultationResult = $conn->query($consultationQuery);


// Tech Trainings
$students = $conn->query("SELECT COUNT(*) as total FROM career_support")->fetch_assoc()['total'];
$resources = $conn->query("SELECT COUNT(*) as total FROM digital_resources")->fetch_assoc()['total'];
$students = $conn->query("SELECT COUNT(*) as total FROM career_support")->fetch_assoc()['total'];
$enrollments = $conn->query("SELECT COUNT(*) as total FROM enrollments")->fetch_assoc()['total'];


$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - ConnectoLead</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<style>
    * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
}

body {
    background-color: #f4f7fc;
    color: #333;
}

.admin-container {
    max-width: 1200px;
    margin: auto;
    padding: 20px;
}
      /* Header */
.header {
    background: #800000;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 60px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.header .logo img {
    height: 50px;
}

.header nav a {
    color: white;
    text-decoration: none;
    margin-left: 20px;
    font-weight: 500;
    transition: 0.3s;
    padding: 8px 16px;
    border-radius: 5px;
}

.header nav a:hover {
    background: rgba(255, 255, 255, 0.2);
}

.admin-welcome {
    display: flex;
    align-items: center;
    gap: 15px;
}

.btn {
    padding: 8px 14px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    cursor: pointer;
    transition: 0.2s;
}

.btn-logout {
    background-color: #dc3545;
    color: white;
}

.btn-upgrade {
    background-color: #28a745;
    color: white;
}

.btn-delete {
    background-color: #ff4d4d;
    color: white;
    margin-left: 5px;
}

.chart-section, .table-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
}

.chart-section h2, .table-section h2 {
    margin-bottom: 15px;
}

#registrationChart {
    width: 100%;
    height: 400px;
}

#searchInput {
    margin-bottom: 15px;
    padding: 10px;
    width: 100%;
    max-width: 300px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.display {
    width: 100%;
    border-collapse: collapse;
}

.display th, .display td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.display tr:hover {
    background-color: #f1f1f1;
}

.feature-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.feature-box {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.feature-box h3 {
    margin-bottom: 15px;
}
.footer {
    background-color: var(--secondary-color);
    color: white;
    padding: 30px 10px;
    text-align: center;
    font-size: 18px;
}
.footer span {
    font-weight: bold;
    color: var(--main-color);
}

.footer {
    background: grey;
    color: white;
    text-align: center;
    padding: 20px;
    font-size: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.footer a {
    color:blue;
    text-decoration: none;
    font-weight: 600;
}

.footer a:hover {
    text-decoration: underline;
}

.tab-menu {
    background: #800000;
    display: flex;
    justify-content: center;
    padding: 10px 0;
    gap: 20px;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.tab-menu a {
    color: white;
    font-weight: 600;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 6px;
    transition: background 0.3s;
}

.tab-menu a:hover {
    background: rgba(255, 255, 255, 0.2);
}

html {
  scroll-behavior: smooth;
}

.tab-nav {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin: 30px 0;
  flex-wrap: wrap;
}

.tab-nav a {
  background: #800000;
  color: white;
  padding: 10px 20px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 600;
  transition: background 0.3s;
}

.tab-nav a:hover {
  background: #a52a2a;
}


.section-header {
    font-size: 22px;
    margin-bottom: 15px;
    border-left: 5px solid #800000;
    padding-left: 15px;
    color: #800000;
}

.feature-row {
    display: flex;
    flex-wrap: wrap;   /* Ensures it wraps on smaller screens */
    gap: 20px;
    margin-bottom: 30px;
}

.feature-box {
    flex: 1;
    min-width: 280px;
    max-width: 33%;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    text-align: center;
}



</style>
<body>
    <div class="admin-container">
        <header class="header">
            <h2>ConnectoLead Admin Panel</h2>
            <img src="./Images/logo0.png" alt="ConnecToLead" width="50">
            <div class="admin-welcome">
                <p>Hello, <strong><?php echo $_SESSION['username']; ?></strong> (Admin)</p>
                <a href="logout.php" class="btn btn-logout">Logout</a>
            </div>
        </header><hr>

        <!-- Tab Navigation -->
        <div class="tab-nav">
            <a href="#parentsHub" class="tab-btn">Parents</a>
            <a href="#womenHub" class="tab-btn">Women</a>
            <a href="#seniorsHub" class="tab-btn">Seniors</a>
            <a href="#techHub" class="tab-btn">Tech</a>
        </div>



        <div class="chart-section">
            <h2>Registrations Overview</h2>
            <canvas id="registrationChart"></canvas>
        </div>


        <!-- Feature Stats Section -->
        <div class="feature-stats">
        <div class="feature-section">
            <div id="parentsHub">
                <h3 class="section-header">Parents Tech Hub</h3>
                <div class="feature-row">
                    <div class="feature-box">
                        <h4>Tech Workshops</h4>
                        <canvas id="parentsChart"></canvas>
                    </div>
                    <div class="feature-box">
                        <h4>Tech Resources</h4>
                        <canvas id="parentsChart2"></canvas>
                    </div>
                    <div class="feature-box">
                        <h4>Discussion Forums</h4>
                        <canvas id="parentsChart3"></canvas>
                    </div>
                </div>
            </div>

            <div id="womenHub">
                <h3 class="section-header">Women Legal & Health Hub</h3>
                <div class="feature-row">
                    <div class="feature-box">
                        <h4>Doctors & Lawyers</h4>
                        <canvas id="womenChart"></canvas>
                    </div>
                    <div class="feature-box">
                        <h4>Total Booked Consultations</h4>
                        <canvas id="womenChart2"></canvas>
                    </div>
                    <div class="feature-box">
                        <h4>Total Cases Registered</h4>
                        <canvas id="womenChart3"></canvas>
                    </div>
                </div>
            </div>

            <div id="seniorsHub">
                <h3 class="section-header">Senior Support Hub</h3>
                <div class="feature-row">
                    <div class="feature-box">
                        <h4>Mentorship</h4>
                        <canvas id="seniorsChart"></canvas>
                    </div>
                    <div class="feature-box">
                        <h4>Senior Stories</h4>
                        <canvas id="seniorsChart2"></canvas>
                    </div>
                    <div class="feature-box">
                        <h4>Events</h4>
                        <canvas id="seniorsChart3"></canvas>
                    </div>
                </div>
            </div>

            <div id="techHub">
                <h3 class="section-header">Tech Trainings</h3>
                <div class="feature-row">
                    <div class="feature-box">
                        <h4>Career Support Provided</h4>
                        <canvas id="techChart"></canvas>
                    </div>
                    <div class="feature-box">
                        <h4>Digital Resources</h4>
                        <canvas id="techChart2"></canvas>
                    </div>
                    <div class="feature-box">
                        <h4>Career Support Provided</h4>
                        <canvas id="techChart3"></canvas>
                    </div>

                </div>
            </div>
        </div>



        </div><br><br>

        <!-- Consultation Table Section -->
        <div class="table-section">
            <h2>Consultation Requests</h2>
            <table id="consultationTable" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Consultation Topic</th>
                        <th>Consultation Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $consultationResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td><?= $row['username']; ?></td>
                            <td><?= ucfirst($row['topic']); ?></td>
                            <td><?= $row['preferred_datetime']; ?></td>
                            <td><?= ucfirst($row['consultation_mode']); ?></td>
                            <td>
                                <!-- Actions: You can add options like marking as completed or deleting -->
                                <button onclick="confirmComplete(<?= $row['id']; ?>)" class="btn btn-upgrade">Mark as Completed</button>
                                <button onclick="confirmDeleteConsultation(<?= $row['id']; ?>)" class="btn btn-delete">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>


        <div class="table-section">
            <h2>Registered Users</h2>
            <input type="text" id="searchInput" placeholder="Search by name or email..." />
            <table id="usersTable" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= $row['username']; ?></td>
                        <td><?= $row['email']; ?></td>
                        <td><?= ucfirst($row['role']); ?></td>
                        <td><?= ucfirst($row['phone']); ?></td>
                        <td><?= $row['Registration_time']; ?></td>
                        <td>
                            <?php if ($row['role'] === 'user'): ?>
                                <button onclick="confirmUpgrade(<?= $row['id']; ?>)" class="btn btn-upgrade">Make Admin</button>
                            <?php endif; ?>
                            <button onclick="confirmDeleteUser(<?= $row['id']; ?>)" class="btn btn-delete">Delete</button>
                        </td>
                        
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>


    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(() => {
            $('#usersTable').DataTable();

            $('#searchInput').on('keyup', function () {
                $('#usersTable').DataTable().search(this.value).draw();
            });
        });

        function confirmUpgrade(id) {
            if (confirm("Make this user an admin?")) {
                window.location.href = "upgrade_role.php?id=" + id;
            }
        }

        function confirmDeleteUser(id) {
            if (confirm("Are you sure you want to delete this user?")) {
                window.location.href = "delete_user.php?id=" + id;
            }
        }

        // Parents Tech Hub
        new Chart(document.getElementById('parentsChart'), {
            type: 'bar',
            data: {
                labels: ['Registered Parents'],
                datasets: [{
                    label: 'Total',
                    data: [<?= $parentsResult1['total'] ?>],
                    backgroundColor: '#007bff'
                }]
            }
        });
        new Chart(document.getElementById('parentsChart2'), {
            type: 'bar',
            data: {
                labels: ['Total Resources'],
                datasets: [{
                    label: 'Total',
                    data: [<?= $parentsResult2['total'] ?>],
                    backgroundColor: 'lightblue'
                }]
            },
            options: {
                indexAxis: 'y',
            }
        });
        new Chart(document.getElementById('parentsChart3'), {
            type: 'line',
            data: {
                labels: ['Total Threads'],
                datasets: [{
                    label: 'Total',
                    data: [<?= $parentsResult3['total'] ?>],
                    backgroundColor: 'blue'
                }]
            }
        });

        // Women Health and Safety Hub
        new Chart(document.getElementById('womenChart'), {
            type: 'bar',
            data: {
                labels: ['Health Professionals'],
                datasets: [
                    {
                        label: 'Doctors',
                        data: [<?= $doctors ?>],
                        backgroundColor: '#bd6f66ff'
                    },
                    {
                        label: 'Lawyers',
                        data: [<?= $lawyers ?>],
                        backgroundColor: '#f79287ff'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Doctors and Lawyers Breakdown'
                    }
                },
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                }
            }
        });


        new Chart(document.getElementById('womenChart2'), {
            type: 'line',
            data: {
                labels: ['2025-07-23', '2025-07-24', '2025-07-25', '2025-07-26', '2025-07-27', '2025-07-28', '2025-07-29'],
                datasets: [{
                    label: 'Consultations Booked',
                    data: [2, 3, 1, 4, 5, 2, 3],
                    backgroundColor: '#e99990ff',
                    borderColor: '#c0392b',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        new Chart(document.getElementById('womenChart3'), {
            type: 'bar',
            data: {
                labels: ['Legal Cases'],
                datasets: [{
                    label: 'Total Cases',
                    data: [<?= $cases ?>],
                    backgroundColor: '#eb7c6fff'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });


        // Seniors Support Hub
        new Chart(document.getElementById('seniorsChart'), {
            type: 'bar',
            data: {
                labels: ['Mentorship'],
                datasets: [{
                    label: 'Mentorship Accessed',
                    data: [<?= $mentorship ?>],
                    backgroundColor: [ '#17a2b8']
                }]
            }
        });

        new Chart(document.getElementById('seniorsChart2'), {
            type: 'line',
            data: {
                labels: ['Stories Posted'],
                datasets: [{
                    label: 'Total',
                    data: [<?= $storyCount ?>],
                    backgroundColor: '#6dceddff'
                }]
            },
            options: {
                indexAxis: 'y',
            }
        });

        new Chart(document.getElementById('seniorsChart3'), {
            type: 'bar',
            data: {
                labels: ['Events'],
                datasets: [{
                    label: 'Events Done',
                    data: [<?= $events ?>],
                    backgroundColor: [ '#124249ff']
                }]
            }
        });


        // Tech Trainings
        new Chart(document.getElementById('techChart'), {
            type: 'bar',
            data: {
                labels: ['Career Support Provided'],
                datasets: [{
                    label: 'Total Students',
                    data: [<?= $students ?>],
                    backgroundColor: '#e78e8eff'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });


        new Chart(document.getElementById('techChart2'), {
            type: 'bar',
            data: {
                labels: ['Digital Resources'],
                datasets: [{
                    label: 'Total Resources',
                    data: [<?= $resources ?>],
                    backgroundColor: '#f5c2c2ff'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

                
        new Chart(document.getElementById('techChart3'), {
            type: 'bar',
            data: {
            labels: ['Total Students', 'Enrollments'],
            datasets: [{
                label: 'Count',
                data: [<?= $students ?>, <?= $enrollments ?>],
                backgroundColor: ['#a33141ff', '#ec4f4fff']
            }]
            },
            options: {
            responsive: true,
            plugins: {
                title: {
                display: true,
                text: 'Students vs Enrollments'
                }
            },
            scales: {
                y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
                }
            }
            }
        });




        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active classes
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                // Activate selected tab
                btn.classList.add('active');
                document.getElementById(btn.dataset.tab).classList.add('active');
            });
        });

        const chartData = <?php echo json_encode($chartData); ?>;
        const labels = chartData.map(data => data.reg_date); // Registration dates
        const values = chartData.map(data => data.count);  // Number of registrations


        const ctx = document.getElementById('registrationChart').getContext('2d');
        const registrationChart = new Chart(ctx, {
            type: 'line',  // Line chart type
            data: {
                labels: labels,  // X-axis labels (dates)
                datasets: [{
                    label: 'New Registrations',  // The label for the line
                    data: values,  // The y-axis data (count of registrations)
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',  // Background color (transparent)
                    borderColor: '#007bff',  // Line color
                    borderWidth: 2,  // Line width
                    tension: 0.3,  // Line smoothness (controls the curve)
                    fill: true  // Fill the area under the line
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true  // Start y-axis from 0
                    }
                }
            }
        });


        $(document).ready(() => {
            $('#consultationTable').DataTable(); // Apply DataTable to the new consultation table

            // Optional: Implementing search
            $('#searchInput').on('keyup', function () {
                $('#consultationTable').DataTable().search(this.value).draw();
            });
        });

        // Mark as Completed
        function confirmComplete(id) {
            if (confirm("Mark this consultation as completed?")) {
                window.location.href = "complete_consultation.php?id=" + id;  // Redirect to a PHP script to handle completion
            }
        }

        //Delete consultation
        function confirmDeleteConsultation(id) {
            if (confirm("Delete this consultation?")) {
                window.location.href = "delete_consultation.php?id=" + id;
            }
        }


    </script>

    
    <div class="footer">&copy; 2024-2030 ConnecToLead | All Rights Reserved</div>
</body>
</html>
