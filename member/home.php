<?php
include "../service/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["is_login"]) || $_SESSION["is_login"] !== true) {
    header("Location: ../login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username'] ?? 'Member');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Home</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="member-body">
    <?php include "../includes/header.php"; ?>

    <main class="member-shell">
        <section class="member-hero">
            <div class="member-hero-copy">
                <p class="member-badge">Member Section</p>
                <h1>Welcome back, <?php echo $username; ?></h1>
                <p class="member-lead">Explore the marketplace, manage your arsenal, and track your latest transactions.</p>
            </div>
            <div class="member-actions">
                <a href="marketplace.php" class="btn-primary">Go to Marketplace</a>
                <a href="arsenal.php" class="btn-secondary">View Arsenal</a>
            </div>
        </section>

        <section class="member-highlights">
            <article class="member-card">
                <h2>Discover weapons</h2>
                <p>Browse rare weapons and the best deals available in the market.</p>
            </article>
            <article class="member-card">
                <h2>Secure your loadout</h2>
                <p>Keep your arsenal updated and ready to dominate any mission.</p>
            </article>
            <article class="member-card">
                <h2>Track transactions</h2>
                <p>Review your buy history and monitor credits with ease.</p>
            </article>
        </section>
    </main>
</body>

</html>