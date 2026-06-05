<?php
include "../service/database.php";
<<<<<<< HEAD
session_start();
=======

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
>>>>>>> 072699691270c9097b301ca0f7a4e3d7385c81a0

if (!isset($_SESSION["is_login"]) || $_SESSION["is_login"] !== true || $_SESSION["role"] !== 'guest') {
    header("Location: ../login.php");
    exit();
}

<<<<<<< HEAD
// Master data dibuat lengkap agar data stats & trait tersentralisasi di sini
$array = [
    [
        "gun" => "Desert Eagle 'Saint Edge'",
        "rarity" => "Epic",
        "type" => "Handgun",
        "price" => 6500,
    ],
    [
        "gun" => "White Fang-465 'Artic Howl'",
        "rarity" => "Epic",
        "type" => "Assault Rifle",
        "price" => 142000,
    ],
    [
        "gun" => "AR-73/223 'Urban Spectre'",
        "rarity" => "Rare",
        "type" => "Assault Rifle",
        "price" => 7900,
    ],
    [
        "gun" => "L85A 'Divine Spectre'",
        "rarity" => "Epic",
        "type" => "Handgun",
        "price" => 6500,
    ],
    [
        "gun" => "G11 'Caseless Edge'",
        "rarity" => "Epic",
        "type" => "Handgun",
        "price" => 6500,
    ],
    [
        "gun" => "AK-15 'Guardian'",
        "rarity" => "Common",
        "type" => "Assault Rifle",
        "price" => 100000,
    ],
    [
        "gun" => "MG42 'Destroyer Mark II'",
        "rarity" => "Legendary",
        "type" => "Machine Gun",
        "price" => 1168000,
    ],
    [
        "gun" => "VX-Raptor 'Sky Hunter'",
        "rarity" => "Common",
        "type" => "Assault Rifle",
        "price" => 4500,
    ],
];

// LOGIK BARU: Menangkap trigger dari confirm-btn untuk disimpan ke Session
if (isset($_POST["confirm_purchase"])) {
    $selected_gun_name = $_POST["selected_gun"] ?? '';
    foreach ($array as $item) {
        if ($item["gun"] === $selected_gun_name) {
            $_SESSION["selected_weapon"] = $item; // Menyimpan full data objek ke session
            header("Location: transaction.php");
            exit();
        }
    }
}

$rarityColors = [
    "Common" => "#b0b0b0",
    "Rare" => "#4a90d9",
    "Epic" => "#9b59b6",
    "Legendary" => "#f0a500",
];
=======
$search = trim($_GET['search'] ?? '');
$selectedType = $_GET['type'] ?? '';
$selectedRarity = $_GET['rarity'] ?? '';
$selectedMaxPrice = isset($_GET['max_price']) ? (int) $_GET['max_price'] : 0;

$items = [];
$result = $db->query("SELECT id, gun, rarity, type, price, description, damage, accuracy, fire_rate FROM weapons ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $result->free();
}

$types = array_values(array_unique(array_column($items, 'type')));
$rarities = array_values(array_unique(array_column($items, 'rarity')));

$prices = array_column($items, 'price');
sort($prices);
$minPrice = $prices ? min($prices) : 0;
$maxPrice = $prices ? max($prices) : 0;
if ($selectedMaxPrice === 0) {
    $selectedMaxPrice = $maxPrice;
}

$filteredItems = array_filter($items, function ($item) use ($search, $selectedType, $selectedRarity, $selectedMaxPrice) {
    $matchesSearch = $search === '' || stripos($item['gun'], $search) !== false || stripos($item['type'], $search) !== false || stripos($item['rarity'], $search) !== false;
    $matchesType = $selectedType === '' || $item['type'] === $selectedType;
    $matchesRarity = $selectedRarity === '' || $item['rarity'] === $selectedRarity;
    $matchesPrice = $item['price'] <= $selectedMaxPrice;
    return $matchesSearch && $matchesType && $matchesRarity && $matchesPrice;
});

function rarityColor($rarity)
{
    return ['Common' => '#b0b0b0', 'Rare' => '#4a90d9', 'Epic' => '#9b59b6', 'Legendary' => '#f0a500'][$rarity] ?? '#ffffff';
}
>>>>>>> 072699691270c9097b301ca0f7a4e3d7385c81a0
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

<body class="member-body">
    <?php include "../includes/header.php"; ?>

<<<<<<< HEAD
    <section id="backgrounds">
        <div id="wrapper">
            <div id="header-content-container">
                <h1 id="header-h1">Marketplace</h1>
                <p class="caption">Only the strongest survive. Choose your weapons enforce your dominance</p>
=======
    <main class="member-shell">
        <section class="market-hero">
            <div class="market-hero-copy">
                <span class="member-badge">Marketplace</span>
                <h1>Find your next weapon</h1>
                <p class="member-lead">Filter by type, rarity, and price to discover the best gear for your loadout.</p>
