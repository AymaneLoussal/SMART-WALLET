<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// جلب بطاقات المستخدم
$stmt = $conn->prepare("SELECT id, card_name FROM cards WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cards = $stmt->get_result();

if (isset($_POST['add_income'])) {
    $card_id = $_POST['card_id'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    // 1️⃣ إضافة الدخل
    $stmt = $conn->prepare(
        "INSERT INTO incomes (user_id, card_id, amount, description)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("iids", $user_id, $card_id, $amount, $description);
    $stmt->execute();

    // 2️⃣ تحديث رصيد البطاقة
    $stmt = $conn->prepare(
        "UPDATE cards SET balance = balance + ? WHERE id=? AND user_id=?"
    );
    $stmt->bind_param("dii", $amount, $card_id, $user_id);
    $stmt->execute();

    header("Location: ../cards/index.php");
    exit;
}
?>

<h2>Add Income</h2>

<form method="post">
    <select name="card_id" required>
        <option value="">Select card</option>
        <?php while ($card = $cards->fetch_assoc()): ?>
            <option value="<?= $card['id'] ?>">
                <?= htmlspecialchars($card['card_name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <input type="number" step="0.01" name="amount" placeholder="Amount" required>
    <input type="text" name="description" placeholder="Description">
    <button name="add_income">Add Income</button>
</form>
