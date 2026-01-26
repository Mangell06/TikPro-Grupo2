<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Asegúrate de que esta ruta es correcta. Si no usas composer, apunta al src de PHPMailer
require 'vendor/autoload.php'; 

if (!empty($_SESSION['user_id'])) {
    header("Location: discover.php");
    exit;
}

include("includes/database.php");

$error = "";
$loginSuccess = false;
$showVerify = false; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $action = $_POST['action'] ?? 'login';
    $now = date("Y-m-d H:i:s"); // Fuente de tiempo única para PHP y MySQL

    if ($action === 'recover') {
        // --- 1. SOLICITAR CÓDIGO ---
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date("Y-m-d H:i:s", strtotime('+15 minutes'));

            $upd = $pdo->prepare("UPDATE users SET code_activate = ?, code_expire = ? WHERE id = ?");
            $upd->execute([$code, $expires, $user['id']]);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'mangell0624@gmail.com';
                $mail->Password   = 'wlpw zjuu axlg bsbn';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('simbiodb@gmail.com', 'SIMBIO');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Codi de verificacio - SIMBIO';
                $mail->Body    = "El teu codi d'accés és: <b style='font-size:24px;'>$code</b><br>Caduca en 15 minuts.";

                $mail->send();
                $error = "Codi enviat! Revisa el teu email.";
                $showVerify = true; 
            } catch (Exception $e) {
                $error = "Error en enviar l'email.";
            }
        } else {
            $error = "Email no trobat.";
        }
    } elseif ($action === 'verify_code') {
        // --- 2. VALIDAR CÓDIGO ---
        $inputCode = trim($_POST['verify_code'] ?? '');
        
        // Buscamos coincidencia exacta de Email + Código + Que no haya expirado respecto a PHP
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND code_activate = ? AND code_expire >= ? LIMIT 1");
        $stmt->execute([$email, $inputCode, $now]);
        $user = $stmt->fetch();

        if ($user) {
            // Éxito: Limpiamos el código para que sea de un solo uso
            $pdo->prepare("UPDATE users SET code_activate = NULL, code_expire = NULL WHERE id = ?")->execute([$user['id']]);
            
            $_SESSION['user_id'] = $user['id'];
            $loginSuccess = true;
        } else {
            $error = "Codi incorrecte o caducat.";
            $showVerify = true; // Mantener la pantalla del código activa
        }
    } else {
        // --- 3. LOGIN NORMAL ---
        $password = $_POST['password'] ?? '';
        if ($email && $password) {
            $password_hashed = hash('sha256', $password);
            $stmt = $pdo->prepare("SELECT id, is_active FROM users WHERE email = ? AND password = ? LIMIT 1");
            $stmt->execute([$email, $password_hashed]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if ($user['is_active'] == 0) {
                    $error = "Usuari encara no verificat.";
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $loginSuccess = true;
                }
            } else {
                $error = "Email o contrasenya incorrectes.";
            }
        }
    }
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
    <header class="main-header"><h1 class="header-title">SIMBIO</h1></header>

    <div id="login-container">
        <h2 id="login-title">Iniciar sessió</h2>
        
        <form id="login-form" method="post"> 
            <input type="hidden" name="action" id="form-action" value="login">

            <label id="label-email" for="email">Email</label>
            <input id="input-email" type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>

            <div id="password-group">
                <label id="label-password" for="password">Contrasenya</label>
                <input id="input-password" type="password" name="password" required>
            </div>

            <div id="verify-group" style="display: none;">
                <label for="verify_code">Codi de 6 dígits</label>
                <input id="input-verify" type="text" name="verify_code" maxlength="6" placeholder="000000" autocomplete="off">
            </div>

            <button id="login-button" type="submit">Iniciar sessió</button>

            <div id="options-links">
                <a id="register-button" href="register.php">Donar d'alta a l'usuari</a><br>
                <a id="forgott-button" href="javascript:void(0)" onclick="toggleRecoveryMode()">M'he oblidat de la contrasenya</a>
            </div>
        </form>
    </div>

    <script>
    function toggleRecoveryMode() {
        const title = document.getElementById('login-title');
        const passwordGroup = document.getElementById('password-group');
        const verifyGroup = document.getElementById('verify-group');
        const loginButton = document.getElementById('login-button');
        const forgotLink = document.getElementById('forgott-button');
        const actionInput = document.getElementById('form-action');
        const emailInput = document.getElementById('input-email');
        const passInput = document.getElementById('input-password');
        const verifyInput = document.getElementById('input-verify');

        if (actionInput.value === 'login') {
            title.innerText = "Recuperar Codi";
            passwordGroup.style.display = 'none';
            verifyGroup.style.display = 'none';
            passInput.required = false;
            emailInput.readOnly = false;
            loginButton.innerText = "Enviar codi per email";
            forgotLink.innerText = "Tornar al Login";
            actionInput.value = 'recover';
        } else {
            title.innerText = "Iniciar sessió";
            passwordGroup.style.display = 'block';
            verifyGroup.style.display = 'none';
            passInput.required = true;
            emailInput.readOnly = false;
            loginButton.innerText = "Iniciar sessió";
            forgotLink.innerText = "M'he oblidat de la contrasenya";
            actionInput.value = 'login';
        }
    }

    // Activar modo verificación si el PHP lo requiere
    <?php if ($showVerify): ?>
        document.getElementById('login-title').innerText = "Verificar Codi";
        document.getElementById('password-group').style.display = 'none';
        document.getElementById('verify-group').style.display = 'block';
        document.getElementById('input-password').required = false;
        document.getElementById('input-verify').required = true;
        document.getElementById('input-email').readOnly = true; // Evitar cambios de email aquí
        document.getElementById('login-button').innerText = "Validar i Entrar";
        document.getElementById('form-action').value = 'verify_code';
    <?php endif; ?>
    </script>
</body>
<?php endif; ?>

<script type="module">
import { showNotification } from './notificaciones.js';
import { sendLog } from './create-logs.js';
import { loadNotifications } from './load-notifications.js';

loadNotifications();

<?php if ($loginSuccess): ?>
(async () => {
    await sendLog(`Usuario "<?= addslashes($email) ?>" inició sesión`);
    window.location.href = 'discover.php';
})();
<?php elseif ($error): ?>
showNotification('info', <?= json_encode($error) ?>);
<?php endif; ?>
</script>
</html>