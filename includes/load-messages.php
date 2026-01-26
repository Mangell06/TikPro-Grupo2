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
    $data = $_POST["data_message"];
    $id_chat = $data["id_chat"];
    $last_date = isset($data["last-date"]) ? $data["last-date"] : null;

    $sql = "SELECT id, sender, text_message, date_message, read_status
    FROM messages
    WHERE id_chat = :id_chat";

    $params = [
        ':id_chat' => (int)$id_chat
    ];

    if ($last_date) {
        $sql .= " AND date_message > :last_date";
        $params[':last_date'] = $last_date;
    }

    $sql .= " ORDER BY date_message ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updateStmt = $pdo->prepare("UPDATE messages SET read_status = 1 WHERE id = ?");
    $ultimosmensajes = [];
    foreach ($rows as $row) {
        if ($row['read_status'] === 0 && $row['sender'] != $user_id) {
            $updateStmt->execute([$row['id']]);
            $row['read_status'] = 1;
        }
        $ultimosmensajes[] = [
            "text_message" => $row['text_message'],
            "date_message" => $row['date_message'],
            "is_mine" => ($row['sender'] == $user_id),
            "read_status" => $row['read_status']
        ];
    }

    echo json_encode($ultimosmensajes);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de base de datos"]);
}

?>
