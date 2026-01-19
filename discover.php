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
<main id="discover-container">
</main>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="module">
import { showNotification } from './notificaciones.js';
import { createElement } from './createElement.js';
import { sendLog } from './create-logs.js';

$(document).on("keydown", function(e) {
    if ((e.which || e.keyCode) == 116 || ((e.ctrlKey || e.metaKey) && (e.which || e.keyCode) == 82)) {
        e.preventDefault();
        console.log("Refresh prevented");
    }
});

$("#nav-logout").on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} a cerrado sesion`));

$(document).on("keydown", function(e) {
    if ((e.which || e.keyCode) == 116 || ((e.ctrlKey || e.metaKey) && (e.which || e.keyCode) == 82)) {
        e.preventDefault();
        console.log("Refresh prevented");
    }
});

const currentUser = <?php echo json_encode($user['name']); ?>;
let projectsData = [];
let projectsShows = []

function createCard(projectData) {
    const divCard = createElement("<div></div>", "", "project-card");

    if (!projectData) {
        divCard.addClass("final-card");

        const divInfo = createElement("<div></div>", divCard, "final-info-card");
        createElement("<p></p>", divInfo).text("No hi ha més videos per mostrar");
        createElement("<p></p>", divInfo).text("¿Vols tornar a veurels?");
        const btnTornar = createElement("<button></button>", divInfo).text("Torna");

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

        return divCard;
    }
    
    if (projectData.liked) {
        const star = createElement("<div></div>", divCard, "liked-star");
        star.text("★");
    }
    createElement("<video controls muted autoplay loop playsinline></video>", divCard, "", { 
        src: projectData.video
    });

    const mother = createElement("<div></div>", divCard, "allInfoDiv");

    const divButtons = createElement("<div></div>", mother, "actions");

    if (!projectData.liked) {
        const btnLike = createElement("<button></button>", divButtons, "like").text("M'agrada");
        const btnNope = createElement("<button></button>", divButtons, "nope").text("No m'interessa");

        btnNope.on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} presionó "No M'interessa" en el proyecto ${projectsData[0].title} con id ${projectsData[0].id_project}`));
        btnLike.on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} presionó "M'agrada" en el proyecto ${projectsData[0].title} con id ${projectsData[0].id_project}`));
    } else {
        const btnNext = createElement("<button></button>", divButtons, "nope").text("Següent");
        btnNext.on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} presionó "següent" en el proyecto ${projectsData[0].title} con id ${projectsData[0].id_project}`));
    }

    const title = createElement("<p></p>", mother,"project-title").text(projectData.title);
    createElement("<pre></pre>", mother).text(projectData.username +" - "+ projectData.entity_name);
    createElement("<p></p>", mother, "trunc").text(projectData.description);

    const ancoreDiv = createElement("<div></div>", mother, "divAncore");
    createElement("<a href='profile.php'></a>", ancoreDiv, "ancore").text("Perfil");
    createElement("<a href='chats.php'></a>", ancoreDiv, "ancore").text("Chat");
    const infoButton = createElement("<button></button>", ancoreDiv, "ancore").text("Detalls");
    infoButton.on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} presionó "Detalls" en el proyecto ${projectsData[0].title} con id ${projectsData[0].id_project}`));

    const divInfo = createElement("<div></div>", mother, "project-info hiddenSuave");
    const infoButtonClick = () => {
        divInfo.toggleClass("hiddenSuave");
        mother.toggleClass("allInfoDiv");
        divCard.toggleClass("dimLight");
        sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} toggle info: ${divInfo.hasClass("hiddenSuave") ? 'oculto' : 'visible'}`);
    }

    const infoButtonClose = createElement("<button></button>", divInfo, "info-toggle").text("Amagar detalls");
    infoButtonClose.on("click", () => sendLog(`Usuario ${<?php echo json_encode($user['name']); ?>} presionó "Amagar detalls" en el proyecto ${projectsData[0].title} con id ${projectsData[0].id_project}`));
    infoButton.on("click", infoButtonClick);
    infoButtonClose.on("click", infoButtonClick);

    createElement("<p></p>", divInfo,"project-title").text(projectData.title);
    createElement("<pre></pre>", divInfo).text(projectData.username +" - "+ projectData.entity_name);
    createElement("<p></p>", divInfo).text(projectData.description);
    createElement("<p></p>", divInfo, "bold").text("Categories: ");

    const divTags = createElement("<div></div>", divInfo, "tags");
    (projectData.tags || []).forEach(tag => {
        createElement("<span></span>", divTags).text(tag);
    });

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
        showNotification("error","No hi ha cap projecte",<?php echo json_encode($user['name']); ?>);
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
                showNotification("error", err.error,<?php echo json_encode($user['name']); ?>);
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

for (let i = projectsData.length; i < 2; i++) {
    await readDB();
}

async function loadCard() {
    if ($("#discover-container .project-card").length > 0) {
        deleteCard();
    }
    if (projectsData.length === 0) {
        const finalCard = createCard(null);
        $("#discover-container").append(finalCard);
        sendLog(`Usuario ${currentUser} no tiene proyectos disponibles`);
        return;
    }

    const cardDoom = createCard(projectsData[0]);
    addCardEvents(cardDoom);
    $("#discover-container").append(cardDoom);

    readDB();
}

function addCardEvents(card) {
    card.find(".like").on("click", () => handleAction(card, "like"));
    card.find(".nope").on("click", () => handleAction(card, "nope"));
}

async function handleAction(card, action) {
    card.addClass(action === "like" ? "swipe-left" : "swipe-right");
    setTimeout(() => {
        loadCard();
    }, 400);

   if (action === "like" && projectsData[0]) {
        try {
            const res = await fetch("includes/like-cards.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ 
                    project: projectsData[0].id_project,
                    liked: projectsData[0].liked ? 1 : 0
                })
            });
            if (!res.ok) {
                const err = await res.json();
                showNotification("error", err.error,<?php echo json_encode($user['name']); ?>);
                sendLog(`Error: ${err.error}`);
            } else {
                showNotification("info","💖 Match! Anar al xat",<?php echo json_encode($user['name']); ?>);
            }
        } catch (e) {
            showNotification("error","Error enviando like",<?php echo json_encode($user['name']); ?>);
        }
    }

    deleteData();
}

showNotification("info","Benvingut, " + <?php echo json_encode($user['name']); ?>,<?php echo json_encode($user['name']); ?>);
loadCard();
</script>
</body>
</html>
