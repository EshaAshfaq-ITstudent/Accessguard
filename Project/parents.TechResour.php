<?php
// Database connection settings
$host = "localhost";
$user = "u655850112_site";
$pass = "Q0jAJnA][";
$db = "u655850112_site";  

$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch resources from the database
$sql = "SELECT * FROM tech_resources ORDER BY created_at DESC";
$result = $conn->query($sql);

// Handle form submission for adding new resources
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $video_url = $_POST['video_url'];
    $article_url = $_POST['article_url'];
    $description = $_POST['description'];

    // Insert new resource into the database
    $stmt = $conn->prepare("INSERT INTO tech_resources (title, video_url, article_url, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $video_url, $article_url, $description);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>Resource added successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tech Resources</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      background: #eef2f7;
    }
    header {
      background-color: #680f0f;
      color: white;
      padding: 20px 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    .header-left {
      position: absolute;
      left: 40px; 
      display: flex;
      align-items: center;
    }
    .header-left img {
      height: 70px; 
      margin-right: 10px;
    }
    .header-title {
      font-size: 28px;
      font-weight: bold;
      color: white;
      margin: 0;
    }
    main {
      max-width: 900px;
      margin: auto;
      background: white;
      padding: 40px 20px;
      border-radius: 12px;
    }
    .resource {
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 10px;
      margin-bottom: 20px;
    }
    a {
      color: #023468;
      text-decoration: none;
    }
    .back-link {
      display: inline-block;
      margin-bottom: 20px;
      color: #680f0f;
      text-decoration: none;
      font-weight: bold;
    }

    .back-link:hover {
      text-decoration: underline;
    }
    .resource-links {
      margin-top: 10px;
      display: flex;
      gap: 15px;
    }
    .resource-links a {
      padding: 8px 15px;
      background-color: #680f0f;
      color: white;
      border-radius: 6px;
      text-decoration: none;
    }
    .resource-links a:hover {
      background-color: #8a2c2c;
    }

    form {
      margin-top: 40px;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    form input, form textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    form button {
      padding: 10px 20px;
      background-color: #680f0f;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    form button:hover {
      background-color: #8a2c2c;
    }
  </style>
</head>
<body>
    <header>
        <div class="header-left">
          <img src="./Images/logo0.png" alt="Logo" />
        </div>
        <h1 class="header-title">Tech Resources</h1>
    </header>
    
    <main>
    <a href="parentshub.html" class="back-link"
      ><i class="bi bi-arrow-left"></i> Back to Portal</a
    >
        <h2>Helpful Guides & Tutorials</h2>

        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="resource">
            <h3><a href="<?= htmlspecialchars($row['video_url']) ?>" target="_blank"><?= htmlspecialchars($row['title']) ?></a></h3>
            <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
            <div class="resource-links">
                <a href="<?= htmlspecialchars($row['video_url']) ?>" target="_blank">Watch Tutorial</a>
                <?php if (!empty($row['article_url'])): ?>
                <a href="<?= htmlspecialchars($row['article_url']) ?>" target="_blank">Read Article</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>

        <h2>Add a New Resource</h2>
        <form method="POST">
            <input type="text" name="title" placeholder="Resource Title" required>
            <input type="url" name="video_url" placeholder="Video URL (YouTube or other)" required>
            <input type="url" name="article_url" placeholder="Article URL (Optional)">
            <textarea name="description" placeholder="Resource Description" rows="4" required></textarea>
            <button type="submit">Add Resource</button>
        </form>
    </main>
</body>
</html>

<?php
$conn->close();
?>
