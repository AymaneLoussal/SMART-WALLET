<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$cards = $conn->prepare("SELECT id, card_name FROM cards WHERE user_id=?");
$cards->bind_param("i", $user_id);
$cards->execute();
$cards = $cards->get_result();

$cats = $conn->query("SELECT * FROM categories");

if (isset($_POST['add'])) {
    $card_id = $_POST['card_id'];
    $category_id = $_POST['category_id'];
    $amount = $_POST['amount'];

    // 1️⃣ احسب مجموع مصاريف هذا الشهر لهذه الفئة
    $stmt = $conn->prepare("
        SELECT IFNULL(SUM(amount),0) total
        FROM expenses
        WHERE user_id=? AND category_id=?
          AND MONTH(created_at)=MONTH(CURRENT_DATE())
          AND YEAR(created_at)=YEAR(CURRENT_DATE())
    ");
    $stmt->bind_param("ii", $user_id, $category_id);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];

    // 2️⃣ جلب الحد الشهري
    $stmt = $conn->prepare("
        SELECT monthly_limit FROM limits
        WHERE user_id=? AND category_id=?
    ");
    $stmt->bind_param("ii", $user_id, $category_id);
    $stmt->execute();
    $limitRow = $stmt->get_result()->fetch_assoc();

    if ($limitRow && ($total + $amount) > $limitRow['monthly_limit']) {
        die("Limit exceeded. Expense blocked.");
    }

    // 3️⃣ أضف المصروف
    $stmt = $conn->prepare("
        INSERT INTO expenses (user_id, card_id, category_id, amount)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiid", $user_id, $card_id, $category_id, $amount);
    $stmt->execute();

    // 4️⃣ خصم الرصيد من البطاقة
    $stmt = $conn->prepare("
        UPDATE cards SET balance = balance - ?
        WHERE id=? AND user_id=?
    ");
    $stmt->bind_param("dii", $amount, $card_id, $user_id);
    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>
<form method="post">
    <select name="card_id" required>
        <?php while ($c = $cards->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= $c['card_name'] ?></option>
        <?php endwhile; ?>
    </select>

    <select name="category_id" required>
        <?php while ($c = $cats->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
        <?php endwhile; ?>
    </select>

    <input type="number" step="0.01" name="amount" required>
    <button name="add">Add Expense</button>
</form>