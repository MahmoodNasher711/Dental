



<?php 
require_once('header_patient.php');
require_once 'config.php';

//session_start();
$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login/login.php');
};

if(isset($_GET['logout'])){
   unset($user_id);
   session_destroy();
   header('location:login/login.php');
} ?>
<img src="img/bg01.jpg" alt="" style=' position: absolute;
  background-repeat: no-repeat;
  width: 100%;
  margin: 0px ;
  margin-top:0;
height:100%;'>
<div class="patien-page-2" style =  'position: relative;'>
  <div class="hero-no-image-5">
    <div class="small-container-6">
      <div class="content-37">
      <div class="text-88"><span style = 'color: #e717ba;'>Just</span> Smile</div>
        <div class="text-88" style = 'font-size: 20px;'>DENTAL CLINIC</div>
        <div class="text-118">
          Easy, efficient way for getting fast appointment with one of the
          best dentists in the country
        </div>
        <a href="appontment.php" target="_blank" class="button-9">
          <div class="text-116">Book an Appointment Now</div>
        </a>
      </div>
    </div>
  </div>
  <div class="newsletter-form-2">
    <div class="container-20">
      <div class="section-title-11">
        <!-- <h2 class="get-in-touch-12">Join Our Newsletter</h2> -->
        <!-- <div class="join-our-mailing-list-to-get-everyday-tips-and-advices-for-your-dental-care-2">
          Join our mailing list to get everyday tips and advices for your
          dental care
        </div> -->
      <!-- </div> -->

      <!-- <div class="form-wrapper-9 w-form"> -->
        <!-- <form id="webflow-form" name="webflow-form" data-name="Webflow Form" method="get" class="form-8" data-wf-page-id="65be7b0d148e4c2b5419621e" data-wf-element-id="cc276373-6163-de6a-ab9c-7152b80f27bc"> -->
          <!-- <div class="input-wrapper-12"> -->
            <!-- <input class="form-text-input-21 w-input" maxlength="256" name="name-4" data-name="Name 4" placeholder="Enter your email" type="text" id="name-4" /> -->
          <!-- </div> -->
          <!-- <input type="submit" data-wait="Please wait..." class="form-button-8 w-button" value="Subscribe" /> -->
        <!-- </form>
        <div class="w-form-done">
        </div>
        <div class="w-form-fail">
        </div> -->
      </div>
    </div>
    <?php require_once ('userbooking.php') ?>
  </div>

  <?php require_once('footer.php'); ?>
