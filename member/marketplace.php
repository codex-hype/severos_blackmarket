<?php include "../service/database.php";
session_start();
if (isset($_POST["logout"])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}
$rarityColors = ["Common" => "#b0b0b0", "Rare" => "#4a90d9", "Epic" => "#9b59b6", "Legendary" => "#f0a500",];

$array = [["gun" => "Desert Eagle 'Saint Edge'", "rarity" => "Epic", "type" => "Handgun", "price" => 6500,], ["gun" => "White Fang-465 'Artic Howl'", "rarity" => "Epic", "type" => "Assault Rifle", "price" => 142000,], ["gun" => "AR-73/223 'Urban Spectre'", "rarity" => "Rare", "type" => "Assault Rifle", "price" => 7900,], ["gun" => "L85A 'Divine Spectre'", "rarity" => "Epic", "type" => "Handgun", "price" => 6500,], ["gun" => "G11 'Caseless Edge'", "rarity" => "Epic", "type" => "Handgun", "price" => 6500,], ["gun" => "AK-15 'Guardian'", "rarity" => "Common", "type" => "Assault Rifle", "price" => 100000,], ["gun" => "MG42 'Destroyer Mark II'", "rarity" => "Legendary", "type" => "Machine Gun", "price" => 1168000,], ["gun" => "VX-Raptor 'Sky Hunter'", "rarity" => "Common", "type" => "Assault Rifle", "price" => 4500,],];


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        #lol {
            border: none;
            outline: none;
            cursor: pointer;
        }
    </style>
</head>

<body> <?php include "../includes/header.php"; ?>
    <section id="backgrounds">
        <div id="wrapper">
            <div id="header-content-container">
                <h1 id="header-h1">Marketplace</h1>
                <p class="caption">Only the strongest survive. Choose your weapons enforce your dominance</p>
            </div>
            <div id="search-container"> <input type="search" name="search" id="search" placeholder="Search weapons..">
                <div class="divider divider-horizontal"></div>
            </div>
        </div>
        <form action="marketplace.php" method="post" id="purchase-form"> <input type="hidden" name="selected_gun"
                id="selected-gun-input"> <input type="hidden" name="confirm_purchase" value="1">
            <div class="containercard">
                <?php foreach ($array as $item):
                    $color = $rarityColors[$item['rarity']] ?? '#fff'; ?>
                    <div class="carditem" data-gun="<?= htmlspecialchars($item['gun']) ?>"
                        data-rarity="<?= htmlspecialchars($item['rarity']) ?>"
                        data-type="<?= htmlspecialchars($item['type']) ?>" data-price="<?= $item['price'] ?>"
                        data-color="<?= $color ?>">
                        <div> <input id="lol" style="background:none; border:none; color: <?= $color ?>" name="gun_name"
                                value="<?= htmlspecialchars($item['gun']) ?> " readonly> <input id="lol"
                                style="background:none; border:none; color: <?= $color ?>" name="type"
                                value="<?= htmlspecialchars($item['type']) ?>" readonly> <input id="lol"
                                style="background:none; border:none; color: <?= $color ?>" name="rarity"
                                value="<?= htmlspecialchars($item['rarity']) ?>" readonly> <input id="lol"
                                style="color:#9a9a9a; background:none; border:none;" name="price"
                                value="Type: <?= htmlspecialchars($item['type']) ?>" readonly> <input id="lol"
                                style="color:#9a9a9a; background:none; border:none;" name="color"
                                value="Price: $<?= number_format($item['price']) ?>" readonly> </div>
                    </div> <?php endforeach; ?>
            </div>
        </form>
        <div class="filtercontainer">
            <h1>Filters</h1> <span id="important-msg">Price Range</span> <input type="range" name="range" id="range">
            <p class="caption">Weapon Type</p> <?php foreach ($array as $item): ?>
                <div> <input type="checkbox" class="checkbox-filter"> <?= htmlspecialchars($item['gun']) ?> </div>
            <?php endforeach; ?>
        </div>
    </section> <?php include "../includes/footer.php"; ?>
    <script src="../assets/script.js"></script>
</body>

</html> <?php