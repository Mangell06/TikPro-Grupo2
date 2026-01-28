<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/oak_4986983.png" type="image/png">
    <link rel="stylesheet" href="../styles.css">
    <title>Admin - SIMBIO</title>
</head>
<body id=login-body>
    <header class="main-header">
        <!-- <h1 class="header-title">SIMBIO</h1> -->
        <?php
            include("../includes/database.php");
            $userAdmin =  $_SESSION['admin_id'];
            $stmt = $pdo->prepare("SELECT name FROM users WHERE id = :idadmin");
            $stmt->execute(['idadmin' => $userAdmin]);

            $adminInfo = $stmt->fetch();
            
            if ($adminInfo) {
                echo "<h3 class='user'>" . htmlspecialchars($adminInfo['name']) . "</h3>";
            } else {
                echo "<h1>Usuari no trobat</h1>";
            }
            ?>
            <a href="/admin/logout.php" id="nav-logout" class="logout-button">Tancar sessió</a>
    </header>
    <section class="admin">
        <h2>Benvingut/da, administrador/a!</h2>
        <p>Utilitza el menú de navegació per gestionar el sistema.</p>
        <ul>
            <li><a href="projects.php">Gestionar projectes</a></li>
        </ul>
    </section>
    <script type="module">
        import { sendLog } from '/create-logs.js';
        sendLog(`El usuario con id <?php echo json_encode($_SESSION["admin_id"]); ?> ha iniciado sessión en admin`);
    </script>
</body>
</html>