<?php
session_start();
include("database.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    http_response_code(401);
    echo json_encode(["error" => "Sesió no iniciada"]);
    exit;
}

$userId = (int) $_SESSION['user_id'];

if (!isset($_POST['project']) || !is_numeric($_POST['project']) || $_POST['project'] <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Proyecte invalid"]);
    exit;
}
$projectId = (int) $_POST['project'];

$liked = isset($_POST['liked']) ? (int) $_POST['liked'] : 0;

try {
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Proyecte no encontrat"]);
        exit;
    }

    if ($liked === 1) {
        $stmt = $pdo->prepare("DELETE FROM likes WHERE id_user = ? AND id_project = ?");
        $stmt->execute([$userId, $projectId]);
        $action = "removed";
    } else {
        $stmt = $pdo->prepare("INSERT INTO likes (id_user, id_project) VALUES (?, ?)");
        $stmt->execute([$userId, $projectId]);
        $action = "added";
    }

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "action" => $action,
        "project" => $projectId,
        "message" => $action === "added" ? "Match registrat" : "Like eliminat"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error intern del servidor",
        "details" => $e->getMessage()
    ]);
}
?>