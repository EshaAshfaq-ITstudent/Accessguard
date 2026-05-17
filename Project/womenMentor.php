<?php
$servername = "localhost";
$username = "u655850112_site";
$password = "Q0jAJnA][";
$dbname = "u655850112_site";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Only process the form when it's submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Basic check to avoid undefined key warning
  $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
  $email = isset($_POST['email']) ? $_POST['email'] : '';
  $profession = isset($_POST['profession']) ? $_POST['profession'] : '';
  $reason = isset($_POST['reason']) ? $_POST['reason'] : '';

  // You can also validate that they are not empty before inserting
  if (!empty($fullname) && !empty($email) && !empty($profession) && !empty($reason)) {
    $sql = "INSERT INTO mentorship_requests (fullname, email, profession, reason)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $fullname, $email, $profession, $reason);

    if ($stmt->execute()) {
      echo "Mentorship request submitted successfully!";
    } else {
      echo "Error: " . $stmt->error;
    }

    $stmt->close();
  } else {
    echo "Please fill out all fields.";
  }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mentorship Page</title>
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
      justify-content: center; /* Centers the heading */
      position: relative; /* So absolute children like logo are positioned relative to header */
    }

    .header-left {
      position: absolute;
      left: 40px; /* Pushes it to the left */
      display: flex;
      align-items: center;
    }

    .header-left img {
      height: 70px; /* Adjust as needed */
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
    .form-section textarea {
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
      background-color: #680f0f;
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
    <h1 class="header-title">Mentorship</h1>
  </header>


<!-- Back to Portal Link Positioned Below Header -->
<div class="back-container">
  <a href="women.html" class="back-link">
    <i class="bi bi-arrow-left"></i> Back to Portal
  </a>
</div>

<!-- Mentors Section -->
<div class="team-container">
  <div class="mentor-card" onclick="showPopup('Ayesha Khan', 'Lawyer', `Ayesha is a senior human rights lawyer with over 15 years of experience.\nShe has worked on various legal reforms for women’s rights.\nHer focus is on domestic violence and workplace harassment laws.\nShe believes in empowering women through legal awareness.\nAyesha has mentored hundreds of survivors.`)">
    <h3>Ayesha Khan</h3>
    <span>Lawyer</span>
    <p>Expert in legal rights, protection laws, and social justice.</p>
  </div>

  <div class="mentor-card" onclick="showPopup('Dr. Meher Ali', 'Mental Health Consultant', `Dr. Meher is a certified psychologist specializing in women’s trauma.\nShe helps women overcome anxiety, PTSD, and abuse recovery.\nShe believes in holistic care and emotional support.\nShe offers both one-on-one and group therapy.\nHer warmth and empathy make her a favorite among clients.`)">
    <h3>Dr. Meher Ali</h3>
    <span>Mental Health Consultant</span>
    <p>Specialist in counseling, trauma therapy, and emotional healing.</p>
  </div>

  <div class="mentor-card" onclick="showPopup('Sara Malik', 'Health Advisor', `Sara is a nutritionist and health advisor for women.\nShe has worked with NGOs to improve female health literacy.\nHer work includes reproductive health education.\nShe helps women plan healthy lifestyles and wellness routines.\nSara is passionate about inclusive health access.`)">
    <h3>Sara Malik</h3>
    <span>Health Advisor</span>
    <p>Focused on women's physical well-being and nutrition guidance.</p>
  </div>

  <div class="mentor-card" onclick="showPopup('Amna Rehman', 'Family Lawyer', `Amna specializes in family law, divorce, and child custody cases.\nShe advocates for women in emotionally challenging situations.\nHer mission is to ensure justice and peace in family matters.\nAmna provides consultation both online and in-person.\nShe empowers women to know their legal options.`)">
    <h3>Amna Rehman</h3>
    <span>Family Lawyer</span>
    <p>Expert in legal family rights and relationship counseling.</p>
  </div>
</div>

<!-- Pop-up Modal -->
<div class="popup" id="mentorPopup">
  <div class="popup-content">
    <h2 id="popupName"></h2>
    <div class="profession" id="popupProfession"></div>
    <p id="popupDescription"></p>
    <button class="close-btn" onclick="closePopup()">Close</button>
  </div>
</div>

<!-- Mentorship Request Form -->
<div class="form-section">
  <h2>Request Mentorship</h2>
  <form action="womenMentor.php" method="POST">
    <div style="width: 95%;">
      <input type="text" name="fullname" placeholder="Full Name" required />
      <input type="email" name="email" placeholder="Email Address" required />
      <input type="text" name="profession" placeholder="Your Profession" required />
      <textarea name="reason" rows="5" placeholder="Why do you want mentorship?" required></textarea>
    </div>
    <button type="submit">Submit Request</button>
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
