<?php
$servername = "localhost";
$username = "u655850112_site";
$password = "Q0jAJnA][";
$dbname = "u655850112_site";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$doctorQuery = "
  SELECT u.username, u.email, u.phone, p.bio, p.expertise, p.profile_picture
  FROM users u
  JOIN user_profiles p ON u.id = p.user_id
  WHERE u.role = 'doctor'
";

$result = $conn->query($doctorQuery);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $doctorUsers[] = $row;
  }
}

$confirmation = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitize and collect inputs
  $category = $_POST['category'];
  $date = $_POST['date'];
  $time = $_POST['time'];
  $doctor = $_POST['doctor']; 

  // Dummy doctor name (just to complete your query logic)
  // You can modify this to select a specific doctor in future
  $doctor = $doctorUsers[0]['username'] ?? 'Not Assigned';

  $stmt = $conn->prepare("INSERT INTO doctor_consultations (category, preferred_date, preferred_time, doctor_name) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssds", $category, $date, $time, $doctor);

  if ($stmt->execute()) {
    $confirmation = "Your consultation with $doctor has been booked for $date at $time.";
  } else {
    $confirmation = "Booking failed: " . $stmt->error;
  }

  $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Women Empowerment - Health Resources</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
      body {
        font-family: "Segoe UI", sans-serif;
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
        color: white;
        margin: 0;
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
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
      }

      h2, h3 {
        color: #680f0f;
      }

      label {
        font-weight: 500;
      }

      select, input, button, textarea {
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

      .anonymous-checkbox {
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
      }


      .back-link {
        display: inline-block;
        margin-bottom: 20px;
        color: #680f0f;
        text-decoration: none;
        font-weight: bold;
      }

      .doctor-card {
        background-color: white;
        padding: 12px;
        border-radius: 10px;
        width: calc(50% - 10px); /* Two cards per row with gap */
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        display: flex;
        gap: 12px;
        align-items: center;
        border: 1px solid #eee;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        max-height: 140px;
        overflow: hidden;
      }

      .doctor-card:hover {
        transform: scale(1.01);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
      }

      .doctor-card img {
        width: 65px;
        height: 65px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #680f0f20;
      }

      .doctor-info h3 {
        margin: 0 0 4px 0;
        font-size: 16px;
        color: #680f0f;
      }

      .doctor-info p {
        margin: 2px 0;
        font-size: 13px;
        color: #555;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
      }

      .doctor-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px; 
      }

      /* Responsive for mobile: stack vertically */
      @media (max-width: 768px) {
        .doctor-card {
          width: 100%;
          flex-direction: column;
          text-align: center;
          max-height: none;
        }

        .doctor-info p {
          -webkit-line-clamp: 2;
        }
      }

    </style>
  </head>
  <body>
    <header>
      <div class="header-left">
        <img src="./Images/logo0.png" alt="Logo" />
      </div>
      <h1 class="header-title">Health Support</h1>
    </header>

    <main>
      <?php if (!empty($confirmation)): ?>
        <script>alert('<?php echo $confirmation; ?>');</script>
      <?php endif; ?>

      <a href="women.html" class="back-link"><i class="bi bi-arrow-left"></i> Back to Portal</a>

      <section>
        <h2>Understanding Key Health Concerns</h2>
        <p><strong>Mental Health:</strong> Recognize anxiety, stress, depression early. Therapy and lifestyle changes help.</p>
        <p><strong>Reproductive Health:</strong> Learn about menstrual cycles and contraceptive use. Regular checkups are vital.</p>
        <p><strong>Nutrition:</strong> Balanced diet prevents obesity, anemia, and supports energy levels.</p>
      </section>

      <section>
        <h2>Our Health Experts</h2>
        <div class="doctor-grid">
          <?php foreach ($doctorUsers as $doc): ?>
            <div class="doctor-card">
              <img src="<?php echo !empty($doc['profile_picture']) ? htmlspecialchars($doc['profile_picture']) : 'https://via.placeholder.com/100'; ?>" alt="Doctor Profile" />
              <div class="doctor-info">
                <h3><?php echo htmlspecialchars($doc['username']); ?></h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($doc['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($doc['phone']); ?></p>
                <?php if (!empty($doc['expertise'])): ?>
                  <p><strong>Expertise:</strong> <?php echo htmlspecialchars($doc['expertise']); ?></p>
                <?php endif; ?>
                <?php if (!empty($doc['bio'])): ?>
                  <p><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($doc['bio'])); ?></p>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>


      <section>
        <h2>Book a Health Consultation</h2>
        <form method="POST">
          <label for="category">Health Category:</label>
          <select name="category" id="category" required>
            <option value="mental">Mental Health</option>
            <option value="reproductive">Reproductive Health</option>
            <option value="nutrition">Nutrition</option>
          </select>

          <label for="doctor">Select a Doctor:</label>
          <select name="doctor" id="doctor" required>
            <?php foreach ($doctorUsers as $doc): ?>
              <option value="<?php echo htmlspecialchars($doc['username']); ?>">
                <?php echo htmlspecialchars($doc['username'] . ' (' . $doc['expertise'] . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label for="date">Preferred Date:</label>
          <input type="date" name="date" id="date" required />

          <label for="time">Preferred Time:</label>
          <input type="time" name="time" id="time" required />

          <button type="submit">Request Consultation</button>
        </form>
      </section>


      <section>
        <h2>Emergency Health Help</h2>
        <p>If you are in urgent need, click below to speak with emergency support.</p>
        <button onclick="window.location.href='tel:1234567890'">
          <i class="bi bi-telephone-fill"></i> Call Emergency Helpline
        </button>
      </section>
    </main>

    <script>
      function loadHealthResources() {
        const category = document.getElementById("health-category").value;
        const contentDiv = document.getElementById("resource-content");

        let content = "";
        switch (category) {
          case "mental":
            content = `
              <h3>Mental Health Resources</h3>
              <ul>
                <li><a href="#">Guide to Managing Stress</a></li>
                <li><a href="#">Signs of Anxiety & What to Do</a></li>
                <li><a href="#">Practicing Mindfulness Daily</a></li>
              </ul>`;
            break;
          case "reproductive":
            content = `
              <h3>Reproductive Health Resources</h3>
              <ul>
                <li><a href="#">Understanding Your Menstrual Cycle</a></li>
                <li><a href="#">Safe Contraception Options</a></li>
                <li><a href="#">Fertility and Family Planning</a></li>
              </ul>`;
            break;
          case "nutrition":
            content = `
              <h3>Nutrition Resources</h3>
              <ul>
                <li><a href="#">Building a Balanced Plate</a></li>
                <li><a href="#">Foods that Boost Women's Energy</a></li>
                <li><a href="#">Meal Planning Tips</a></li>
              </ul>`;
            break;
          default:
            content = "<p>Please select a category.</p>";
        }

        contentDiv.innerHTML = content;
      }
    </script>
  </body>
</html>
