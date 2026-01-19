<?php
session_start();
include("database.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Sessió no iniciada"]);
    exit;
}

try {
    $excludeIds = [];
    if (isset($_GET['exclude_projects']) && $_GET['exclude_projects'] !== '') {
        $excludeIds = array_map('intval', explode(',', $_GET['exclude_projects']));
    }

    $whereSql = '';
    if (!empty($excludeIds)) {
        $excludeIds = array_filter($excludeIds, fn($id) => $id > 0);
        if (!empty($excludeIds)) {
            $whereSql = 'WHERE p.id NOT IN (' . implode(',', $excludeIds) . ')';
        }
    }
    error_log("Exclude IDs: " . implode(',', $excludeIds));

    $userId = $_SESSION['user_id'];

    // Obtener categorías del usuario
    $stmtCat = $pdo->prepare("SELECT id_category FROM categories_user WHERE id_user = :iduser");
    $stmtCat->execute(['iduser' => $userId]);
    $userCategories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

    $orderSql = '';
    if (!empty($userCategories)) {
        // Convierte el array en lista para SQL
        $userCatList = implode(',', $userCategories);

        // LEFT JOIN temporal con categorías del usuario para marcar coincidencias
        $orderSql = "LEFT JOIN categories_project ucp 
                    ON p.id = ucp.id_project AND ucp.id_category IN ($userCatList)";
    }

    // Consulta
    $sql = "
    SELECT p.id, p.title, p.description, p.video, GROUP_CONCAT(c.name) AS tags, COUNT(ucp.id_category) AS coincidences,
    u.name, u.entity_name, u.entity_type FROM projects p
    LEFT JOIN users u ON p.id_owner = u.id
    LEFT JOIN categories_project cp ON p.id = cp.id_project
    LEFT JOIN categories c ON cp.id_category = c.id
    $orderSql
    $whereSql
    GROUP BY p.id
    ORDER BY coincidences DESC
    LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $projects = [];

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tagsArray = $row['tags'] ? explode(',', $row['tags']) : [];
        $projects[] = [
            "id_project" => (int)$row["id"],
            "title" => $row["title"],
            "description" => $row["description"],
            "video" => $row["video"],
            "tags" => $tagsArray,
            "username" => $row["name"],
            "entity_name" => $row["entity_name"],
            "entity_type" => $row["entity_type"], 
        ];
    }

    http_response_code(200);
    echo json_encode($projects);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "No s'ha pogut conectar amb el servidor",
        "details" => $e->getMessage()
    ]);
}

?>