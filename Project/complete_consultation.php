<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $consultationId = $_GET['id'];

    // Database connection
    $servername = "localhost";
    $username = "u655850112_site";
    $password = "Q0jAJnA][";
    $dbname = "u655850112_site";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update consultation status to 'Completed'
    $updateQuery = "UPDATE consultations SET status = 'Completed' WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $consultationId);

    if ($stmt->execute()) {
        header("Location: admin_panel.php?status=consultation_completed");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
