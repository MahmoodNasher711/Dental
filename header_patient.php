 
<?php

include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];



?>





<?php
 

      ?>
  <link rel="stylesheet" href="css/style.css">
  <div data-animation="default" data-collapse="medium" data-duration="400" data-easing="ease" data-easing2="ease" role="banner" class="navbar-logo-left-15 w-nav">
    <div class="navbarcontainer-15 w-container">
      <div class="navbar-content-14">
        <div class="navbar-brand-14">
        <img src="img/WhatsApp Image 2024-02-09 at 17.16.43_50080ff5.jpg" loading="lazy" width="80" height="80" alt="" class="logo-white-11" style="border-radius: 50%; width: 60px; height: 55px;" />
        </div>
        <nav role="navigation" class="navbar-menu-11 w-nav-menu">
          <a href="patient_home.php" class="navbar-link-26 w-nav-link">
            <div class="text-82">Home</div>
          </a><a href="contact_u.php" class="navbar-link-27 w-nav-link">
            <div class="text-82">Contact Us</div>
         
          </a>
          <a href="login/update_profile.php" class="navbar-link-27 w-nav-link" style = "width:100px;">
        <img 
        
        <?php
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

if($stmt->rowCount() > 0){
  $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<img src="<?php echo !empty($fetch['image']) ? 'login/uploaded_img/'.$fetch['image'] : 'login system with avatar/images/default-avatar.png'; ?>" alt="Profile image" title='Edit' style="border-radius: 50%; width: 60px; height: 55px;">


</a>
        </nav>
      </div>
      <div class="w-nav-button">
        <div class="w-icon-nav-menu"></div>
      </div>
    </div>
  </div>
  <?php
    //  }
      ?>