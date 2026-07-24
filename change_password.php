<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: dashboard.php');
}

// Database credentials
$host = "localhost";
$user = "root";
$password = "";
$dbname = "students_DB";

// Connect to the database
$conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);

// Get the logged-in user's data
$username = $_SESSION['username'];

//Fetch data from the database table "admin" based on the logged-in user's username.
try {
    $sql = 'SELECT * FROM admin WHERE username = ?';
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Check if the form has been submitted
if (isset($_POST['change_password'])) {
    // Validate the new password
    $new_password = $_POST['new_password'];
    if (empty($new_password)) {
        $error_message = 'Please enter a new password.';
    } else if (strlen($new_password) < 3) {
        $error_message = 'The new password must be at least 3 characters long.';
    }

    // Validate the username and email
    $username = $_POST['username'];
    $email = $_POST['email'];
    if (empty($username) || empty($email)) {
        $error_message = 'Please enter both username and email.';
    }

    // Check if the username and email match a record in the database
    try {
        $sql = "SELECT * FROM admin WHERE username = ? AND email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $email]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_data) {
            $error_message = 'Invalid username or email.';
        } else {
            // Update the password for the matching record
            $sql = "UPDATE admin SET password = ? WHERE username = ? AND email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$new_password, $username, $email]);

            // Show a success message
            $success_message = 'The password has been successfully changed for user ' . $username . '.';
        }
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password</title>

  <link rel="icon" href="./assets/img/fav.png">

  <link rel="stylesheet" href="./assets/styles/change_password.css">

</head>

<body>

  <h1>Change Password</h1>
  <div id="hline"></div>

  <div id="form-wrapper">
    <form action="change_password.php" method="post">
      <input type="text" name="username" placeholder="Username">
      <input type="email" name="email" placeholder="Email">
      <input type="password" name="new_password" placeholder="New Password">
      <div id="form-buttons">
        <input type="submit" name="change_password" value="Change Password" class="button">
        <button type="button" onclick="window.location.href='index.php'" class="cancel">Cancel</button>
      </div>
    </form>

    <?php if (isset($error_message)) : ?>
    <script type="text/javascript">
    alert('<?php echo $error_message; ?>');
    </script>
    <?php endif; ?>

    <?php if (isset($success_message)) : ?>
    <script>
    alert('<?php echo $success_message; ?>');
    window.location.href = 'index.php';
    </script>
    <?php endif; ?>
  </div>
</body>

<footer>
  <p>&copy; All Rights Reserved 2023</p>
</footer>

</html>