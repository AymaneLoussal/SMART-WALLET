<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT incomes.amount, incomes.description, incomes.created_at, cards.card_name
    FROM incomes
    JOIN cards ON incomes.card_id = cards.id
    WHERE incomes.user_id=?
    ORDER BY incomes.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$incomes = $stmt->get_result();
?>

<h2>Income History</h2>
<a href="add.php">➕ Add Income</a>

<table border="1" cellpadding="10">
    <tr>
        <th>Card</th>
        <th>Amount</th>
        <th>Description</th>
        <th>Date</th>
    </tr>

<?php while ($row = $incomes->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['card_name']) ?></td>
    <td><?= $row['amount'] ?> DH</td>
    <td><?= htmlspecialchars($row['description']) ?></td>
    <td><?= $row['created_at'] ?></td>
</tr>
<?php endwhile; ?>
</table>
