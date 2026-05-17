<?php
$host = "localhost";
$user = "u655850112_site";
$pass = "Q0jAJnA][";
$db = "u655850112_site";

$conn = new mysqli($host, $user, $pass, $db);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'], $_POST['body'], $_POST['comment'])) { 
    $title = $_POST['title'];
    $body = $_POST['body'];
    $comment = $_POST['comment'];
    $name = $_POST['name'] ?? 'Anonymous';
    
    $stmt1 = $conn->prepare("INSERT INTO forum_threads (title, body) VALUES (?, ?)");
    $stmt1->bind_param("ss", $title, $body);
    $stmt1->execute();
    $thread_id = $stmt1->insert_id;
    $stmt1->close();

    $stmt2 = $conn->prepare("INSERT INTO forum_comments (thread_id, commenter_name, comment_text) VALUES (?, ?, ?)");
    $stmt2->bind_param("iss", $thread_id, $name, $comment);
    $stmt2->execute();
    $stmt2->close();

    echo "
    <script>
      alert('Discussion posted!');
      window.location.href = 'parents.CommFor.php';
    </script>
    ";

    exit;
}

if (isset($_POST['comment_text'], $_POST['thread_id'])) {
    $comment = $_POST['comment_text'];
    $thread_id = $_POST['thread_id'];
    $name = $_POST['name'] ?? 'Anonymous';

    $stmt = $conn->prepare("INSERT INTO forum_comments (thread_id, commenter_name, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $thread_id, $name, $comment);
    $stmt->execute();
    $stmt->close();

    echo "
    <script>
      alert('Comment added!');
      window.location.href = 'parents.CommFor.php';
    </script>
    ";
    exit;
}

// Function to get comments for a specific thread
function getComments($conn, $thread_id) {
    $stmt = $conn->prepare("SELECT * FROM forum_comments WHERE thread_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("i", $thread_id);
    $stmt->execute();
    return $stmt->get_result();
}

$threads = $conn->query("SELECT * FROM forum_threads ORDER BY created_at DESC");
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Parent's Community Forums</title>
    <style>
      body {
        font-family: "Segoe UI", sans-serif;
        background-color: #f9f9f9;
        padding: 0;
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
      .header-title {
        font-size: 28px;
        font-weight: bold;
        color: white;
        margin: 0;
      }
      main {
        max-width: 900px;
        margin: auto;
        padding: 40px 20px;
        background: white;
        border-radius: 12px;
      }
      h2 {
        color: #023468;
      }
      .forum-thread {
        border: 1px solid #ccc;
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 10px;
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
      .comment {
        margin-top: 10px;
        padding: 10px;
        background-color: #f1f1f1;
        border-radius: 6px;
      }
      textarea,
      input[type="text"] {
        width: 100%;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
        resize: vertical;
        margin-top: 10px;
      }
      button {
        margin-top: 10px;
        padding: 8px 16px;
        background-color: #680f0f;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
      }
      button:hover {
        background-color: #8a2c2c;
      }
      .discussion-divider {
        margin: 40px 0 10px;
        border-top: 2px dashed #ccc;
        padding-top: 20px;
      }
    </style>
  </head>
  <body>
    <header>
      <div class="header-left">
        <img src="./Images/logo1.png" alt="Logo" />
      </div>
      <h1 class="header-title">Parent's Community Forums</h1>
    </header>

    <main>
    <a href="parentshub.html" class="back-link"
      ><i class="bi bi-arrow-left"></i> Back to Portal</a
    >
      <h2>Latest Discussions</h2>

      <?php while ($row = $threads->fetch_assoc()): ?>
      <div class="forum-thread">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <p><?= nl2br(htmlspecialchars($row['body'])) ?></p>

        <?php
          $comments = getComments($conn, $row['id']);
          while ($comment = $comments->fetch_assoc()): ?>
        <div class="comment">
          <strong><?= htmlspecialchars($comment['commenter_name']) ?>:</strong>
          <?= nl2br(htmlspecialchars($comment['comment_text'])) ?>
        </div>
        <?php endwhile; ?>

        <div class="add-comment">
          <form method="POST" action="parents.CommFor.php">
            <input type="hidden" name="thread_id" value="<?= $row['id'] ?>" />
            <input type="text" name="name" placeholder="Your Name (optional)" />
            <textarea
              name="comment_text"
              placeholder="Add your opinion..."
              required
            ></textarea>
            <button type="submit">Post Comment</button>
          </form>
        </div>
      </div>
      <?php endwhile; ?>

      <div class="discussion-divider">
        <h2>Start a New Discussion</h2>
        <form method="POST" action="parents.CommFor.php"class="add-discussion">
          <input
            type="text"
            name="title"
            placeholder="Discussion Title"
            required
          />
          <textarea
            name="body"
            placeholder="What's your discussion about?"
            required
          ></textarea>
          <textarea
            name="comment"
            placeholder="Start the conversation with your comment..."
            required
          ></textarea>
          <input type="text" name="name" placeholder="Your Name (optional)" />
          <button type="submit">Add Discussion</button>
        </form>
      </div>
    </main>
  </body>
</html>
