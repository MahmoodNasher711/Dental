<?php

require_once '../config.php';

//session_start();
// $user_id = $_SESSION['user_id'];

// if(!isset($user_id)){
//    header('location:../login/login.php');
// };

// if(isset($_GET['logout'])){
//    unset($user_id);
//    session_destroy();
//    header('location:../login/login.php');
// } 

    
    

    
    if($_GET){
        //import database
        $id=$_GET["id"];
        $result001= $conn->query("select * from dentists where dentist_id=$id;");
        $email=($result001->fetch())["dentist_email"];
        //$sql= $database->query("delete from webuser where email='$email';");
        $sql= $conn->query("delete from dentists where dentist_email='$email';");
        //print_r($email);
        header("location: doctors.php");
    }
    

?>
