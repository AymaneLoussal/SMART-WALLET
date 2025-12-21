<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_POST['add_card'])) {
    $card_name = $_POST['card_name'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare(
        "INSERT INTO cards (user_id, card_name) VALUES (?, ?)"
    );
    $stmt->bind_param("is", $user_id, $card_name);
    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>

<h2>Add Card</h2>

<form method="post">
    <input type="text" name="card_name" placeholder="Card name (CIH, BP...)" required>
    <button name="add_card">Add</button>
</form>
