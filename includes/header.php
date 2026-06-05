<?php

session_start();

if (isset($_POST["index"])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <header>


        <h1 style="font-size: 1.5rem; font-weight: bold; color: #fff;" id="geader">Severos</h1>

        <ul aria-label="Auth Navigation" id="authen">
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
            ?>
        </ul>

    </header>

    <script src="assets/script.js"></script>
</body>

</html>