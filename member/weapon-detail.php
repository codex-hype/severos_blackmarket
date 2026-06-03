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
    $stmt = $db->prepare("SELECT id, gun, rarity, type, price, description, damage, accuracy, fire_rate FROM weapons WHERE id = ?");
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

function rarityColor($rarity)
{
    return ['Common' => '#b0b0b0', 'Rare' => '#4a90d9', 'Epic' => '#9b59b6', 'Legendary' => '#f0a500'][$rarity] ?? '#ffffff';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weapon Detail</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="member-body">
    <?php include "../includes/header.php"; ?>

    <main class="member-shell">
        <section class="member-page-intro">
            <h1>Weapon Details</h1>
            <p class="member-lead">Review the complete profile for this weapon before you check out.</p>
        </section>

        <section class="weapon-detail-grid">
            <div class="weapon-detail-card">
                <span class="rarity-pill" style="background: <?= rarityColor($item['rarity']); ?>;"><?= htmlspecialchars($item['rarity']); ?></span>
                <h2><?= htmlspecialchars($item['gun']); ?></h2>
                <p class="weapon-detail-type">Type: <?= htmlspecialchars($item['type']); ?></p>
                <p class="weapon-detail-description"><?= htmlspecialchars($item['description']); ?></p>
                <div class="weapon-stats">
                    <div><strong>Damage</strong><span><?= htmlspecialchars($item['damage']); ?></span></div>
                    <div><strong>Accuracy</strong><span><?= htmlspecialchars($item['accuracy']); ?></span></div>
                    <div><strong>Fire Rate</strong><span><?= htmlspecialchars($item['fire_rate']); ?></span></div>
                </div>
                <p class="weapon-price">Price: $<?= number_format($item['price']); ?></p>
                <div class="weapon-actions">
                    <a class="btn-primary" href="checkout.php?id=<?= $item['id']; ?>">Checkout</a>
                    <a class="btn-secondary" href="marketplace.php">Back to Marketplace</a>
                </div>
            </div>
        </section>
    </main>

    <?php include "../includes/footer.php"; ?>
</body>

</html>