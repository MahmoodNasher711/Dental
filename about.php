<?php require_once('header.php') ?>
<div class="about-2">
  <div class="contact-form-8">
    <div class="container-14">
      <div class="section-title-11">
        <div class="frame-11">
          <h2 class="get-in-touch-8">About us</h2>
        </div>
      </div>
      <div class="text-96">
        <div class="text-96-text-96">
          <span class="span-message">
          <div class="row">
                <div class="col-2"></div>
                <div class="col-8">
                <h1>About Us</h1>
                <?php
                        require_once('config.php');
                        $sql="select * from about_us order by about_id desc";
                        $exe=$conn->query($sql);
                        $row=$exe->fetch();
                        $text=$row['about_text'];
                        echo "<p>$text</p>";
                ?>
                </div>
                <div class="col-2"></div>
        </div>

        
            </span>
        </div>
      </div>
    </div>
  </div>
  <?php require_once('footer.php') ?>