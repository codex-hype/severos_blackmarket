<?php
include "../service/database.php";
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["gun_name"]) && isset($_POST["gun_price"])) {

        $weaponName = $_POST["gun_name"];
        $price = (float) $_POST["gun_price"];

        $quantity = 1;
        $subtotal = $price * $quantity;



        $query =
            "SELECT transactionID
             FROM transactiondetail
             ORDER BY transactionID DESC
             LIMIT 1";

        $result = mysqli_query($db, $query);

        if ($result && mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_assoc($result);

            $lastID = $row["transactionID"];

            $number = intval(substr($lastID, 2));

            $newNumber = $number + 1;

            $transactionID =
                "TD" .
                str_pad($newNumber, 3, "0", STR_PAD_LEFT);

        } else {

            $transactionID = "TD001";
        }


        $stmt = mysqli_prepare(
            $db,
            "INSERT INTO transactiondetail
            (transactionID, quantity, subtotal)
            VALUES (?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sii",
            $transactionID,
            $quantity,
            $subtotal
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        header("Location: transaction.php");
        exit();
    }
}


if (!isset($_SESSION["credit"])) {
    $_SESSION["credit"] = 3096540;
}

if (isset($_POST["topup"])) {

    $amount = (int) $_POST["amount"];

    if ($amount > 0) {
        $_SESSION["credit"] += $amount;
    }

    header("Location: transaction.php");
    exit();
}


$monthSpend = 0;

$monthQuery =
    "SELECT SUM(subtotal) AS total
     FROM transactiondetail
     WHERE MONTH(createdAt)=MONTH(NOW())
     AND YEAR(createdAt)=YEAR(NOW())";

$monthResult = mysqli_query($db, $monthQuery);

if ($monthResult) {

    $row = mysqli_fetch_assoc($monthResult);

    $monthSpend = $row["total"] ?? 0;
}


$transactions = mysqli_query(
    $db,
    "SELECT *
     FROM transactiondetail
     ORDER BY createdAt DESC"
);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Transactions</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        body {
            background: #000;
            color: white;
        }

        .transaction-wrapper {
            width: 90%;
            margin: auto;
            padding-top: 40px;
        }

        .transaction-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .transaction-title h1 {
            font-size: 40px;
        }

        .transaction-title p {
            color: #ccc;
        }

        .transaction-grid {
            display: flex;
            gap: 20px;
        }

        .left-panel {
            flex: 2;
        }

        .right-panel {
            flex: 1;
        }

        .header-row {
            display: grid;
            grid-template-columns:
                1fr 1fr 1fr;

            background: white;
            color: black;

            padding: 15px;
            font-weight: bold;
            border-radius: 5px;
        }

        .transaction-row {

            display: grid;

            grid-template-columns:
                1fr 1fr 1fr;

            border: 1px solid white;

            margin-top: 10px;

            padding: 15px;

            border-radius: 5px;
        }

        .summary-box {

            border: 1px solid white;

            padding: 20px;

            border-radius: 5px;
        }

        .summary-box h3 {
            margin-bottom: 20px;
        }

        .summary-box p {
            margin-bottom: 20px;
        }

        .topup-input {

            width: 100%;

            padding: 10px;

            margin-bottom: 10px;
        }

        .topup-btn {

            width: 100%;

            border: none;

            background: #007bff;

            color: white;

            padding: 12px;

            cursor: pointer;
        }

        .topup-btn:hover {
            background: #0056b3;
        }
    </style>

</head>

<body>

    <?php include "../includes/header.php"; ?>

    <section id="backgrounds">

        <div id="wrapper">
            <div id="header-content-container">
                <h1 id="header-h1">Transactions</h1>
                <p class="caption">Every purchase seals your fate. Power is bought not given.</p>
            </div>
        </div>

        <div class="transaction-grid">

            <div class="left-panel">

                <div class="header-row">

                    <div>Transaction ID</div>

                    <div>Date</div>

                    <div>Total Amount</div>

                </div>

                <?php while ($row = mysqli_fetch_assoc($transactions)): ?>

                    <div class="transaction-row">

                        <div>
                            <?= htmlspecialchars(
                                $row["transactionID"]
                            ) ?>
                        </div>

                        <div>
                            <?= $row["createdAt"] ?>
                        </div>
                        <div>
                            $<?= number_format(
                                $row["subtotal"],
                                2
                            ) ?>
                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

            <div class="right-panel">

                <div class="summary-box">

                    <h3>
                        This Month's Spending
                    </h3>

                    <p>
                        <strong>
                            $<?= number_format(
                                $monthSpend,
                                2
                            ) ?>
                        </strong>
                    </p>

                    <hr>

                    <br>

                    <h3>
                        Your Credit
                    </h3>

                    <p>

                        <strong>

                            $<?= number_format(
                                $_SESSION["credit"],
                                2
                            ) ?>

                        </strong>

                    </p>

                    <hr>

                    <br>

                    <h3>
                        Top Up Credit
                    </h3>

                    <form method="POST">

                        <input type="number" name="amount" placeholder="Amount" class="topup-input" required>

                        <button type="submit" name="topup" class="topup-btn">

                            Top Up

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </section>

    <?php include "../includes/footer.php"; ?>

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