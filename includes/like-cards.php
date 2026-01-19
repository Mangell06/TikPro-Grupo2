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

if (!isset($_POST['project']) || $_POST['project'] === '' || !is_numeric($_POST['project']) || $_POST['project'] <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Proyecte invalid"]);
    exit;
}

$projectId = (int) $_POST['project'];

try {
    /* 4. Existe el proyecto */
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Proyecte no encontrat"]);
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT 1 FROM likes WHERE id_user = ? AND id_project = ?"
    );
    $stmt->execute([$userId, $projectId]);

    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(["error" => "m'agrada ya registrat"]);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO likes (id_user, id_project) VALUES (?, ?)"
    );
    $stmt->execute([$userId, $projectId]);

    if ($stmt->rowCount() !== 1) {
        http_response_code(500);
        echo json_encode(["error" => "No s'ha pogut fer el match"]);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Match registrat"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error intern del servidor"
    ]);
}
