<?php
session_start();

// Si ya hay sesión, redirige a discover
if (!empty($_SESSION['user_id'])) {
    header("Location: discover.php");
    exit;
}

include("includes/database.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {

        // Hash igual que el seeder
        $password_hashed = hash('sha256', $password);

        $stmt = $pdo->prepare(
            "SELECT id, email 
             FROM users 
             WHERE email = ? AND password = ? 
             LIMIT 1"
        );
        $stmt->execute([$email, $password_hashed]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['user_id']    = $user['id'];

            header("Location: discover.php");
            exit;
        } else {
            $error = "Email o contrasenya incorrectes";
        }
    } else {
        $error = "Introdueix email i contrasenya";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sessió</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="icono-simbio.png" type="image/png">
</head>
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
    </form>
</div>
<script type="module">
import { showNotification } from './notificaciones.js';
import { sendLog } from './create-logs.js'; // tu función para guardar logs

// Capturar el formulario
const form = document.getElementById('login-form');
const emailInput = document.getElementById('input-email');

form.addEventListener('submit', async (e) => {
    // Log: intento de login
    sendLog(`Intent de login amb email: ${emailInput.value}`);
});

<?php if ($error): ?>
    showNotification('error', <?= json_encode($error) ?>);
<?php endif; ?>
</script>

</body>
</html>
