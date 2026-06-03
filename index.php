<?php
include "service/database.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Severos Blackmarket</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div id="homebackground">
        <div id="header-content">
            <section class="slider-container">
                <div class="slider-wrapper">
                    <div class="slider">
                        <img id="slide-1" src="assets/severos.jpg" alt="Severos Blackmarket Hero" width="800"
                            height="500">
                        <img id="slide-2"
                            src="https://images.unsplash.com/photo-1657586640569-4a3d4577328c?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1200&q=80"
                            alt="3D rendering of an imaginary green planet in space">
                        <img id="slide-3"
                            src="https://images.unsplash.com/photo-1656077217715-bdaeb06bd01f?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1200&q=80"
                            alt="3D rendering of an imaginary blue planet in space">
                    </div>
                    <div class="slider-nav">
                        <a href="#slide-1"></a>
                        <a href="#slide-2"></a>
                        <a href="#slide-3"></a>
                    </div>
                </div>
            </section>
            <h2 id="home-h1">Loyalty breaks ━ firepower doesn't</h2>
            <p class="tagline taglinehome">
                "Severos Blackmarket is not just a store ━ it's a survival network.
                Every weapon has a story. Every buyer, a purpose."
            </p>
        </div>
    </div>

    <script src="assets/script.js"></script>
    <?php include 'includes/footer.php'; ?>

</body>

</html>