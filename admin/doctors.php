
<?php
// require_once '../config.php';

// //session_start();
// $user_id = $_SESSION['user_id'];

// if(!isset($user_id)){
//    header('location:../login/login.php');
// };

// if(isset($_GET['logout'])){
//    unset($user_id);
//    session_destroy();
//    header('location:../login/login.php');
// } 
// ?>





<?php
include("../config.php");
if(isset($_GET['action'],$_GET['id']) and intval($_GET['id'])>0)
    {
            if($_GET['action']=='delete')
            {
                $q= $conn->prepare("delete from dentists where dentist_id=:id");
                $q->execute(array("id"=>$_GET['id']));
                $deletedRows = $q->rowCount();
                
                if($deletedRows > 0)
                     echo "<h2 id='message' class='alert alert-danger'>One Row Deleted Successfully</h2>";
                else echo "<script>alert('Can\\'t Delete row');</script>";
                
               
            }
            }?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Doctors</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
</style>
<link href="../css/bootstrap.min.css" rel="stylesheet" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" />
	<link href="css/custom.css" rel="stylesheet" />
</head>
<body>

    <?php


    session_start();

    // if(isset($_SESSION["user"])){
    //     if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
    //         header("location: ../login.php");
    //     }

    // }else{
    //     header("location: ../login.php");
    // }
    
    

    //import database
    

    
    ?>
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title">Administrator</p>
                                    <p class="profile-subtitle">admin@gmail.com</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                <a href="logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                    </table>
                    </td>
                
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-dashbord" >
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor menu-active menu-icon-doctor-active">
                        <a href="doctors.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">dentists</p></a></div>
                    </td>
                </tr>
             
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">Appointment</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">Patients</p></a></div>
                    </td>
                </tr>

            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%">
                        <a href="doctors.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        

                            
                            <?php
                   if($_SERVER["REQUEST_METHOD"]=="POST")
                   {
                       if(empty($_POST["cname"]))
                       
                           $errors["cname"]="<span style='color:red;'>Enter Name of Category</span>";
                       
                       else if(empty($_POST["cid"]))
                       {
                           $sql= "insert into dentists(dentist_name,dentist_age,dentist_email,dentist_brief,image) 
                           values(:name,:age,:email,:brife,:img)";
                           $q=$conn->prepare($sql);
                           $q->execute(array("name"=>$_POST["cname"],"age"=>$_POST["age"],"email"=>$_POST["email"],"brife"=>$_POST["brife"],"img"=>$_POST["image"]));
                           if($q->rowcount())
                               echo "<h3 class='alert alert-success'>One Row Inserted</h3>";
                           else
                               echo "<h3 class='alert alert-danger'>couldn't Insert Row</h3>";
                           
                       }
                       
                   }
                   
?>
                            
                       
                        
                        </form>
                        
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 
                        date_default_timezone_set('Asia/Kolkata');

                        $date = date('Y-m-d');
                        echo $date;
                        ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>


                </tr>
               
                <tr >
                    <td colspan="2" style="padding-top:30px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">Add New Doctor</p>
                        <div class="form-group">
			<div class="input-group">
				<span class="input-group-addon"><span class="glyphicon glyphicon-tags"></span>
				</span>
                <Form class="form-signin" method="post">
				<input type="hidden" class="form-control" name="cid"  />
				<input type="text" class="form-control" name="cname" placeholder="Name" 
				value="<?php if(isset($row)) echo $row["dentist_name"];  ?>" />
				<input type="text" class="form-control" name="age" placeholder="age" 
				value="<?php if(isset($row)) echo $row["dentist_age"];  ?>" />
				<input type="text" class="form-control" name="email" placeholder="email" 
				value="<?php if(isset($row)) echo $row["dentist_email"];  ?>" />
				<input type="text" class="form-control" name="brife" placeholder="brief" 
				value="<?php if(isset($row)) echo $row["dentist_brife"];  ?>" />
				<input type="file" class="form-control" name="image" placeholder="image" 
				value="<?php if(isset($row)) echo $row["image"];  ?>" />
				<input type="submit" value="save"/> 
    </Form>
				
				
			</div>
			<?php   if(isset($errors["cname"])) echo $errors["cname"]; ?>
					
		</div>
                    </td>
                    <td colspan="2">
                            </a></td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:10px;">
                    </td>
                    
                </tr>
           
                  
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                                <th class="table-headin">
                                    
                                
                                Doctor Id
                                
                                </th>
                                <th class="table-headin">
                                    
                                
                                Doctor Name
                                
                                </th>
                                <th class="table-headin">
                                    
                                
                                Doctor age
                                
                                </th>
                                <th class="table-headin">
                                    Email
                                </th>
                             
                             
                                <th class="table-headin">
                                    
                                    Event
                                    
                                </tr>
                        </thead>
                        <tbody>
                        
                        <?php
                                echo "<datalist id='doctors'>";
                                $sql = "select  *  from  dentists";
                                $list11=   $conn->prepare($sql);
                                $list11->execute();
                                $q= $list11->rowcount();
                                if($q>0)
{

    $rows =$list11->fetchall();
    foreach($rows as $r)
    {
       $id= $r['dentist_id'];
        $name= $r['dentist_name'];
        $age= $r['dentist_age'];
        $email= $r['dentist_email'];
        



 echo "<tr>";
 echo " <td> $id </td>";
 echo " <td> $name </td>";
 echo " <td> $age </td>";
 echo " <td> $email </td>";
 echo " <td> ";
 
 echo "<a href='?action=delete&id=$id' title='delete'> <button>Delete</button></a>";
 
echo " </td>";
 
 echo "</tr>";
    }
}

?>
 
                            </tbody>

                        </table>
                        </div>
                        </center>
                   </td> 
                </tr>
                       
                        
                        
            </table>
        </div>
    </div>
    <?php 
    if($_GET){
        
        $id=$_GET["id"];
        $action=$_GET["action"];
        if($action=='drop'){
            $nameget=$_GET["name"];
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2>Are you sure?</h2>
                        <a class="close" href="doctors.php">&times;</a>
                        <div class="content">
                            You want to delete this record<br>('.substr($nameget,0,40).').
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <a href="delete-doctor.php?id='.$id.'" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"<font class="tn-in-text">&nbsp;Yes&nbsp;</font></button></a>&nbsp;&nbsp;&nbsp;
                        <a href="doctors.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font></button></a>

                        </div>
                    </center>
            </div>
            </div>
            ';
        }elseif($action=='view'){
            $sqlmain= "select * from doctor where docid='$id'";
            $result= $database->query($sqlmain);
            $row=$result->fetch_assoc();
            $name=$row["dentist_name"];
            $email=$row["dentist_email"];
             $spe=$row["dentist_brief"];
            
         
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2></h2>
                        <a class="close" href="doctors.php">&times;</a>
                        <div class="content">
                            eDoc Web App<br>
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                        
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Details.</p><br><br>
                                </td>
                            </tr>
                            
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Name: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.$id.'
                                </td>
                                
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    '.$name.'
                                </td>
                                
                            </tr>
                                <tr>
                                    <td class="label-td" colspan="2">
                                        '.$age.'<br><br>
                                    </td>
                                    
                                </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Email" class="form-label">Email: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                               // .$email.<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="nic" class="form-label">delet </label>
                                </td>
                            </tr>
                       
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Tele" class="form-label">Telephone: </label>
                                </td>
                            </tr>
                        
                          
                        
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="doctors.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                
                                    
                                </td>
                
                            </tr>
                           

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            ';
        }
   



    }; 
    

?>
</div>

</body>
</html>