<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

if (!isset($_POST['data_message']) || !is_array($_POST['data_message'])) {
    http_response_code(400);
    echo json_encode(["error" => "Datos inválidos"]);
    exit;
}

try {
    include("database.php");

    $user_id = $_SESSION['user_id'];
    $data = $_POST['data_message'];

    if (!isset($data['id_chat']) || !isset($data['message'])) {
        http_response_code(400);
        echo json_encode(["error" => "Faltan datos necesarios"]);
        exit;
    }

    $id_chat = (int)$data['id_chat'];
    $text_message = trim($data['message']);

    if ($text_message === "") {
        http_response_code(400);
        echo json_encode(["error" => "Mensaje vacío"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO messages (id_chat, sender, text_message, date_message, read_status)
        VALUES (:id_chat, :sender, :text_message, NOW(), 0)
    ");

    $stmt->execute([
        ':id_chat' => $id_chat,
        ':sender' => $user_id,
        ':text_message' => $text_message
    ]);

    echo json_encode(["success" => true, "message" => "Mensaje enviado"]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de base de datos"]);
}
?>