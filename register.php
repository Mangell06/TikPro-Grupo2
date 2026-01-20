<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta d'usuari</title>
    <link rel="stylesheet" href="/styles.css">
    <link rel="icon" href="icono-simbio.png" type="image/png">
</head>
<body id="register-body">
<header class="main-header">
    <h1 class="header-title">SIMBIO - Alta d'usuari</h1>
</header>

<div id="register-container">
    <h2 id="register-title">Registrar un nou compte</h2>

    <form id="register-form" method="post" enctype="multipart/form-data">
        <label for="input-name">Nom complet</label>
        <input id="input-name" type="text" name="name" placeholder="Pere" required>

        <label for="input-email">Email</label>
        <input id="input-email" type="email" name="email" placeholder="usuari@domain.dom" required>

        <label for="input-tfn">Telèfon</label>
        <input id="input-tfn" type="text" name="tfn" placeholder="+34675432891" required>

        <label for="input-password">Contrasenya</label>
        <input id="input-password" type="password" name="password" placeholder="**********" required>

        <label for="input-poblation">Població</label>
        <input id="input-poblation" type="text" placeholder="Barcelona" name="poblation" required>

        <label for="input-entity-name">Nom de l'entitat</label>
        <input id="input-entity-name" type="text" name="entity_name" placeholder="Google" required>

        <label for="input-entity-type">Tipus d'entitat</label>
        <select id="input-entity-type" name="entity_type" required>
            <option value="center">Centre</option>
            <option value="company">Empresa</option>
        </select>

        <label for="input-logo">Logo (opcional)</label>
        <input id="input-logo" type="file" name="logo_image" accept="image/*">

        <label for="input-presentation">Presentació (opcional)</label>
        <textarea id="input-presentation" name="presentation"></textarea>

        <button id="register-button" type="submit">Registrar</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="module">
import { showNotification } from '/notificaciones.js';
import { sendLog } from '/create-logs.js';

$('#register-form').on('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const name = formData.get('name');
    if (name > 12 || name < 3) {
        showNotification("warning","El nombre te que ser menor a 13 carecters y major a 3 caracters");
    }
    const email = formData.get('email');
    let countPointEmail = 0;
    for (const characterEmail of email) {
        if (characterEmail === ".") countPointEmail ++;
    }
    if (countPointEmail !== 1) {
        showNotification("warning","En el correu te que haber una extensió");
    }
    const tfn = formData.get('tfn');
    let onlyNumbers = true;
    for (const num of tfn.slice(1,-1)) {
        if (num > 0 || num < 9) {
            onlyNumbers = false;
            break;
        }
    }
    if (!onlyNumbers || tfn[0] !== "+") {
        showNotification("warning","El nombre de telefon te aquest format (+34675842021)");
    }
    const password = formData.get('password');
    const poblation = formData.get('poblation');
    const entity_name = formData.get('entity_name');
    const entity_type = formData.get('entity_type');
    const logo_image = formData.get('logo_image'); // tipo File
    const presentation = formData.get('presentation');

    console.log({ name, email, tfn, password, poblation, entity_name, entity_type, logo_image, presentation });

});
</script>
</body>
</html>