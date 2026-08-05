<?php require('header_patient.php') ?>
<div class="contact-2">
  <div class="contact-form-9">
    <div class="container-13">
      <div class="section-title-10">
        <h2 class="get-in-touch-7">Contact us</h2>
        <div class="text-91">Let us know how we can help you</div>
      </div>
      <div class="form-wrapper-6 w-form">
        <form id="webflow-form" name="webflow-form" data-name="Webflow Form" method="post" class="form-5" data-wf-page-id="65be7b0d148e4c2b5419621e" data-wf-element-id="137147c4-b4a3-21c3-a270-028c28298d62">
          <div class="input-wrapper-9">
            <label for="name" class="text-89">Name</label><input class="form-text-input-14 w-input" maxlength="256" name="name_sender" data-name="Name" placeholder="Your full name" type="text" id="name" />
          </div>
          <div class="input-wrapper-9">
            <label for="name" class="text-89">Email</label><input class="form-text-input-15 w-input" maxlength="256" name="email_sender" data-name="Name" placeholder="me@company.com" type="text" id="name" />
          </div>
          <div class="input-wrapper-9">
            <label for="name" class="text-89">Message</label><textarea placeholder="Your message..." maxlength="5000" id="field" name="message" data-name="Field" class="form-textarea-2 w-input"></textarea>
          </div>
          <input type="submit" data-wait="Please wait..." class="form-button-6 w-button" value="Send message" name="send" />
        </form>
        <?php
require_once('config.php');

if (isset($_POST['send'])) {
    $name = isset($_POST['name_sender']) ? $_POST['name_sender'] : '';
    $email = isset($_POST['email_sender']) ? $_POST['email_sender'] : '';
    $message = isset($_POST['message']) ? $_POST['message'] : '';

    // تحقق من فراغ الحقول
    if (empty($name) || empty($email) || empty($message)) {
        echo "<script>window.alert('Please fill in all fields');</script>";
    } else {
        // تحقق من البريد الإلكتروني
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>window.alert('Invalid email address');</script>";
        } else {
            // تحقق من الأحرف المسموح بها في الاسم والرسالة
            if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
                echo "<script>window.alert('Only letters and white space allowed in Name');</script>";
            } elseif (strlen($message) > 5000) {
                echo "<script>window.alert('Message should not exceed 5000 characters');</script>";
            } else {
                // يمكنك إجراء الإدخال إلى قاعدة البيانات هنا
                $n_text = str_replace("'", "\'",  "$message");
                $sql = "INSERT INTO contact_us (cont_user_name, cont_user_email, cont_user_text) VALUES ('$name', '$email', '$n_text')";
                $exe = $conn->query($sql);

                if ($exe) {
                    echo "<script>window.alert('Thanks For Your Contact');</script>";
                } else {
                    echo 'Insert error';
                }
            }
        }
    }
}
?>
        <script>

        </script>
        <div class="w-form-done">
        </div>
        <div class="w-form-fail">
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once('footer.php') ?>