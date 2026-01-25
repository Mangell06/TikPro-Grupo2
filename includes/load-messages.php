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

     $stmtOwner = $pdo->prepare("SELECT id_project FROM projects WHERE id = ?");
    $stmtOwner->execute([$id_chat]);
    $id_project = $stmtOwner->fetch(PDO::FETCH_ASSOC);

    $stmtOwner = $pdo->prepare("SELECT id_owner FROM projects WHERE id = ?");
    $stmtOwner->execute([$id_project]);
    $owner = $stmtOwner->fetch(PDO::FETCH_ASSOC);

    if (!$owner) {
        echo json_encode([]);
        exit;
    }

    $owner_id = $owner['id_owner'];


    $sql = "SELECT sender, text_message, date_message, read_status 
        FROM messages
        WHERE id_project = :project_id 
        AND (
            (sender = :user1 AND destination = :owner1)
            OR 
            (sender = :owner2 AND destination = :user2)
        )";
    $params = [
        ':project_id' => (int)$id_project,
        ':user1' => (int)$user_id,
        ':owner1' => (int)$owner_id,
        ':owner2' => (int)$owner_id,
        ':user2' => (int)$user_id
    ];
    if ($last_date) {
        $sql .= " AND date_message > :last_date";
        $params[':last_date'] = $last_date;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $ultimosmensajes = [];
    foreach ($rows as $row) {
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
