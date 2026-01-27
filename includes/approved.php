<?php
include("database.php");
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$projectId = $data['projectId'] ?? null;

if ($projectId) {
    try {
        $sql = "UPDATE projects SET state = 'active' WHERE id = :project_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['project_id' => $projectId]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
}
exit;