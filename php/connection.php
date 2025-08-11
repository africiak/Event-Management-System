<?php

//Database configuration
$host = 'localhost';
$dbname ='hiveflow';
$username = 'root';
$password = '';

//connection
try{
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connectionStatus = 'success';
    $connectionMessage = 'Connection successful!';
}catch(PDOException $e){
    $connectionStatus = 'fail';
    $connectionMessage = $e->getMessage();
}

?>