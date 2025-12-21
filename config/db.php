<?php 
$conn = new mysqli("localhost", "root", "", "smartWallet");

if($conn->connect_error){
    die("Database connection failed");
}
?>