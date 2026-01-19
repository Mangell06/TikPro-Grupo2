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
        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = :iduser");
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
import { createElement } from './createElement.js';
import { sendLog } from './create-logs.js'; // importar función de logging

// preventDefault F5
$(document).on("keydown", function(e) {
    if ((e.which || e.keyCode) == 116 || ((e.ctrlKey || e.metaKey) && (e.which || e.keyCode) == 82)) {
        e.preventDefault();
        console.log("Refresh prevented");
    }
});

// preventDefault F5
$(document).on("keydown", function(e) {
    if ((e.which || e.keyCode) == 116 || ((e.ctrlKey || e.metaKey) && (e.which || e.keyCode) == 82)) {
        e.preventDefault();
        console.log("Refresh prevented");
    }
});

const currentUser = <?php echo json_encode($user['name']); ?>;
let projectsData = [];
let projectsShows = []

// funcion para crear elementos.


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
    
    
    const ancoreDiv = createElement("<div></div>", mother, "divAncore");
    const profile = createElement("<a href='profile.php'></a>", ancoreDiv, "ancore").text("Perfil");
    const chats = createElement("<a href='chats.php'></a>", ancoreDiv, "ancore").text("Chat");
    const infoButton = createElement("<button></button>", ancoreDiv, "ancore").text("Detalls");
    
    const divInfo = createElement("<div></div>", mother, "project-info hiddenSuave");
    
    const infoButtonClick = () => {
        divInfo.toggleClass("hiddenSuave");
        mother.toggleClass("allInfoDiv");
        divCard.toggleClass("dimLight");
        sendLog(`Usuario ${<?php echo json_encode($user['username']); ?>} toggle info: ${divInfo.hasClass("hiddenSuave") ? 'oculto' : 'visible'}`);
    }

    const infoButtonClose = createElement("<button></button>", divInfo, "info-toggle").text("Amagar detalls");
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

            btnNope.on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} presionó Nope en el proyecto con id ${projectData.id_project}`));
            btnLike.on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} presionó Like en el proyecto con id ${projectData.id_project}`));
        } else {
            const btnNext = createElement("<button></button>", divButtons, "nope").text("Seguent");
            btnNext.on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} presionó next en el proyecto con id ${projectData.id_project}`));

        }

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
            projectsData = [];
            for (let i = projectsData.length; i < 3; i++) {
                await readDB();
            }
            loadCard();
            sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} vuelve a ver los projectos`);
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
    await fetch(`includes/load-cards.php?exclude_projects=${projectsShows}`, {
    method: "POST",
    headers: {
        "Content-Type": "application/x-www-form-urlencoded"
    },
    body: new URLSearchParams({
        exclude_projects: projectsShows.join(",")
    })
    }).then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                showNotification("error", err.error);
                sendLog(`Error: ${err.error}`);
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
    if ($("#discover-container .project-card").length > 0) {
        deleteCard();
    }
    if (projectsData.length === 0) {
        await readDB();  // cargar al menos un proyecto
        if (projectsData.length === 0) {
            const finalCard = createCard(null); // carta final
            $("#discover-container").append(finalCard);
            sendLog(`Usuario ${currentUser} no tiene proyectos disponibles`);
            return;
        }
    }

    // Obtener el estado like del primer proyecto
    const liked = await isLike(projectsData[0].id_project);

    // Crear la carta con los datos correctos
    const cardDoom = createCard(projectsData[0], liked);
    addCardEvents(cardDoom);
    $("#discover-container").append(cardDoom);

    deleteData();
    // Leer más proyectos para mantener el buffer (asíncrono)
    readDB();
}

function addCardEvents(card) {
    card.find(".like").on("click", () => handleAction(card, "like"));
    card.find(".nope").on("click", () => handleAction(card, "nope"));
}

async function handleAction(card, action) {
    card.addClass(action === "like" ? "swipe-right" : "swipe-left");
    if (projectsData.length > 0) {
        sendLog(`Usuario ${currentUser} swiped ${action} en proyecto ${projectsData[0].id_project}`);
    }

    setTimeout(() => {
        loadCard();
    }, 400);

    if (action === "like" && projectsData[0]) {
        showNotification("info","💖 Match! Anar al xat");
        try {
            const res = await fetch("includes/like-cards.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ project: projectsData[0].id_project })
            });
            if (!res.ok) {
                const err = await res.json();
                showNotification("error", err.error);
                sendLog(`Error: ${err.error}`);
            } else {
                sendLog(`Usuario ${currentUser} dio like al proyecto ${projectsData[0].title} con id ${projectsData[0].id_project}`);
            }
        } catch (e) {
            showNotification("error","Error enviando like");
        }
    }
}

async function isLike(projectId) {
    try {
        const response = await fetch("includes/check-like.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ project: projectId })
        });

        if (!response.ok) {
            const err = await response.json();
            throw new Error(err.error);
        }

        const data = await response.json();
        return data.exists === true; // devuelve true si el like ya existe

    } catch (err) {
        return false; // fallback
    }
}

showNotification("info","Benvingut, " + <?php echo json_encode($user['username']); ?>);
loadCard();
</script>
</body>
</html>
