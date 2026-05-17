<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // PHP backend handling
    $host = "localhost";
    $user = "u655850112_site";
    $pass = "Q0jAJnA][";
    $db = "u655850112_site";

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        echo "error: Database connection failed";
        exit;
    }

    $name = $_POST['name'];
    $email = $_POST['email'];
    $interest = $_POST['interest'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO career_support (name, email, interest, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $interest, $message);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: Could not submit form";
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mentorship - ConnectoLead</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      margin: 0;
      background-color: #f3f6f9;
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
      color: white;
      margin: 0;
    }
    main {
      max-width: 1100px;
      margin: auto;
      padding: 40px 20px;
    }
    section {
      background-color: #f4e9f4;
      padding: 30px;
      margin-bottom: 40px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .section-title {
      text-align: center;
      color: #023468;
      font-size: 28px;
      margin-bottom: 40px;
    }
    h2 {
      color: #023468;
      margin-bottom: 20px;
    }
    .mentor-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }
    .mentor-card {
      flex: 1 1 230px;
      background-color: #f7f9fb;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    }
    .mentor-card img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 50%;
      margin-bottom: 10px;
    }
    form {
      display: flex;
      flex-direction: column;
    }
    form input,
    form textarea,
    form select {
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
    }
    form button {
      padding: 12px;
      background-color: #023468;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
    }
    form button:hover {
      background-color: #021e3a;
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
    @media (max-width: 600px) {
      .mentor-grid {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <img src="./Images/logo0.png" alt="Logo" />
    </div>
    <h1 class="header-title">Mentorship & Career Support</h1>
  </header>

  <main>
    <a href="Techtraining.html" class="back-link">
      <i class="bi bi-arrow-left"></i> Back to Portal
    </a>

    <section>
      <h2 class="section-title">About</h2>
      <p>
        Tech Trainings offers a range of workshops, courses, and personalized
        learning plans designed to equip students with essential technical
        skills. These trainings cover various fields, including programming,
        digital marketing, and design.
      </p>
      <p>
        Whether you're a beginner or someone wanting to upgrade your existing skills, our curated resources and structured training programs will help you succeed.
      </p>
    </section>

    <!-- Mentors -->
    <section>
      <h2>Meet Our Mentors</h2>
      <div class="mentor-grid">
        <div class="mentor-card">
          <img src="https://via.placeholder.com/100" alt="Mentor 1" />
          <h4>Ayesha Khan</h4>
          <p>Frontend Developer | HTML, CSS, JS</p>
        </div>
        <div class="mentor-card">
          <img src="https://via.placeholder.com/100" alt="Mentor 2" />
          <h4>Rabia Sheikh</h4>
          <p>Digital Marketing Expert | SEO & Social Media</p>
        </div>
        <div class="mentor-card">
          <img src="https://via.placeholder.com/100" alt="Mentor 3" />
          <h4>Sana Malik</h4>
          <p>Graphic Designer | Canva & Adobe Suite</p>
        </div>
        <div class="mentor-card">
          <img src="https://via.placeholder.com/100" alt="Mentor 4" />
          <h4>Mehwish Tariq</h4>
          <p>Full Stack Developer | Python & React</p>
        </div>
      </div>
    </section>

    <!-- Mentorship Form -->
    <section>
      <h2>Request a Mentorship Session</h2>
      <form id="mentorshipForm">
        <input type="text" name="name" placeholder="Your Full Name" required />
        <input type="email" name="email" placeholder="Email Address" required />
        <select name="interest" required>
          <option value="">Select Area of Interest</option>
          <option>Web Development</option>
          <option>Graphic Design</option>
          <option>Digital Marketing</option>
          <option>Full Stack Programming</option>
        </select>
        <textarea
          name="message"
          rows="4"
          placeholder="Describe your goals or questions..."
          required
        ></textarea>
        <button type="submit">Submit Request</button>
      </form>
    </section>
  </main>

  <script>
    document
      .getElementById("mentorshipForm")
      .addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch("TechT.MentCar.php", {
          method: "POST",
          body: formData,
        })
          .then((res) => res.text())
          .then((data) => {
            if (data.trim() === "success") {
              alert("Thank you! Your mentorship request has been submitted.");
              this.reset();
            } else {
              alert(data);
            }
          })
          .catch(() => {
            alert("Server error. Please try again later.");
          });
      });
  </script>
</body>
</html>
