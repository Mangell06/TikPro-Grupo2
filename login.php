<?php
session_start();
include("database.php"); // Debe apuntar a la MISMA BD que el seeder

// === REDIRIGIR SI YA ESTÁ LOGUEADO ===
if (isset($_SESSION['user_email'])) {
    header("Location: ola.php");
    exit;
}

// === PROCESAR LOGIN ===
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {

        // MISMO hash que el seeder
        $password_hashed = hash('sha256', $password);

        $stmt = $pdo->prepare(
            "SELECT ID_User, Email 
             FROM Users 
             WHERE Email = ? AND Password = ? 
             LIMIT 1"
        );

        $stmt->execute([$email, $password_hashed]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_email'] = $user['Email'];
            $_SESSION['user_id']    = $user['ID_User'];

            header("Location: discover.php");
            exit;
        } else {
            $error = "Email o contraseña incorrectos";
        }
    } else {
        $error = "Introduce email y contraseña";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body id="login-body">
<header class="main-header">
    <h1 class="header-title">WORKTEAM</h1>
</header>

<div id="login-container">
    <h2 id="login-title">Iniciar sesión</h2>

    <?php if ($error): ?>
        <p id="login-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form id="login-form" method="post">
        <label id="label-email" for="email">Email</label>
        <input id="input-email" type="email" name="email" required>

        <label id="label-password" for="password">Contraseña</label>
        <input id="input-password" type="password" name="password" required>

        <button id="login-button" type="submit">Entrar</button>
    </form>
</div>

</body>
</html>
