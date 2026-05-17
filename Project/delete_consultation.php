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

    // Delete consultation record
    $deleteQuery = "DELETE FROM consultations WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $consultationId);

    if ($stmt->execute()) {
        header("Location: admin_panel.php?status=consultation_deleted");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
