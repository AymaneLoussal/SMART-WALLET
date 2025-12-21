<?php
session_start();
require "../config/db.php";
require "../config/scheduler.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
ensure_salary_columns($conn);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monthly_salary = isset($_POST['monthly_salary']) ? floatval(str_replace(',', '.', $_POST['monthly_salary'])) : 0;
    $salary_card_id = isset($_POST['salary_card_id']) ? intval($_POST['salary_card_id']) : null;

    if ($monthly_salary < 0) {
        $error = 'Salary must be zero or positive.';
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE users SET monthly_salary = ?, salary_card_id = ? WHERE id = ?");
        $stmt->bind_param('dii', $monthly_salary, $salary_card_id, $user_id);
        $stmt->execute();

        header('Location: ../cards/index.php');
        exit;
    }
}

$stmt = $conn->prepare("SELECT id, card_name, balance FROM cards WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$cards = $stmt->get_result();

$stmt = $conn->prepare("SELECT monthly_salary, salary_card_id FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
?>

<h2>Set Monthly Salary</h2>

<?php if ($error): ?>
    <div style="color:red"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post">
    <label>Monthly salary amount:</label>
    <input type="text" name="monthly_salary" value="<?php echo htmlspecialchars($me['monthly_salary'] ?? ''); ?>" required>

    <label>Deposit to (your card):</label>
    <select name="salary_card_id" required>
        <option value="">-- select card --</option>
        <?php while ($c = $cards->fetch_assoc()): ?>
            <option value="<?php echo $c['id']; ?>" <?php echo ($me['salary_card_id'] == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['card_name']) . ' (Balance: ' . number_format($c['balance'], 2) . ')'; ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Save Salary</button>
</form>

<?php
// end
?>