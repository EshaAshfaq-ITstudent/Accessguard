<?php
$servername = "localhost";
$username = "u655850112_site"; 
$password = "Q0jAJnA][";     
$dbname = "u655850112_site";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Use isset() to avoid undefined key warnings
if (isset($_POST['workshop']) && isset($_POST['name']) && isset($_POST['email'])) {
  $workshop = $_POST['workshop'];
  $name = $_POST['name'];
  $email = $_POST['email'];

  // Insert into DB
  $stmt = $conn->prepare("INSERT INTO parent_workshops (workshop_name, parent_name, email) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $workshop, $name, $email);

  if ($stmt->execute()) {
    echo "<script>alert('Registration successful for $workshop'); window.location.href='parents.TechWork.php';</script>";
  } else {
    echo "Error: " . $stmt->error;
  }

  $stmt->close();
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tech Workshops for Parents</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
      rel="stylesheet"
    />
    <style>
      body {
        font-family: "Segoe UI", sans-serif;
        background: #f0f4f8;
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
        padding: 40px 20px;
        max-width: 1000px;
        margin: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      }

      h2,
      h3 {
        color: #023468;
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

      .trainer-card {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        background: #f9f9f9;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
      }

      .trainer-card img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
      }

      .trainer-info {
        flex: 1;
      }

      .workshop {
        border: 1px solid #ccc;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 30px;
        background: #fefefe;
      }

      form {
        margin-top: 15px;
      }

      input,
      button {
        padding: 10px;
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
        cursor: pointer;
      }

      button:hover {
        background-color: #5a0d0d;
      }
    </style>
  </head>
  <body>
    <header>
      <div class="header-left">
        <img src="./Images/logo0.png" alt="Logo" />
      </div>
      <h1 class="header-title">Tech Workshops</h1>
    </header>

    <main>
    <a href="parentshub.html" class="back-link"
      ><i class="bi bi-arrow-left"></i> Back to Portal</a
    >

      <section>
        <h2>Meet the Trainers</h2>

        <div class="trainer-card">
          <img src="https://via.placeholder.com/100" alt="Trainer 1" />
          <div class="trainer-info">
            <h3>Ms. Sarah Ahmed</h3>
            <p><strong>Expertise:</strong> Smart Home Devices</p>
            <p><strong>Profession:</strong> IoT Specialist at TechX</p>
          </div>
        </div>

        <div class="trainer-card">
          <img src="https://via.placeholder.com/100" alt="Trainer 2" />
          <div class="trainer-info">
            <h3>Mr. Bilal Khan</h3>
            <p><strong>Expertise:</strong> Cybersecurity & Safe Internet Use</p>
            <p>
              <strong>Profession:</strong> Senior Security Analyst at CyberSafe
            </p>
          </div>
        </div>

        <div class="trainer-card">
          <img src="https://via.placeholder.com/100" alt="Trainer 3" />
          <div class="trainer-info">
            <h3>Dr. Nida Shaikh</h3>
            <p><strong>Expertise:</strong> Educational Tech & Apps</p>
            <p>
              <strong>Profession:</strong> Professor of Educational Technology,
              JUW
            </p>
          </div>
        </div>
      </section>

      <h2>Upcoming Workshops</h2>

      <div class="workshop">
        <h3>Intro to Smart Devices</h3>
        <p>
          Understand how to set up and use smart home devices, parental
          controls, and more.
        </p>
        <p><strong>Date:</strong> April 25, 2025</p>
        <form action="parents.TechWork.php" method="POST">
          <input type="hidden" name="workshop" value="Intro to Smart Devices" />
          <input type="text" name="name" placeholder="Your Name" required />
          <input type="email" name="email" placeholder="Your Email" required />
          <button type="submit">Register</button>
        </form>
      </div>

      <div class="workshop">
        <h3>Safe Browsing for Kids</h3>
        <p>
          Learn tools and habits to keep your children safe online.
        </p>
        <p><strong>Date:</strong> May 5, 2025</p>
        <form action="parents.TechWork.php" method="POST">
          <input type="hidden" name="workshop" value="Safe Browsing for Kids" />
          <input type="text" name="name" placeholder="Your Name" required />
          <input type="email" name="email" placeholder="Your Email" required />
          <button type="submit">Register</button>
        </form>
      </div>

      <div class="workshop">
        <h3>Digital Tools for Homework Help</h3>
        <p>How to use educational apps and platforms to support your child's learning.</p>
        <p><strong>Date:</strong> May 12, 2025</p>
        <form action="parents.TechWork.php" method="POST">
          <input type="hidden" name="workshop" value="Digital Tools for Homework Help" />
          <input type="text" name="name" placeholder="Your Name" required />
          <input type="email" name="email" placeholder="Your Email" required />
          <button type="submit">Register</button>
        </form>
      </div>
    </main>
  </body>
</html>
