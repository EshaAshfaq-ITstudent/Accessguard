<?php
// Database connection
$servername = "localhost";
$username = "u655850112_site"; // your database username
$password = "Q0jAJnA]["; // your database password
$dbname = "u655850112_site";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    // Prepare the SQL query to insert form data into the database
    $sql = "INSERT INTO submissions(name, email, subject, message) 
            VALUES ('$name', '$email', '$subject', '$message')";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        $success_message = "Thank you for contacting us! Your message has been received.";
    } else {
        $error_message = "Error: " . $sql . "<br>" . $conn->error;
    }
    
    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us - ConnectOLead</title>
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap"
    />
    <style>
      body {
        font-family: "Poppins", sans-serif;
        margin: 0;
        padding: 0;
        background: #f8f9fa;
      }

      /* Header */
      .header {
        background: #800000;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 60px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      }

      .header .logo img {
        height: 50px;
      }

      .header nav a {
        color: white;
        text-decoration: none;
        margin-left: 20px;
        font-weight: 500;
        transition: 0.3s;
        padding: 8px 16px;
        border-radius: 5px;
      }

      .header nav a:hover {
        background: rgba(255, 255, 255, 0.2);
      }

      /* Form Wrapper */
      .form-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        padding: 20px;
      }

      .contact-container {
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        text-align: center;
        max-width: 500px;
        width: 100%;
      }

      h2 {
        color: #800000;
        font-weight: 600;
        font-size: 26px;
        margin-bottom: 20px;
      }

      label {
        display: block;
        text-align: left;
        font-weight: 600;
        margin: 10px 0 5px;
        color: #333;
      }

      input,
      textarea {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        box-sizing: border-box;
        transition: 0.3s ease;
      }

      input:focus,
      textarea:focus {
        border-color: #800000;
        outline: none;
        box-shadow: 0 0 8px rgba(128, 0, 0, 0.3);
      }

      .btn-submit {
        background: #800000;
        color: white;
        padding: 14px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 600;
        width: 100%;
        transition: background 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      }

      .btn-submit:hover {
        background: #660000;
        transform: scale(1.02);
      }

      /* Footer */
      .footer {
        background: maroon;
        color: white;
        text-align: center;
        padding: 20px;
        font-size: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
      }

      .footer a {
        color: Maroon;
        text-decoration: none;
        font-weight: 600;
      }

      .footer a:hover {
        text-decoration: underline;
      }

      @media (max-width: 768px) {
        .header {
          flex-direction: column;
          text-align: center;
          padding: 15px;
        }

        .header nav {
          margin-top: 10px;
        }

        .header nav a {
          display: block;
          margin: 5px 0;
        }

        .contact-container {
          padding: 30px;
        }

        .footer {
            background-color: var(--secondary-color);
            color: white;
            padding: 30px 10px;
            text-align: center;
            font-size: 18px;
        }
        .footer span {
            font-weight: bold;
            color: var(--main-color);
        }
      }
    </style>
  </head>
  <body>
    <!-- Header -->
    <div class="header">
        <div>
            <img src="./Images/logo0.png" alt="ConnecToLead" width="50">
            <span>ConnecToLead</span>
        </div>
      <nav>
        <a href="index.html">Home</a>
        <a href="workshop_registration.php">Registration</a>
        <a href="contact.php">Contact</a>
        <a href="dashboard.php">Profile</a>
      </nav>
    </div>

    <!-- Contact Form -->
    <div class="form-wrapper">
      <div class="contact-container">
        <h2>Contact Us</h2>
        
        <?php
        if (isset($success_message)) {
            echo "<p style='color: green;'>$success_message</p>";
        } elseif (isset($error_message)) {
            echo "<p style='color: red;'>$error_message</p>";
        }
        ?>

        <form action="" method="POST">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" required />

          <label for="email">Email</label>
          <input type="email" id="email" name="email" required />

          <label for="subject">Subject</label>
          <input type="text" id="subject" name="subject" required />

          <label for="message">Message</label>
          <textarea id="message" name="message" rows="4" required></textarea>

          <button type="submit" class="btn-submit">Submit</button>
        </form>
      </div>
    </div>

    <!-- Footer -->
    <div class="footer">&copy; 2024-2030 ConnecToLead | All Rights Reserved</div>
  </body>
</html>
