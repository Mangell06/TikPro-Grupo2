<?php
include("database.php");
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        exit;
    }

    $searchcat = isset($_POST['categoryData']['searchcat']) ? trim($_POST['categoryData']['searchcat']) : '';
    
    $excludeNames = !empty($_POST['categoryData']['excludecategory']) 
    ? array_map('trim', explode(',', $_POST['categoryData']['excludecategory'])) 
    : [];

    $sql = "SELECT name FROM categories WHERE 1=1";
    $params = [];

    if ($searchcat !== '') {
        $sql .= " AND name LIKE ?";
        $params[] = "%$searchcat%";
    }

    if (!empty($excludeNames)) {
        $placeholders = implode(',', array_fill(0, count($excludeNames), '?'));
        $sql .= " AND name NOT IN ($placeholders)";
        $params = array_merge($params, $excludeNames);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "categories" => $categories
    ]);

} catch (PDOException $err) {
    http_response_code(500);
    echo json_encode(["error" => "Error de base de dades: " . $err->getMessage()]);
}
?>