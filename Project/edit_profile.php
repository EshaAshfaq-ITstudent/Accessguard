<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "mywebsite");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$bio = $expertise = $profile_picture = "";

// Check if profile exists
$sql = "SELECT bio, expertise, profile_picture FROM user_profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($bio, $expertise, $profile_picture);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bio = $_POST['bio'];
    $expertise = $_POST['expertise'];

    // Handle file upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir); // Ensure directory exists
        $filename = basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . time() . "_" . $filename;

        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
        $profile_picture = $target_file;
    }

    // Check if profile already exists
    $check = $conn->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Update
        $sql = "UPDATE user_profiles SET bio=?, expertise=?, profile_picture=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $bio, $expertise, $profile_picture, $user_id);
    } else {
        // Insert
        $sql = "INSERT INTO user_profiles (bio, expertise, profile_picture, user_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $bio, $expertise, $profile_picture, $user_id);
    }
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header("Location: professional_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        form { max-width: 600px; margin: auto; background: #f9f9f9; padding: 20px; border-radius: 10px; }
        textarea, input[type=text], input[type=file] { width: 100%; margin-bottom: 15px; padding: 8px; }
        button { background: maroon; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Edit Your Profile</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Bio:</label>
        <textarea name="bio"><?php echo htmlspecialchars($bio); ?></textarea>

        <label>Expertise:</label>
        <input type="text" name="expertise" value="<?php echo htmlspecialchars($expertise); ?>">

        <label>Profile Picture:</label>
        <input type="file" name="profile_picture">
        <?php if ($profile_picture): ?>
            <p>Current: <img src="<?php echo $profile_picture; ?>" height="100" /></p>
        <?php endif; ?>

        <button type="submit">Save Profile</button>
    </form>
</body>
</html>
