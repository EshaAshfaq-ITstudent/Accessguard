<?php
session_start();

// Only allow access if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ensure an ID is provided via GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Don't allow admin to delete themselves
    if ($_SESSION['user_id'] == $id) {
        echo "<script>alert('You cannot delete your own account!'); window.location.href = 'admin_panel.php';</script>";
        exit();
    }

    // Connect to DB
    $conn = new mysqli("localhost", "root", "", "u655850112_site");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully!'); window.location.href = 'admin_panel.php';</script>";
    } else {
        echo "<script>alert('Error deleting user.'); window.location.href = 'admin_panel.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    // If no ID provided
    header("Location: admin_panel.php");
    exit();
}
?>
