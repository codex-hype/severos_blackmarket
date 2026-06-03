<?php
include "../service/database.php";
session_start();


if (!isset($_SESSION["is_login"]) || $_SESSION["is_login"] !== true || $_SESSION["role"] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST["logout"])) {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <section id="background">
        <?php echo "Welcome, " . htmlspecialchars($_SESSION['username']); ?>
        <form action="dashboard.php" method="POST">
            <button type="submit" name="logout">Logout</button>
        </form>
    </section>
</body>

</html>