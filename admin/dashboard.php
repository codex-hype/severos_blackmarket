<<<<<<< HEAD
<?php
include "../service/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["is_login"]) || $_SESSION["is_login"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$displayName = htmlspecialchars($_SESSION['username'] ?? 'Administrator');

$paymentsCount = 0;
$weaponsCount = 0;
$typesCount = 0;
$usersCount = 0;

$result = $db->query("SELECT COUNT(*) AS count FROM transactions");
if ($result) {
    $row = $result->fetch_assoc();
    $paymentsCount = (int)($row['count'] ?? 0);
}

$result = $db->query("SELECT COUNT(*) AS count FROM weapons");
if ($result) {
    $row = $result->fetch_assoc();
    $weaponsCount = (int)($row['count'] ?? 0);
}

$result = $db->query("SELECT COUNT(*) AS count FROM weapon_types");
if ($result) {
    $row = $result->fetch_assoc();
    $typesCount = (int)($row['count'] ?? 0);
}

$result = $db->query("SELECT COUNT(*) AS count FROM msuser");
if ($result) {
    $row = $result->fetch_assoc();
    $usersCount = (int)($row['count'] ?? 0);
}

$methodsCount = 0;
$result = $db->query("SELECT COUNT(DISTINCT payment_method) AS count FROM transactions");
if ($result) {
    $row = $result->fetch_assoc();
    $methodsCount = (int)($row['count'] ?? 0);
}

$topUsers = [];
$result = $db->query("SELECT u.username, COUNT(*) AS transaction_count, SUM(t.total_amount) AS total_spent
    FROM transactions t
    LEFT JOIN msuser u ON t.user_id = u.id
    GROUP BY u.id
    ORDER BY transaction_count DESC
    LIMIT 3");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $topUsers[] = $row;
    }
}

$topWeapons = [];
$result = $db->query("SELECT w.gun, SUM(t.quantity) AS sold_count
    FROM transactions t
    LEFT JOIN weapons w ON t.weapon_id = w.id
    GROUP BY w.id
    ORDER BY sold_count DESC
    LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $topWeapons[] = $row;
    }
}

$topMethods = [];
$result = $db->query("SELECT payment_method, COUNT(*) AS usage_count
    FROM transactions
    GROUP BY payment_method
    ORDER BY usage_count DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $topMethods[] = $row;
    }
}

$monthlyData = array_fill(1, 12, ['count' => 0, 'revenue' => 0]);
$currentYear = date('Y');
$result = $db->query("SELECT MONTH(created_at) AS month, COUNT(*) AS count, SUM(total_amount) AS revenue
    FROM transactions
    WHERE YEAR(created_at) = $currentYear
    GROUP BY MONTH(created_at)");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $month = (int)$row['month'];
        if ($month >= 1 && $month <= 12) {
            $monthlyData[$month] = [
                'count' => (int)$row['count'],
                'revenue' => (int)$row['revenue'],
            ];
        }
    }
}

$recentTransactions = [];
$result = $db->query("SELECT t.id, u.username, w.gun, t.quantity, t.total_amount, t.status, t.created_at
    FROM transactions t
    LEFT JOIN msuser u ON t.user_id = u.id
    LEFT JOIN weapons w ON t.weapon_id = w.id
    ORDER BY t.created_at DESC
    LIMIT 7");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentTransactions[] = $row;
    }
}

