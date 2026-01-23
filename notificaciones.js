import { sendLog } from './create-logs.js';

let notificationCount = 0;
export function showNotification(type, message, username='', exist=false) {
    //Sumar la cantidad de notificaciones mostradas
    notificationCount++;
    // Crear contenedor
    const notif = document.createElement('div');
    notif.className = `notification ${type} show`;
    //Animación de crear el margin, cuando hay más notificaciones
    if (notificationCount > 1){
        notif.style.marginTop = 100*notificationCount+"px";
    }
    // Texto
    const divNoti = document.createElement('div');
    divNoti.classList.add("notification-section");
    const text = document.createElement('span');
    text.textContent = message;
    notif.appendChild(divNoti);
    divNoti.appendChild(text);

    // Botón de cerrar
    const closeBtn = document.createElement('button');
    closeBtn.className = 'close-btn';
    closeBtn.innerHTML = '&times;';
    closeBtn.onclick = async function () {
        notif.remove();
        notificationCount--;
        if (username !== "") {
            sendLog(`Usuario ${username} a cerrat la notificació`);
        } else {
            sendLog(`El usuario a cerrat la notificació`);
        }
        await fetch('includes/delete-notifications-session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                console.warn('No se pudo borrar la notificación en el servidor:', data.error);
            }
        })
        .catch(err => console.error('Error borrando la notificación:', err));
    };
    notif.appendChild(closeBtn);

    // Añadir al body
    document.body.appendChild(notif);

    fetch('includes/create-notifications-session.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ message: message, type: type, exist: exist })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            console.warn('No se pudo registrar la notificación en el servidor:', data.error);
        }
    })
    .catch(err => console.error('Error enviando la notificación:', err));
   
}