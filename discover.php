<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descobrir</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="icono-simbio.png" type="image/png">
</head>
<body id="discover-body">
    <header class="header-discovered">
        <div class="profile-chat-header">
            <a href="profile.php">Perfil</a>
            <a href="chats.php">Chats</a>
        </div>
        <div class="close-session">
            <?php
        include("includes/database.php");

        $iduser = $_SESSION["user_id"];

        // Preparar y ejecutar la consulta
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id_user = :iduser");
        $stmt->execute(['iduser' => $iduser]);

        // Obtener el resultado
        $user = $stmt->fetch();
        
        // Mostrar el nombre
        if ($user) {
            echo "<h3 class='user'>" . htmlspecialchars($user['username']) . "</h3>";
        } else {
            echo "<h1>Usuari no encontrat</h1>";
        }
        ?>
        
        <a href="logout.php" id="nav-logout" class="logout-button">Tancar sessió</a>
    
        </div>
        </header>
<main id="discover-container">
</main>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="module">
import { showNotification } from './notificaciones.js';
import { sendLog } from './create-logs.js'; // importar función de logging

// preventDefault F5
$(document).on("keydown", function(e) {
    if ((e.which || e.keyCode) == 116 || ((e.ctrlKey || e.metaKey) && (e.which || e.keyCode) == 82)) {
        e.preventDefault();
        console.log("Refresh prevented");
    }
});

let projectsData = [];

// funcion para crear elementos.
function createElement(tag, parent = "", className = "", attr = {}) {
    const $element = $(tag);
    if (className) $element.addClass(className);
    if (attr && typeof attr === "object") {
        for (const key in attr) $element.attr(key, attr[key]);
    }
    if (parent) $(parent).append($element);
    return $element;
}

// funcion para crear cartas
function createCard(projectData) {
    const divCard = createElement("<div></div>", "", "project-card");
    
    createElement("<video controls muted autoplay loop playsinline></video>", divCard, "", { 
        src: projectData.video,
    });
    const mother =createElement("<div></div>", divCard, "allInfoDiv");
    
    const divButtons = createElement("<div></div>", mother, "actions");
    const btnLike = createElement("<button></button>", divButtons, "like").text("M'agrada");
    const btnNope = createElement("<button></button>", divButtons, "nope").text("No m'agrada");
    
    const title =createElement("<p></p>", mother,"project-title").text(projectData.title);
    const userWithEntity =createElement("<pre></pre>", mother).text(projectData.username +" - "+ projectData.entity_name);
    const description =createElement("<p></p>", mother, "trunc").text(projectData.description);
    
    const infoButton = createElement("<button></button>", mother, "info-toggle").text("Mostra info");

    const divInfo = createElement("<div></div>", mother, "project-info hiddenSuave");
    
    const infoButtonClick = () => {
        divInfo.toggleClass("hiddenSuave");
        mother.toggleClass("allInfoDiv");
        divCard.toggleClass("dimLight");
        sendLog(`Usuario ${<?php echo json_encode($user['username']); ?>} toggle info: ${divInfo.hasClass("hiddenSuave") ? 'oculto' : 'visible'}`);
    }

    const infoButtonClose = createElement("<button></button>", divInfo, "info-toggle").text("Amagar info");
    infoButton.on("click", infoButtonClick);
    infoButtonClose.on("click", infoButtonClick);
    
    
    console.log(projectData);
    createElement("<p></p>", divInfo,"project-title").text(projectData.title);
    createElement("<pre></pre>", divInfo).text(projectData.username +" - "+ projectData.entity_name);
    createElement("<p></p>", divInfo).text(projectData.description);
    createElement("<p></p>", divInfo, "bold").text("Categories: ");


    const divTags = createElement("<div></div>", divInfo, "tags");
    (projectData.tags || []).forEach(tag => {
        createElement("<span></span>", divTags).text(tag);
    });

    btnNope.on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['username']); ?>} presionó Like en proyecto ${projectData.id_project}`));
    btnLike.on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['username']); ?>} presionó Nope en proyecto ${projectData.id_project}`));

    return divCard;
}

function deleteData() {
    if (projectsData.length !== 0) {
        projectsData.shift();
        return true;
    }
    return false;
}

function deleteCard() {
    const cardDoom = $("#discover-container .project-card");
    if (!cardDoom.length) {
        showNotification("error","No hi ha cap projecte");
        sendLog(`Usuario ${<?php echo json_encode($user['username']); ?>} intento eliminar carta pero no habia ninguna`);
        return false;
    }
    cardDoom.remove();
    sendLog(`Usuario ${<?php echo json_encode($user['username']); ?>} eliminó la carta visible`);
    return true;
}

async function readDB() {
    const lastId = projectsData.length ? projectsData[0].id_project : 0;

    await fetch(`includes/load-cards.php?exclude_id=${lastId}`)
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                showNotification("error",err.error);
                sendLog(`Error fetch proyectos: ${err.error}`);
            });
        }
        return response.json();
    })
    .then(projects => {
        const newProject = projects;
        const exists = projectsData.some(p => p.id_project === newProject.id_project);
        if (!exists) {
            projectsData.push(newProject);
            sendLog(`Usuario ${<?php echo json_encode($user['username']); ?>} cargó proyecto ${newProject.id_project}`);
        }
    });
}

while (projectsData.length-1 < 3) {
    await readDB();
}

async function loadCard() {

    if (projectsData.length === 0) {
        showNotification("error","No hi ha projectes");
        sendLog(`Usuario ${<?php echo json_encode($user['username']); ?>} no tiene proyectos disponibles`);
        return false;
    }

    if ($("#discover-container .project-card").length > 0) {
        deleteCard();
        deleteData();
    }

    const cardDoom = createCard(projectsData[0]);
    addCardEvents(cardDoom); 
    $("#discover-container").append(cardDoom);

    while (projectsData.length-1 < 3) {
        await readDB();
    }

    return true;
}

function addCardEvents(card) {
    card.find(".like").on("click", () => handleAction(card, "like"));
    card.find(".nope").on("click", () => handleAction(card, "nope"));
}

function handleAction(card, action) {
    card.addClass(action === "like" ? "swipe-right" : "swipe-left");
    sendLog(`Usuario ${<?php echo json_encode($user['username']); ?>} swiped ${action} en proyecto ${projectsData[0].id_project}`);

    setTimeout(() => {
        if (!loadCard()) showNotification("error","No s'ha pogut carregar el seguent projecte");
    }, 400);

    if (action === "like") {
        showNotification("info","💖 Match! Anar al xat");
    }
}

sendLog(`Usuario ${<?php echo json_encode($user['username']); ?>} abrió la página Discover`);
showNotification("info","Benvingut, " + <?php echo json_encode($user['username']); ?>);
loadCard();
</script>
</body>
</html>
