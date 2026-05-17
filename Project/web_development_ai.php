<?php
$servername = "localhost";
$username = "u655850112_site";
$password = "Q0jAJnA][";
$dbname = "u655850112_site";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success_message = $error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $father_name = trim($_POST['father_name']);
    $cnic = trim($_POST['cnic']);
    $email = trim($_POST['email']);
    $university = trim($_POST['university']);
    $course = trim($_POST['course']);
    $payment_method = trim($_POST['payment_method']);
    
    // Handle file upload
    $screenshot_name = "";
    if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] == 0) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $screenshot_name = time() . "_" . basename($_FILES['payment_screenshot']['name']);
        move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $upload_dir . $screenshot_name);
    }
    
    if (empty($name) || empty($email) || empty($course)) {
        $error_message = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("INSERT INTO enrollments (name, father_name, cnic, email, university, course, payment_method, payment_screenshot) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $name, $father_name, $cnic, $email, $university, $course, $payment_method, $screenshot_name);
        if ($stmt->execute()) {
            $success_message = "Thank you for enrolling! We will contact you soon.";
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COURSE TITLE - ConnecToLead</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #1e293b;
            line-height: 1.6;
        }
        header {
            background-color: #680f0f;
            color: white;
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            flex-wrap: wrap;
        }
        .header-left {
            position: absolute;
            left: 40px;
        }
        .header-left img {
            height: 60px;
        }
        .header-title {
            font-size: 24px;
            font-weight: bold;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #680f0f;
            text-decoration: none;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .course-header {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        .course-header-inner {
            display: flex;
            flex-wrap: wrap;
        }
        .course-image {
            flex: 0.8;
            min-width: 280px;
        }
        .course-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .course-title-section {
            flex: 1.5;
            padding: 35px;
        }
        .course-title-section h1 {
            color: #023468;
            font-size: 32px;
            margin-bottom: 15px;
        }
        .course-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 20px 0;
        }
        .badge {
            background: #f1f5f9;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .badge i {
            color: #680f0f;
        }
        .course-fee-box {
            background: #f4e9f4;
            padding: 15px 20px;
            border-radius: 16px;
            margin: 20px 0;
            display: inline-block;
        }
        .course-fee-box .fee {
            font-size: 28px;
            font-weight: bold;
            color: #680f0f;
        }
        .enroll-now-btn {
            background: #680f0f;
            color: white;
            padding: 14px 35px;
            border: none;
            border-radius: 40px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            text-decoration: none;
        }
        .enroll-now-btn:hover {
            background: #4a0a0a;
            transform: translateY(-2px);
        }
        .info-section {
            background: white;
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .info-section h2 {
            color: #023468;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #680f0f;
            display: inline-block;
        }
        .info-section h3 {
            color: #680f0f;
            margin: 20px 0 15px 0;
        }
        .info-section ul, .info-section ol {
            padding-left: 25px;
            margin: 15px 0;
        }
        .info-section li {
            margin: 10px 0;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .module-card {
            background: #f8fafc;
            padding: 18px;
            border-radius: 16px;
            border-left: 4px solid #680f0f;
        }
        .module-card h4 {
            color: #023468;
            margin-bottom: 10px;
        }
        .tools-list {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 15px;
        }
        .tool-tag {
            background: #e6f0fa;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 500;
        }
        .enroll-bottom {
            background: linear-gradient(135deg, #f4e9f4 0%, #e8d9e8 100%);
            padding: 40px;
            border-radius: 24px;
            text-align: center;
        }
        .enroll-bottom h2 {
            color: #023468;
            margin-bottom: 25px;
        }
        .enroll-form {
            max-width: 500px;
            margin: 0 auto;
        }
        .enroll-form input, .enroll-form select {
            width: 100%;
            padding: 14px 18px;
            margin: 12px 0;
            border: 1px solid #cbd5e1;
            border-radius: 50px;
            font-size: 15px;
        }
        .enroll-form button {
            width: 100%;
            padding: 14px;
            background: #680f0f;
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
        }
        .enroll-form button:hover {
            background: #4a0a0a;
        }
        .alert {
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #166534;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #991b1b;
        }
        @media (max-width: 768px) {
            .header-left {
                position: static;
                margin-bottom: 10px;
            }
            header {
                flex-direction: column;
                text-align: center;
            }
            .course-title-section {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
<header>
    <div class="header-left">
        <img src="Images/logo0.png" alt="Logo">   
    </div>
    <h2 class="header-title">ConnecToLead - Empowering Women in Tech</h2>
</header>

    <div class="container">
    <a href="TechT.WorkCour.php" class="back-link"><i class="bi bi-arrow-left"></i> Back to All Courses</a>

        <!-- COURSE HEADER SECTION -->
        <div class="course-header">
            <div class="course-header-inner">
            <div class="course-image">
            <img src="Images/AIwebsite.jpg" alt="AI for Life Sciences">
</div>
                <div class="course-title-section">
                    <h1>Web Development Through AI</h1>
                    <div class="course-badges">
                        <span class="badge"><i class="bi bi-star-fill" style="color:#f5a623;"></i> RATING 4.8 ★</span>
                        <span class="badge"><i class="bi bi-clock"></i> 48 Hours</span>
                        <span class="badge"><i class="bi bi-laptop"></i> Online Mode</span>
                        <span class="badge"><i class="bi bi-award"></i> Certificate Included</span>
                    </div>
                    <div class="course-fee-box">
                        <span class="fee">PKR 6000</span>
                        <span style="font-size: 14px;"> + GST</span>
                    </div>
                    <div>
                        <a href="#enroll" class="enroll-now-btn"><i class="bi bi-cart-check"></i> Enroll Now</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- COURSE OVERVIEW -->
        <div class="info-section">
            <h2><i class="bi bi-info-circle"></i> Course Overview</h2>
            <p>This course teaches modern web development using AI-powered tools that accelerate coding, design, debugging, and deployment. Participants will learn how AI assistants can help build responsive websites efficiently while understanding core web technologies.</p>
        </div>

       <!-- LEARNING OUTCOMES -->
<div class="info-section">
    <h2><i class="bi bi-trophy"></i> Learning Outcomes</h2>
    <p>By the end of this course, participants will be able to:</p>
    <ul>
        <li>Build responsive websites using HTML, CSS, and JavaScript</li>
        <li>Build responsive websites using HTML, CSS, and JavaScript</li>
        <li>Develop basic full-stack web applications</li>
        <li>Integrate APIs and deploy websites online</li>
    </ul>
</div>

<!-- COURSE MODULES (Detailed) -->
<div class="info-section">
    <h2><i class="bi bi-book"></i> Course Modules</h2>
    <div class="grid-2">

        <!-- Module 1 -->
        <div class="module-card">
            <h4>📘 Module 1: Introduction to Web Development & AI Tools (6 Hours)</h4>
            <ul>
                <li>Web development ecosystem</li>
                <li>AI coding assistants (ChatGPT, Copilot, etc.)</li>
                <li>Development environment setup</li>
            </ul>
        </div>

        <!-- Module 2 -->
        <div class="module-card">
            <h4>📘 Module 2: HTML & AI-Assisted Content Creation (8 Hours)</h4>
            <ul>
                <li>HTML fundamentals</li>
                <li>Structuring webpages</li>
                <li>AI tools for rapid layout generation</li>
            </ul>
        </div>

        <!-- Module 3 -->
        <div class="module-card">
            <h4>📘 Module 3: CSS & Responsive Design (8 Hours)</h4>
            <ul>
                <li>Styling webpages</li>
                <li>Flexbox and Grid</li>
                <li>AI‑assisted UI design</li>
            </ul>
        </div>

        <!-- Module 4 -->
        <div class="module-card">
            <h4>📘 Module 4: JavaScript Fundamentals (8 Hours)</h4>
            <ul>
                <li>Variables, functions, events</li>
                <li>DOM manipulation</li>
                <li>AI‑assisted debugging</li>
            </ul>
        </div>

        <!-- Module 5 -->
        <div class="module-card">
            <h4>📘 Module 5: APIs and Dynamic Websites (8 Hours)</h4>
            <ul>
                <li>REST APIs</li>
                <li>Fetching data</li>
                <li>Integrating external services</li>
            </ul>
        </div>

        <!-- Module 6 -->
        <div class="module-card">
            <h4>📘 Module 6: Deployment & Optimization (6 Hours)</h4>
            <ul>
                <li>Hosting platforms</li>
                <li>GitHub Pages / Netlify</li>
                <li>AI‑assisted performance optimization</li>
            </ul>
        </div>

        <!-- Module 7 -->
        <div class="module-card">
            <h4>📘 Module 7: Final Project (4 Hours)</h4>
            <ul>
                <li>Build and deploy a complete website</li>
            </ul>
        </div>

    </div>
</div>

<!-- TOOLS & TECHNOLOGIES -->
<div class="info-section">
    <h2><i class="bi bi-tools"></i> Tools & Technologies Covered</h2>
    <div class="tools-list">
        <span class="tool-tag">HTML</span>
        <span class="tool-tag">CSS</span>
        <span class="tool-tag">JavaScript</span>
        <span class="tool-tag">GitHub</span>
        <span class="tool-tag">AI tools</span>
       
    </div>
</div>

<!-- TARGET AUDIENCE -->
<!-- TARGET AUDIENCE -->
<div class="info-section">
    <h2><i class="bi bi-people"></i> Target Audience</h2>
    <ul>
        <li><strong>Students</strong> of Computer Science, Software Engineering, and IT</li>
        <li><strong>Beginners</strong> who want to learn web development with AI assistance</li>
        <li><strong>Freelancers</strong> looking to build websites faster using AI tools</li>
        <li><strong>Entrepreneurs</strong> who want to create their own websites or web apps</li>
        <li><strong>Career switchers</strong> aiming to move into Web Development or AI-assisted development</li>
    </ul>
</div>

        <!-- ASSESSMENT METHOD -->
        <div class="info-section">
            <h2><i class="bi bi-clipboard-check"></i> Assessment & Certification</h2>
            <ul>
                <li><strong>Hands-on Exercises:</strong> Practical coding assignments after each module</li>
                <li><strong>Mini Project:</strong> Real-world dataset analysis or application development</li>
                <li><strong>Final Assessment:</strong> Comprehensive evaluation at course end</li>
                <li><strong>Certificate:</strong> Course completion certificate from ConnecToLead</li>
            </ul>
        </div>

        <!-- WHY THIS COURSE? -->
        <div class="info-section">
            <h2><i class="bi bi-gem"></i> Why Choose This Course?</h2>
            <div class="grid-2">
                <div>
                    <ul>
                        <li>✅ Industry-relevant curriculum</li>
                        <li>✅ Hands-on projects and real-world case studies</li>
                        <li>✅ Learn from experienced instructors</li>
                        <li>✅ Flexible online learning schedule</li>
                    </ul>
                </div>
                <div>
                    <ul>
                        <li>✅ Lifetime access to course materials</li>
                        <li>✅ Dedicated support and doubt clearing</li>
                        <li>✅ Certificate of completion</li>
                        <li>✅ Career guidance and mentorship</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ENROLLMENT SECTION -->
        <div class="enroll-bottom" id="enroll">
            <h2><i class="bi bi-pencil-square"></i> Enroll in This Course</h2>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php elseif (!empty($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="enroll-form">
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="father_name" placeholder="Father's Name" required>
        <input type="text" name="cnic" placeholder="CNIC (e.g. 42101-1234567-1)" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="university" placeholder="University / College Name" required>
        <input type="text" name="course" value="Web Development Through AI" readonly style="background:#f0f0f0; color:#333;">
        <select name="payment_method" required style="width:100%; padding:14px 18px; margin:12px 0; border:1px solid #cbd5e1; border-radius:50px; font-size:15px;">
            <option value="">-- Select Payment Method --</option>
            <option>JazzCash</option>
            <option>EasyPaisa</option>
            <option>Bank Transfer</option>
        </select>
        <label style="text-align:left; display:block; padding-left:15px; font-size:14px; color:#555; margin-top:8px;">
            📎 Upload Payment Screenshot:
        </label>
        <input type="file" name="payment_screenshot" accept="image/*" required style="width:100%; padding:10px 15px; margin:8px 0 12px; border:1px solid #cbd5e1; border-radius:50px; font-size:14px;">
        <button type="submit"><i class="bi bi-send"></i> Submit Enrollment</button>
    </form>
</div>
            <p style="margin-top: 20px; font-size: 14px; color: #666;">
                <i class="bi bi-lock"></i> Your information is secure. We'll contact you within 24 hours.
            </p>
        </div>
    </div>

    <script>
        // Smooth scroll for enroll button
        document.querySelectorAll('.enroll-now-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('#enroll').scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>