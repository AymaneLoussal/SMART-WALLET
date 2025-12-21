<?php
require "../config/db.php";
if(isset($_POST['register'])){
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);
    $stmt->execute();

        $user_id = $conn->insert_id;

        $defaultCardName = "Main Card";

        $stmt = $conn->prepare(
            "INSERT INTO cards (user_id, card_name, balance, is_primary)
            VALUES (?, ?, 0, 1)"
        );
        $stmt->bind_param("is", $user_id, $defaultCardName);
        $stmt->execute();


    header("location: ../auth/login.php");

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>authentification</title>
</head>
<body>
    <form action="" method="post">
        <input type="text" name="full_name" placeholder="full name" required>
        <input type="email" name="email" placeholder="email" required>
        <input type="password" name="password" placeholder="passord" required>
        <button name="register">Register</button>
    </form>
</body>
</html>