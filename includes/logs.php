<?php
$logDir = "/var/www/logs";
$today = date('Y-m-d');
$logFile = $logDir . "/$today.txt";

// Leer JSON
$input = json_decode(file_get_contents('php://input'), true);
$text = isset($input['text']) ? trim($input['text']) : '';

// IP enviada por JS (NO confiable)
$clientIp = $input['client_ip'] ?? null;

// IP real del servidor (fallback)
$serverIp = $_SERVER['REMOTE_ADDR'] ?? 'IP_desconocida';

// Elegir IP
$ip = $clientIp ?: $serverIp;

// Log
$time = date('H:i:s');
$line = "[$time] [$ip] $text" . PHP_EOL;

$result = file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
if ($result === false) {
    error_log("Error escribiendo log en $logFile");
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'No se pudo escribir el log']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?>