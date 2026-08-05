<?php

include 'config.php';

$message = [];

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);
    $cpassword = trim($_POST['cpassword']);
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . $image;

    // Validate username
    if (empty($name)) {
        $message[] = 'Username cannot be empty!';
    }

    // Validate email
    if (!$email) {
        $message[] = 'Invalid email format!';
    }

    // Validate password
    if (empty($password)) {
        $message[] = 'Password cannot be empty!';
    }

    // Confirm password match
    if ($password !== $cpassword) {
        $message[] = 'Confirm password does not match!';
    }

    // Validate image size and type
    if ($image_size > 2000000) {
        $message[] = 'Image size is too large!';
    } elseif (!in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'])) {
        $message[] = 'Invalid image format! Accepted formats: JPG, JPEG, PNG.';
    }

    // Check if the user already exists
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $message[] = 'User already exists!';
        } else {
            // If all validations pass, proceed with registration
            if (empty($message)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, image) VALUES (:name, :email, :password, :image)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':image', $image);
                $stmt->execute();

                move_uploaded_file($image_tmp_name, $image_folder);
                $message[] = 'Registered successfully!';
                header('location: login.php');
            }
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
   <link rel="stylesheet" href="../css/style.css">
  </head>

<body>
<div class="home-page-4">
  <div data-animation="default" data-collapse="medium" data-duration="400" data-easing="ease" data-easing2="ease" role="banner" class="navbar-logo-left-16 w-nav">
    <div class="navbarcontainer-16 w-container">
      <div class="navbar-content-15">
        <div class="navbar-brand-15">
          <img src="../img/WhatsApp Image 2024-02-09 at 17.16.43_50080ff5.jpg" loading="lazy" width="80" height="80" alt="" class="logo-white-11" style="border-radius: 50%; width: 60px; height: 55px;" />
        </div>
        <nav role="navigation" class="navbar-menu-12 w-nav-menu">
          <a href="index.php" class="navbar-link-27 w-nav-link">
            <div class="text-86">Home</div>
          </a><a href="contact_us.php" class="navbar-link-27 w-nav-link">
            <div class="text-86">Contact Us</div>
          </a><a href="about.php" class="navbar-link-27 w-nav-link">
            <div class="text-86">About</div>
          </a><a href="login/login.php" class="navbar-link-27 w-nav-link">
            <div class="text-86">Log in</div>
          </a><a href="login/login.php" class="navbar-button-13 w-nav-link">
            <div class="text-87">Book An Appointment</div>
          </a>
        </nav>
      </div>
      <div class="w-nav-button">
        <div class="w-icon-nav-menu"></div>
      </div>
    </div>
  </div>
<div class="form-container">
   <form action="" method="post" enctype="multipart/form-data">
      <h3>Register now</h3>
      <?php
      if (!empty($message)) {
         foreach ($message as $msg) {
            echo '<div class="message">' . htmlspecialchars($msg) . '</div>';
         }
      }
      ?>
      <input type="text" name="name" placeholder="Enter username" class="box" required>
      <input type="email" name="email" placeholder="Enter email" class="box" required>
      <input type="password" name="password" placeholder="Enter password" class="box" required>
      <input type="password" name="cpassword" placeholder="Confirm password" class="box" required>
      <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png" required>
      <input type="submit" name="submit" value="Register now" class="btn">
      <p>Already have an account? <a href="login.php">Login now</a></p>
   </form>
</div>
</body>
</html>