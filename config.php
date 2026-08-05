<?php
	

	$dsn="mysql:host=localhost;dbname=appointment_system;charset=utf8";
	$user="root";
	$pass="";
	// $optionl=array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8');
	
	try{
	$conn=new PDO($dsn,$user,$pass);
	//echo "Connected to Database";
	}
	catch(PDOException $e){
		exit($e->getMessage());
	}



?>


