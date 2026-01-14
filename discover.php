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
    let projectsData = [];

    // funcion para crear elementos.
    function createElement(tag, parent = "", className = "", attr = {}) {
        // crear el elemento.
        const $element = $(tag);

        // añadir clase si tiene.
        if (className) {
            $element.addClass(className);
        }

        // añadir atributos si tiene.
        if (attr && typeof attr === "object") {
            for (const key in attr) {
                $element.attr(key, attr[key]);
            }
        }

        // añadir al padre si tiene.
        if (parent) {
            $(parent).append($element);
        }

        // devolver el elemento creado.
        return $element;
    }

    // funcion para crear cartas
    function createCard(projectData) {
        // Crear el div de la carta
        const divCard = createElement("<div></div>", "", "project-card");
        
        // Añadir el video del proyecto
        createElement("<video></video>", divCard, "", { 
            src: projectData.video,
            controls: true,
            muted: true,
            autoplay: true,
            loop: true,
            playsinline: true 
        });

        // Crear botón de toggle info (siempre visible)
        const infoButton = createElement("<button></button>", divCard, "info-toggle").text("Mostrar info");
        
        // Evento para mostrar/ocultar divInfo
        infoButton.on("click", () => {
            divInfo.toggleClass("hidden");
            // Cambiar texto según estado
            infoButton.text(divInfo.hasClass("hidden") ? "Mostrar info" : "Ocultar info");
        });

        // Crear el div de información (inicialmente oculto)
        const divInfo = createElement("<div></div>", divCard, "project-info hidden");

        // Añadir párrafo de descripción
        createElement("<p></p>", divInfo).text(projectData.description);

        // Crear div para tags
        const divTags = createElement("<div></div>", divInfo, "tags");

        // Añadir cada tag
        (projectData.tags || []).forEach(tag => {
            createElement("<span></span>", divTags).text(tag);
        });

        // Crear div de botones principales
        const divButtons = createElement("<div></div>", divCard, "actions");

        // Botones de like/nope
        createElement("<button></button>", divButtons, "nope").text("Like");
        createElement("<button></button>", divButtons, "like").text("Nope");

        return divCard;
    }

    /* Funcion para eliminar Informacion */
    function deleteData() {
        if (projectsData.length !== 0) {
            projectsData.shift();
            return true;
        }
        return false;
    }

    /* Funcion para eliminar Cartas */
    function deleteCard() {
        // Selecciona la carta visible (solo hay una)
        const cardDoom = $("#discover-container .project-card");

        if (!cardDoom.length) {
            showNotification("error","No hi ha cap projecte");
            return false;
        } // no hay carta

        // Eliminar la carta actual
        cardDoom.remove();
        return true;        
    }

    async function readDB() {
        // tomar la id del primer proyecto mostrado (si existe)
        const lastId = projectsData.length ? projectsData[0].id_project : 0;

        await fetch(`includes/load-cards.php?exclude_id=${lastId}`)
        .then(response => {
            // ERROR: mostrar y cortar
            if (!response.ok) {
                    return response.json().then(err => {
                    showNotification("error",err.error);
                });
            }

            // OK → continuar
            return response.json();
        })
        .then(projects => {
            const newProject = projects; // tu fetch devuelve un objeto único
            // comprobar si ya está en projectsData
            const exists = projectsData.some(p => p.id_project === newProject.id_project);
            if (!exists) {
                projectsData.push(newProject);
            }
        });
    }

    // funcion para cargar siguiente carta.
    async function loadCard() {
        console.log(projectsData);
        while (projectsData.length-1 < 3) {
            await readDB(); // esperar a que projectsData se llene
        }

        if (projectsData.length === 0) {
            showNotification("error","No hi ha projectes");
            return false;
        }

        // eliminar carta anterior si existe
        if ($("#discover-container .project-card").length > 0) {
            deleteCard();
            deleteData();
        }

        // crear carta solo después de que projectsData tenga datos
        const cardDoom = createCard(projectsData[0]);
        addCardEvents(cardDoom); 
        $("#discover-container").append(cardDoom);

        return true;
    }

    function addCardEvents(card) {
        card.find(".like").on("click", () => handleAction(card, "like"));
        card.find(".nope").on("click", () => handleAction(card, "nope"));
    }

    // Accion al darle Like a una carta.
    function handleAction(card, action) {
        card.addClass(action === "like" ? "swipe-right" : "swipe-left");

        setTimeout(() => {
            if (!loadCard()) showNotification("error","No s'ha pogut carregar el seguent projecte");
        }, 400);

        if (action === "like") {
            showNotification("info","💖 Match! Anar al xat");
        }
    }

    loadCard();
</script>
</body>
</html>
