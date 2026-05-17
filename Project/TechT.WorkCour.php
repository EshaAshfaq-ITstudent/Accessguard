<?php
// Database connection variables
$servername = "localhost";
$username = "u655850112_site";
$password = "Q0jAJnA][";
$dbname = "u655850112_site";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Validate and sanitize inputs (basic)
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $course = trim($_POST['course']);
  
  if (empty($name) || empty($email) || empty($course)) {
    $error_message = "Please fill all the required fields.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message = "Invalid email format.";
  } else {
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO enrollments (name, email, course) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $course);
    
    if ($stmt->execute()) {
      $success_message = "Thank you for enrolling! We will reach out to you soon.";
    } else {
      $error_message = "Error: " . $stmt->error;
    }
    
    $stmt->close();
  }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Technical Training - Workshops & Courses</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
      background-color: #f8f9fa;
      margin: 0;
      padding: 0;
      color: #1e293b;
    }

    /* header – unchanged color scheme */
    header {
      background-color: #680f0f;
      color: white;
      padding: 20px 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
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
      letter-spacing: -0.2px;
    }

    /* main container width remains same (max-width:1100px) */
    main {
      max-width: 1100px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    /* section background unchanged (soft purple/pink) */
    section {
      background: #f4e9f4;
      padding: 30px;
      border-radius: 12px;
      margin-bottom: 40px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }

    h2 {
      color: #023468;
      margin-bottom: 24px;
      font-size: 1.8rem;
      font-weight: 600;
      border-left: 5px solid #680f0f;
      padding-left: 18px;
    }

    /* course grid – 3 columns, same as before */
    .course-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }

    .course-card {
      background: #fff;
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .course-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 18px 28px -8px rgba(104, 15, 15, 0.2);
    }

    .course-card img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      background: #e2e8f0;
    }

    .course-card-body {
      padding: 18px 16px 20px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .course-card h4 {
      margin: 0 0 8px 0;
      color: #023468;
      font-size: 1.2rem;
      font-weight: 700;
    }

    /* detailed description (like second image style) – longer, meaningful text */
    .course-description {
      font-size: 0.85rem;
      line-height: 1.45;
      color: #334155;
      margin-bottom: 16px;
      min-height: 60px;
    }

    /* meta row for rating + duration (clean badges) */
    .course-meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
      margin-top: 4px;
      flex-wrap: wrap;
      gap: 8px;
    }

    .rating-badge {
      background: #fef3e2;
      padding: 4px 12px;
      border-radius: 30px;
      font-size: 0.75rem;
      font-weight: 600;
      color: #b45309;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .duration-badge {
      background: #eef2ff;
      padding: 4px 12px;
      border-radius: 30px;
      font-size: 0.75rem;
      font-weight: 500;
      color: #1e3a8a;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    /* fee styling (optional but professional) */
    .course-fee {
      font-weight: 700;
      color: #680f0f;
      background: #fff0f0;
      padding: 4px 12px;
      border-radius: 30px;
      font-size: 0.8rem;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    /* button styling: "Enroll Now" maroon outline with hover fill – matches color scheme */
    .buy-btn {
      width: 100%;
      background: transparent;
      border: 2px solid #680f0f;
      color: #680f0f;
      padding: 10px 0;
      font-size: 0.9rem;
      font-weight: 700;
      border-radius: 40px;
      cursor: pointer;
      transition: all 0.25s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 12px;
    }

    .buy-btn i {
      font-size: 1rem;
    }

    .buy-btn:hover {
      background: #680f0f;
      color: white;
      box-shadow: 0 6px 12px rgba(104, 15, 15, 0.25);
    }

    /* success stories grid */
    .success-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 24px;
    }

    .success-item {
      flex: 1 1 220px;
      background-color: #fff;
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      transition: transform 0.2s;
    }

    .success-item:hover {
      transform: translateY(-4px);
    }

    .success-item img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      background: #d9d0e0;
    }

    .success-item div {
      padding: 14px;
    }

    .success-item h4 {
      color: #680f0f;
      margin-bottom: 6px;
      font-size: 1rem;
    }

    .success-item p {
      font-size: 0.8rem;
      line-height: 1.4;
      color: #2c3e50;
    }

    /* modal styles (unchanged style but functional) */
    .modal {
      display: none;
      position: fixed;
      z-index: 99;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(3px);
    }

    .modal-content {
      background-color: #fff;
      margin: 8% auto;
      padding: 28px 24px;
      border-radius: 24px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 20px 30px rgba(0, 0, 0, 0.2);
    }

    .modal-content input,
    .modal-content select {
      width: 100%;
      padding: 12px 14px;
      margin: 10px 0;
      border-radius: 40px;
      border: 1px solid #cbd5e1;
      font-size: 0.9rem;
    }

    .modal-content input:focus,
    .modal-content select:focus {
      outline: none;
      border-color: #680f0f;
      box-shadow: 0 0 0 2px rgba(104, 15, 15, 0.2);
    }

    .close {
      float: right;
      font-size: 28px;
      cursor: pointer;
      transition: color 0.2s;
    }

    .close:hover {
      color: #680f0f;
    }

    button.enroll-btn {
      background-color: #023468;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 40px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 12px;
      width: 100%;
    }

    button.enroll-btn:hover {
      background-color: #011f3f;
    }
    .buy-btn {
    display: inline-block;
    text-align: center;
    text-decoration: none;
}
    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 24px;
      color: #680f0f;
      text-decoration: none;
      font-weight: 600;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    @media (max-width: 780px) {
      .course-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 550px) {
      .course-grid {
        grid-template-columns: 1fr;
      }
      .header-left {
        position: static;
        margin-right: 12px;
      }
      header {
        flex-wrap: wrap;
        gap: 10px;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <img src="./Images/logo0.png" alt="Logo" />
    </div>
    <h2 class="header-title">Workshops and courses</h2>
  </header>

  <main>
    <a href="Techtraining.html" class="back-link"><i class="bi bi-arrow-left"></i> Back to Portal</a>

    <!-- Course section with detailed descriptions & ENROLL NOW button -->
    <section>
      <h2>📖 Available Courses</h2>
      <div class="course-grid">

        <!-- 1. Canva for Beginners – detailed description like second reference -
        <div class="course-card">
          <img src="./Images/canva.jpg" alt="Canva course" onerror="this.src='https://placehold.co/400x200?text=Canva'">
          <div class="course-card-body">
            <h4>Canva for Beginners</h4>
            <p class="course-description">Learn to create stunning visuals and presentations using Canva's drag-and-drop tools — perfect for social media, business use, and professional branding.</p>
            <div class="course-meta">
              <span class="rating-badge"><i class="bi bi-star-fill"></i> 4.8</span>
              <span class="duration-badge"><i class="bi bi-clock"></i> 6 Hours</span>
              <span class="course-fee">Rs.1000</span>
            </div>
            <button class="buy-btn" onclick="openForm('Canva for Beginners')"><i class="bi bi-cart-check"></i> Enroll Now</button>
          </div>
        </div>

         2. Microsoft Word & Excel 
        <div class="course-card">
          <img src="./Images/wordexcel.jpg" alt="MS Office" onerror="this.src='https://placehold.co/400x200?text=Excel+Word'">
          <div class="course-card-body">
            <h4>Microsoft Word & Excel</h4>
            <p class="course-description">Master essential document creation and data analysis with real-world exercises in Word and Excel. Boost productivity and office skills.</p>
            <div class="course-meta">
              <span class="rating-badge"><i class="bi bi-star-fill"></i> 4.7</span>
              <span class="duration-badge"><i class="bi bi-clock"></i> 8 Hours</span>
              <span class="course-fee">Rs.1000</span>
            </div>
            <button class="buy-btn" onclick="openForm('Microsoft Word & Excel')"><i class="bi bi-cart-check"></i> Enroll Now</button>
          </div>
        </div>

         Frontend Fundamentals 
        <div class="course-card">
          <img src="./Images/frontendfundamental.jpg" alt="Frontend" onerror="this.src='https://placehold.co/400x200?text=Frontend'">
          <div class="course-card-body">
            <h4>Frontend Fundamentals</h4>
            <p class="course-description">Build beautiful websites from scratch with foundational web technologies: HTML, CSS, and modern responsive design. No prior coding experience needed!</p>
            <div class="course-meta">
              <span class="rating-badge"><i class="bi bi-star-fill"></i> 4.9</span>
              <span class="duration-badge"><i class="bi bi-clock"></i> 10 Hours</span>
              <span class="course-fee">Rs.2000</span>
            </div>
            <button class="buy-btn" onclick="openForm('Frontend Fundamentals')"><i class="bi bi-cart-check"></i> Enroll Now</button>
          </div>
        </div>

         4. Backend Development 
        <div class="course-card">
          <img src="./Images/backend.jpg" alt="Backend" onerror="this.src='https://placehold.co/400x200?text=Backend'">
          <div class="course-card-body">
            <h4>Backend Development</h4>
            <p class="course-description">Explore backend systems like a pro — learn server-side logic, databases, debugging techniques, and API fundamentals to enhance development productivity.</p>
            <div class="course-meta">
              <span class="rating-badge"><i class="bi bi-star-fill"></i> 4.6</span>
              <span class="duration-badge"><i class="bi bi-clock"></i> 12 Hours</span>
              <span class="course-fee">Rs.3000</span>
            </div>
            <button class="buy-btn" onclick="openForm('Backend Development')"><i class="bi bi-cart-check"></i> Enroll Now</button>
          </div>
        </div>
  -->

        <!-- 5. AI for Life Sciences (detailed) -->
        <div class="course-card">
          <img src="./Images/lifescience.jpg" alt="AI Life Sciences" onerror="this.src='https://placehold.co/400x200?text=AI+Life+Sciences'">
          <div class="course-card-body">
            <h4>AI for Life Sciences</h4>
            <p class="course-description">Cutting-edge AI applications in healthcare, genomics, and drug discovery. Learn how machine learning transforms biology and medicine.</p>
            <div class="course-meta">
              <span class="rating-badge"><i class="bi bi-star-fill"></i> 4.5</span>
              <span class="duration-badge"><i class="bi bi-clock"></i> 48 Hours</span>
              <span class="course-fee"> Rs.6000</span>
            </div>
            <a href="ai_life_sciences.php" class="buy-btn"><i class="bi bi-cart-check"></i>Enroll Now</a>
          </div>
        </div>

        <!-- 6. Web Development Through AI -->
        <div class="course-card">
          <img src="./Images/AIwebsite.jpg" alt="Web Dev AI" onerror="this.src='https://placehold.co/400x200?text=Web+AI'">
          <div class="course-card-body">
            <h4>Web Development Through AI</h4>
            <p class="course-description">Build modern websites using AI-powered tools and copilots. Speed up development with smart code generation and design assistance.</p>
            <div class="course-meta">
              <span class="rating-badge"><i class="bi bi-star-fill"></i> 4.8</span>
              <span class="duration-badge"><i class="bi bi-clock"></i> 48 Hours</span>
              <span class="course-fee">Rs.6000</span>
            </div>
            <a href="web_development_ai.php" class="buy-btn"><i class="bi bi-cart-check"></i>Enroll Now</a>
          </div>
        </div>

        <!-- 7. Data Analysis with AI -->
        <div class="course-card">
          <img src="./Images/dataanalysis.jpg" alt="Data Analysis" onerror="this.src='https://placehold.co/400x200?text=Data+AI'">
          <div class="course-card-body">
            <h4>Data Analysis with AI</h4>
            <p class="course-description">Analyze data using Python, Pandas, and AI-driven insights. Perfect for aspiring data analysts and business intelligence enthusiasts.</p>
            <div class="course-meta">
              <span class="rating-badge"><i class="bi bi-star-fill"></i> 4.8</span>
              <span class="duration-badge"><i class="bi bi-clock"></i> 48 Hours</span>
              <span class="course-fee">Rs.10000</span>
            </div>
            <a href="data_analysis_ai.php" class="buy-btn"><i class="bi bi-cart-check"></i>Enroll Now</a>
          </div>
        </div>

        <!-- 8. Personal Branding through LinkedIn -->
        <div class="course-card">
          <img src="./Images/linkedin.jpg" alt="LinkedIn Branding" onerror="this.src='https://placehold.co/400x200?text=LinkedIn'">
          <div class="course-card-body">
            <h4>Personal Branding through LinkedIn</h4>
            <p class="course-description">Build a strong professional presence, optimize your profile, network effectively, and grow career opportunities with LinkedIn mastery.</p>
            <div class="course-meta">
              <span class="rating-badge"><i class="bi bi-star-fill"></i> 4.8</span>
              <span class="duration-badge"><i class="bi bi-clock"></i> 24 Hours</span>
              <span class="course-fee"> Rs.8000</span>
            </div>
            <a href="linkedin_branding.php" class="buy-btn"><i class="bi bi-cart-check"></i>Enroll Now</a>
          </div>
        </div>

        <!-- 9. Python 60 Days Bootcamp -->
        <div class="course-card">
          <img src="./Images/python.jpg" alt="Python Bootcamp" onerror="this.src='https://placehold.co/400x200?text=Python'">
          <div class="course-card-body">
            <h4>Python 60 Days Bootcamp</h4>
            <p class="course-description">Learn Python from zero to real-world projects. Includes data structures, OOP, automation, and capstone project — become job-ready.</p>
            <div class="course-meta">
              <span class="rating-badge"><i class="bi bi-star-fill"></i> 4.8</span>
              <span class="duration-badge"><i class="bi bi-calendar-week"></i> 60 Days</span>
              <span class="course-fee"> Rs.10000</span>
            </div>
            <a href="python_bootcamp.php" class="buy-btn"><i class="bi bi-cart-check"></i>Enroll Now</a>          </div>
        </div>

        <!-- 10. Full-Stack Web Development – Pro Track (extra to match workshop style) 
        <div class="course-card">
          <img src="./Images/fullstack.jpg" alt="Full Stack" onerror="this.src='https://placehold.co/400x200?text=Full+Stack'">
          <div class="course-card-body">
            <h4>Full-Stack Web Development – Pro Track</h4>
            <p class="course-description">Master both frontend & backend in just 1 month — build real-world projects, APIs, and learn deployment skills with modern frameworks.</p>
            <div class="course-meta">
              <span class="rating-badge"><i class="bi bi-star-fill"></i> 4.9</span>
              <span class="duration-badge"><i class="bi bi-clock"></i> 1 Month Intensive</span>
              <span class="course-fee"> Rs.6000</span>
            </div>
            <button class="buy-btn" onclick="openForm('Full-Stack Web Development – Pro Track')"><i class="bi bi-cart-check"></i> Enroll Now</button>
          </div>
        </div>-->

      </div>
    </section>

    <!-- Success Stories section (unchanged layout but better readability) -->
    <section>
      <h2>🏅 Our Success Stories</h2>
      <div class="success-grid">
        <div class="success-item">
          <img src="./Images/JTECH2025.jpg" alt="JTECH 2025" onerror="this.src='https://placehold.co/400x200?text=JTECH'">
          <div>
            <h4>JTECH 2025</h4>
            <p>At JTECH 2025, we proudly showcased our startup *ConnectoLead* and won a prize voucher from Trend Micro for our innovative impact.</p>
          </div>
        </div>
        <div class="success-item">
          <img src="./Images/Workshop.jpg.jpeg" alt="Graphic Design Day" onerror="this.src='https://placehold.co/400x200?text=Design+Day'">
          <div>
            <h4>Graphic Design Day</h4>
            <p>Empowering Students at Falconhouse Grammar School. Participants explored design thinking while creating real-world posters and flyers using Canva tools.</p>
          </div>
        </div>
        <div class="success-item">
          <img src="./Images/Workshop1.jpg" alt="Canva online session" onerror="this.src='https://placehold.co/400x200?text=Online+Session'">
          <div>
            <h4>Canva Design online session</h4>
            <p>We hosted an online tech training session focused on Canva, welcoming students who registered through our social media platforms.</p>
          </div>
        </div>
        <div class="success-item">
          <img src="./Images/juwerita.jpg" alt="JUW ERITA" onerror="this.src='https://placehold.co/400x200?text=JUW+ERITA'">
          <div>
            <h4>JUW ERITA</h4>
            <p>At JUW-ERITA, we showcased our project and pitched our business to esteemed judges, receiving immense appreciation for our innovation and vision.</p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Enrollment Modal (unchanged but functional) -->
  <div class="modal" id="enrollModal">
    <div class="modal-content">
      <span class="close" onclick="closeForm()">&times;</span>
      <h3>📝 Course Enrollment</h3>
      
      <!-- PHP messages will be rendered here if any backend logic is added, but we keep same structure -->
      <?php if (!empty($success_message)) : ?>
        <p style="color:green; font-weight:bold;"><?php echo $success_message; ?></p>
      <?php elseif (!empty($error_message)) : ?>
        <p style="color:red; font-weight:bold;"><?php echo $error_message; ?></p>
      <?php endif; ?>
      
      <form method="POST" action="">
        <input type="text" name="name" placeholder="Your Full Name" required />
        <input type="email" name="email" placeholder="Your Email Address" required />
        <select name="course" required>
          <option value="">-- Select Course --</option>
          <option>Canva for Beginners</option>
          <option>Microsoft Word & Excel</option>
          <option>Frontend Fundamentals</option>
          <option>Backend Development</option>
          <option>AI for Life Sciences</option>
          <option>Web Development Through AI</option>
          <option>Data Analysis with AI</option>
          <option>Personal Branding through LinkedIn</option>
          <option>Python 60 Days Bootcamp</option>
          <option>Full-Stack Web Development – Pro Track</option>
        </select>
        <button type="submit" class="enroll-btn">Submit Enrollment</button>
      </form>
    </div>
  </div>

<script>
  function openForm(courseName) {
    document.getElementById('enrollModal').style.display = 'block';
    const select = document.querySelector('#enrollModal select[name="course"]');
    if (select) {
      select.value = courseName;
    }
  }

  function closeForm() {
    document.getElementById('enrollModal').style.display = 'none';
  }

  function submitEnrollment(e) {
    e.preventDefault();
    alert('Thank you for enrolling! We will reach out to you soon.');
    closeForm();
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    const modal = document.getElementById('enrollModal');
    if (event.target === modal) {
      modal.style.display = "none";
    }
  }
</script>

</body>
</html>
