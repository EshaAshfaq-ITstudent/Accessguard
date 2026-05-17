<?php
$host = 'localhost';
$user = 'u655850112_site';
$password = 'Q0jAJnA][';
$database = 'u655850112_site';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// If request is AJAX (JS fetch), handle JSON response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $title = $_POST['title'] ?? '';
    $story = $_POST['story'] ?? '';

    if ($title && $story) {
        $stmt = $conn->prepare("INSERT INTO senior_stories (title, story) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $story);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Story submitted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to save story"]);
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing title or story"]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $result = $conn->query("SELECT title, story, created_at FROM senior_stories ORDER BY created_at DESC");
    $stories = [];
    while ($row = $result->fetch_assoc()) {
        $stories[] = $row;
    }
    echo json_encode($stories);
    exit;
}

#Sessionn Registration 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['session_register'])) {
    $session_name = $_POST['session_name'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($session_name && $name && $email) {
        $stmt = $conn->prepare("INSERT INTO session_registrations (session_name, name, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $session_name, $name, $email);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF'] . "?registered=1");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Senior Engagement - Education & Knowledge</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet"/>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #fffafc;
      color: #333;
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
    }
    main {
      max-width: 1100px;
      margin: 40px auto;
      padding: 20px;
    }
    section {
      background: #f4e9f4;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 40px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    h2, h3 {
      color: #680f0f;
    }
    label {
      font-weight: 500;
    }
    input, textarea, button {
      padding: 12px;
      font-size: 16px;
      border-radius: 8px;
      border: 1px solid #ccc;
      width: 100%;
      margin-top: 10px;
    }
    button {
      background-color: #680f0f;
      color: white;
      border: none;
      margin-top: 15px;
      cursor: pointer;
    }
    button:hover {
      background-color: #023468;
    }
    ul {
      padding-left: 20px;
    }
  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <img src="./Images/logo0.png" alt="Logo" />
    </div>
    <h1 class="header-title">Education & Knowledge</h1>
  </header>

  <main>
    <a href="seniorcitizen.html" class="back-link"><i class="bi bi-arrow-left"></i> Back to Portal</a>

    <section data-aos="fade-up">
      <h2>About</h2>
      <p>This platform allows senior citizens to share their knowledge and experience with others while also learning from their peers.</p>
    </section>

    <section data-aos="fade-up">
      <h2>Share Your Story or Advice</h2>
      <form id="storyForm">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" placeholder="Your Story Title" required>
        <label for="story">Story:</label>
        <textarea id="story" name="story" placeholder="Write your story here..." required></textarea>
        <button type="submit">Submit Story</button>
      </form>
      <div id="storyFeedback"></div>
    </section>

    <section data-aos="fade-up">
      <h2>Read Experiences from Others</h2>
      <div id="storyList">
        <p>Loading stories...</p>
      </div>
    </section>

    <section data-aos="fade-up">
      <h2>Join a Knowledge Exchange Session</h2>
      <!-- Registration Modal -->
        <div id="registerModal" style="display: none; position: fixed; top: 0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); justify-content:center; align-items:center;">
          <div style="background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 500px; position: relative;">
            <h3>Register for <span id="sessionTitle"></span></h3>
            <form method="POST">
              <input type="hidden" id="sessionName" name="session_name">
              <label for="name">Your Name:</label>
              <input type="text" name="name" required>
              <label for="email">Your Email:</label>
              <input type="email" name="email" required>
              <input type="hidden" name="session_register" value="1">
              <button type="submit">Register</button>
            </form>
            <button onclick="closeModal()" style="margin-top: 10px; background: #ccc;">Cancel</button>
          </div>
        </div>
      <ul>
        <li><strong>Session 1:</strong> Digital Literacy for Seniors - 10th April 2025 - <button onclick="openModal('Digital Literacy for Seniors')">Register now</button></li>
        <li><strong>Session 2:</strong> Life Lessons for Youth - 15th April 2025 - <button onclick="openModal('Life Lessons for Youth')">Register now</button></li>
      </ul>
    </section>

<?php if (isset($_GET['registered'])): ?>
  <div style="background: #d4edda; padding: 15px; color: #155724; text-align: center;">
    Thank you! You have successfully registered for the session.
  </div>
<?php endif; ?>



  </main>

  <div class="footer" style="background-color: #023468; color: white; padding: 20px; text-align: center;">
    &copy; 2025 Our Company | All Rights Reserved
  </div>

  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init({ duration: 1000 });

    document.getElementById('storyForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(response => response.json())
      .then(data => {
        const feedback = document.getElementById('storyFeedback');
        if (data.status === 'success') {
          feedback.innerHTML = `<p>${data.message}</p>`;
          this.reset();
          loadStories();
        } else {
          feedback.innerHTML = `<p style="color: red">${data.message}</p>`;
        }
      });
    });

    function loadStories() {
      fetch(window.location.href, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(res => res.json())
      .then(data => {
        const storyList = document.getElementById('storyList');
        storyList.innerHTML = '';
        if (data.length === 0) {
          storyList.innerHTML = '<p>No stories available yet. Be the first to share!</p>';
        } else {
          data.forEach(story => {
            const storyBlock = document.createElement('div');
            storyBlock.style.marginBottom = '20px';
            storyBlock.innerHTML = `<h3>${story.title}</h3><p>${story.story}</p><small>Posted on ${new Date(story.created_at).toLocaleDateString()}</small>`;
            storyList.appendChild(storyBlock);
          });
        }
      });
    }

    loadStories();

    function openModal(sessionName) {
      document.getElementById('registerModal').style.display = 'flex';
      document.getElementById('sessionName').value = sessionName;
      document.getElementById('sessionTitle').textContent = sessionName;
    }

    function closeModal() {
      document.getElementById('registerModal').style.display = 'none';
    }
  </script>
</body>
</html>
