let notificationCount = 0;
export function showNotification(type, message) {
    //Sumar la cantidad de notificaciones mostradas
    notificationCount++;
    // Crear contenedor
    const notif = document.createElement('div');
    notif.className = `notification ${type} show`;
    //Animación de crear el margin, cuando hay más notificaciones
    if (notificationCount > 1){
        notif.style.marginTop = 5*notificationCount+"px";
    }
    // Texto
    const text = document.createElement('span');
    text.textContent = message;
    notif.appendChild(text);

    // Botón de cerrar
    const closeBtn = document.createElement('button');
    closeBtn.className = 'close-btn';
    closeBtn.innerHTML = '&times;';
    closeBtn.onclick = () => {
        notif.remove();
        notificationCount--;
    };
    notif.appendChild(closeBtn);

    // Añadir al body
    document.body.appendChild(notif);
}