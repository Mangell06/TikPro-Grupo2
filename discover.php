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
    <title>Descobrir</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body id="discover-body">
    <header class="header-discovered">
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
            echo "<h1> Bienvenido, " . htmlspecialchars($user['username']) . "</h1>";
        } else {
            echo "<h1>Usuario no encontrado</h1>";
        }
        ?>
    </header>
<main id="discover-container">
</main>

<nav id="bottom-nav">
    <button id="nav-profile">👤</button>
    <button id="nav-chat">💬</button>
    <a href="logout.php" id="nav-logout" class="logout-button">🚪</a>
</nav>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="module">
import { showNotification } from './notificaciones.js';
import { sendLog } from './create-logs.js'; // importar función de logging

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
    
    createElement("<video></video>", divCard, "", { 
        src: projectData.video,
        controls: true,
        muted: true,
        autoplay: true,
        loop: true,
        playsinline: true 
    });

    const infoButton = createElement("<button></button>", divCard, "info-toggle").text("Mostrar info");

    const divInfo = createElement("<div></div>", divCard, "project-info hidden");

    infoButton.on("click", () => {
        divInfo.toggleClass("hidden");
        infoButton.text(divInfo.hasClass("hidden") ? "Mostrar info" : "Ocultar info");
        sendLog(`Usuario ${<?php echo json_encode($user['username']); ?>} toggle info: ${divInfo.hasClass("hidden") ? 'oculto' : 'visible'}`);
    });

    createElement("<p></p>", divInfo).text(projectData.description);

    const divTags = createElement("<div></div>", divInfo, "tags");
    (projectData.tags || []).forEach(tag => {
        createElement("<span></span>", divTags).text(tag);
    });

    const divButtons = createElement("<div></div>", divCard, "actions");
    const btnNope = createElement("<button></button>", divButtons, "nope").text("Like");
    const btnLike = createElement("<button></button>", divButtons, "like").text("Nope");

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

async function loadCard() {
    while (projectsData.length-1 < 3) {
        await readDB();
    }

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
loadCard();
</script>
</body>
</html>
