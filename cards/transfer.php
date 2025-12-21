<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $source_card_id = isset($_POST['source_card_id']) ? intval($_POST['source_card_id']) : 0;
    $target_email = trim($_POST['target_email'] ?? '');
    $target_card_name = trim($_POST['target_card_name'] ?? '');
    $amount = isset($_POST['amount']) ? floatval(str_replace(',', '.', $_POST['amount'])) : 0;
    $description = trim($_POST['description'] ?? '');

    if ($amount <= 0) {
        $error = 'Enter a valid amount greater than zero.';
    }

    if (!$error) {
        $stmt = $conn->prepare("SELECT id, balance FROM cards WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $source_card_id, $user_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $error = 'Source card not found.';
        } else {
            $source = $res->fetch_assoc();
            if ($source['balance'] < $amount) {
                $error = 'Insufficient balance on source card.';
            }
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("SELECT c.id, c.balance, c.user_id FROM cards c JOIN users u ON c.user_id = u.id WHERE u.email = ? AND c.card_name = ? LIMIT 1");
        $stmt->bind_param("ss", $target_email, $target_card_name);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $error = 'Target card not found for that email and card name.';
        } else {
            $target = $res->fetch_assoc();
            if ($target['id'] == $source_card_id) {
                $error = 'Cannot transfer to the same card.';
            }
        }
    }

    if (!$error) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE cards SET balance = balance - ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $source_card_id);
            $stmt->execute();

            $stmt = $conn->prepare("UPDATE cards SET balance = balance + ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $target['id']);
            $stmt->execute();

            // ensure transfers table exists
            $conn->query("CREATE TABLE IF NOT EXISTS transfers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                from_card_id INT,
                to_card_id INT,
                amount DECIMAL(10,2),
                description VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $stmt = $conn->prepare("INSERT INTO transfers (from_card_id, to_card_id, amount, description) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iids", $source_card_id, $target['id'], $amount, $description);
            $stmt->execute();

            // Record an expense for the sender (category NULL) and income for the recipient
            // Insert expense (sender)
            $stmt = $conn->prepare("INSERT INTO expenses (user_id, card_id, category_id, amount) VALUES (?, ?, NULL, ?)");
            $stmt->bind_param("iid", $user_id, $source_card_id, $amount);
            $stmt->execute();

            // Insert income (recipient)
            $stmt = $conn->prepare("INSERT INTO incomes (user_id, card_id, amount, Description) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iids", $target['user_id'], $target['id'], $amount, $description);
            $stmt->execute();

            $conn->commit();

            header("Location: index.php?transfer=success");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Transfer failed: ' . $e->getMessage();
        }
    }
}

$stmt = $conn->prepare("SELECT id, card_name, balance FROM cards WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cards = $stmt->get_result();
?>

<h2>Transfer Money</h2>

<?php if ($error): ?>
    <div style="color: red;"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post">
    <label>From (your card):</label>
    <select name="source_card_id" required>
        <?php while ($c = $cards->fetch_assoc()): ?>
            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['card_name']) . ' (Balance: ' . number_format($c['balance'], 2) . ')'; ?></option>
        <?php endwhile; ?>
    </select>

    <label>Recipient email:</label>
    <input type="email" name="target_email" required>

    <label>Recipient card name:</label>
    <input type="text" name="target_card_name" required>

    <label>Amount:</label>
    <input type="text" name="amount" required>

    <label>Description (optional):</label>
    <input type="text" name="description">

    <button type="submit">Send</button>
</form>

<?php
// end
?>