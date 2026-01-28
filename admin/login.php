<?php
session_start();
include("../includes/database.php");

$correctSession = false;

$admin = null;
$message = "";

if (!empty($_POST['email']) && !empty($_POST['password'])) {
    //pasar contraseña hasheada
    $password = hash('sha256', $_POST['password']);
    $stmt = $pdo->prepare("SELECT email, password, id, name, user_role FROM users WHERE email = :email AND password= :password AND user_role = 'admin';");
    $stmt->execute(['email' => $_POST['email'], 'password' => $password]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: index.php");
        $correctSession = true;
        exit;
    } else {
        $message = "El usuari o la contrasenya no són correctes o no es admin";
    }


}
?>

<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/oak_4986983.png" type="image/png">
    <link rel="stylesheet" href="../styles.css">
    <title>Iniciar sessió - Admin</title>

</head>

<body id="login-body">
    <header class="main-header">
        <h1 class="header-title">SIMBIO</h1>
    </header>
    <div id="login-container">
        <h2 id="login-title">Iniciar sessió</h2>
        <form method="post" id="login-form">
            <label for="email" id="label-email">Email</label>
            <input type="email" name="email" id="input-email">
            <label for="password" id="label-password">Contrasenya</label>
            <input type="password" name="password" id="input-password">
            <button type="submit" id="login-button">Iniciar sessió</button>
        </form>
    </div>

    <script type="module">
        import { showNotification } from '../notificaciones.js';
        import { sendLog } from '../create-logs.js';
        const message = <?php echo json_encode($message); ?>;
        if (message) {
            showNotification("error", message);
            // sendLog("error", "Intent d'inici de sessió fallit a l'administració amb l'email: " + inputEmail);
        }

        const correctSession = <?php echo json_encode($correctSession); ?>;
        const admin = <?php echo json_encode($admin); ?>;
        const loginButton = document.getElementById('login-button');
        const loginForm = document.getElementById('login-form');
        loginButton.addEventListener('click', function (event) {
            
            const inputEmail = document.getElementById('input-email').value;
            const inputPassword = document.getElementById('input-password').value;
            if (inputEmail === "" || inputPassword === "" && !message) {
                event.preventDefault();
                showNotification("error", "Has d'omplir tots els camps");
            }
        });

    </script>
</body>

</html>