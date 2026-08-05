<?php
include ('booking_header.php');
require_once('config.php');

?>


                                

<div class="booking">
  <div class="contact-form-8">
    <div class="team-rectangles-4">
      <div class="container-12" style ='
    width: 100%;
    max-width: 200%;
    grid-row-gap: 64px;
    flex-direction: column;
    justify-content: flex;
    align-items: center;
    display: flex;
'>
        <div class="section-title-9">
          <div class="text-83">Book With a Dentist</div>
          <div class="text-84"> <pre>
   Welcome to Our Dental Clinic
    Book your appointment with experienced dentists who care about your  health. </pre>
          </div>
        </div>
        <div class="columns-12">

<?php
// if (isset($_POST['send'])) {
  $sql= "SELECT * FROM  dentists ";
  
       $exe = $conn->prepare($sql);

           $exe->execute();
           if($exe->rowcount()):
           $row =$exe->fetchall();
          foreach($row as $rows ):
        $name = $rows['dentist_name'];
        $email = $rows['dentist_brief'];
     
        $img = $rows['image'];
        $id= $rows['dentist_id'];
        

?>

          <div class="card-5">
            
            <div class="image-wrapper-7">
              <img src="imgs/<?php  echo $img;?>" alt="image">
              
        
            </div>

            <div class="info-7">
              
              <div class="name-4"><?php echo "Dr. $name" ?></div>
              <div class="description-2">
                 <?php echo $email?>
              </div>
              <a href="login/login.php?id=<?php echo $id ; ?>" class="button-5"  name = "read">
                <div class="text-85">
                  << Read More>>
                </div>
              </a><a href="login/login.php" class="button-6">
                <div class="text-85">Book Appointment</div>
             
               </a>
               </div>
              </div>
              <?php endforeach; endif;?>
            </div>  
            </div>
          </div> 
        </div>       
      </div>     
    </div>
  </div>
</div>
