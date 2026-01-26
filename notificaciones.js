import { sendLog } from './create-logs.js';

let notificationCount = 0;

const divFather = document.createElement('div');
divFather.classList.add('notiFather');
document.body.appendChild(divFather);
export function showNotification(type, message, username='', exist=false) {
    //Sumar la cantidad de notificaciones mostradas
    
    // Crear contenedor
    const notif = document.createElement('div');
    notif.className = `notification-section notification ${type} show`;
    //Animación de crear el margin, cuando hay más notificaciones
    
    notificationCount++;
    // Texto
    const text = document.createElement('span');
    text.textContent = message;
    notif.appendChild(text);
    // Append al padre
    divFather.appendChild(notif);

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