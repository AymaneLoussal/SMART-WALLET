<?php 
session_start();
require "../config/db.php";
require "../config/mailer.php"; 

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if($user && password_verify($password, $user['password'])){

        $otp = rand(100000, 999999);
        $expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        $stmt = $conn->prepare(
            "INSERT INTO otp_codes (user_id, otp_code, expires_at) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iss", $user['id'], $otp, $expires);
        $stmt->execute();

        $_SESSION['otp_user'] = $user['id'];

        sendOTP($user['email'], $otp);

        header("Location: otp.php");
        exit;

    }else{
        echo "Invalid login";
    }
}
?>




<form method="post">
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button name="login">Login</button>
</form>