<?php 
session_start();
$servername = "localhost";
$username = "u655850112_site";
$password = "Q0jAJnA][";
$dbname = "u655850112_site";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$lawyerQuery = "
  SELECT u.username, u.email, u.phone, p.bio, p.expertise, p.profile_picture
  FROM users u
  JOIN user_profiles p ON u.id = p.user_id
  WHERE u.role = 'lawyer'
";

$result = $conn->query($lawyerQuery);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $lawyerUsers[] = $row;
  }
}
// Fetch legal consultants from users table
$consultants = [];
$sql = "SELECT id, username FROM users WHERE role = 'lawyer'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $consultants[] = $row;
  }
}

// Handle consultation booking
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_consultation'])) {
    $legal_expert = $_POST['legal_expert'];
    $consultation_date = $_POST['consultation_date'];
    $consultation_time = $_POST['consultation_time'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

    $stmt = $conn->prepare("INSERT INTO legal_consultations (user_id, legal_expert, consultation_date, consultation_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $legal_expert, $consultation_date, $consultation_time);
    $stmt->execute();
    $stmt->close();
}

// Handle legal resource addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_resource'])) {
    $title = $_POST['resource_title'];
    $description = $_POST['resource_description'];
    $url = $_POST['url'];

    $stmt = $conn->prepare("INSERT INTO legal_resources (title, description, url) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $description, $url);

    if ($stmt->execute()) {
        echo "Resource added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch resources
$resources = [];
$res = $conn->query("SELECT * FROM legal_resources ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) {
    $resources[] = $row;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ConnectoLead - Legal Assistance</title>
  <style>
    /* your existing styles... */
    body {
      font-family: Arial, sans-serif;
      background-color: #fffafc;
      margin: 0;
      padding: 0;
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
      margin: 30px auto;
      padding: 20px;
    }

    section {
      background: #f4e9f4;
      padding: 20px;
      margin-bottom: 30px;
      border-radius: 10px;
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
    }

    section h2 {
      color: #680f0f;
      margin-bottom: 15px;
    }

    label,
    select,
    input,
    textarea,
    button {
      display: block;
      width: 100%;
      margin-bottom: 10px;
      padding: 10px;
      font-size: 16px;
      border-radius: 6px;
    }

    button {
      background-color: #680f0f;
      color: white;
      border: none;
      cursor: pointer;
    }

    button:hover {
      background-color: #4d0a0a;
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

    .message {
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 6px;
      font-weight: bold;
    }

    .success {
      background-color: #d4edda;
      color: #155724;
    }

    .error {
      background-color: #f8d7da;
      color: #721c24;
      }

    .lawyer-card {
      display: flex;
      align-items: center;
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-bottom: 20px;
      gap: 20px;
      transition: transform 0.2s ease;
    }

    .lawyer-card:hover {
      transform: translateY(-2px);
    }

    .lawyer-card img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 50%;
      border: 2px solid #ccc;
    }

    .lawyer-info h3 {
      margin: 0;
      font-size: 18px;
      color: #680f0f;
      text-transform: capitalize;
    }

    .lawyer-info p {
      margin: 5px 0;
      font-size: 14px;
    }

  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <img src="./Images/logo0.png" alt="Logo" />
    </div>
    <h1 class="header-title">Legal Assistance</h1>
  </header>

  <main>
    <a href="women.html" class="back-link">← Back to Portal</a>
      <section>
        <h2>Our Legal Experts</h2>
        <?php foreach ($lawyerUsers as $leg): ?>
          <div class="lawyer-card">
            <img src="<?php echo !empty($leg['profile_picture']) ? htmlspecialchars($leg['profile_picture']) : 'https://via.placeholder.com/100'; ?>" alt="Lawyer Profile" />
            <div class="lawyer-info">
              <h3><?php echo htmlspecialchars($leg['username']); ?></h3>
              <p><strong>Email:</strong> <?php echo htmlspecialchars($leg['email']); ?></p>
              <p><strong>Phone:</strong> <?php echo htmlspecialchars($leg['phone']); ?></p>
              <?php if (!empty($leg['expertise'])): ?>
                <p><strong>Expertise:</strong> <?php echo htmlspecialchars($leg['expertise']); ?></p>
              <?php endif; ?>
              <?php if (!empty($leg['bio'])): ?>
                <p><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($leg['bio'])); ?></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </section>

    <section>
      <h2>Book a Legal Consultation</h2>
      <form method="POST" action="">
        <input type="hidden" name="book_consultation" value="1">
        <label for="legal-expert">Select a Legal Expert:</label>
        <select id="legal-expert" name="legal_expert" required>
          <option value="">-- Choose Expert --</option>
          <?php foreach ($consultants as $consultant): ?>
            <option value="<?= htmlspecialchars($consultant['username']) ?>">
              <?= htmlspecialchars($consultant['username']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <label for="legal-date">Select Date:</label>
        <input type="date" id="legal-date" name="consultation_date" required>
        <label for="legal-time">Select Time:</label>
        <input type="time" id="legal-time" name="consultation_time" required>
        <button type="submit">Book Consultation</button>
      </form>
    </section>

    <section>
      <h2>Add Legal Resource</h2>
      <form method="POST" action="">
        <input type="hidden" name="add_resource" value="1">

        <label for="title">Resource Title:</label>
        <input type="text" name="resource_title" id="title" required>

        <label for="description">Description:</label>
        <textarea name="resource_description" id="description" required></textarea>

        <label for="url">Resource Link (URL):</label>
        <input type="url" name="url" id="url" placeholder="https://example.com">

        <button type="submit">Submit Resource</button>
      </form>
    </section>

    <section>
      <h2>Legal Resources</h2>
      <?php if (!empty($resources)): ?>
        <ul>
          <?php foreach ($resources as $res): ?>
            <li>
              <strong><?= htmlspecialchars($res['title']) ?></strong><br>
              <?= nl2br(htmlspecialchars($res['description'])) ?><br>
              <?php if (!empty($res['url'])): ?>
                <a href="<?= htmlspecialchars($res['url']) ?>" target="_blank">Visit Resource</a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>No resources found.</p>
      <?php endif; ?>
    </section>

    <section>
      <h2>Multilingual Support</h2>
      <label for="language-select">Choose Language:</label>
      <select id="language-select">
        <option value="en">English</option>
        <option value="hi">Hindi</option>
        <option value="es">Spanish</option>
      </select>
      <button onclick="translatePage()">Translate</button>
    </section>
  </main>

  <script>
    function searchLegalResources() {
      const query = document.getElementById("legal-query").value;
      document.getElementById("legal-results").innerHTML =
        `<p>Showing results for <strong>${query}</strong>...</p>`;
    }

    function translatePage() {
      const lang = document.getElementById("language-select").value;
      alert(`Page will be translated to ${lang.toUpperCase()}`);
    }
  </script>
</body>
</html>
