<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=optimis7_andersonportal;charset=utf8mb4', 'optimis7_andersonportaluser', 'YVFw#7n&8+3T');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    echo "Connection failed : ". $e->getMessage();
}
    /*** Base URL ***/
    $url= "https://andersonroadchildcare.com.au/andersonportal/";
?>

