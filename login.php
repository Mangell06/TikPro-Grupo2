<?php
session_start();

if (!empty($_SESSION['user_id'])) {
    header("Location: discover.php");
    exit;
}

include("includes/database.php");

$error = "";
$loginSuccess = false;
$isUnverified = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {

        // Hash igual que el seeder
        $password_hashed = hash('sha256', $password);

        $stmt = $pdo->prepare(
            "SELECT id, email, is_active
             FROM users 
             WHERE email = ? AND password = ? 
             LIMIT 1"
        );
        $stmt->execute([$email, $password_hashed]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['is_active'] == 0) {
                $error = "Usuario aún no ha verificado su cuenta";
                $isUnverified = true;
            } else {
                $_SESSION['user_id'] = $user['id'];
                $loginSuccess = true;
            }
        } else {
            $error = "Email o contrasenya incorrectes";
        }
    } else {
        $error = "Introdueix email i contrasenya";
    }
}

$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

if ($requestUri !== $scriptName && strpos($requestUri, $scriptName . '/') === 0) {
    header("Location: $scriptName");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sessió</title>
    <link rel="stylesheet" href="styles.css?q=1">
    <link rel="icon" href="icono-simbio.png" type="image/png">
</head>
<?php if (!$loginSuccess): ?>
<body id="login-body">
<header class="main-header">
    <h1 class="header-title">SIMBIO</h1>
</header>

<div id="login-container">
    <h2 id="login-title">Iniciar sessió</h2>
    <form id="login-form" method="post">
        <label id="label-email" for="email">Email</label>
        <input id="input-email" type="email" name="email" required>

        <label id="label-password" for="password">Contrasenya</label>
        <input id="input-password" type="password" name="password" required>

        <button id="login-button" type="submit">Iniciar sessió</button>

        <a id="register-button" href="register.php">Donar d'alta a l'usuari</a>
    </form>
</div>
</body>
<?php endif; ?>
<script type="module">
import { showNotification } from './notificaciones.js';
import { sendLog } from './create-logs.js';
import { loadNotifications } from './load-notifications.js';

loadNotifications();

// Si el login fue exitoso, hacer log y redirigir
<?php if ($loginSuccess): ?>
(async () => {
    await sendLog(`Usuario "<?= addslashes($email) ?>" inició sesión`);
    window.location.href = 'discover.php';
})();
<?php elseif ($error): ?>
showNotification('error', <?= json_encode($error) ?>);
<?php if (empty($isUnverified)): ?>
sendLog(`Intent de login amb email: <?= addslashes($email) ?>`);
<?php endif; ?>
<?php endif; ?>
</script>
</html>