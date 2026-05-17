<?php
session_start(); // Keep this only once at the top of the PHP script

$servername = "localhost";
$username = "u655850112_site";
$password = "Q0jAJnA][";
$dbname = "u655850112_site";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $school_college = $_POST['school_college'];
    $contact_number = $_POST['contact_number'];
    $num_students = $_POST['num_students'];
    $school_address = $_POST['school_address'];
    $requirements = $_POST['requirements'];
    $workshop = $_POST['workshop'];

    $stmt = $conn->prepare("INSERT INTO registrations (name, email, school_college, contact_number, num_students, school_address, requirements, workshop) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssisss", $name, $email, $school_college, $contact_number, $num_students, $school_address, $requirements, $workshop);
    
    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workshop Registration - ConnectOLead</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
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

        .registration-container {
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

        input, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #800000;
            outline: none;
            box-shadow: 0 0 8px rgba(128, 0, 0, 0.3);
        }

        .btn-register {
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

        .btn-register:hover {
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
            color:maroon;
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

            .registration-container {
                padding: 30px;
            }

            .footer {
                flex-direction: column;
                text-align: center;
                gap: 10px;
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
            <a href="workshop_registration">Registration</a>
            <a href="contact.php">Contact</a>
             <a href="dashboard.php">Profile</a>
        </nav>
    </div>

    <!-- Form -->
    <div class="form-wrapper">
        <div class="registration-container">
            <h2>Register for Workshops & Trainings</h2>
            <form action="" method="POST">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="school_college">School/College Name</label>
                <input type="text" id="school_college" name="school_college" required>

                <label for="contact_number">Contact Number</label>
                <input type="text" id="contact_number" name="contact_number" required>

                <label for="num_students">No. of Students</label>
                <input type="number" id="num_students" name="num_students" required>

                <label for="school_address">School/College Address</label>
                <input type="text" id="school_address" name="school_address" required>

                <label for="requirements">Any Other Requirement</label>
                <input type="text" id="requirements" name="requirements">

                <label for="workshop">Select Workshop</label>
                <select id="workshop" name="workshop" required>
                    <option value="Graphic Designing">Graphic Designing</option>
                    <option value="Video and Photo Editing">Video and Photo Editing</option>
                    <option value="Microsoft Office Management">Microsoft Office Management</option>
                </select>

                <button type="submit" class="btn-register">Register</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <span>&copy; 2025 ConnectOLead. All rights reserved.</span>
        <span><a href="https://connectolead.org/">Visit our website</a></span>
    </div>

</body>
</html>
