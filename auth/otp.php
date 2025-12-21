<?php
session_start();
require "../config/db.php";
require "../config/scheduler.php";

if (!isset($_SESSION['otp_user'])) {
    die("No OTP session");
}

if (isset($_POST['verify'])) {
    $otp = trim($_POST['otp']);
    $user_id = $_SESSION['otp_user'];

    $stmt = $conn->prepare("
        SELECT * FROM otp_codes
        WHERE user_id=? AND otp_code=?
    ");
    $stmt->bind_param("is", $user_id, $otp);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['user_id'] = $user_id;
        unset($_SESSION['otp_user']);

        $stmt = $conn->prepare("DELETE FROM otp_codes WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // credit monthly salary if due for this user
        credit_user_salary($conn, $user_id);

        header("Location: ../cards/index.php");
        exit;
    } else {
        echo "Invalid OTP";
    }
}
?>

<form method="post">
    <input type="text" name="otp" required>
    <button name="verify">Verify</button>
</form>