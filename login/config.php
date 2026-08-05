
<?php

$host = 'localhost';
$dbname = 'appointment_system';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   // echo 'Connection successful';
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>