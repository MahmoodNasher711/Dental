<?php

include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_GET['logout'])){
   unset($user_id);
   session_destroy();
   header('location:login.php');
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>home</title>

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<div class="container">

   <div class="profile">
   <?php
   $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
   $stmt->bindParam(':user_id', $user_id);
   $stmt->execute();

   if($stmt->rowCount() > 0){
      $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
   }

   if(empty($fetch['image'])){
      echo '<img src="images/default-avatar.png">';
   }else{
      echo '<img src="uploaded_img/'.$fetch['image'].'">';
   }
?>

      <h3><?php echo $fetch['username']; ?></h3>
      <a href="update_profile.php" class="btn">update profile</a>
      <a href="home.php?logout=<?php echo $user_id; ?>" class="delete-btn">logout</a>
      <p>new <a href="login.php">login</a> or <a href="register.php">register</a></p>
   </div>

</div>

</body>
</html>