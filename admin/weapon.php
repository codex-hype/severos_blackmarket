<?php
include "../service/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["is_login"]) || $_SESSION["is_login"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$messageType = 'success';
$errors = [];

$weaponTypes = [];
$typeResult = $db->query("SELECT id, type_name FROM weapon_types ORDER BY type_name ASC");
if ($typeResult) {
    while ($row = $typeResult->fetch_assoc()) {
        $weaponTypes[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_weapon' || $action === 'update_weapon') {
        $gun = trim($_POST['gun'] ?? '');
        $rarity = trim($_POST['rarity'] ?? 'Common');
        $type = trim($_POST['type'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $damage = trim($_POST['damage'] ?? '');
        $accuracy = trim($_POST['accuracy'] ?? '');
        $fire_rate = trim($_POST['fire_rate'] ?? '');

        if ($gun === '' || $type === '' || $price === '' || $description === '' || $damage === '' || $accuracy === '' || $fire_rate === '') {
            $errors[] = 'All weapon fields are required.';
        }

        if (!in_array($rarity, ['Common', 'Rare', 'Epic', 'Legendary'], true)) {
            $errors[] = 'Please select a valid rarity.';
        }

        if ($price !== '' && !preg_match('/^\d+$/', $price)) {
            $errors[] = 'Price must be a valid whole number.';
        }

        if (empty($errors)) {
            $typeStmt = $db->prepare("SELECT id FROM weapon_types WHERE type_name = ?");
            if ($typeStmt) {
                $typeStmt->bind_param('s', $type);
                $typeStmt->execute();
                $typeStmt->store_result();
                if ($typeStmt->num_rows === 0) {
                    $errors[] = 'Selected weapon type does not exist.';
                }
                $typeStmt->close();
            } else {
                $errors[] = 'Unable to validate weapon type.';
            }
        }

        if (empty($errors)) {
            if ($action === 'update_weapon') {
                $weaponId = intval($_POST['weapon_id'] ?? 0);
                if ($weaponId <= 0) {
                    $errors[] = 'Invalid weapon selected for update.';
                } else {
                    $stmt = $db->prepare("UPDATE weapons SET gun = ?, rarity = ?, type = ?, price = ?, description = ?, damage = ?, accuracy = ?, fire_rate = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param('sssissssi', $gun, $rarity, $type, $price, $description, $damage, $accuracy, $fire_rate, $weaponId);
                        if ($stmt->execute()) {
                            $message = 'Weapon details updated successfully.';
                        } else {
                            $errors[] = 'Unable to update weapon. Please try again.';
                        }
                        $stmt->close();
                    } else {
                        $errors[] = 'Unable to update weapon. Please try again.';
                    }
                }
            } else {
                $stmt = $db->prepare("INSERT INTO weapons (gun, rarity, type, price, description, damage, accuracy, fire_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param('sssisiss', $gun, $rarity, $type, $price, $description, $damage, $accuracy, $fire_rate);
                    if ($stmt->execute()) {
                        $message = 'Weapon added to inventory successfully.';
                    } else {
                        $errors[] = 'Unable to add weapon. Please try again.';
                    }
                    $stmt->close();
                } else {
                    $errors[] = 'Unable to add weapon. Please try again.';
                }
            }
        }
    } elseif ($action === 'delete_weapon') {
        $weaponId = intval($_POST['weapon_id'] ?? 0);
        if ($weaponId <= 0) {
            $errors[] = 'Invalid weapon selection for deletion.';
        } else {
            $stmt = $db->prepare("DELETE FROM weapons WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $weaponId);
                if ($stmt->execute()) {
                    $message = 'Weapon removed from inventory.';
                } else {
                    $errors[] = 'Unable to delete weapon. Please try again.';
                }
                $stmt->close();
            } else {
                $errors[] = 'Unable to delete weapon. Please try again.';
            }
        }
    }
}

$editId = intval($_GET['edit_id'] ?? 0);
$editWeapon = null;
if ($editId > 0) {
    $stmt = $db->prepare("SELECT * FROM weapons WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $result = $stmt->get_result();
        $editWeapon = $result ? $result->fetch_assoc() : null;
        $stmt->close();
    }
    if (!$editWeapon) {
        $errors[] = 'Selected weapon could not be loaded for editing.';
    }
}

$weapons = [];
$result = $db->query("SELECT * FROM weapons ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $weapons[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Weapons</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="admin-body">
    <?php include "../includes/header.php"; ?>

    <main class="admin-dashboard">
        <section class="admin-hero">
            <div class="admin-hero-copy">
                <p class="admin-badge">Administrator Panel</p>
                <h1>Weapons</h1>
                <p class="admin-lead">Add, update, or remove weapons from the inventory with a single administrator
                    interface.</p>
            </div>
            <div class="admin-hero-actions">
                <a href="dashboard.php" class="btn-secondary">Dashboard</a>
                <a href="weapon.php" class="btn-admin">Refresh</a>
            </div>
        </section>

        <?php
        $adminNavItems = [
            'dashboard.php' => 'Dashboard',
            'payment.php' => 'Payments',
            'weapon.php' => 'Weapons',
            'weapontype.php' => 'Weapon Types',
        ];
        $currentPage = basename($_SERVER['PHP_SELF']);
        ?>

        <nav class="admin-nav" aria-label="Admin navigation">
            <ul>
                <?php foreach ($adminNavItems as $file => $label): ?>
                <li>
                    <a href="<?php echo htmlspecialchars($file); ?>"
                        class="admin-nav-link <?php echo $currentPage === $file ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($label); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <?php if (!empty($errors)): ?>
        <div class="error-banner"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
        <?php elseif ($message): ?>
        <div class="success-banner"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="weapon-management-grid">
            <section class="weapon-form-card">
                <h2><?php echo $editWeapon ? 'Edit Weapon' : 'Add New Weapon'; ?></h2>
                <form method="post">
                    <input type="hidden" name="action"
                        value="<?php echo $editWeapon ? 'update_weapon' : 'add_weapon'; ?>">
                    <?php if ($editWeapon): ?>
                    <input type="hidden" name="weapon_id" value="<?php echo htmlspecialchars($editWeapon['id']); ?>">
                    <?php endif; ?>

                    <div class="field-row">
                        <label for="gun">Weapon Name</label>
                        <input id="gun" name="gun" type="text"
                            value="<?php echo htmlspecialchars($_POST['gun'] ?? ($editWeapon['gun'] ?? '')); ?>"
                            required>
                    </div>

                    <div class="field-row">
                        <label for="rarity">Rarity</label>
                        <select id="rarity" name="rarity" required>
                            <?php foreach (['Common','Rare','Epic','Legendary'] as $rarityOption): ?>
                            <option value="<?php echo $rarityOption; ?>"
                                <?php echo ((isset($_POST['rarity']) && $_POST['rarity'] === $rarityOption) || (!isset($_POST['rarity']) && isset($editWeapon['rarity']) && $editWeapon['rarity'] === $rarityOption)) ? 'selected' : ''; ?>>
                                <?php echo $rarityOption; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field-row">
                        <label for="type">Type</label>
                        <select id="type" name="type" required>
                            <option value="">Select a weapon type</option>
                            <?php foreach ($weaponTypes as $typeOption): ?>
                            <option value="<?php echo htmlspecialchars($typeOption['type_name']); ?>"
                                <?php echo ((isset($_POST['type']) && $_POST['type'] === $typeOption['type_name']) || (!isset($_POST['type']) && isset($editWeapon['type']) && $editWeapon['type'] === $typeOption['type_name'])) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($typeOption['type_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field-row">
                        <label for="price">Price</label>
                        <input id="price" name="price" type="number" min="0"
                            value="<?php echo htmlspecialchars($_POST['price'] ?? ($editWeapon['price'] ?? '')); ?>"
                            required>
                    </div>

                    <div class="field-row">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"
                            required><?php echo htmlspecialchars($_POST['description'] ?? ($editWeapon['description'] ?? '')); ?></textarea>
                    </div>

                    <div class="field-row">
                        <label for="damage">Damage</label>
                        <input id="damage" name="damage" type="text"
                            value="<?php echo htmlspecialchars($_POST['damage'] ?? ($editWeapon['damage'] ?? '')); ?>"
                            required>
                    </div>

                    <div class="field-row">
                        <label for="accuracy">Accuracy</label>
                        <input id="accuracy" name="accuracy" type="text"
                            value="<?php echo htmlspecialchars($_POST['accuracy'] ?? ($editWeapon['accuracy'] ?? '')); ?>"
                            required>
                    </div>

                    <div class="field-row">
                        <label for="fire_rate">Fire Rate</label>
                        <input id="fire_rate" name="fire_rate" type="text"
                            value="<?php echo htmlspecialchars($_POST['fire_rate'] ?? ($editWeapon['fire_rate'] ?? '')); ?>"
                            required>
                    </div>

                    <div class="weapon-actions">
                        <button type="submit"
                            class="btn-admin"><?php echo $editWeapon ? 'Update Weapon' : 'Add Weapon'; ?></button>
                        <?php if ($editWeapon): ?>
                        <a href="weapon.php" class="btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <section class="weapon-table-card">
                <h2>Weapon Inventory</h2>
                <?php if (count($weapons) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Rarity</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Damage</th>
                            <th>Accuracy</th>
                            <th>Fire Rate</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($weapons as $weapon): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($weapon['gun']); ?></td>
                            <td><?php echo htmlspecialchars($weapon['rarity']); ?></td>
                            <td><?php echo htmlspecialchars($weapon['type']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($weapon['price'])); ?></td>
                            <td><?php echo htmlspecialchars($weapon['damage']); ?></td>
                            <td><?php echo htmlspecialchars($weapon['accuracy']); ?></td>
                            <td><?php echo htmlspecialchars($weapon['fire_rate']); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn-secondary"
                                        href="?edit_id=<?php echo htmlspecialchars($weapon['id']); ?>">Edit</a>
                                    <form method="post" class="inline-form"
                                        onsubmit="return confirm('Delete this weapon?');">
                                        <input type="hidden" name="action" value="delete_weapon">
                                        <input type="hidden" name="weapon_id"
                                            value="<?php echo htmlspecialchars($weapon['id']); ?>">
                                        <button type="submit" class="btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">No weapon inventory is available yet.</div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>

</html>