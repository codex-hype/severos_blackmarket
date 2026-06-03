<?php

$hostname = "localhost";
$username = "root";
$password = "";
$database = "severos_blackmarket";

mysqli_report(MYSQLI_REPORT_OFF);
$db = new mysqli($hostname, $username, $password, $database);

if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}

$db->set_charset('utf8mb4');

$status = $db->query("SHOW TABLE STATUS LIKE 'msuser'");
if ($status) {
    $row = $status->fetch_assoc();
    if ($row && (is_null($row['Engine']) || stripos($row['Comment'], "doesn't exist in engine") !== false)) {
        $db->query("DROP TABLE IF EXISTS msuser");
    }
}

$createTable = "CREATE TABLE IF NOT EXISTS msuser (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    credit INT NOT NULL DEFAULT 200,
    role ENUM('admin','guest') NOT NULL DEFAULT 'guest',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$db->query($createTable)) {
    die("Database setup failed: " . $db->error);
}

$adminCheck = $db->query("SELECT COUNT(*) AS cnt FROM msuser");
if ($adminCheck) {
    $row = $adminCheck->fetch_assoc();
    if (isset($row['cnt']) && (int)$row['cnt'] === 0) {
        $adminPassword = password_hash('admin12345', PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO msuser (username, password, email, credit, role) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $username = 'Administrator';
            $email = 'admin@gmail.com';
            $credit = 1000;
            $role = 'admin';
            $stmt->bind_param('sssds', $username, $adminPassword, $email, $credit, $role);
            $stmt->execute();
            $stmt->close();
        }
    }
}

$createWeaponsTable = "CREATE TABLE IF NOT EXISTS weapons (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    gun VARCHAR(255) NOT NULL,
    rarity ENUM('Common','Rare','Epic','Legendary') NOT NULL DEFAULT 'Common',
    type VARCHAR(100) NOT NULL,
    price INT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    damage VARCHAR(50) NOT NULL,
    accuracy VARCHAR(50) NOT NULL,
    fire_rate VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$db->query($createWeaponsTable)) {
    die("Database setup failed: " . $db->error);
}

$weaponCount = $db->query("SELECT COUNT(*) AS cnt FROM weapons");
if ($weaponCount) {
    $weaponRow = $weaponCount->fetch_assoc();
    if (isset($weaponRow['cnt']) && (int)$weaponRow['cnt'] === 0) {
        $insertWeapon = $db->prepare("INSERT INTO weapons (gun, rarity, type, price, description, damage, accuracy, fire_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($insertWeapon) {
            $weapons = [
                ["Desert Eagle 'Saint Edge'", 'Epic', 'Handgun', 6500, 'High precision sidearm with elegant gold accents.', '85', '78', '42'],
                ["White Fang-465 'Arctic Howl'", 'Epic', 'Assault Rifle', 142000, 'Cold-steel rifle engineered for long-range dominance.', '92', '81', '68'],
                ["AR-73/223 'Urban Spectre'", 'Rare', 'Assault Rifle', 7900, 'Compact AR built for agility in urban combat.', '62', '73', '75'],
                ["L85A 'Divine Spectre'", 'Epic', 'Handgun', 6500, 'Reliable sidearm with a ghostly finish.', '84', '77', '45'],
                ["G11 'Caseless Edge'", 'Epic', 'Handgun', 6500, 'Futuristic pistol with a smooth, caseless design.', '80', '76', '52'],
                ["AK-15 'Guardian'", 'Epic', 'Assault Rifle', 100000, 'Heavy-hitting rifle built for battlefield supremacy.', '95', '70', '66'],
                ["MG42 'Destroyer Mark II'", 'Legendary', 'Machine Gun', 1168000, 'Legendary heavy machine gun with unstoppable firepower.', '110', '64', '88'],
                ["VX-Raptor 'Sky Hunter'", 'Epic', 'Assault Rifle', 4500, 'Fast-firing rifle designed for hit-and-run tactics.', '70', '74', '82'],
            ];
            foreach ($weapons as $weapon) {
                $insertWeapon->bind_param('sssisiss', $weapon[0], $weapon[1], $weapon[2], $weapon[3], $weapon[4], $weapon[5], $weapon[6], $weapon[7]);
                $insertWeapon->execute();
            }
            $insertWeapon->close();
        }
    }
}

$createWeaponTypesTable = "CREATE TABLE IF NOT EXISTS weapon_types (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    type_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$db->query($createWeaponTypesTable)) {
    die("Database setup failed: " . $db->error);
}

$existingTypesResult = $db->query("SELECT DISTINCT type FROM weapons");
if ($existingTypesResult) {
    $insertType = $db->prepare("INSERT IGNORE INTO weapon_types (type_name) VALUES (?)");
    if ($insertType) {
        while ($row = $existingTypesResult->fetch_assoc()) {
            if (!empty($row['type'])) {
                $insertType->bind_param('s', $row['type']);
                $insertType->execute();
            }
        }
        $insertType->close();
    }
}

$weaponTypeCount = $db->query("SELECT COUNT(*) AS cnt FROM weapon_types");
if ($weaponTypeCount) {
    $typeRow = $weaponTypeCount->fetch_assoc();
    if (isset($typeRow['cnt']) && (int)$typeRow['cnt'] === 0) {
        $defaultTypes = ['Handgun', 'Assault Rifle', 'Machine Gun', 'Sniper Rifle', 'Shotgun', 'SMG'];
        $insertType = $db->prepare("INSERT IGNORE INTO weapon_types (type_name) VALUES (?)");
        if ($insertType) {
            foreach ($defaultTypes as $type) {
                $insertType->bind_param('s', $type);
                $insertType->execute();
            }
            $insertType->close();
        }
    }
}

$createTransactionsTable = "CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    weapon_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    price INT UNSIGNED NOT NULL,
    total_amount INT UNSIGNED NOT NULL,
    shipping_address TEXT NOT NULL,
    status ENUM('Pending','Completed','Cancelled') NOT NULL DEFAULT 'Completed',
    payment_method ENUM('Debit Card','Credit Card','PayPal','Bank Transfer','Crypto') NOT NULL DEFAULT 'Debit Card',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$db->query($createTransactionsTable)) {
    die("Database setup failed: " . $db->error);
}

// Ensure payment_method column exists for older schemas
$colCheck = $db->query("SHOW COLUMNS FROM transactions LIKE 'payment_method'");
if ($colCheck && $colCheck->num_rows === 0) {
    $db->query("ALTER TABLE transactions ADD COLUMN payment_method ENUM('Debit Card','Credit Card','PayPal','Bank Transfer','Crypto') NOT NULL DEFAULT 'Debit Card'");
}

$createArsenalTable = "CREATE TABLE IF NOT EXISTS arsenal (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    weapon_id INT UNSIGNED NOT NULL,
    acquired_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_weapon_unique (user_id, weapon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (!$db->query($createArsenalTable)) {
    die("Database setup failed: " . $db->error);
}

?>