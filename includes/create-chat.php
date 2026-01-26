<?php
session_start();
header('Content-Type: application/json');
include("database.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

if (!isset($_POST['id_project'])) {
    http_response_code(400);
    echo json_encode(["error" => "id_project no proporcionado"]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    $id_project = (int)$_POST['id_project'];

    // Obtener el dueño del proyecto
    $stmt = $pdo->prepare("SELECT id_owner FROM projects WHERE id = ?");
    $stmt->execute([$id_project]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        echo json_encode(["error" => "Proyecto no encontrado"]);
        exit;
    }

    $owner_id = $project['id_owner'];

    // No crear chat si el usuario es el owner
    if ($owner_id == $user_id) {
        echo json_encode(["error" => "No puedes chatear contigo mismo"]);
        exit;
    }

    // Comprobar si ya existe el chat
    $stmt = $pdo->prepare("
        SELECT id FROM chats 
        WHERE user_owner = :owner_id AND other_user = :user_id AND id_project = :id_project
    ");
    $stmt->execute([
        ':owner_id' => $owner_id,
        ':user_id' => $user_id,
        ':id_project' => $id_project
    ]);

    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($chat) {
        http_response_code(200);
        echo json_encode(["success" => true, "id_chat" => $chat['id'], "message" => "Chat existente"]);
        exit;
    }

    // Crear chat
    $stmt = $pdo->prepare("
        INSERT INTO chats (user_owner, other_user, id_project) 
        VALUES (:owner_id, :user_id, :id_project)
    ");
    $stmt->execute([
        ':owner_id' => $owner_id,
        ':user_id' => $user_id,
        ':id_project' => $id_project
    ]);
    
    http_response_code(200);
    $new_chat_id = $pdo->lastInsertId();
    echo json_encode(["success" => true, "id_chat" => $new_chat_id, "message" => "Chat creado"]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error en la base de datos"]);
}
?>