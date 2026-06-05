<?php
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookieParams['path'] ?: '/',
        'domain' => $cookieParams['domain'],
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

session_start();

if (isset($_POST["index"])) {
    session_unset();
    session_destroy();
    session_start();
}

if (isset($_POST["login"])) {
    header("Location: login.php");
    exit();
}


if (isset($_POST["register"])) {
    header("location: register.php");
    exit();
}

if (isset($_POST["marketplace"])) {
    header("location: marketplace.php");
    exit();
}



if (isset($_POST["home"])) {
    session_unset();
    session_destroy();
    header("location: ../index.php");
    exit();
}

if (isset($_POST["transaction"])) {

    header("location: transaction.php");
    exit();
}

if (isset($_POST["arsenal"])) {

    header("location: arsenal.php");
    exit();
}

$username = "";

if (isset($_SESSION["is_login"])) {
    $stmt = $db->prepare("SELECT * FROM msuser WHERE email = ?");
    $stmt->bind_param("s", $_SESSION["email"]);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = $row["username"];
    }
}

?>

<header class="site-header">
    <div class="site-brand">
        <a href="<?php echo htmlspecialchars($homeLink); ?>">Severos</a>
    </div>

    <nav class="site-navigation" aria-label="Main navigation">
        <?php if ($loggedIn && $role === 'guest'): ?>
        <a href="<?php echo htmlspecialchars($homeLink); ?>">Home</a>
        <a href="<?php echo htmlspecialchars($marketplaceLink); ?>">Marketplace</a>
        <a href="<?php echo htmlspecialchars($arsenalLink); ?>">Arsenal</a>
        <a href="<?php echo htmlspecialchars($transactionLink); ?>">Transaction</a>
        <?php elseif ($loggedIn && $role === 'admin'): ?>
        <a href="<?php echo htmlspecialchars($dashboardLink); ?>">Dashboard</a>
        <a href="<?php echo htmlspecialchars($weaponLink); ?>">Weapons</a>
        <a href="<?php echo htmlspecialchars($paymentLink); ?>">Payments</a>
        <a href="<?php echo htmlspecialchars($weaponTypeLink); ?>">Weapon Types</a>
        <?php endif; ?>
    </nav>

    <div class="site-actions">
        <?php if ($loggedIn): ?>
        <span class="user-label"><?php echo $username; ?></span>
        <form action="" method="post" class="logout-form">
            <button type="submit" name="logout">Logout</button>
        </form>
        <?php else: ?>
        <a href="<?php echo htmlspecialchars($loginLink); ?>">Login</a>
        <a href="<?php echo htmlspecialchars($registerLink); ?>">Register</a>
        <?php endif; ?>
    </div>
</header>

<?php if ($loggedIn): ?>
<script>
const severosActive = sessionStorage.getItem('severos_active');
const urlParams = new URLSearchParams(window.location.search);
const initSession = urlParams.get('init') === '1';

<
h1 style = "font-size: 1.5rem; font-weight: bold; color: #fff;"
id = "geader" > Severos < /h1>

    <
    ul aria - label = "Auth Navigation"
id = "authen" >
    <?php
            if (isset($_SESSION["is_login"])) {
                echo '
                 <form action="" method="post">
                <ul style="margin-right: 370px;" aria-label="Main Navigation" id="navigation">
                    <li id="home-page"><form action="" method="post">
                    <button id="home-btn" style="background: none; color: #ffffff; border: none; padding: none; cursor: pointer;" type="submit" name="home">Home</button>
                </form></li>
                    <div class="divider"></div>
                    <li id="marketplace-page"><form action="" method="post">
                    <button id="marketplace-btn" style="background: none; color: #ffffff; border: none; padding: none; cursor: pointer;" type="submit" name="marketplace">Marketplace</button>
                </form></li>
                    <div class="divider"></div>
                    <li id="arsenal-page"><form action="" method="post">
                    <button id="arsenal-btn" style="background: none; color: #ffffff; border: none; padding: none; cursor: pointer;" type="submit" name="arsenal">Arsenal</button>
                </form></li>
                    <div class="divider"></div>
                    <li name="transaction" id="transaction-page"><form action="" method="post">
                    <button id="transaction-btn" style="background: none; color: #ffffff; border: none; padding: none; cursor: pointer;" type="submit" name="transaction">Transaction</button>
                </form></li>
                </ul>
                </form>';
                echo htmlspecialchars($username);
                echo '<div class="divider"></div>';
                echo '<form action="" method="post">
                    <button id="logout-btn" style="background: none; color: #434343; border: none; padding: none; cursor: pointer;" type="submit" name="index">Logout</button>
                </form>';
            } else {

                echo '<li name="login" id="login-page"><a href="login.php">Login</a></li>';
                echo '<div class="divider"></div>';
                echo '<li name="register" id="register-page"><a href="register.php">Register</a></li>';
            }
            ?> <
    /ul>

    <
    /header>


    <
    script src = "assets/script.js" >
</script>
</body>

</html>