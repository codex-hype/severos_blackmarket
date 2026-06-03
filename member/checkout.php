<?php
include "../service/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["is_login"]) || $_SESSION["is_login"] !== true || $_SESSION["role"] !== 'guest') {
    header("Location: ../login.php");
    exit();
}

$itemId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = null;
if ($itemId > 0) {
    $stmt = $db->prepare("SELECT id, gun, rarity, type, price FROM weapons WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();
    }
}

if (!$item) {
    header('Location: marketplace.php');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
    $shipping = trim($_POST['shipping'] ?? '');

    if ($shipping === '') {
        $errors[] = 'Shipping address is required.';
    }

    if (empty($errors)) {
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

        if ($userId <= 0) {
            $errors[] = 'Unable to identify the current user. Please log in again.';
        } else {
            $amount = $item['price'] * $quantity;
            $shippingAddress = trim($_POST['shipping'] ?? '');
            $status = 'Completed';

            $stmt = $db->prepare("INSERT INTO transactions (user_id, weapon_id, quantity, price, total_amount, shipping_address, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('iiiiiss', $userId, $item['id'], $quantity, $item['price'], $amount, $shippingAddress, $status);
                $stmt->execute();
                $stmt->close();
            }

            $arsenalStmt = $db->prepare("INSERT IGNORE INTO arsenal (user_id, weapon_id, acquired_at) VALUES (?, ?, NOW())");
            if ($arsenalStmt) {
                $arsenalStmt->bind_param('ii', $userId, $item['id']);
                $arsenalStmt->execute();
                $arsenalStmt->close();
            }

            header('Location: transaction.php?success=1');
            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="member-body">
    <?php include "../includes/header.php"; ?>

    <main class="member-shell">
        <section class="member-page-intro">
            <h1>Checkout</h1>
            <p class="member-lead">Confirm your order details before finalizing purchase.</p>
        </section>

        <?php if (!empty($errors)): ?>
            <div class="error-banner">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="checkout-grid">
            <div class="checkout-summary">
                <h2>Order summary</h2>
                <p><strong>Weapon:</strong> <?= htmlspecialchars($item['gun']); ?></p>
                <p><strong>Type:</strong> <?= htmlspecialchars($item['type']); ?></p>
                <p><strong>Rarity:</strong> <?= htmlspecialchars($item['rarity']); ?></p>
                <p><strong>Price:</strong> $<?= number_format($item['price']); ?></p>
                <p class="checkout-note">The total will update after entering quantity.</p>
            </div>

            <form action="checkout.php?id=<?= $item['id']; ?>" method="POST" class="checkout-form">
                <div class="field-row">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" min="1" value="<?= htmlspecialchars($_POST['quantity'] ?? 1); ?>">
                </div>
                <div class="field-row">
                    <label for="shipping">Shipping address</label>
                    <textarea id="shipping" name="shipping" rows="4"><?= htmlspecialchars($_POST['shipping'] ?? ''); ?></textarea>
                </div>
                <div class="button-row">
                    <button type="submit" class="btn-primary">Confirm Purchase</button>
                    <a class="btn-secondary" href="weapon-detail.php?id=<?= $item['id']; ?>">Back</a>
                </div>
            </form>
        </section>
    </main>

    <?php include "../includes/footer.php"; ?>
</body>

</html>