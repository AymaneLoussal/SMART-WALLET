<?php
session_start();
require "../config/db.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$sql = "SELECT e.amount, e.created_at, COALESCE(c.name, 'Transfer') AS category, COALESCE(ca.card_name, 'Unknown') AS card_name
    FROM expenses e
    LEFT JOIN categories c ON e.category_id=c.id
    LEFT JOIN cards ca ON e.card_id=ca.id
    WHERE e.user_id=?
    ORDER BY e.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rows = $stmt->get_result();
?>
<a href="add.php">➕ Add Expense</a>
<table border="1">
    <tr>
        <th>Card</th>
        <th>Category</th>
        <th>Amount</th>
        <th>Date</th>
    </tr>
    <?php while ($r = $rows->fetch_assoc()): ?>
        <tr>
            <td><?= $r['card_name'] ?></td>
            <td><?= $r['category'] ?></td>
            <td><?= $r['amount'] ?></td>
            <td><?= $r['created_at'] ?></td>
        </tr>
    <?php endwhile; ?>
</table>