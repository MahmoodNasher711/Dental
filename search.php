<form id="webflow-form" name="webflow-form" data-name="Webflow Form" method="get" class="form-9" data-wf-page-id="65be7b0d148e4c2b5419621e" data-wf-element-id="2e96ed53-4700-ad7f-d997-ebf1b2654bc3">
          <div class="input-wrapper-13">
            <input class="form-text-input-21 w-input" maxlength="256" name="name-5" data-name="Name 5" placeholder="Enter your email" type="text" id="name-5" />
          </div>
          <input type="submit" data-wait="Please wait..." class="form-button-9 w-button" value="Search" name="type" />
        </form>
        <?php
if(isset($_GET['type'])){
    $f=$_GET['name-5'];
    header("location:secretery.php?name-5=$f");
}
?>

