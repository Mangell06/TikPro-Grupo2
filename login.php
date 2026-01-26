<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    $now = date("Y-m-d H:i:s");

    if ($action === 'recover') {
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
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('simbiodb@gmail.com', 'SIMBIO');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Codi de verificació - SIMBIO';
                
                $mail->Body = "
                <div style='background-color: #f4f4f7; padding: 30px; font-family: sans-serif; text-align: center;'>
                    <div style='max-width: 450px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; padding: 40px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 1px solid #e1e1e1;'>
                        <h1 style='color: #1a1a1a; letter-spacing: 4px;'>SIMBIO</h1>
                        <h2 style='color: #333;'>Codi d'accés</h2>
                        <div style='margin: 30px 0; padding: 20px; background-color: #f8f9fa; border: 2px dashed #007bff; border-radius: 8px;'>
                            <span style='font-size: 32px; font-weight: bold; color: #007bff; letter-spacing: 8px;'>$code</span>
                        </div>
                        <p style='color: #999; font-size: 13px;'>Aquest codi és vàlid durant 15 minuts.</p>
                    </div>
                </div>";

                $mail->send();
                $error = "Codi enviat! Revisa el teu email.";
                $showVerify = true; 
            } catch (Exception $e) {
                $error = "Error al enviar l'email.";
            }
        } else {
            $error = "Aquest email no està registrat.";
        }
    } elseif ($action === 'verify_code') {
        $inputCode = trim($_POST['verify_code'] ?? '');
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND code_activate = ? AND code_expire >= ? LIMIT 1");
        $stmt->execute([$email, $inputCode, $now]);
        $user = $stmt->fetch();

        if ($user) {
            $pdo->prepare("UPDATE users SET code_activate = NULL, code_expire = NULL WHERE id = ?")->execute([$user['id']]);
            $_SESSION['user_id'] = $user['id'];
            $loginSuccess = true;
        } else {
            $error = "Codi incorrecte o caducat.";
            $showVerify = true; 
        }
    } else {
        $password = $_POST['password'] ?? '';
        if ($email && $password) {
            $password_hashed = hash('sha256', $password);
            $stmt = $pdo->prepare("SELECT id, is_active FROM users WHERE email = ? AND password = ? LIMIT 1");
            $stmt->execute([$email, $password_hashed]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $loginSuccess = true;
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
    <link rel="icon" href="oak_4986983.png" type="image/png">
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
                <label for="verify_code" style="color: #007bff; font-weight: bold;">Escriu el codi de 6 dígits</label>
                <input id="input-verify" type="text" name="verify_code" maxlength="6" placeholder="000000" autocomplete="off" style="text-align: center; font-size: 20px; letter-spacing: 5px;">
            </div>

            <br><button id="login-button" type="submit">Iniciar sessió</button>

            <div id="options-links" style="margin-top: 15px;">
                <a id="register-button" href="register.php">Donar d'alta a l'usuari</a><br id="br-register">
                <a id="forgott-button" href="javascript:void(0)" onclick="toggleRecoveryMode()" style="color: #666; font-size: 0.9em;">M'he oblidat de la contrasenya</a>
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
        const registerBtn = document.getElementById('register-button');
        const brRegister = document.getElementById('br-register');
        const passInput = document.getElementById('input-password');
        const emailInput = document.getElementById('input-email');

        // Si estamos en modo recover o modo verify_code, volvemos al login normal
        if (actionInput.value === 'recover' || actionInput.value === 'verify_code') {
            title.innerText = "Iniciar sessió";
            passwordGroup.style.display = 'block';
            verifyGroup.style.display = 'none';
            registerBtn.style.display = 'inline-block'; 
            if(brRegister) brRegister.style.display = 'block';
            passInput.required = true;
            emailInput.readOnly = false;
            loginButton.innerText = "Iniciar sessió";
            forgotLink.innerText = "M'he oblidat de la contrasenya";
            actionInput.value = 'login';
        } else {
            // Pasar de login normal a modo recuperación
            title.innerText = "Recuperar Accés";
            passwordGroup.style.display = 'none';
            verifyGroup.style.display = 'none';
            registerBtn.style.display = 'none'; 
            if(brRegister) brRegister.style.display = 'none';
            passInput.required = false;
            loginButton.innerText = "Enviar codi per email";
            forgotLink.innerText = "Tornar al Login";
            actionInput.value = 'recover';
        }
    }

    <?php if ($showVerify): ?>
        // Aplicar estado de verificación y CAMBIAR TEXTO DEL LINK
        document.getElementById('login-title').innerText = "Verificar Codi";
        document.getElementById('password-group').style.display = 'none';
        document.getElementById('register-button').style.display = 'none'; 
        if(document.getElementById('br-register')) document.getElementById('br-register').style.display = 'none';
        document.getElementById('verify-group').style.display = 'block';
        document.getElementById('input-password').required = false;
        document.getElementById('input-verify').required = true;
        document.getElementById('input-email').readOnly = true;
        document.getElementById('login-button').innerText = "Validar i entrar";
        document.getElementById('form-action').value = 'verify_code';
        
        // AQUÍ EL FIX: Cambiamos el texto del enlace cuando ya se ha enviado el código
        document.getElementById('forgott-button').innerText = "Tornar al Login";
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
    await sendLog(`Usuari "<?= addslashes($email) ?>" ha iniciat sessió`);
    window.location.href = 'discover.php';
})();
<?php elseif ($error): ?>
showNotification('info', <?= json_encode($error) ?>);
<?php endif; ?>
</script>
</html>