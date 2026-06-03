<?php
include "../service/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["is_login"]) || $_SESSION["is_login"] !== true || $_SESSION["role"] !== 'guest') {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'] ?? 0;
if ($userId <= 0 && !empty($_SESSION['email'])) {
    $userStmt = $db->prepare("SELECT id FROM msuser WHERE email = ? LIMIT 1");
    if ($userStmt) {
        $userStmt->bind_param('s', $_SESSION['email']);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userRow = $userResult->fetch_assoc();
        if ($userRow) {
            $userId = (int)$userRow['id'];
            $_SESSION['user_id'] = $userId;
        }
        $userStmt->close();
    }
}

if (isset($_SESSION['transactions'])) {
    unset($_SESSION['transactions']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_history'])) {
    if ($userId > 0) {
        $deleteStmt = $db->prepare("DELETE FROM transactions WHERE user_id = ?");
        if ($deleteStmt) {
            $deleteStmt->bind_param('i', $userId);
            $deleteStmt->execute();
            $deleteStmt->close();
        }
    }
    header('Location: transaction.php?cleared=1');
    exit();
}

$transactions = [];
if ($userId > 0) {
    $stmt = $db->prepare("SELECT created_at, quantity, price, total_amount, status, shipping_address, weapon_id FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $weaponName = $row['weapon_id'];
            $weaponStmt = $db->prepare("SELECT gun FROM weapons WHERE id = ? LIMIT 1");
            if ($weaponStmt) {
                $weaponStmt->bind_param('i', $row['weapon_id']);
                $weaponStmt->execute();
                $weaponResult = $weaponStmt->get_result();
                $weaponRow = $weaponResult->fetch_assoc();
                if ($weaponRow) {
                    $weaponName = $weaponRow['gun'];
                }
                $weaponStmt->close();
            }
            $transactions[] = [
                'date' => date('Y-m-d', strtotime($row['created_at'])),
                'item' => $weaponName,
                'amount' => $row['total_amount'],
                'status' => $row['status'],
            ];
        }
        $stmt->close();
    }
}

$purchaseSuccess = isset($_GET['success']) && $_GET['success'] === '1';
$clearSuccess = isset($_GET['cleared']) && $_GET['cleared'] === '1';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="member-body">
    <?php include "../includes/header.php"; ?>

    <main class="member-shell">
        <section class="member-page-intro">
            <h1>Transaction History</h1>
            <p class="member-lead">Review your recent purchases and payment status in one place.</p>
            <?php if ($purchaseSuccess): ?>
                <div class="success-banner">Purchase completed successfully. Your transaction is now recorded.</div>
            <?php endif; ?>
            <?php if ($clearSuccess): ?>
                <div class="success-banner">Transaction history cleared successfully.</div>
            <?php endif; ?>
        </section>

        <section class="transaction-table">
            <?php if (count($transactions) > 0): ?>
                <form method="POST" class="clear-history-form">
                    <button type="submit" name="clear_history" class="btn-secondary">Clear History</button>
                </form>
            <?php endif; ?>
            <?php if (count($transactions) === 0): ?>
                <div class="empty-state">
                    <p>You have no transaction history yet. Your purchases will appear here once completed.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tx['date']); ?></td>
                                <td><?php echo htmlspecialchars($tx['item']); ?></td>
                                <td>$<?php echo number_format($tx['amount']); ?></td>
                                <td><?php echo htmlspecialchars($tx['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <?php include "../includes/footer.php"; ?>
</body>

</html>