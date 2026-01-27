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
    <title>Chats</title>
    <link rel="stylesheet" href="styles.css?q=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icono-simbio.png" type="image/png">
</head>
<body>
    <header class="header-discovered">
    <a href="discover.php" id="backdiscover">retornar Discover</a>
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
    <div id="containermessages">
        
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="module">
        import { createElement } from "./createElement.js";
        import { sendLog } from '/create-logs.js';

        fetch('includes/load-last-messages.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(messages => {
            if (messages.length === 0) {
                createElement("<h1>No hi ha encara converses</h1>","#containermessages","name-project");

                $("#containermessages").append(mainContainer);
            }
            
            messages.forEach(function(message) {
                let imageuser = "uploads/basic-logo-user.png";
                if (message.logo_image) {
                    imageuser = message.logo_image;
                }
                const mainContainer = createElement("<a></a>","","message-item");
                
                $(mainContainer).on('click', async function(e) {
                    e.preventDefault();
                    $("a").css("pointer-events", "none");
                    await sendLog(`Usuario <?php echo json_encode($user['name']); ?> ha seleccionado el chat del proyecto "${message.project_name}"`);
                    window.location.href = `chat.php?talk=${message.id_messages}`;
                });

                if (!message.read_status && message.sendername && message.sendername.toLowerCase() !== "yo") {
                    mainContainer.addClass('unread');
                } else {
                    mainContainer.addClass('read');
                }
                
                const userContainer = createElement("<div></div>",mainContainer,"user-item");
                createElement("<img></img>", userContainer, "user-image", {src:imageuser, alt:"Logo"});
                const usernameparraph = createElement("<p></p>",userContainer,"name-user");
                createElement(`<strong>${message.user_name}</strong>`, usernameparraph);
                if (message.date_message) createElement(`<p>${message.date_message}</p>`,userContainer,"last-date");

                const projectContainer = createElement("<div></div>",mainContainer,"project-item");
                const projectnameparraph = createElement("<p></p>",projectContainer,"name-project");
                createElement(`<strong>${message.project_name}</strong>`, projectnameparraph);

                if (message.text_message) {
                    const textContainer = createElement("<div></div>",mainContainer,"text-item");
                    const textparraph = createElement(`<p>${message.text_message.length > 20 ? message.sendername + ": <br/>" + message.text_message.slice(0, 20) + "..." : message.sendername + ": <br/>" + message.text_message}</p>`,textContainer,"text-message");
                }

                $("#containermessages").append(mainContainer);
            });
        })
        .catch(err => console.error("Error al cargar mensajes:", err));
    </script>
</body>
</html>