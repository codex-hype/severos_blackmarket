<?php
include "../service/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["is_login"]) || $_SESSION["is_login"] !== true || $_SESSION["role"] !== 'guest') {
    header("Location: ../login.php");
    exit();
}

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="member-body">
    <?php include "../includes/header.php"; ?>

    <main class="member-shell">
        <section class="market-hero">
            <div class="market-hero-copy">
                <span class="member-badge">Marketplace</span>
                <h1>Find your next weapon</h1>
                <p class="member-lead">Filter by type, rarity, and price to discover the best gear for your loadout.</p>
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
</body>

</html>