// create-logs.js
export function sendLog(text) {
    fetch('logs/logs.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ text })
    }).catch(err => console.error('Error log:', err));
}