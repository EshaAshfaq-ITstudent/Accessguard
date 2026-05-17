<?php
$host = "localhost";
$username = "u655850112_site";
$password = "Q0jAJnA][";
$dbname = "u655850112_site";

// Establishing connection
$conn = new mysqli($host, $username, $password, $dbname);

// Checking for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieving form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $preferred_datetime = $_POST['preferred_datetime'] ?? ''; // Expecting 'YYYY-MM-DD HH:MM:SS'
    $mode = $_POST['consultation_mode'] ?? '';
    $message = $_POST['message'] ?? NULL; // Message can be NULL
    
    // Debugging output
    error_log("Name: $name, Email: $email, Topic: $topic, Preferred Time: $preferred_datetime, Mode: $mode, Message: $message");

    if ($name && $email && $topic && $preferred_datetime) {
        $sql = "INSERT INTO consultations (name, email, topic, preferred_datetime, consultation_mode, message)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $email, $topic, $preferred_datetime, $mode, $message);
        
        if ($stmt->execute()) {
            echo "<script>alert('Consultation request submitted successfully!');</script>";
        } else {
            error_log("Error executing query: " . $stmt->error);
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

// Closing the connection
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Online Consultation</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #fffaf0;
      margin: 0;
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

    .header-left a {
      color: white;
      text-decoration: none;
      font-size: 16px;
      margin-left: 10px;
    }

    .header-title {
      font-size: 28px;
      font-weight: bold;
      color: white;
      margin: 0;
    }

    .back-container {
      text-align: left;
      padding: 15px 40px 0;
      margin-bottom: -15px;
    }

    .back-link {
      color: #680f0f;
      text-decoration: none;
      font-weight: bold;
      font-size: 16px;
    }

    .back-link i {
      margin-right: 6px;
    }

    .team-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      padding: 40px 20px;
      gap: 30px;
    }

    .mentor-card {
      background-color: #fff;
      border-radius: 16px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
      padding: 25px;
      width: 300px;
      text-align: left;
      transition: transform 0.2s ease;
      cursor: pointer;
    }

    .mentor-card:hover {
      transform: translateY(-6px);
    }

    .mentor-card h3 {
      color: #023468;
      margin-bottom: 5px;
    }

    .mentor-card span {
      display: inline-block;
      margin-bottom: 15px;
      font-weight: bold;
      color: #680f0f;
    }

    .popup {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .popup-content {
      background: #fff;
      padding: 35px;
      border-radius: 20px;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .popup-content h2 {
      color: #023468;
      margin-bottom: 10px;
    }

    .popup-content p {
      color: #444;
      line-height: 1.6;
      margin-top: 10px;
      font-size: 16px;
    }

    .popup-content .profession {
      color: #680f0f;
      font-weight: bold;
    }

    .close-btn {
      background-color: #680f0f;
      color: white;
      padding: 10px 20px;
      margin-top: 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    .form-section {
      background-color: #f5f5f5;
      padding: 40px 20px;
      margin-top: 60px;
      text-align: center;
    }

    .form-section h2 {
      font-size: 28px;
      color: #023468;
      margin-bottom: 20px;
    }

    .form-section form {
      max-width: 600px;
      margin: auto;
      background-color: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .form-section input,
    .form-section textarea,
    .form-section select {
      width: 100%;
      padding: 14px;
      margin-bottom: 20px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
    }

    .form-section button {
      background-color: #680f0f;
      color: white;
      padding: 14px 28px;
      font-size: 16px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
    }

    .form-section button:hover {
      background-color: #540d0d;
    }

    @media (max-width: 600px) {
      .mentor-card {
        width: 90%;
      }

      .form-section form {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <img src="./Images/logo0.png" alt="Logo" />
    </div>
    <h1 class="header-title">Online Consultation</h1>
  </header>

  <div class="back-container">
    <a href="senior citizen.html" class="back-link">
      <i class="bi bi-arrow-left"></i> Back to Portal
    </a>
  </div>

  <div class="team-container">
    <div class="mentor-card" onclick="showPopup('Zainab Sheikh', 'Legal Consultant', `Zainab provides legal guidance for women facing harassment and domestic issues. She offers 1:1 Zoom sessions every Friday and Saturday.`)">
      <h3>Zainab Sheikh</h3>
      <span>Legal Consultant</span>
      <p>Zoom sessions on legal awareness and protection rights.</p>
    </div>

    <div class="mentor-card" onclick="showPopup('Dr. Farah Usman', 'Mental Health Expert', `Dr. Farah specializes in therapy for trauma, anxiety, and personal development. Google Meet slots available every weekday after 5 PM.`)">
      <h3>Dr. Farah Usman</h3>
      <span>Mental Health Expert</span>
      <p>Therapy sessions and emotional health check-ins.</p>
    </div>
  </div>

  <div class="popup" id="mentorPopup">
    <div class="popup-content">
      <h2 id="popupName"></h2>
      <div class="profession" id="popupProfession"></div>
      <p id="popupDescription"></p>
      <button class="close-btn" onclick="closePopup()">Close</button>
    </div>
  </div>

  <div class="form-section">
    <h2>Book a Consultation</h2>
    <form action="consultations.php" method="POST">
        <input type="text" name="name" placeholder="Full Name" required />
        <input type="email" name="email" placeholder="Email Address" required />
        <input type="text" name="topic" placeholder="Consultation Topic" required />
        <input type="datetime-local" name="preferred_datetime" required />
        <select name="consultation_mode" required>
            <option value="">Select Mode</option>
            <option value="Zoom">Zoom</option>
            <option value="Google Meet">Google Meet</option>
        </select>
        <textarea name="message" rows="5" placeholder="Additional Notes (Optional)"></textarea>
        <button type="submit">Submit Consultation</button>
    </form>
  </div>

  <script>
    function showPopup(name, profession, description) {
      document.getElementById("popupName").innerText = name;
      document.getElementById("popupProfession").innerText = profession;
      document.getElementById("popupDescription").innerText = description;
      document.getElementById("mentorPopup").style.display = "flex";
    }

    function closePopup() {
      document.getElementById("mentorPopup").style.display = "none";
    }
  </script>
</body>
</html>
