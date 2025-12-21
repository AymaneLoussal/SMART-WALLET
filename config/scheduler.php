<?php
// Helper to ensure salary columns exist and credit monthly salaries
function ensure_salary_columns($conn)
{
    $check = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME IN ('monthly_salary','salary_card_id','last_salary_at')");
    $check->execute();
    $res = $check->get_result();
    $found = [];
    while ($r = $res->fetch_assoc()) $found[] = $r['COLUMN_NAME'];

    $need = [];
    foreach (['monthly_salary', 'salary_card_id', 'last_salary_at'] as $col) {
        if (!in_array($col, $found)) $need[] = $col;
    }

    if (!empty($need)) {
        $sqlParts = [];
        if (in_array('monthly_salary', $need)) $sqlParts[] = "ADD COLUMN monthly_salary DECIMAL(10,2) DEFAULT 0";
        if (in_array('salary_card_id', $need)) $sqlParts[] = "ADD COLUMN salary_card_id INT DEFAULT NULL";
        if (in_array('last_salary_at', $need)) $sqlParts[] = "ADD COLUMN last_salary_at DATE DEFAULT NULL";

        $sql = "ALTER TABLE users " . implode(', ', $sqlParts);
        $conn->query($sql);
    }
}

function credit_user_salary($conn, $user_id)
{
    ensure_salary_columns($conn);

    $stmt = $conn->prepare("SELECT monthly_salary, salary_card_id, last_salary_at FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user) return;

    $salary = floatval($user['monthly_salary']);
    $card_id = $user['salary_card_id'] ? intval($user['salary_card_id']) : null;
    $last = $user['last_salary_at'];

    if ($salary <= 0 || !$card_id) return;

    $firstOfThisMonth = date('Y-m-01');
    if ($last && $last >= $firstOfThisMonth) {
        // already credited this month
        return;
    }

    // perform transaction: update card balance and insert income, set last_salary_at
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE cards SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param('di', $salary, $card_id);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO incomes (user_id, card_id, amount, Description) VALUES (?, ?, ?, ?)");
        $desc = 'Monthly salary';
        $stmt->bind_param('iids', $user_id, $card_id, $salary, $desc);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE users SET last_salary_at = ? WHERE id = ?");
        $today = date('Y-m-d');
        $stmt->bind_param('si', $today, $user_id);
        $stmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
    }
}
