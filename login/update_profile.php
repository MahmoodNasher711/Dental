<?php

include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];

if(isset($_POST['update_profile'])){

   $update_name = $_POST['update_name'];
   $update_email = $_POST['update_email'];

   $stmt = $pdo->prepare("UPDATE users SET name = :update_name, email = :update_email WHERE id = :user_id");
   $stmt->bindParam(':update_name', $update_name);
   $stmt->bindParam(':update_email', $update_email);
   $stmt->bindParam(':user_id', $user_id);
   //$stmt->execute();

   $old_pass = $_POST['old_pass'];
   $update_pass = ($_POST['update_pass']);
   $new_pass = ($_POST['new_pass']);
   $confirm_pass = ($_POST['confirm_pass']);

   if(!empty($update_pass) || !empty($new_pass) || !empty($confirm_pass)){
      if($update_pass != $old_pass){
         $message[] = 'Old password not matched!';
      }elseif($new_pass != $confirm_pass){
         $message[] = 'Confirm password not matched!';
      }else{
         $stmt = $pdo->prepare("UPDATE users SET password = :confirm_pass WHERE id = :user_id");
         $stmt->bindParam(':confirm_pass', $confirm_pass);
         $stmt->bindParam(':user_id', $user_id);
         $stmt->execute();
         $message[] = 'Password updated successfully!';
      }
   }

   $update_image = $_FILES['update_image']['name'];
   $update_image_size = $_FILES['update_image']['size'];
   $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
   $update_image_folder = 'uploaded_img/'.$update_image;

   if(!empty($update_image)){
      if($update_image_size > 2000000){
         $message[] = 'Image is too large';
      }else{
         $stmt = $pdo->prepare("UPDATE users SET image = :update_image WHERE id = :user_id");
         $stmt->bindParam(':update_image', $update_image);
         $stmt->bindParam(':user_id', $user_id);
         $stmt->execute();

         move_uploaded_file($update_image_tmp_name, $update_image_folder);
         $message[] = 'Image updated successfully!';
      }
   }

}

?>



<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>update profile</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<div class="update-profile">

<?php
   $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
   $stmt->bindParam(':user_id', $user_id);
   $stmt->execute();

   if($stmt->rowCount() > 0){
      $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
   }
?>

   <form action="" method="post" enctype="multipart/form-data">
      <?php
         if($fetch['image'] == ''){
            echo '<img src="images/default-avatar.png">';
         }else{
            echo '<img src="uploaded_img/'.$fetch['image'].'">';
         }
         if(isset($message)){
            foreach($message as $message){
               echo '<div class="message">'.$message.'</div>';
            }
         }
      ?>
      <div class="flex">
         <div class="inputBox">
            <span>username :</span>
            <input type="text" name="update_name" value="<?php echo $fetch['username']; ?>" class="box">
            <span>your email :</span>
            <input type="email" name="update_email" value="<?php echo $fetch['email']; ?>" class="box">
            <span>update your pic :</span>
            <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png" class="box">
         </div>
         <div class="inputBox">
            <input type="hidden" name="old_pass" value="<?php echo $fetch['password']; ?>">
            <span>old password :</span>
            <input type="password" name="update_pass" placeholder="enter previous password" class="box">
            <span>new password :</span>
            <input type="password" name="new_pass" placeholder="enter new password" class="box">
            <span>confirm password :</span>
            <input type="password" name="confirm_pass" placeholder="confirm new password" class="box">
         </div>
      </div>
      <input type="submit" value="update profile" name="update_profile" class="btn">
      <a href="home.php" class="delete-btn">go back</a>
   </form>

</div>

</body>
</html>