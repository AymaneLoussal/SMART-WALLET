<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }
$user_id = $_SESSION['user_id'];

$cats = $conn->query("SELECT * FROM categories");

if (isset($_POST['save'])) {
    $category_id = $_POST['category_id'];
    $limit = $_POST['limit'];

    // حد واحد لكل فئة (تحديث إن وجد)
    $stmt = $conn->prepare("
        INSERT INTO limits (user_id, category_id, monthly_limit)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE monthly_limit=VALUES(monthly_limit)
    ");
    $stmt->bind_param("iid", $user_id, $category_id, $limit);
    $stmt->execute();

    echo "Limit saved";
}
?>
<form method="post">
    <select name="category_id" required>
        <?php while($c=$cats->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
        <?php endwhile; ?>
    </select>
    <input type="number" step="0.01" name="limit" placeholder="Monthly limit" required>
    <button name="save">Save</button>
</form>
