<?php
    session_start();
    include("../includes/database.php");

    if(!empty($_POST['email']) && !empty($_POST['password'])){
        //pasar contraseña hasheada
        $password=hash('sha256', $_POST['password']);
        
        $stmt = $pdo->prepare("SELECT email, password, id, name FROM users WHERE email = :email and password= :password;");
        $stmt->execute(['email' => $_POST['email'], 'password' => $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user){
            header("Location: index.php");
            exit;
        }
        else{
            
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
            <input type="email" name="email" id="input-email">
            <label for="password" id="label-password">Contrasenya</label>
            <input type="password" name="password" id="input-password">
            <button type="submit" id="login-button">Iniciar sessió</button>
        </form>
    </div>
    
    <script type="module">
        import { showNotification } from '../notificaciones.js';
        import { sendLog } from '../create-logs.js';
        <?php 
            if(!$user){
        ?>
                showNotification("error", "El usuari o la contrasenya no són correctes");
        <?php
            } 
        ?>

    </script>
</body>
</html>