$summaryCards = [
    ['title' => 'Payments', 'description' => 'Review recent transactions and resolve payment issues.', 'value' => $paymentsCount],
    ['title' => 'Weapons', 'description' => 'Manage weapon inventory, pricing, and availability.', 'value' => $weaponsCount],
    ['title' => 'Weapon Types', 'description' => 'Update categories and classification labels.', 'value' => $typesCount],
    ['title' => 'Users', 'description' => 'Monitor user accounts and access levels.', 'value' => $usersCount],
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="admin-body">
    <?php include "../includes/header.php"; ?>

    <main class="admin-dashboard">
        <section class="admin-hero">
            <div class="admin-hero-copy">
                <p class="admin-badge">Administrator Panel</p>
                <h1>Welcome back, <?php echo $displayName; ?></h1>
                <p class="admin-lead">Manage the marketplace, payments, weapon listings, and site users from a single control center.</p>
            </div>
            <div class="admin-hero-actions">
                <a href="payment.php" class="btn-admin">View Payments</a>
                <a href="weapon.php" class="btn-secondary">Manage Weapons</a>
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

        <section class="admin-summary">
            <?php foreach ($summaryCards as $card): ?>
                <article class="dashboard-card">
                    <h2><?php echo htmlspecialchars($card['title']); ?></h2>
                    <p><strong><?php echo number_format($card['value']); ?></strong></p>
                    <p><?php echo htmlspecialchars($card['description']); ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="dashboard-overview">
            <article class="overview-card">
                <h3>Total Users</h3>
                <p><?php echo number_format($usersCount); ?></p>
            </article>
            <article class="overview-card">
                <h3>Total Weapons</h3>
                <p><?php echo number_format($weaponsCount); ?></p>
            </article>
            <article class="overview-card">
                <h3>Total Payment Methods</h3>
                <p><?php echo number_format($methodsCount); ?></p>
            </article>
        </section>

        <section class="dashboard-highlights">
            <article class="highlight-card">
                <div class="highlight-title">Top Users</div>
                <ul>
                    <?php if (count($topUsers) > 0): ?>
                        <?php foreach ($topUsers as $user): ?>
                            <li>
                                <div><?php echo htmlspecialchars($user['username'] ?: 'Unknown'); ?></div>
                                <div><?php echo htmlspecialchars(number_format($user['transaction_count'])); ?> Transactions</div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No users found.</li>
                    <?php endif; ?>
                </ul>
            </article>
            <article class="highlight-card">
                <div class="highlight-title">Top Weapons</div>
                <ul>
                    <?php if (count($topWeapons) > 0): ?>
                        <?php foreach ($topWeapons as $weapon): ?>
                            <li>
                                <div><?php echo htmlspecialchars($weapon['gun'] ?: 'Unknown Weapon'); ?></div>
                                <div><?php echo htmlspecialchars(number_format($weapon['sold_count'])); ?> Sold</div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No weapons found.</li>
                    <?php endif; ?>
                </ul>
            </article>
            <article class="highlight-card">
                <div class="highlight-title">Top Payment Methods</div>
                <ul>
                    <?php if (count($topMethods) > 0): ?>
                        <?php foreach ($topMethods as $method): ?>
                            <li>
                                <div><?php echo htmlspecialchars($method['payment_method'] ?: 'Unknown'); ?></div>
                                <div><?php echo htmlspecialchars(number_format($method['usage_count'])); ?> Used</div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No payment methods found.</li>
                    <?php endif; ?>
                </ul>
            </article>
        </section>

        <?php
            $monthLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            $monthlyCounts = array_column($monthlyData, 'count');
            $monthlyRevenue = array_column($monthlyData, 'revenue');
        ?>

        <div class="dashboard-container">
            <div class="chart-section">
                <h2>Monthly Transactions</h2>
                <div class="chart-container">
                    <canvas id="transactionChart"></canvas>
                </div>
            </div>

            <div class="transactions-section">
                <h2>Recent Transactions</h2>
                <table class="transaction-table">
                    <tbody>
                        <?php foreach ($recentTransactions as $tx): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tx['id']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($tx['total_amount'])); ?></td>
                            <td><?php echo htmlspecialchars($tx['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            const ctx = document.getElementById('transactionChart').getContext('2d');
            const transactionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($monthLabels); ?>,
                    datasets: [
                        {
                            label: 'Transactions',
                            data: <?php echo json_encode($monthlyCounts); ?>,
                            backgroundColor: '#4caf50',
                            borderColor: '#4caf50',
                            borderWidth: 1
                        },
                        {
                            label: 'Revenue',
                            data: <?php echo json_encode($monthlyRevenue); ?>,
                            backgroundColor: '#2196f3',
                            borderColor: '#2196f3',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#333'
                            },
                            ticks: {
                                color: '#888'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#888'
                            }
                        }
                    }
                }
            });
        </script>
    </main>
</body>

</html>
=======
>>>>>>> 479298e81e2e7d3f4e5172aa6293f69da2e96c07
