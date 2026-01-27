<?php
session_start();
include("database.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    $ultimosmensajes = [];

    // --- 1. CHATS CON MENSAJES (Conversaciones activas) ---
    $sqlConMensajes = "SELECT 
        ch.id AS id_chat,
        u.name AS username,
        u.logo_image,
        p.title AS project_name,
        m.date_message,
        m.text_message,
        m.read_status,
        m.sender AS senderid,
        su.name AS sendername
    FROM chats ch
    JOIN projects p ON p.id = ch.id_project
    JOIN users u ON (CASE WHEN ch.user_owner = :id1 THEN ch.other_user ELSE ch.user_owner END) = u.id
    JOIN messages m ON m.id_chat = ch.id
    JOIN users su ON su.id = m.sender
    WHERE (ch.user_owner = :id2 OR ch.other_user = :id3)
    AND m.date_message = (
        SELECT MAX(m2.date_message)
        FROM messages m2
        WHERE m2.id_chat = ch.id
    )";

    $stmt1 = $pdo->prepare($sqlConMensajes);
    $stmt1->execute(['id1' => $user_id, 'id2' => $user_id, 'id3' => $user_id]);
    
    while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
        $ultimosmensajes[] = [
            "logo_image"   => $row['logo_image'],
            "id_messages"  => $row['id_chat'],
            "user_name"    => $row['username'],
            "text_message" => $row['text_message'],
            "date_message" => $row['date_message'],
            "read_status"  => (int)$row['read_status'],
            "project_name" => $row['project_name'],
            "sendername"   => ($row['senderid'] == $user_id) ? "yo" : $row['sendername']
        ];
    }

    // --- 2. CHATS VACÍOS (Likes recibidos o Likes dados sin mensajes) ---
    $sqlVacios = "SELECT 
        ch.id AS id_chat,
        u.name AS username,
        u.logo_image,
        p.title AS project_name
    FROM chats ch
    JOIN projects p ON p.id = ch.id_project
    JOIN users u ON (CASE WHEN ch.user_owner = :id1 THEN ch.other_user ELSE ch.user_owner END) = u.id
    WHERE (ch.user_owner = :id2 OR ch.other_user = :id3)
    AND NOT EXISTS (SELECT 1 FROM messages WHERE messages.id_chat = ch.id)";

    $stmt2 = $pdo->prepare($sqlVacios);
    $stmt2->execute(['id1' => $user_id, 'id2' => $user_id, 'id3' => $user_id]);
    
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $ultimosmensajes[] = [
            "logo_image"   => $row['logo_image'],
            "id_messages"  => $row['id_chat'],
            "user_name"    => $row['username'],
            "text_message" => null,
            "date_message" => null,
            "read_status"  => 0,
            "project_name" => $row['project_name'],
            "sendername"   => null
        ];
    }

    // Ordenación final: Mensajes arriba, chats nuevos (nulos) abajo
    usort($ultimosmensajes, function($a, $b) {
        if ($a['date_message'] === $b['date_message']) return 0;
        if ($a['date_message'] === null) return 1;
        if ($b['date_message'] === null) return -1;
        return strcmp($b['date_message'], $a['date_message']);
    });

    header('Content-Type: application/json');
    echo json_encode($ultimosmensajes);

} catch (PDOException $err) {
    http_response_code(500);
    echo json_encode(["error" => "Error de base de datos", "detalle" => $err->getMessage()]);
}