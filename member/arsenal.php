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

$arsenalItems = [];
if ($userId > 0) {
    $arsenalStmt = $db->prepare("SELECT w.gun, w.type, w.rarity, a.acquired_at FROM arsenal a JOIN weapons w ON a.weapon_id = w.id WHERE a.user_id = ? ORDER BY a.acquired_at DESC");
    if ($arsenalStmt) {
        $arsenalStmt->bind_param('i', $userId);
        $arsenalStmt->execute();
        $arsenalResult = $arsenalStmt->get_result();
        while ($row = $arsenalResult->fetch_assoc()) {
            $arsenalItems[] = [
                'name' => $row['gun'],
                'type' => $row['type'],
                'rarity' => $row['rarity'],
                'acquired' => date('Y-m-d', strtotime($row['acquired_at'])),
            ];
        }
        $arsenalStmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsenal</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="member-body">
    <?php include "../includes/header.php"; ?>

    <main class="member-shell">
        <section class="member-page-intro">
            <h1>Your Arsenal</h1>
            <p class="member-lead">Manage the weapons you own and track your strongest gear.</p>
        </section>

        <section class="arsenal-grid">
            <?php if (count($arsenalItems) === 0): ?>
                <div class="empty-state">
                    <p>Your arsenal is empty. Buy weapons in the marketplace to add them here.</p>
                </div>
            <?php else: ?>
                <?php foreach ($arsenalItems as $item): ?>
                    <article class="weapon-card">
                        <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                        <p>Type: <?php echo htmlspecialchars($item['type']); ?></p>
                        <p>Rarity: <?php echo htmlspecialchars($item['rarity']); ?></p>
                        <p>Acquired: <?php echo htmlspecialchars($item['acquired']); ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <?php include "../includes/footer.php"; ?>
</body>

</html>