<?php
include "../service/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["is_login"]) || $_SESSION["is_login"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_payment_status') {
        $paymentId = intval($_POST['payment_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'Pending');
        $method = trim($_POST['method'] ?? 'Debit Card');
        $validStatus = ['Pending', 'Completed', 'Cancelled'];
        $validMethods = ['Debit Card','Credit Card','PayPal','Bank Transfer','Crypto'];

        if ($paymentId <= 0 || !in_array($status, $validStatus, true) || !in_array($method, $validMethods, true)) {
            $errors[] = 'Invalid payment, status or method selected.';
        } else {
            $stmt = $db->prepare("UPDATE transactions SET status = ?, payment_method = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('ssi', $status, $method, $paymentId);
                if ($stmt->execute()) {
                    $message = 'Payment updated successfully.';
                } else {
                    $errors[] = 'Unable to update payment.';
                }
                $stmt->close();
            } else {
                $errors[] = 'Unable to update payment.';
            }
        }
    } elseif ($action === 'delete_payment') {
        $paymentId = intval($_POST['payment_id'] ?? 0);
        if ($paymentId <= 0) {
            $errors[] = 'Invalid payment selected for deletion.';
        } else {
            $stmt = $db->prepare("DELETE FROM transactions WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $paymentId);
                if ($stmt->execute()) {
                    $message = 'Transaction deleted successfully.';
                } else {
                    $errors[] = 'Unable to delete transaction.';
                }
                $stmt->close();
            } else {
                $errors[] = 'Unable to delete transaction.';
            }
        }
    }
}

$transactions = [];
$sql = "SELECT t.id, u.username, w.gun, t.quantity, t.price, t.total_amount, t.status, t.shipping_address, t.created_at
    , t.payment_method
    FROM transactions t
        LEFT JOIN msuser u ON t.user_id = u.id
        LEFT JOIN weapons w ON t.weapon_id = w.id
        ORDER BY t.created_at DESC
        LIMIT 50";
$result = $db->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Payments</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="admin-body">
    <?php include "../includes/header.php"; ?>

    <main class="admin-dashboard">
        <section class="admin-hero">
            <div class="admin-hero-copy">
                <p class="admin-badge">Administrator Panel</p>
                <h1>Payments</h1>
                <p class="admin-lead">Review recent marketplace transactions, update statuses, and remove invalid
                    entries from the ledger.</p>
            </div>
            <div class="admin-hero-actions">
                <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
            </div>
        </section>

        <?php
        $adminNavItems = [
            'dashboard.php' => 'Dashboard',
            'payment.php' => 'Payments',
            'weapon.php' => 'Weapons',
            'weapontype.php' => 'Weapon Types',
        ];
        $currentPage = basename($_SERVER['PHP_SELF']);
        ?>

        <nav class="admin-nav" aria-label="Admin navigation">
            <ul>
                <?php foreach ($adminNavItems as $file => $label): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($file); ?>"
                        class="admin-nav-link <?php echo $currentPage === $file ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($label); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <?php if (!empty($errors)): ?>
        <div class="error-banner"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
        <?php elseif ($message): ?>
        <div class="success-banner"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <section class="transaction-table">
            <h2>Recent Transactions</h2>
            <?php if (count($transactions) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Method</th>
                        <th>Weapon</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['username'] ?: 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($transaction['payment_method'] ?? 'Debit Card'); ?></td>
                        <td><?php echo htmlspecialchars($transaction['gun'] ?: 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($transaction['quantity']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($transaction['price'])); ?></td>
                        <td><?php echo htmlspecialchars(number_format($transaction['total_amount'])); ?></td>
                        <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
                        <td>
                            <div class="table-actions">
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="update_payment_status">
                                    <input type="hidden" name="payment_id"
                                        value="<?php echo htmlspecialchars($transaction['id']); ?>">
                                    <select name="status" style="min-width: 120px;">
                                        <?php foreach (['Pending', 'Completed', 'Cancelled'] as $statusOption): ?>
                                        <option value="<?php echo $statusOption; ?>"
                                            <?php echo $transaction['status'] === $statusOption ? 'selected' : ''; ?>>
                                            <?php echo $statusOption; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="method" style="min-width: 150px; margin-left:8px;">
                                        <?php foreach (['Debit Card','Credit Card','PayPal','Bank Transfer','Crypto'] as $methodOption): ?>
                                        <option value="<?php echo $methodOption; ?>"
                                            <?php echo ($transaction['payment_method'] ?? 'Debit Card') === $methodOption ? 'selected' : ''; ?>>
                                            <?php echo $methodOption; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn-secondary">Save</button>
                                </form>
                                <form method="post" class="inline-form"
                                    onsubmit="return confirm('Delete this transaction?');">
                                    <input type="hidden" name="action" value="delete_payment">
                                    <input type="hidden" name="payment_id"
                                        value="<?php echo htmlspecialchars($transaction['id']); ?>">
                                    <button type="submit" class="btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No transactions have been recorded yet.</div>
            <?php endif; ?>q
        </section>
    </main>
</body>

</html>