>>>>>>> 072699691270c9097b301ca0f7a4e3d7385c81a0
            </div>
            <form action="marketplace.php" method="GET" class="market-search-form">
                <input type="search" name="search" placeholder="Search weapons, type or rarity" value="<?= htmlspecialchars($search); ?>">
                <select name="type">
                    <option value="">All Weapon Types</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= htmlspecialchars($type); ?>" <?= $selectedType === $type ? 'selected' : ''; ?>><?= htmlspecialchars($type); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="rarity">
                    <option value="">All Rarities</option>
                    <?php foreach ($rarities as $rarity): ?>
                        <option value="<?= htmlspecialchars($rarity); ?>" <?= $selectedRarity === $rarity ? 'selected' : ''; ?>><?= htmlspecialchars($rarity); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="max_price" class="range-label">Max price: $<span id="price-value"><?= number_format($selectedMaxPrice); ?></span></label>
                <input type="range" name="max_price" id="max_price" min="<?= $minPrice; ?>" max="<?= $maxPrice; ?>" value="<?= $selectedMaxPrice; ?>">
                <button type="submit" class="btn-primary">Apply filters</button>
            </form>
        </section>

<<<<<<< HEAD
        <form action="marketplace.php" method="post" id="purchase-form">
            <input type="hidden" name="selected_gun" id="selected-gun-input">
            <input type="hidden" name="confirm_purchase" value="1">

            <div class="containercard">
                <?php foreach ($array as $item):
                    $color = $rarityColors[$item['rarity']] ?? '#fff';
                    ?>
                    <div class="carditem" data-source="marketplace" data-gun="<?= htmlspecialchars($item['gun']) ?>"
                        data-rarity="<?= htmlspecialchars($item['rarity']) ?>"
                        data-type="<?= htmlspecialchars($item['type']) ?>" data-price="<?= $item['price'] ?>"
                        data-color="<?= $color ?>">
                        <div>
                            <input id="lol" style="background:none; border:none;  color: <?= $color ?>" name="gun_name"
                                value="<?= htmlspecialchars($item['gun']) ?> " readonly>
                            <input id="lol" style="background:none; border:none;  color: <?= $color ?>" name="type"
                                value="<?= htmlspecialchars($item['type']) ?>" readonly>
                            <input id="lol" style="background:none; border:none;  color: <?= $color ?>" name="rarity"
                                value="<?= htmlspecialchars($item['rarity']) ?>" readonly>
                            <input id="lol" style="color:#9a9a9a; background:none; border:none;" name="price"
                                value="Type: <?= htmlspecialchars($item['type']) ?>" readonly>
                            <input id="lol" style="color:#9a9a9a; background:none; border:none;" name="color"
                                value="Price: $<?= number_format($item['price']) ?>" readonly>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>

        <div class="filtercontainer">
            <h1>Filters</h1>
            <span id="important-msg">Price Range</span>
            <input type="range" name="range" id="range">
            <p class="caption">Weapon Type</p>
            <?php foreach ($array as $item): ?>
                <div>
                    <input type="checkbox" class="checkbox-filter"> <?= htmlspecialchars($item['gun']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php include "../includes/footer.php"; ?>
    <script>
        const pageSource = "marketplace";
    </script>
    <script src="../assets/script.js"></script>
=======
        <section class="market-grid">
            <?php if (empty($filteredItems)): ?>
                <div class="empty-state">
                    <p>No weapons match your search criteria. Try changing the filters.</p>
                </div>
            <?php else: ?>
                <?php foreach ($filteredItems as $item): ?>
                    <article class="market-card">
                        <div class="market-card-header">
                            <span class="rarity-pill" style="background: <?= rarityColor($item['rarity']); ?>;"><?= htmlspecialchars($item['rarity']); ?></span>
                            <span class="price-pill">$<?= number_format($item['price']); ?></span>
                        </div>
                        <h2><?= htmlspecialchars($item['gun']); ?></h2>
                        <p class="market-type"><?= htmlspecialchars($item['type']); ?></p>
                        <p class="market-description"><?= htmlspecialchars($item['description']); ?></p>
                        <div class="market-card-footer">
                            <a class="btn-secondary" href="weapon-detail.php?id=<?= $item['id']; ?>">View Details</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <?php include "../includes/footer.php"; ?>
    <script>
        const priceRange = document.getElementById('max_price');
        const priceValue = document.getElementById('price-value');
        if (priceRange && priceValue) {
            priceRange.addEventListener('input', function () {
                priceValue.textContent = Number(priceRange.value).toLocaleString();
            });
        }
    </script>
>>>>>>> 072699691270c9097b301ca0f7a4e3d7385c81a0
</body>

</html>