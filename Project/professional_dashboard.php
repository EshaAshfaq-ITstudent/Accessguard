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
$sql1 = "SELECT username, email, role FROM users WHERE id = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$stmt1->bind_result($username, $email, $role);
$stmt1->fetch();
$stmt1->close();

// Fetch profile info
$sql2 = "SELECT bio, expertise, profile_picture FROM user_profiles WHERE user_id = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$stmt2->bind_result($bio, $expertise, $profile_picture);
$stmt2->fetch();
$stmt2->close();

// Fetch consultations for this doctor
$consultationSql = "SELECT preferred_date, preferred_time, category FROM doctor_consultations WHERE doctor_name = ?";
$stmt3 = $conn->prepare($consultationSql);
$stmt3->bind_param("s", $username);  // Using username to match doctor_name
$stmt3->execute();
$result = $stmt3->get_result();
$stmt3->close();

// Fetch consultations for this lawyer
$consultationSql = "SELECT consultation_date, consultation_time, category FROM legal_consultations WHERE legal_expert = ?";
$stmt4 = $conn->prepare($consultationSql);
$lawyer_username = $_SESSION['username']; // or another variable holding the logged-in lawyer's username
$stmt4->bind_param("s", $lawyer_username); // bind the parameter
$stmt4->execute();
$result = $stmt4->get_result();

// Fetch results (optional: loop through them)
$consultations = [];
while ($row = $result->fetch_assoc()) {
    $consultations[] = $row;
}
$stmt4->close();
$conn->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Professional Dashboard - ConnecToLead</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    /* Same styles as user dashboard */
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

    .cards {
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

    .center-button {
        display: flex;
        justify-content: center;
        margin-top: 30px;
    }

    .btn-logout {
        background-color: #1f1481;
        padding: 10px 30px;
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        text-align: center;
    }

    .btn-logout:hover {
        background-color: grey;
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
<header class="header">
    <h2>ConnectoLead Professional Dashboard</h2>
            <img src="./Images/logo0.png" alt="ConnecToLead" width="50">
            <div class="Welcome">
                <p>Hello, <strong><?php echo $_SESSION['username']; ?></strong></p>
            </div>
        </header>

<!-- Dashboard Content -->
<div class="dashboard-container">
    <div class="user-info">
    <?php if ($profile_picture): ?>
        <p><img src="<?php echo $profile_picture; ?>" height="100" style="border-radius: 50px;" /></p>
    <?php endif; ?>
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <p>Email: <?php echo htmlspecialchars($email); ?></p>
    <p>Role: <?php echo htmlspecialchars(ucfirst($role)); ?></p>
    <p><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($bio)); ?></p>
    <p><strong>Expertise:</strong> <?php echo htmlspecialchars($expertise); ?></p><br>
    <a href="edit_profile.php" class="btn">Edit Profile</a>
    </div>
    
    <div class="card">
        <?php if ($role === 'doctor'): ?>
        <h3>Booked Consultations</h3>
        <?php if ($result->num_rows > 0): ?>
            <ul style="text-align: left; list-style: disc inside; padding: 10px 40px;">
            <?php while ($row = $result->fetch_assoc()): ?>
                <li>
                <?php echo htmlspecialchars($row['preferred_date'] . ' at ' . $row['preferred_time'] . ' (' . $row['category'] . ')'); ?>
                </li>
            <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No consultations yet.</p>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <?php if ($role === 'lawyer'): ?>
        <h3>Booked Consultations</h3>
        <?php if (count($consultations) > 0): ?>
            <ul style="text-align: left; list-style: disc inside; padding: 10px 40px;">
            <?php foreach ($consultations as $row): ?>
                <li>
                <?= htmlspecialchars($row['consultation_date'] . ' at ' . $row['consultation_time'] . ' (' . $row['category'] . ')') ?>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No consultations yet.</p>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="card">
      <h4>Knowledge Exchange</h4>
      <p>Share your expertise through sessions and content.</p>
      <a href="knowledge_sharing.php" class="btn">Get Involved</a>
    </div>
  </div>

    <div class="center-button">
    <a href="logout.php" class="btn btn-logout">Logout</a>
    </div>

</div>

<!-- Footer -->
<div class="footer">&copy; 2024-2030 ConnecToLead | All Rights Reserved</div>

</body>
</html>
