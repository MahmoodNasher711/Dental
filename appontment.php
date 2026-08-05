<?php require_once('header_patient.php') ;
require 'config.php';

//session_start();
$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login/login.php');
};

if(isset($_GET['logout'])){
   unset($user_id);
   session_destroy();
   header('location:login/login.php');
} 
?>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // تحقق من وجود القيم وتعيينها
    $patient_name = isset($_POST["patient_name"]) ? $_POST["patient_name"] : '';
    $patient_age = isset($_POST["patient_age"]) ? $_POST["patient_age"] : '';
    $patient_email = isset($_POST["patient_email"]) ? $_POST["patient_email"] : '';
    $phone_number = isset($_POST["phone_number"]) ? $_POST["phone_number"] : '';
    $patient_gender = isset($_POST["patient_gender"]) ? $_POST["patient_gender"] : '';

    // تحقق من فراغ الحقول
    if (empty($patient_name) || empty($patient_age) || empty($patient_email) || empty($phone_number) || empty($patient_gender)) {
        
        echo "<script>window.alert('Please fill in all fields');</script>";
    } else {
        // تحقق من البريد الإلكتروني
        if (!filter_var($patient_email, FILTER_VALIDATE_EMAIL)) {
           
            echo "<script>window.alert('Invalid email address.');</script>";

        } else {
            try {
                // استخدام PDO لتأسيس اتصال بقاعدة البيانات
              

                // التحقق من البريد الإلكتروني مرة أخرى قبل إجراء الإدخال في قاعدة البيانات
                $check_email_query = "SELECT COUNT(*) FROM patients WHERE patient_email = :patient_email";
                $stmt_check_email = $conn->prepare($check_email_query);
                $stmt_check_email->bindParam(':patient_email', $patient_email);
                $stmt_check_email->execute();
                $email_exists = $stmt_check_email->fetchColumn();

                if (!$email_exists) {
                    // إدراج بيانات المريض باستخدام prepared statement
                    $sql_insert_patient = "INSERT INTO patients (patient_name, patient_age, patient_email, phone_number, patient_gender) 
                                        VALUES (:patient_name, :patient_age, :patient_email, :phone_number, :patient_gender)";
                    $stmt_insert_patient = $conn->prepare($sql_insert_patient);
                    $stmt_insert_patient->bindParam(':patient_name', $patient_name);
                    $stmt_insert_patient->bindParam(':patient_age', $patient_age);
                    $stmt_insert_patient->bindParam(':patient_email', $patient_email);
                    $stmt_insert_patient->bindParam(':phone_number', $phone_number);
                    $stmt_insert_patient->bindParam(':patient_gender', $patient_gender);

                    if ($stmt_insert_patient->execute()) {
                        // إذا نجح إدراج المريض، قم بإدراج موعد الحجز
                        $appointment_date = $_POST["appointment_date"];
                        $dentist_id = $_POST["dentist_id"];
                        $services = $_POST["services"];

                        $patient_id = $conn->lastInsertId();

                        $sql_insert_appointment = "INSERT INTO appointments (patient_id, dentist_id, appointment_date, services) 
                                                VALUES (:patient_id, :dentist_id, :appointment_date, :services)";
                        $stmt_insert_appointment = $conn->prepare($sql_insert_appointment);
                        $stmt_insert_appointment->bindParam(':patient_id', $patient_id);
                        $stmt_insert_appointment->bindParam(':dentist_id', $dentist_id);
                        $stmt_insert_appointment->bindParam(':services', $services);
                        $stmt_insert_appointment->bindParam(':appointment_date', $appointment_date);

                        if ($stmt_insert_appointment->execute()) {
                         header ("location:booking_successfully.php");                       
                        } else {
                         
                            echo "<script>window.alert('Error inserting appointment data.');</script>";

                        }
                    } else {
                        echo "<script>window.alert('Error inserting patient data.');</script>";


                    }
                } else {
                    echo "<script>window.alert('Email $patient_email already exists.');</script>";

                }
            } catch (PDOException $e) {
                echo "Database error: " . $e->getMessage();
            } finally {
                // إغلاق الاتصال بقاعدة البيانات في أي حالة
                $conn = null;
            }
        }
    }
}
?>

<!-- Your HTML form remains unchanged -->
    <div class="sign-up-2">

<div class="contact-form-8">
  <div class="container-18">
    <div class="section-title-11">
      <h2 class="get-in-touch-10">Make An Appointments</h2>
    </div>
    <div class="form-wrapper-7 w-form">
      <form id="webflow-form" name="webflow-form" data-name="Webflow Form" method="post" class="form-6" data-wf-page-id="65be7b0d148e4c2b5419621e" data-wf-element-id="7d3405bb-b20f-a0ab-ef6b-5cc15e3de896">
        <div class="input-wrapper-10">
          <label for="name-2" class="text-105">Name</label><input class="form-text-input-16 w-input" maxlength="256" name="patient_name" data-name="Name 2" placeholder="Your full name" type="text" id="name-2" />
        </div>
        <div class="input-wrapper-10">
          <label for="name-2" class="text-105">Age</label><input class="form-text-input-17 w-input" maxlength="256" name="patient_age" data-name="Name 2" placeholder="20" type="text" id="name-2" />
        </div>
        <div class="input-wrapper-10">
          <label for="name-2" class="text-105">Phone number</label><input class="form-text-input-18 w-input" maxlength="256" name="phone_number" data-name="Name 2" placeholder="777 777 777" type="text" id="name-2" />
        </div>
        <div class="input-wrapper-10">
          <label for="name-2" class="text-105">Email</label><input class="form-text-input-19 w-input" maxlength="256" name="patient_email" data-name="Name 2" placeholder="me@gmail.com" type="email" id="name-2" />
        </div>
        <!-- <div class="input-wrapper-10">
          <label for="name-2" class="text-105">Password</label><input class="form-text-input-20 w-input" maxlength="256" name="pass" data-name="Name 2" placeholder="*" type="password" id="name-2" />
        </div> -->
        <div class="input-wrapper-10">
          <label for="name-2" class="text-105">Appointment_date</label><input class="form-text-input-20 w-input" maxlength="256" name="appointment_date" data-name="Name 2" placeholder="" type="date" id="name-2" />
        </div>
        <div class="input-wrapper-10">
          <label for="name-2" class="text-105">Dentist</label><input class="form-text-input-20 w-input" maxlength="256" name="dentist_id" data-name="Name 2" placeholder="" type="number" id="name-2" />
        </div>
 
        <div class="input-wrapper-1" >
        <label for="name-2" class="text-105" > Services </label>

        <select name="services" required >
            <option value="1">Cleaning</option>
            <option value="2" > Surgery</option>
        </select><br>
        <label for="name-2" class="text-105" > Patient Gender: </label>

        <select name="patient_gender" >
            <option value="1">Male</option>
            <option value="2">Female</option>
        </select><br>
        </div>
        <input type="submit" data-wait="Please wait..." class="form-button-7 w-button" value="REGISTER" />
      </form>
      <div class="w-form-done">
        <div>Thank you! Your submission has been received!</div>
      </div>
      <div class="w-form-fail">
        <div>Oops! Something went wrong while submitting the form.</div>
      </div>
    </div>
  </div>
</div>
</div>
</div>
<?php require_once('footer.php') ?>