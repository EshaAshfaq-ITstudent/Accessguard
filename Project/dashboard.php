<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// DB connection
$servername = "localhost";
$username = "u655850112_site";
$password = "Q0jAJnA][";
$dbname = "u655850112_site";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$sql = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();

$workshops = [];
$sql = "SELECT course, enrolled_at 
        FROM enrollments 
        WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email); // $email comes from logged-in user info
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $workshops[] = $row;
}

$stmt->close();



$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Dashboard - ConnecToLead</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
        --main-color: #800000;
        --secondary-color: #1f1481;
        --bg-color: #f9f9f9;
        --text-color: #333;
        --card-shadow: rgba(0, 0, 0, 0.1);
    }

    * {
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        margin: 0;
        padding: 0;
        background-color: var(--bg-color);
    }

    .header {
        background: var(--main-color);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 60px;
        box-shadow: 0 4px 10px var(--card-shadow);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .header .logo {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .header .logo img {
        height: 40px;
    }

    .header nav a {
        color: white;
        text-decoration: none;
        margin-left: 20px;
        font-weight: 500;
        transition: 0.3s;
    }

    .header nav a:hover {
        text-decoration: underline;
    }

    .dashboard-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 8px 24px var(--card-shadow);
        text-align: center;
    }

    h2 {
        color: var(--main-color);
        font-size: 28px;
    }

    .user-info {
        margin-bottom: 30px;
    }

    .user-info p {
        font-size: 16px;
        margin: 5px 0;
    }

    .workshops {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }

    .card {
        background: #fff;
        border-top: 4px solid var(--secondary-color);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 6px 16px var(--card-shadow);
        transition: transform 0.2s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card h4 {
        color: var(--main-color);
        margin-bottom: 10px;
        font-size: 20px;
    }

    .card p {
        margin-bottom: 15px;
    }

    .btn {
        background-color: var(--main-color);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
        font-weight: 600;
        transition: background 0.3s;
    }

    .btn:hover {
        background-color: var(--secondary-color);
    }

    .btn-logout {
        margin-top: 30px;
        background-color: #444;
    }

    .btn-logout:hover {
        background-color: darkblue;
    }

    .footer {
        background: var(--main-color);
        color: white;
        text-align: center;
        padding: 20px;
        margin-top: 40px;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            text-align: center;
        }

        .header nav {
            margin-top: 10px;
        }

        .header nav a {
            display: block;
            margin: 8px 0;
        }

        .dashboard-container {
            padding: 20px;
        }
    }
  </style>
</head>
<body>

<!-- Header -->
<div class="header">
  <div class="logo">
    <img src="./Images/logo0.png" alt="ConnecToLead Logo">
    <span style="font-weight: bold;">ConnecToLead</span>
  </div>
  <nav>
    <a href="index.html">Home</a>
    <a href="workshop_registration.php">Workshop</a>
    <a href="contact.php">Contact</a>
    <a href="dashboard.php"><?php echo htmlspecialchars($username); ?></a>
    <a href="logout.php" class="btn btn-logout">Logout</a>
  </nav>
</div>

<!-- Dashboard Content -->
<div class="dashboard-container">
  <div class="user-info">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <p>Email: <?php echo htmlspecialchars($email); ?></p>
  </div>

    <h3>Your Enrolled Courses</h3>
    <?php if (count($workshops) === 0): ?>
    <p>You haven't enrolled in any courses yet. <a href="TechT.WorkCour.php">Browse Courses</a></p>
    <?php else: ?>
    <div class="workshops">
        <?php foreach ($workshops as $workshop): ?>
        <div class="card">
            <h4><?php echo htmlspecialchars($workshop['course']); ?></h4>
            <p>Enrolled At: <?php echo $workshop['enrolled_at']; ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>


  <a href="logout.php" class="btn btn-logout">Logout</a>
</div>

<!-- Footer -->
<div class="footer">&copy; 2024-2030 ConnecToLead | All Rights Reserved</div>

</body>
</html>
