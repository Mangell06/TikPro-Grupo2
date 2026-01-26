<?php
    session_start();
    include("../includes/database.php");

    $message = "";

    if(!empty($_POST['email']) && !empty($_POST['password'])){
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = :email AND user_role = 'admin' LIMIT 1");
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $hashedInput = hash('sha256', $password);

        if($admin && $hashedInput === $admin['password']){
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: index.php");
            exit;
        } else {
            $message = "El usuari o la contrasenya no són correctes";
        }
    }
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../oak_4986983.png" type="image/png">
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
            <input type="email" name="email" id="input-email" required>
            <label for="password" id="label-password">Contrasenya</label>
            <input type="password" name="password" id="input-password" required>
            <button type="submit" id="login-button">Iniciar sessió</button>
        </form>
    </div>
    
    <script type="module">
        import { showNotification } from '../notificaciones.js';
        const messageFromServer = <?php echo json_encode($message); ?>;

        if (messageFromServer) {
            showNotification("error", messageFromServer);
        }

        const loginForm = document.getElementById('login-form');
        
        loginForm.addEventListener('submit', function(event) {
            const inputEmail = document.getElementById('input-email').value;
            const inputPassword = document.getElementById('input-password').value;

            if (inputEmail === "" || inputPassword === "") {
                event.preventDefault();
                showNotification("error", "Has d'omplir tots els camps");
            }
        });
    </script>
</body>
</html>