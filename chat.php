<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = $_SERVER['SCRIPT_NAME'];

    if ($requestUri !== $scriptName && strpos($requestUri, $scriptName . '/') === 0) {
        // Redirige a la URL correcta
        header("Location: $scriptName");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="styles.css?q=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icono-simbio.png" type="image/png">
</head>
<body>
    <header class="header-discovered">
    <a href="discover.php" id="backdiscover">Discover</a>
    <a href="messages.php" id="backdiscover">Misatges</a>
    <div class="close-session">
        <?php
    include("includes/database.php");

    $iduser = $_SESSION["user_id"];

    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = :iduser");
    $stmt->execute(['iduser' => $iduser]);

    $user = $stmt->fetch();
    
    if ($user) {
        echo "<h3 class='user'>" . htmlspecialchars($user['name']) . "</h3>";
    } else {
        echo "<h1>Usuari no encontrat</h1>";
    }
    ?>
    <a href="logout.php" id="nav-logout" class="logout-button">Tancar sessió</a>
    </div>
    </header>
    <form method="post">
        <div id="destinyuser"></div>
        <div id="messagesblock"></div>
        <div>
            <input type="text" placeholder="Envia un missatge...">
            <button>Envia</button>
        </div>
    </form>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $("form").on('submit', async function(e) {
            e.preventDefault();
            
        });
    </script>
</body>
</html>