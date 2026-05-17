<?php
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $servername = "localhost";
  $username = "u655850112_site";
  $password = "Q0jAJnA][";
  $dbname = "u655850112_site";

  if ($conn->connect_error) {
    die("<script>alert('Database connection failed.');</script>");
  }

  $name = $_POST['full-name'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $experience = $_POST['experience'];
  $course = $_POST['course-name'];

  $stmt = $conn->prepare("INSERT INTO digital_resources (full_name, email, phone, experience_level, course_name) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $name, $email, $phone, $experience, $course);

  if ($stmt->execute()) {
    echo "<script>alert('Enrollment successful for $course!');</script>";
  } else {
    echo "<script>alert('Something went wrong. Try again later.');</script>";
  }

  $stmt->close();
  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Technical Training - Digital Resources</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      margin: 0;
      background-color: #ffffff;
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
    h2 {
      color: #023468;
      margin-bottom: 20px;
    }
    .section-title {
      text-align: center;
      color: #023468;
      font-size: 28px;
      margin-bottom: 40px;
    }
    .course-section {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      justify-content: space-between;
    }
    .course-card {
      flex: 1 1 320px;
      background-color: #fff;
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    .course-card h3 {
      font-size: 22px;
      color: #023468;
      margin-bottom: 15px;
    }
    .course-card p {
      color: #555;
      font-size: 16px;
      margin-bottom: 15px;
    }
    .course-card ul {
      list-style-type: none;
      padding: 0;
      color: #555;
      text-align: left;
    }
    .course-card ul li {
      margin-bottom: 8px;
    }
    .cta-btn {
      padding: 12px;
      background-color: #023468;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
      display: inline-block;
      width: 100%;
    }
    .cta-btn:hover {
      background-color: #021e3a;
    }
    .enroll-form {
      background-color: #f9f9f9;
      padding: 20px;
      border-radius: 8px;
      margin-top: 15px;
    }
    .enroll-form input,
    .enroll-form select {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
      margin-bottom: 15px;
    }
    .enroll-form button {
      padding: 12px;
      background-color: #023468;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      width: 100%;
    }
    .enroll-form button:hover {
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
    @media (max-width: 768px) {
      .course-section {
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
    <h2 class="header-title">Digital Resources</h2>
  </header>

  <main>
    <a href="Techtraining.html" class="back-link"><i class="bi bi-arrow-left"></i> Back to Portal</a>

    <section>
      <h2 class="section-title">About</h2>
      <p>We offer workshops, courses, and training plans to equip learners with essential digital skills like coding, marketing, and design.</p>
      <p>Whether you’re just starting or looking to upgrade your tech toolkit, we’ve got something tailored for you!</p>
    </section>

    <section>
      <h2 class="section-title">Explore Our Courses</h2>
      <div class="course-section">

        <!-- Web Development -->
        <div class="course-card">
          <h3>Web Development</h3>
          <p>Learn to build modern websites using HTML, CSS, JS, and Git.</p>
          <ul>
            <li>Responsive Design</li>
            <li>JavaScript Basics</li>
            <li>Version Control</li>
            <li>Deployment</li>
          </ul>
          <div class="enroll-form">
            <h4>Enroll in Web Development</h4>
            <form method="post" action="TechDigiResources.php">
              <input type="hidden" name="course-name" value="Web Development">
              <input type="text" name="full-name" placeholder="Full Name" required />
              <input type="email" name="email" placeholder="Email Address" required />
              <input type="tel" name="phone" placeholder="Phone Number" required />
              <select name="experience" required>
                <option value="">Experience Level</option>
                <option>Beginner</option>
                <option>Intermediate</option>
                <option>Advanced</option>
              </select>
              <button type="submit">Enroll Now</button>
            </form>
          </div>
        </div>

        <!-- Digital Marketing -->
        <div class="course-card">
          <h3>Digital Marketing</h3>
          <p>Master SEO, social media, ads, and blogging to boost brands online.</p>
          <ul>
            <li>SEO & SMM</li>
            <li>Google & FB Ads</li>
            <li>Content Strategy</li>
            <li>Analytics</li>
          </ul>
          <div class="enroll-form">
            <h4>Enroll in Digital Marketing</h4>
            <form method="post" action="digital_resources.php">
              <input type="hidden" name="course-name" value="Digital Marketing">
              <input type="text" name="full-name" placeholder="Full Name" required />
              <input type="email" name="email" placeholder="Email Address" required />
              <input type="tel" name="phone" placeholder="Phone Number" required />
              <select name="experience" required>
                <option value="">Experience Level</option>
                <option>Beginner</option>
                <option>Intermediate</option>
                <option>Advanced</option>
              </select>
              <button type="submit">Enroll Now</button>
            </form>
          </div>
        </div>

        <!-- Graphic Design -->
        <div class="course-card">
          <h3>Graphic Design</h3>
          <p>Use Canva & Adobe tools to craft logos, posters, and social media graphics.</p>
          <ul>
            <li>Design Basics</li>
            <li>Canva & Adobe Tools</li>
            <li>Branding & Logos</li>
          </ul>
          <div class="enroll-form">
            <h4>Enroll in Graphic Design</h4>
            <form method="post" action="digital_resources.php">
              <input type="hidden" name="course-name" value="Graphic Design">
              <input type="text" name="full-name" placeholder="Full Name" required />
              <input type="email" name="email" placeholder="Email Address" required />
              <input type="tel" name="phone" placeholder="Phone Number" required />
              <select name="experience" required>
                <option value="">Experience Level</option>
                <option>Beginner</option>
                <option>Intermediate</option>
                <option>Advanced</option>
              </select>
              <button type="submit">Enroll Now</button>
            </form>
          </div>
        </div>

        <!-- Programming Fundamentals -->
        <div class="course-card">
          <h3>Programming Fundamentals</h3>
          <p>Start coding with Python, Java or C++. Learn logic, OOP, and problem-solving.</p>
          <ul>
            <li>Basic Programming</li>
            <li>Data Structures</li>
            <li>OOP Concepts</li>
            <li>Intro to Databases</li>
          </ul>
          <div class="enroll-form">
            <h4>Enroll in Programming</h4>
            <form method="post" action="digital_resources.php">
              <input type="hidden" name="course-name" value="Programming Fundamentals">
              <input type="text" name="full-name" placeholder="Full Name" required />
              <input type="email" name="email" placeholder="Email Address" required />
              <input type="tel" name="phone" placeholder="Phone Number" required />
              <select name="experience" required>
                <option value="">Experience Level</option>
                <option>Beginner</option>
                <option>Intermediate</option>
                <option>Advanced</option>
              </select>
              <button type="submit">Enroll Now</button>
            </form>
          </div>
        </div>

      </div>
    </section>
  </main>
</body>
</html>
