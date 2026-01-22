<?php
session_start();
header('Content-Type: application/json');

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

// Validar que message y type existan
if (!isset($_POST['message']) || trim($_POST['message']) === '') {
    http_response_code(400);
    echo json_encode(["error" => "Mensaje inválido"]);
    exit;
}

if (!isset($_POST['type']) || !in_array($_POST['type'], ['success', 'error', 'warning'])) {
    http_response_code(400);
    echo json_encode(["error" => "Tipo de notificación inválido"]);
    exit;
}

// Crear el array de notificaciones si no existe
if (!isset($_SESSION['notifications'])) $_SESSION['notifications'] = [];

$exists = isset($_POST["exist"]) && $_POST["exist"] === 'true';

if (!$exists) {
    $_SESSION['notifications'][] = [
        "message" => $_POST['message'],
        "type" => $_POST['type']
    ];
}

echo json_encode([
    "success" => true,
    "count" => count($_SESSION['notifications'])
]);
?>