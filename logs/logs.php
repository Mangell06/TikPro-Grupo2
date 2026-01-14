<?php
// Carpeta de logs
$logDir = ".";

// Obtener la fecha para el archivo diario
$today = date('Y-m-d');
$logFile = $logDir . "/$today.txt";

// Obtener datos de la petición
$input = json_decode(file_get_contents('php://input'), true);
$text  = isset($input['text']) ? trim($input['text']) : '';

// Obtener la IP del usuario
$ip = $_SERVER['REMOTE_ADDR'] ?? 'IP desconocida';

// Preparar línea de log
$time = date('H:i:s');
$line = "[$time] [$ip] $text" . PHP_EOL;

// Guardar en el archivo
file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);

// Devolver respuesta JSON
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'line' => $line]);