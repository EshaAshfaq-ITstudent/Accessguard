<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn = new mysqli("localhost", "root", "", "u655850112_site");

    if ($conn->connect_error) {
        die("Connection failed");
    }

    $conn->query("UPDATE users SET role='admin' WHERE id=$id");
    $conn->close();

    header("Location: admin_panel.php");
}
?>
