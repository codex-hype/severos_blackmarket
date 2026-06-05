<?php
include "../service/database.php";
session_start();
if (isset($_POST['source'])) {
    $_SESSION['weapon_source'] = $_POST['source'];
}

$source = $_SESSION['weapon_source'] ?? 'marketplace';

if (isset($_POST["back"])) {

    if (($source ?? '') === "arsenal") {
        header("Location: arsenal.php");
    } else {
        header("Location: marketplace.php");
    }

    exit();
}


if (isset($_POST["transaction"])) {


}

if (isset($_POST["logout"])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}


$w_name = $_POST['gun_name'] ?? $_POST['selected_gun'] ?? 'White Fang-465 "Arctic Howl"';
$w_type = $_POST['type'] ?? 'Assault Rifle';
$w_rarity = $_POST['rarity'] ?? 'Epic';
$w_price = $_POST['price'] ?? 142.00;
$color = $_POST['color'] ?? '#9b59b6';

$w_trait = "A precision-built assault rifle designed for harsh operational climates. Its internal stabilizers maintain accuracy even during rapid fire, making it dependable for long missions.";
$w_damage = 210;
$w_fire = 52;
$w_mag = 30;
$w_recoil = 20;
$w_sold = 81;
$w_stock = 1495;

if (isset($db) && $db instanceof mysqli) {
    $stmt = $db->prepare("SELECT * FROM weapon WHERE gun = ? OR name = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $w_name, $w_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $weapon = $result->fetch_assoc();
            $w_trait = $weapon['trait'] ?? $weapon['description'] ?? $w_trait;
            $w_damage = $weapon['damage'] ?? $w_damage;
            $w_fire = $weapon['fire_rate'] ?? $w_fire;
            $w_mag = $weapon['magazine_size'] ?? $w_mag;
            $w_recoil = $weapon['recoil'] ?? $w_recoil;
            $w_sold = $weapon['sold'] ?? $w_sold;
            $w_stock = $weapon['stock'] ?? $w_stock;
        }
        $stmt->close();
    }
}


