<?php

include 'config.php';
session_start();

$message = [];

if (isset($_POST['submit'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);

    if (!$email) {
        $message[] = 'Invalid email format!';
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && password_verify($password, $result['password'])) {
            $_SESSION['user_id'] = $result['id'];

            // Set a cookie with user information
            setcookie('user_email', $result['email'], time() + (86400 * 30), "/"); // 86400 seconds = 1 day

            // Check user role and redirect accordingly
            if ($result['role'] == 'admin') {
                header('location: ../admin/index.php');
                exit;
            } 
         else {
                header('location: ../patient_home.php');
                exit;
            }
        } else {
            $message[] = 'Incorrect email or password!';
        }
    } catch (PDOException $e) {
        $message[] = 'Error: ' . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="form-container">
   <form action="" method="post" enctype="multipart/form-data">
      <h3>Login now</h3>
      <?php
      if (!empty($message)) {
         foreach ($message as $msg) {
            echo '<div class="message">' . htmlspecialchars($msg) . '</div>';
         }
      }
      ?>
      <input type="email" name="email" placeholder="Enter email" class="box" required>
      <input type="password" name="password" placeholder="Enter password" class="box" required>
      <input type="submit" name="submit" value="Login now" class="btn">
      <p>Don't have an account? <a href="register.php">Register now</a></p>
   </form>
</div>
</body>
</html>