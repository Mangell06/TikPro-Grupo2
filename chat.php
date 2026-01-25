<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    if (!isset($_GET['talk'])) {
        header("Location: messages.php");
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
    <form id="chatform" method="post">
        <div id="destinyuser">
            <?php
            try {
                include("includes/database.php");
                $projectid = $_GET["talk"];
                $sql = "SELECT p.title AS projectname, u.name AS username, u.logo_image 
                FROM projects p JOIN users u ON u.id = p.id_owner 
                JOIN messages m ON m.id_project = p.id WHERE m.id = ?;";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$projectid]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $srclogoimg = $row["logo_image"];
                if (empty($srclogoimg)) {
                    $srclogoimg = "uploads/basic-logo-user.png";
                }
                $username = $row["username"];
                $titleproject = $row["projectname"];
                echo "<img src='$srclogoimg' />";
                echo "<p><strong>$username</strong></p>";
                echo "<p>$titleproject</p>";
            }  catch (PDOException $e) {
                error_log("ERROR SQL: " . $e->getMessage());
            }
            ?>
        </div>
        <div id="messagesblock"></div>
        <div>
            <input type="text" placeholder="Envia un missatge...">
            <button>Envia</button>
        </div>
    </form>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="module">
        import { showNotification } from '/notificaciones.js';
        import { createElement } from '/createElement.js';
        import { sendLog } from '/create-logs.js';
        import { loadNotifications } from './load-notifications.js';
        const urlParams = new URLSearchParams(window.location.search);
        const idchat = urlParams.get('talk');
        let lastDate = null;

        async function syncChat() {
            // 1. Construir el diccionario de datos
            const payload = new URLSearchParams();
            payload.append('data_message[id_chat]', idchat);
            
            // Solo añadimos la fecha si ya tenemos mensajes en pantalla
            if (lastDate) {
                payload.append('data_message[last-date]', lastDate);
            }

            // 2. Ejecutar el fetch
            fetch('includes/load-messages.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: payload.toString()
            })
            .then(response => response.json())
            .then(mensajes => {
                if (mensajes.length > 0) {
                    mensajes.forEach(msg => {
                        const messageContainer = createElement(
                            "<div></div>",
                            "#messagesblock",
                            msg.is_mine ? "usermessagecontainer" + (!msg.read_status ? " unreadmessage" : "") : "destinymessagecontainer"
                        );
                        createElement(`<p>${msg.text_message}</p>`, messageContainer, "textmessagechat");
                        createElement(`<p>${msg.date_message}</p>`, messageContainer, "textmessagechat");
                        lastDate = msg.date_message;
                    });
                }
            })
            .catch(error => showNotification("error", error));
        }

        syncChat();

        // Ejecución cada 5 segundos
        setInterval(syncChat, 5000);
        
        $("form").on('submit', async function(e) {
            e.preventDefault();
        });
    </script>
</body>
</html>