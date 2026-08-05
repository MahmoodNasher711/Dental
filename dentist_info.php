

<?php require_once('header_patient.php') ?>
<?php require_once('config.php') ?>


<?php

        // if (isset($_POST['send'])) {
          //require_once('config.php');
          $sql= "SELECT * from dentists where dentist_id =:id ;";
          $q= $conn->prepare($sql);
          $q->execute(array(":id"=>$_GET['id']));
          $stmt=$q->rowcount();
          if($stmt)
          {
            $row = $q->fetch(); 
           $name = $row['dentist_name'];
          $email = $row['dentist_brief'];
          $img = $row['image'];

          

?>
<div class="dentists-details">
  <div class="hero-heading-right">
    <div class="container-15">
      <div class="column-2">
        <div class="image-wrapper-9">
        <img src="imgs/<?php  echo $img;?>" alt="image">
         </div>
      </div>
      <div class="column-25">
        <div class="column-25">
          <div class="text-100"><?php echo "Dr\ $name" ?></div>
          <div class="qualifications-hes-the-uks-leading-restorative-dentist-he-was-a-finalist-at-the-2015-aesthetic-dentistry-awards-for-best-restorative-case-he-was-at-the-mayo-clinic-one-of-the-worlds-top-oral-rehabilitation-centers-for-three-years-he-worked-to-become-an-advocate-for-biomimetic-dentistry---minimally-invasive-procedures-that-emulate-the-natural-properties-of-patients-teeth">
            <div class="qualifications-hes-the-uks-leading-restorative-dentist-he-was-a-finalist-at-the-2015-aesthetic-dentistry-awards-for-best-restorative-case-he-was-at-the-mayo-clinic-one-of-the-worlds-top-oral-rehabilitation-centers-for-three-years-he-worked-to-become-an-advocate-for-biomimetic-dentistry---minimally-invasive-procedures-that-emulate-the-natural-properties-of-patients-teeth qualifications-hes-the-uks-leading-restorative-dentist-he-was-a-finalist-at-the-2015-aesthetic-dentistry-awards-for-best-restorative-case-he-was-at-the-mayo-clinic-one-of-the-worlds-top-oral-rehabilitation-centers-for-three-years-he-worked-to-become-an-advocate-for-biomimetic-dentistry---minimally-invasive-procedures-that-emulate-the-natural-properties-of-patients-teeth">
              <span class="">
              <?php echo  $email ?></span>
            </div>
          </div>
        </div>
        <div class="actions">
          <a href="appontment.php" class="button-8">
            <div class="text-101">Book Appointment</div>
          </a>
        </div>
        <?php?>
      </div>
    </div>
  </div>
</div>
<?php } ?>
<?php require_once('footer.php') ?>