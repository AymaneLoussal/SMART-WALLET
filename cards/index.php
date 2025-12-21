<?php
session_start();
require "../config/db.php";

// حماية الصفحة
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// جلب بطاقات المستخدم
$stmt = $conn->prepare("SELECT * FROM cards WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cards = $stmt->get_result();
?>

<h2>My Cards</h2>

<a href="add.php">➕ Add new card</a>

<table border="1" cellpadding="10">
    <tr>
        <th>Card Name</th>
        <th>Balance</th>
        <th>Primary</th>
    </tr>

<?php while ($card = $cards->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($card['card_name']) ?></td>
    <td><?= $card['balance'] ?> DH</td>
    <td><?= $card['is_primary'] ? 'Yes' : 'No' ?></td>
</tr>
<?php endwhile; ?>
</table>
