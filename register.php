<?php
include "service/database.php";

$errors = [];

if (isset($_POST["register"])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);


    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = 'Semua field wajib diisi.';
    } else {
        if (strlen($username) < 8) {
            $errors['username'] = 'Username minimal 8 karakter.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Password minimal 8 karakter.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email tidak valid.';
        }
    }


    if (empty($errors)) {
        $hpassword = password_hash($password, PASSWORD_BCRYPT);
        $credit = 200;
        $role = "guest";

        $sql = "INSERT INTO msuser (username, password, email, credit, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            $errors['general'] = 'Database error. Please try again later.';
        } else {
            $stmt->bind_param("sssis", $username, $hpassword, $email, $credit, $role);
            if ($stmt->execute()) {
                $stmt->close();
                $db->close();
                header("Location: login.php");
                exit();
            }

            if ($stmt->errno === 1062) {
                $errors['email'] = 'Email sudah terdaftar.';
            } else {
                $errors['general'] = 'Database error: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
    $db->close();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <?php
    include "includes/header.php";
    ?>
    <section id="background">

        <div class="card">
            <h1>Register</h1>
            <p class="tagline">Sign up. Load up. Stand out.</p>
            <form action="register.php" method="POST">
                <p class="highlighter">
                    Create your account by filling in the information below.
                </p>
                <?php if (!empty($errors['general']) || !empty($errors[0])): ?>
                    <div style="background:#ffe0e0; border:1px solid red; border-radius:6px;
                                padding:10px 14px; margin-bottom:14px;">
                        <strong style="color:red; font-size:13px;">
                            <?php echo htmlspecialchars($errors['general'] ?? $errors[0]); ?>
                        </strong>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                        placeholder="Enter your username(min. 8 characters)"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" />
                    <?php if (!empty($errors['username'])): ?>
                        <strong style="margin-top:6px; color:red; font-size:12px; font-style:italic; display:block;">
                            <?php echo htmlspecialchars($errors['username']); ?>
                        </strong>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email( ..@gmail.com)"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
                    <?php if (!empty($errors['email'])): ?>
                        <strong style="margin-top:6px; color:red; font-size:12px; font-style:italic; display:block;">
                            <?php echo htmlspecialchars($errors['email']); ?>
                        </strong>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                        placeholder="Enter your password(min. 8 characters)" />
                    <?php if (!empty($errors['password'])): ?>
                        <strong style="margin-top:6px; color:red; font-size:12px; font-style:italic; display:block;">
                            <?php echo htmlspecialchars($errors['password']); ?>
                        </strong>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-register" name="register">Register</button>
            </form>

            <p class="register-link">Already have an account? <a href="login.php">Login here.</a></p>
        </div>
    </section>

    <?php include "includes/footer.php"; ?>
</body>

</html>