$w_rarity = strtoupper($w_rarity);
$display_price = number_format((float) $w_price, 2);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weapon Detail - <?= htmlspecialchars($w_name) ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://kit.fontawesome.com/6c54edd315.js" crossorigin="anonymous"></script>
    <style>
        #backgrounds {
            padding: 40px 10%;
        }

        .back-container {
            display: flex;
            justify-content: center;
            margin-bottom: 50px;
        }

        .back-form button {
            background: none;
            border: none;
            color: #ffffff;
            cursor: pointer;
            font-size: 14px;
        }

        .back-form button:hover {
            text-decoration: underline;
        }

        .main-layout {
            display: flex;
            gap: 50px;
            align-items: flex-start;
            justify-content: center;
        }


        .left-side {
            flex: 1;
            max-width: 400px;
        }

        .weapon-name-box {
            border: 1px solid #ffffff;
            padding: 70px 40px;
            text-align: center;
            margin-bottom: 30px;
        }

        .weapon-name-box h1 {
            font-size: 26px;
            margin: 0;
            font-weight: bold;
            line-height: 1.4;
        }

        .meta-info {
            font-size: 15px;
            font-weight: bold;
        }

        .meta-info p {
            margin: 18px 0;
        }

        .meta-info span.normal-weight {
            font-weight: normal;
        }

        .right-side {
            flex: 1.2;
            max-width: 600px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .trait-text {
            color: #cccccc;
            line-height: 1.6;
            font-size: 14px;
            margin-bottom: 40px;
        }

        .stats-grid {
            display: flex;
            gap: 60px;
            margin-bottom: 40px;
        }

        .stats-column {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .stat-row {
            font-size: 14px;
            display: flex;
            gap: 6px;
        }

        .stat-row .label {
            color: #ffffff;
        }

        .stat-row .value {
            font-weight: bold;
            color: #ffffff;
        }

        .btn-purchase {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-purchase:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <?php include "../includes/header.php"; ?>

    <section id="backgrounds">

        <div class="back-container">
            <form action="weapondetail.php" method="POST" class="back-form">
                <button name="back" type="submit">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to
                    <?= $source === 'arsenal' ? 'Arsenal' : 'Marketplace' ?>

                </button>
            </form>
        </div>

        <div class="main-layout">
            <div class="left-side">
                <div class="weapon-name-box">
                    <h1><?= htmlspecialchars($w_name) ?></h1>
                </div>
                <div class="meta-info">
                    <p>Type: <span class="normal-weight"><?= htmlspecialchars($w_type) ?></span></p>
                    <p>Rarity: <span
                            style="color: <?= htmlspecialchars($color) ?>;"><?= htmlspecialchars($w_rarity) ?></span>
                    </p>
                </div>
            </div>

            <div class="right-side">
                <div class="section-title">Weapon Trait</div>
                <p class="trait-text"><?= htmlspecialchars($w_trait) ?></p>

                <div class="stats-grid">
                    <div class="stats-column">
                        <div class="section-title">Weapon Stats</div>
                        <div class="stat-row"><span class="label">Damage:</span> <span
                                class="value"><?= $w_damage ?></span></div>
                        <div class="stat-row"><span class="label">Fire Rate:</span> <span
                                class="value"><?= $w_fire ?></span></div>
                        <div class="stat-row"><span class="label">Magazine Size:</span> <span
                                class="value"><?= $w_mag ?></span></div>
                        <div class="stat-row"><span class="label">Recoil:</span> <span
                                class="value"><?= $w_recoil ?></span></div>
                    </div>

                    <div class="stats-column">
                        <div class="section-title">Weapon Sales</div>
                        <div class="stat-row"><span class="label">Sold:</span> <span class="value"><?= $w_sold ?></span>
                        </div>
                        <div class="stat-row"><span class="label">Stock:</span> <span
                                class="value"><?= $w_stock ?></span></div>
                        <div class="stat-row"><span class="label">Price:</span> <span
                                class="value">$<?= $display_price ?></span></div>
                    </div>
                </div>


                <?php if ($source === 'marketplace'): ?>

                    <form action="transaction.php" method="POST">

                        <input type="hidden" name="transaction_type" value="purchase">

                        <input type="hidden" name="gun_name" value="<?= htmlspecialchars($w_name) ?>">

                        <input type="hidden" name="gun_price" value="<?= $w_price ?>">

                        <button type="submit" class="btn-purchase">
                            Purchase Weapon
                        </button>

                    </form>

                <?php else: ?>

                    <?php
                    $sellPrice = $w_price * 0.5;
                    ?>

                    <div style="margin-top:20px;">

                        <div class="section-title">
                            Sell Weapon
                        </div>

                        <form action="transaction.php" method="POST">

                            <input type="hidden" name="transaction_type" value="sell">

                            <input type="hidden" name="gun_name" value="<?= htmlspecialchars($w_name) ?>">

                            <input type="hidden" name="gun_price" value="<?= $w_price ?>">

                            <button type="submit" name="amount" value="1"
                                style="cursor:pointer;color:#fff;background:#d62929;border:none;padding:10px;margin-right:5px;">
                                Sell 1
                                <br>
                                +$<?= number_format($sellPrice, 2) ?>
                            </button>

                            <button type="submit" name="amount" value="5"
                                style="cursor:pointer;color:#fff;background:#d62929;border:none;padding:10px;margin-right:5px;">
                                Sell 5
                                <br>
                                +$<?= number_format($sellPrice * 5, 2) ?>
                            </button>

                            <button type="submit" name="amount" value="10"
                                style="cursor:pointer;color:#fff;background:#d62929;border:none;padding:10px;margin-right:5px;">
                                Sell 10
                                <br>
                                +$<?= number_format($sellPrice * 10, 2) ?>
                            </button>

                            <button type="submit" name="amount" value="all"
                                style="cursor:pointer;color:#fff;background:#d62929;border:none;padding:10px;">
                                Sell All
                            </button>

                        </form>

                        <p style="font-size:13px;color:#ccc;margin-top:15px;">
                            *You will receive 50% of the weapon's price upon selling.
                        </p>

                    </div>

                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include "../includes/footer.php"; ?>
    <script src="../assets/script.js"></script>
</body>

</html>