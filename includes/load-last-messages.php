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
                m.id,
                m.sender,
                m.destination,
                u.logo_image,
                u.name AS nameuser,
                m.text_message,
                m.date_message,
                m.read_status,
                p.title AS projectname,
                u_sender.name as sendername,
                u_sender.id as senderid
            FROM messages m
            INNER JOIN projects p ON m.id_project = p.id
            JOIN users u_sender ON u_sender.id = m.sender
            INNER JOIN users u ON u.id = CASE WHEN m.sender = ? THEN m.destination ELSE m.sender END
            INNER JOIN (
                SELECT  
                    id_project, 
                    LEAST(sender, destination) AS user1,
                    GREATEST(sender, destination) AS user2, 
                    MAX(date_message) AS last_date
                FROM messages 
                WHERE ? IN (sender, destination)
                GROUP BY id_project, LEAST(sender, destination), GREATEST(sender, destination)
            ) last_msgs 
            ON m.id_project = last_msgs.id_project 
            AND LEAST(m.sender, m.destination) = last_msgs.user1
            AND GREATEST(m.sender, m.destination) = last_msgs.user2 
            AND m.date_message = last_msgs.last_date
            ORDER BY m.date_message DESC;";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ultimosmensajes = [];
    foreach ($rows as $row) {
        $sendername = $row['senderid'] === $user_id ? "yo" : $row['sendername'];

        $ultimosmensajes[] = [
            "logo_image"   => $row['logo_image'],
            "id_messages"   => $row['id'],
            "user_name"    => $row['nameuser'],
            "text_message" => $row['text_message'],
            "date_message" => $row['date_message'],
            "read_status"  => $row['read_status'],
            "project_name" => $row['projectname'],
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
