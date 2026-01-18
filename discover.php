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
    <link rel="icon" href="icono-simbio.png" type="image/png">
</head>
<body id="discover-body">
    <header class="header-discovered">
        <?php
        include("includes/database.php");

        $iduser = $_SESSION["user_id"];

        // Preparar y ejecutar la consulta
        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = :iduser");
        $stmt->execute(['iduser' => $iduser]);

        // Obtener el resultado
        $user = $stmt->fetch();

        // Mostrar el nombre
        if ($user) {
            echo "<h1> Benvingut, " . htmlspecialchars($user['name']) . "</h1>";
        } else {
            echo "<h1>Usuari no encontrat</h1>";
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

const currentUser = <?php echo json_encode($user['name']); ?>;
let projectsData = [];
let projectsShows = []

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
    let divCard = createElement("<div></div>", "", "project-card");

    if (projectData) {
        // Carta normal con proyecto

        createElement("<video></video>", divCard, "", { 
            src: projectData.video,
            controls: true,
            muted: true,
            autoplay: true,
            loop: true,
            playsinline: true 
        });

        const infoButton = createElement("<button></button>", divCard, "info-toggle").text("Mostra info");
        const divInfo = createElement("<div></div>", divCard, "project-info hidden");

        infoButton.on("click", () => {
            divInfo.toggleClass("hidden");
            infoButton.text(divInfo.hasClass("hidden") ? "Mostra info" : "Amagar info");
            sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} toggle info: ${divInfo.hasClass("hidden") ? 'oculto' : 'visible'}`);
        });

        createElement("<p></p>", divInfo).text(projectData.description);

        const divTags = createElement("<div></div>", divInfo, "tags");
        (projectData.tags || []).forEach(tag => {
            createElement("<span></span>", divTags).text(tag);
        });

        const divButtons = createElement("<div></div>", divCard, "actions");
        const btnNope = createElement("<button></button>", divButtons, "nope").text("Nope");
        const btnLike = createElement("<button></button>", divButtons, "like").text("Like");

        btnNope.on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} presionó Nope en proyecto ${projectData.id_project}`));
        btnLike.on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} presionó Like en proyecto ${projectData.id_project}`));

    } else {
        // Carta final sin proyecto
        divCard.addClass("final-card");

        const divInfo = createElement("<div></div>", divCard, "final-info-card");
        createElement("<p></p>", divInfo).text("No hi han mes projectes.");
        createElement("<p></p>", divInfo).text("¿Vols tornar a veurels?");
        const btnTornar = createElement("<button></button>", divInfo).text("Torna a Veurels");

        btnTornar.on("click", async () => {
            btnTornar.prop("disabled", true).addClass("loading").text("Carregant");

            projectsShows = [];
            sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} vuelve a ver los projectos`);
            
            // Cargar los datos de los nuevos proyectos
            for (let i = projectsData.length; i < 3; i++) {
                await readDB();
            }

            // Limpiar contenedor
            if ($("#discover-container .project-card").length > 0) {
                deleteCard();
                deleteData();
            }

            if (projectsData[0]) {
                const newCard = await createCard(projectsData[0]);
                addCardEvents(newCard);
                $("#discover-container").append(newCard);
            }
        });
    }

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
        sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} intento eliminar carta pero no habia ninguna`);
        return false;
    }
    cardDoom.remove();
    sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} eliminó la carta visible`);
    return true;
}

async function readDB() {
    const excludeIds = projectsShows.join(',');
    await fetch(`includes/load-cards.php?exclude_projects=${projectsShows}`)
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                showNotification("error", err.error);
                sendLog(`Error fetch proyectos: ${err.error}`);
            });
        }
        return response.json();
    })
    .then(projects => {
        if (projects.length === 0) return;

        const newProject = projects[0];
        projectsData.push(newProject);
        projectsShows.push(newProject.id_project);
        sendLog(`Usuario <?php echo json_encode($user['name']); ?> cargó proyecto ${newProject.id_project}`);
    });
}

for (let i = projectsData.length; i < 3; i++) {
    await readDB();
}

async function loadCard() {

    if (projectsData.length === 0) {
        const finalCard = createCard(projectsData[0]);
        $("#discover-container").append(finalCard);
        sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} no tiene proyectos disponibles`);
        return;
    }

    if ($("#discover-container .project-card").length > 0) {
        deleteCard();
        deleteData();
    }

    const cardDoom = createCard(projectsData[0]);
    addCardEvents(cardDoom); 
    $("#discover-container").append(cardDoom);
    readDB();


    return;
}

function addCardEvents(card) {
    card.find(".like").on("click", () => handleAction(card, "like"));
    card.find(".nope").on("click", () => handleAction(card, "nope"));
}

function handleAction(card, action) {
    card.addClass(action === "like" ? "swipe-right" : "swipe-left");
    sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} swiped ${action} en proyecto ${projectsData[0].id_project}`);

    setTimeout(() => {
        loadCard();
    }, 400);

    if (action === "like") {
        showNotification("info","💖 Match! Anar al xat");
    }
}

sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} abrió la página Discover`);
loadCard();
</script>
</body>
</html>
