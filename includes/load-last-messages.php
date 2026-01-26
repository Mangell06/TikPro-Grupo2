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

    $sql = "SELECT 
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
    JOIN users u 
        ON CASE 
            WHEN ch.user_owner = :myid THEN ch.other_user 
            ELSE ch.user_owner 
        END = u.id
    JOIN projects p 
        ON p.id = ch.id_project
    JOIN messages m 
        ON m.id_chat = ch.id
    AND m.date_message = (
        SELECT MAX(m2.date_message)
        FROM messages m2
        WHERE m2.id_chat = ch.id
    )
    JOIN users su
    ON su.id = m.sender;";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ultimosmensajes = [];
    foreach ($rows as $row) {
        $sendername = $row['senderid'] === $user_id ? "yo" : $row['sendername'];

        $ultimosmensajes[] = [
            "logo_image"   => $row['logo_image'],
            "id_messages"   => $row['id_chat'],
            "user_name"    => $row['username'],
            "text_message" => $row['text_message'],
            "date_message" => $row['date_message'],
            "read_status"  => $row['read_status'],
            "project_name" => $row['project_name'],
            "sendername" => $sendername
        ];
    }

    echo json_encode($ultimosmensajes);

} catch (PDOException $err) {
    http_response_code(500);
    echo json_encode([]);
    exit;
}
?>
