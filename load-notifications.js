import { showNotification } from './notificaciones.js';

// Función para cargar todas las notificaciones guardadas en sesión
export async function loadNotifications() {
    try {
        const res = await fetch('includes/load-notifications.php');
        const data = await res.json();

        if (Array.isArray(data.notifications)) {
            let newData = [];
            data.notifications.forEach(notif => {
                if (notif && typeof notif.type === 'string' && typeof notif.message === 'string') {
                    showNotification(notif.type, notif.message, "", true);
                }
            });
        }
    } catch (err) {
        console.error('Error cargando notificaciones:', err);
    }
}
