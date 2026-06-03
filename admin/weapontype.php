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
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $typeName = trim($_POST['type_name'] ?? '');

    if ($action === 'add_type' || $action === 'update_type') {
        if ($typeName === '') {
            $errors[] = 'Type name cannot be empty.';
        }

        if (empty($errors)) {
            if ($action === 'update_type') {
                $typeId = intval($_POST['type_id'] ?? 0);
                if ($typeId <= 0) {
                    $errors[] = 'Invalid type selection for update.';
                } else {
                    $stmt = $db->prepare("UPDATE weapon_types SET type_name = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param('si', $typeName, $typeId);
                        if ($stmt->execute()) {
                            if ($stmt->affected_rows >= 0) {
                                $message = 'Weapon type updated successfully.';
                            }
                        } else {
                            $errors[] = 'Unable to update weapon type. It may already exist.';
                        }
                        $stmt->close();
                    } else {
                        $errors[] = 'Unable to update weapon type.';
                    }
                }
            } else {
                $stmt = $db->prepare("INSERT INTO weapon_types (type_name) VALUES (?)");
                if ($stmt) {
                    $stmt->bind_param('s', $typeName);
                    if ($stmt->execute()) {
                        $message = 'Weapon type added successfully.';
                    } else {
                        $errors[] = 'Unable to add weapon type. It may already exist.';
                    }
                    $stmt->close();
                } else {
                    $errors[] = 'Unable to add weapon type.';
                }
            }
        }
    } elseif ($action === 'delete_type') {
        $typeId = intval($_POST['type_id'] ?? 0);
        if ($typeId <= 0) {
            $errors[] = 'Invalid type selection for deletion.';
        } else {
            $stmt = $db->prepare("SELECT type_name FROM weapon_types WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $typeId);
                $stmt->execute();
                $result = $stmt->get_result();
                $typeRow = $result ? $result->fetch_assoc() : null;
                $stmt->close();

                if (!$typeRow) {
                    $errors[] = 'Weapon type not found.';
                } else {
                    $checkStmt = $db->prepare("SELECT COUNT(*) AS count FROM weapons WHERE type = ?");
                    if ($checkStmt) {
                        $checkStmt->bind_param('s', $typeRow['type_name']);
                        $checkStmt->execute();
                        $countResult = $checkStmt->get_result();
                        $countRow = $countResult ? $countResult->fetch_assoc() : null;
                        $checkStmt->close();

                        if ($countRow && (int)$countRow['count'] > 0) {
                            $errors[] = 'This weapon type is in use by existing weapons and cannot be deleted.';
                        } else {
                            $deleteStmt = $db->prepare("DELETE FROM weapon_types WHERE id = ?");
                            if ($deleteStmt) {
                                $deleteStmt->bind_param('i', $typeId);
                                if ($deleteStmt->execute()) {
                                    $message = 'Weapon type deleted successfully.';
                                } else {
                                    $errors[] = 'Unable to delete weapon type.';
                                }
                                $deleteStmt->close();
                            } else {
                                $errors[] = 'Unable to delete weapon type.';
                            }
                        }
                    } else {
                        $errors[] = 'Unable to verify weapon type usage.';
                    }
                }
            } else {
                $errors[] = 'Unable to verify weapon type.';
            }
        }
    }
}

$editId = intval($_GET['edit_id'] ?? 0);
$editType = null;
if ($editId > 0) {
    $stmt = $db->prepare("SELECT * FROM weapon_types WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $result = $stmt->get_result();
        $editType = $result ? $result->fetch_assoc() : null;
        $stmt->close();
    }
    if (!$editType) {
        $errors[] = 'Selected weapon type could not be loaded for editing.';
    }
}

$types = [];
$sql = "SELECT wt.id, wt.type_name, COUNT(w.id) AS weapon_count FROM weapon_types wt LEFT JOIN weapons w ON w.type = wt.type_name GROUP BY wt.id ORDER BY wt.type_name ASC";
$result = $db->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $types[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Weapon Types</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="admin-body">
    <?php include "../includes/header.php"; ?>

    <main class="admin-dashboard">
        <section class="admin-hero">
            <div class="admin-hero-copy">
                <p class="admin-badge">Administrator Panel</p>
                <h1>Weapon Types</h1>
                <p class="admin-lead">Create and manage weapon classification labels used across your inventory.</p>
            </div>
            <div class="admin-hero-actions">
                <a href="dashboard.php" class="btn-secondary">Dashboard</a>
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
                        <a href="<?php echo htmlspecialchars($file); ?>" class="admin-nav-link <?php echo $currentPage === $file ? 'active' : ''; ?>">
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

        <section class="weapon-management-grid">
            <div class="weapon-form-card">
                <h2><?php echo $editType ? 'Edit Weapon Type' : 'Add New Weapon Type'; ?></h2>
                <form method="post">
                    <input type="hidden" name="action" value="<?php echo $editType ? 'update_type' : 'add_type'; ?>">
                    <?php if ($editType): ?>
                        <input type="hidden" name="type_id" value="<?php echo htmlspecialchars($editType['id']); ?>">
                    <?php endif; ?>

                    <div class="field-row">
                        <label for="type_name">Type Name</label>
                        <input id="type_name" name="type_name" type="text" value="<?php echo htmlspecialchars($_POST['type_name'] ?? ($editType['type_name'] ?? '')); ?>" required>
                    </div>

                    <div class="weapon-actions">
                        <button type="submit" class="btn-admin"><?php echo $editType ? 'Update Type' : 'Create Type'; ?></button>
                        <?php if ($editType): ?>
                            <a href="weapontype.php" class="btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="weapon-table-card">
                <h2>Weapon Types</h2>

                <?php if (count($types) > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Type Name</th>
                                <th>Weapon Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($types as $type): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($type['type_name']); ?></td>
                                    <td><?php echo htmlspecialchars($type['weapon_count']); ?></td>
                                    <td>
                                        <div class="table-actions">
                                            <a class="btn-secondary" href="?edit_id=<?php echo htmlspecialchars($type['id']); ?>">Edit</a>
                                            <form method="post" class="inline-form" onsubmit="return confirm('Delete this weapon type?');">
                                                <input type="hidden" name="action" value="delete_type">
                                                <input type="hidden" name="type_id" value="<?php echo htmlspecialchars($type['id']); ?>">
                                                <button type="submit" class="btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">No weapon types are available yet.</div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>

</html>
