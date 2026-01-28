// create-logs.js
let cachedIp = null;

async function getClientIp() {
    if (cachedIp) return cachedIp;

    try {
        const res = await fetch('https://api.ipify.org?format=json');
        const data = await res.json();
        cachedIp = data.ip;
        return cachedIp;
    } catch (e) {
        return 'IP_JS_desconocida';
    }
}

export async function sendLog(text) {
    const ip = await getClientIp();

    fetch('/logs/logs.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            text,
            client_ip: ip
        })
    }).catch(err => console.error('Error log:', err));
}