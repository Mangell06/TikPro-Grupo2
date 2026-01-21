<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

if (!isset($_POST['message']) || trim($_POST['message']) === '') {
    http_response_code(400);
    echo json_encode(["error" => "Mensaje inválido"]);
    exit;
}

if (!isset($_SESSION['notifications']) || !is_array($_SESSION['notifications']) || count($_SESSION['notifications']) === 0) {
    echo json_encode(["success" => true, "count" => 0]);
    exit;
}

$messageToDelete = trim($_POST['message']);

// Buscar la notificación con ese mensaje
foreach ($_SESSION['notifications'] as $index => $notif) {
    if (isset($notif['message']) && $notif['message'] === $messageToDelete) {
        array_splice($_SESSION['notifications'], $index, 1);
        break; // solo eliminar la primera coincidencia
    }
}

echo json_encode([
    "success" => true,
    "count" => count($_SESSION['notifications'])
]);
?>