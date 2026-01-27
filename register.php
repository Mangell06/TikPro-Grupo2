<?php
session_start();
if (isset($_GET['validate'])) {
    include("includes/database.php");
    if (!isset($_SESSION['notifications'])) {
        $_SESSION['notifications'] = [];
    }
    $code = $_GET['validate'];

    $stmt = $pdo->prepare("SELECT id, code_expire FROM users WHERE code_activate = ? AND is_active = 0");
    $stmt->execute([$code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $now = new DateTime();
        $expire = new DateTime($user['code_expire']);

        if ($now <= $expire) {
            $stmt = $pdo->prepare("UPDATE users SET is_active = 1, code_activate = NULL, code_expire = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);
            $_SESSION['notifications'][] = [
                "message" => "Compte verificat correctament",
                "type" => "info"
            ];
        } else {
            $_SESSION['notifications'][] = [
                "message" => "El codi ha expirat",
                "type" => "error"
            ];
        }
    } else {
        $_SESSION['notifications'][] = [
            "message" => "Codi invàlid o usuari ja verificat",
            "type" => "warning"
        ];
    }
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta d'usuari</title>
    <link rel="stylesheet" href="/styles.css?q=1">
    <link rel="icon" href="oak_4986983.png" type="image/png">
</head>
<body id="register-body">
<header class="main-header">
    <h1 class="header-title">SIMBIO</h1>
</header>

<div id="register-container">
    <h2 id="login-title">Registrar un nou compte</h2>

    <form id="register-form" method="post" enctype="multipart/form-data">
        <label for="input-name">Nom complet</label>
        <input id="input-name" type="text" name="input-name" placeholder="Pere" required>

        <label for="input-email">Email</label>
        <input id="input-email" type="email" name="input-email" placeholder="usuari@domain.dom" required>

        <label for="input-tfn">Telèfon</label>
        <input id="input-tfn" type="text" name="input-tfn" placeholder="+34675432891" required>

        <label for="input-password">Contrasenya</label>
        <input id="input-password" type="password" name="input-password" placeholder="**********" required>

        <label for="input-poblation">Població</label>
        <input id="input-poblation" type="text" placeholder="Barcelona" name="input-poblation" required>

        <label for="input-entity-name">Nom d'entitat</label>
        <input id="input-entity-name" type="text" name="input-entity_name" placeholder="Google" required>

        <label for="input-image-usuari">Imatge usuari</label>
        <input id="input-image-usuari" type="file" name="input-image-usuari" placeholder="Google" required>

        <label for="input-entity-type">Tipus d'entitat</label>
        <select id="input-entity-type" name="input-entity_type" required>
            <option value="center">Centre</option>
            <option value="company">Empresa</option>
        </select>

        <div id="view-categories"></div>
        <button id="open-categories-btn" class="buttonEtiquetes" type="button">Obrir categories</button>

        <label for="input-presentation">Presentació (opcional)</label>
        <textarea id="input-presentation" name="input-presentation"></textarea>

        <button id="register-button" type="submit">Registrar</button>

        <a id="register-button" href="login.php">Tinc un conta</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="module">
import { showNotification } from '/notificaciones.js';
import { sendLog } from '/create-logs.js';
import { loadNotifications } from './load-notifications.js';

loadNotifications();

let categoriaSeleccionada = [];
let seleccionTemporal = null;

function updateCategoriesView() {
    const $container = $('#view-categories');
    $container.empty();

    categoriaSeleccionada.forEach((cat, index) => {
        const $tag = $(`
            <div class="category-tag">
                <span>${cat}</span> 
                
                <span class="remove-cat" data-index="${index}">×</span>
            </div>
        `);
        $container.append($tag);
    });

    const count = categoriaSeleccionada.length;
    $('#open-categories-btn').text(count > 0 ? `Categories: ${count} seleccionades` : 'Obrir categories');
}

$(document).on('click', '.remove-cat', function() {
    const index = $(this).data('index');
    categoriaSeleccionada.splice(index, 1); // Eliminamos del array
    updateCategoriesView(); // Refrescamos la vista
});

function loadCategories(query = '') {
    const categoryData = {
        searchcat: query,
        excludecategory: categoriaSeleccionada.join(',') 
    };

    fetch('includes/search-category.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ 
            'categoryData[searchcat]': categoryData.searchcat,
            'categoryData[excludecategory]': categoryData.excludecategory
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const html = data.categories.map(c => `
                <div class="category-item" 
                     data-name="${c.name}">
                    ${c.name}
                </div>
            `).join('');
            $('#extra-content').html(html || '<div>No hi ha categories disponibles</div>');
        }
    })
    .catch(err => console.error("Error carregant categories:", err));
}

$('#open-categories-btn').on('click', function() {
    sendLog(`Usuario abrió el selector de categorías`);
    const $overlay = $('<div id="modal-overlay"></div>').css({
        position: 'fixed', top: 0, left: 0, width: '100%', height: '100%',
        background: 'rgba(0,0,0,0.5)', zIndex: 9998
    });

    const $modal = $(`
        <div id="categories-modal">
            <button id="close-modal">✖</button>
            <h3>Categories</h3>
            <input type="text" id="category-input" placeholder="Escriu per filtrar..." />
            <div id="extra-content" ></div>
            <button id="save-category" class="buttonEtiquetes">Guardar</button>
        </div>
    `).css({
        position: 'fixed', top: '50%', left: '50%', transform: 'translate(-50%, -50%)',
        background: '#fff', padding: '20px', width: '300px', borderRadius: '8px',
        boxShadow: '0 2px 10px rgba(0,0,0,0.3)', zIndex: 9999
    });

    // 2. Añadir al DOM
    $('body').append($overlay).append($modal);
    
    // 3. Cargar categorías iniciales
    loadCategories('');

    // Evento de filtrado
    $('#category-input').on('input', function (e) {
        const value = e.target.value.trim();
        loadCategories(value.length < 3 ? '' : value);
    });

    // Manejo de selección VISUAL
    $(document).off('click', '.category-item').on('click', '.category-item', function() {
        $('.category-item').css('background', 'transparent');
        $(this).css('background', '#d1e7dd');
        seleccionTemporal = $(this).data('name'); 
    });

    // Función para cerrar
    const closeModal = () => {
        $modal.remove();
        $overlay.remove();
        seleccionTemporal = null;
    };

    $overlay.on('click', closeModal);
    $modal.find('#close-modal').on('click', closeModal);

    // Acción de GUARDAR
    $modal.find('#save-category').on('click', function() {
        if (seleccionTemporal) {
            categoriaSeleccionada.push(seleccionTemporal);
            updateCategoriesView();
        }
        closeModal();
    });
});

function sendRegister(formDataObject) {
    fetch('includes/register-user.php', {
        method: 'POST',
        body: formDataObject // Enviem directament el FormData
    })
    .then(res => res.json())
    .then(res => {
        if (res.error) {
            showNotification('error', res.error);
            $("#register-button").prop('disabled', false);
        } else {
            showNotification('info', 'Usuari registrat! Revisa el correu.');
            window.location.href = 'login.php';
        }
    })
    .catch(err => {
        showNotification('error', "Error de connexió");
        $("#register-button").prop('disabled', false);
    });
}

$('#register-form').on('submit', (e) => {
    e.preventDefault();
    let isValid = true;
    const formData = new FormData(e.target);

    const name = formData.get('input-name');
    if (name.length  > 12 || name.length  < 3) {
        showNotification("warning","El nombre ha de ser menor a 13 caràcters i major a 3 caràcters");
        sendLog(`Intento de registro fallido: Nombre fuera de rango (${name})`);
        isValid = false;
    }

    const email = formData.get('input-email');
    let countPointEmail = 0;
    let isDomain = false;
    for (const characterEmail of email) {
        if (characterEmail === "." && isDomain) countPointEmail ++;
        if (characterEmail === "@") isDomain = true;
    }
    if (countPointEmail !== 1) {
        showNotification("warning","En el correu ha d'haver-hi una extensió");
        isValid = false;
    }

    const tfn = formData.get('input-tfn');
    let onlyNumbers = true;
    for (const num of tfn.slice(1)) {
        if (num < "0" || num > "9") onlyNumbers = false;
    }

    if (!onlyNumbers || tfn[0] !== "+" || tfn.length < 12 || tfn.length > 13) {
        showNotification("warning","El nombre del telèfon ha de tenir aquest format (+34675842021, +321675842021 o +04675842021)");
        sendLog(`Intento de registro fallido: Formato de teléfono no válido (${tfn})`);
        isValid = false;
    }

    const password = formData.get('input-password');
    if (password.length < 8) {
        showNotification("warning","La contrasenya ha de fer més de 8 caràcters de llargada");
        sendLog(`Intento de registro fallido: Contraseña no cumple criterios de seguridad para el email ${email}`);
        isValid = false;
    }

    // Inicializar flags
    let hasUpper = false;
    let hasLower = false;
    let hasNumber = false;
    let hasSpecial = false;

    // Lista de caracteres especiales permitidos
    const specialChars = "!@#$%^&*(),.?\":{}|<>";

    // Comprobar cada carácter
    for (let i = 0; i < password.length; i++) {
        const c = password[i];
        if (c >= 'A' && c <= 'Z') hasUpper = true;
        else if (c >= 'a' && c <= 'z') hasLower = true;
        else if (c >= '0' && c <= '9') hasNumber = true;
        else if (specialChars.indexOf(c) !== -1) hasSpecial = true;
    }

    // Mostrar notificaciones según falten criterios
    if (!hasUpper){
        showNotification("warning", "La contrasenya ha de tindre almenys una lletra majúscula");
        isValid = false;
    }
    if (!hasLower) {
        showNotification("warning", "La contrasenya ha de tindre almenys una lletra minúscula");
        isValid = false;
    }
    if (!hasNumber){
        showNotification("warning", "La contrasenya ha de tindre almenys un número");
        isValid = false;
    } 
    if (!hasSpecial) {
        showNotification("warning", "La contrasenya ha de tindre almenys un caràcter especial");
        isValid = false;
    } 

    const poblation = formData.get('input-poblation');
    if (poblation.length > 22 || poblation.length < 3) {
        showNotification("warning","La població hi ha de menor a 18 caràcters i major a 3 caràcters");
        isValid = false;
    }

    const entity_name = formData.get('input-entity_name');
    if (entity_name.length  > 22 || entity_name.length  < 3) {
        showNotification("warning","El nom de l'entitat ha de ser menor a 18 caràcters i major a 3 caràcters");
        isValid = false;
    }

    const entity_type = formData.get('input-entity_type');

    let presentation = false;

    const imageFile = formData.get('input-image-usuari');
    let logo = null;
    if (imageFile && imageFile.size > 0 && imageFile.type.startsWith("image/")) {
        logo = imageFile;
    }
    if (imageFile && imageFile.size > 0 && !(imageFile.type.startsWith("image/"))) {
        showNotification("warning","Solo puedes subir imagenes");
        return;
    }


    if (formData.get('input-presentation').trim() !== "") {
        presentation = formData.get('input-presentation');
    }

    // Substitueix la part final del teu $('#register-form').on('submit', ...)
    if (isValid) {
        if (categoriaSeleccionada.length === 0) {
            showNotification("warning", "Has de seleccionar almenys una categoria");
            return;
        }

        $("#register-button").prop('disabled', true);
        
        // 1. Creem el FormData
        const formDataEnviar = new FormData();
        
        // 2. Afegim les dades de text (com a JSON, tal com ho tenies)
        const registerData = {
            username: name,
            email: email,
            tfn: tfn,
            password: password,
            population: poblation,
            nameentity: entity_name,
            typeentity: entity_type,
            categories: categoriaSeleccionada,
            presentation: formData.get('input-presentation').trim()
        };
        formDataEnviar.append('registerData', JSON.stringify(registerData));

        // 3. Afegim el FITXER FÍSIC
        const imageInput = document.getElementById('input-image-usuari');
        if (imageInput.files[0]) {
            formDataEnviar.append('input-image-usuari', imageInput.files[0]);
        }

        // 4. Cridem a la funció (la modificarem a continuació)
        sendRegister(formDataEnviar);
    }
});
</script>
</body>
</html>