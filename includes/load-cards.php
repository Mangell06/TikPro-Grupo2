<?php
    session_start();
    include("database.php");

    // indicar que la respuesta es json
    header('Content-Type: application/json');

    // comprobar sesión
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            "error" => "Sessió no iniciada"
        ]);
        exit;
    }

    $excludeId = isset($_GET['exclude_id']) ? intval($_GET['exclude_id']) : 0;

    try {
        // consulta
        $stmt = $pdo->prepare("
            SELECT p.id_project, p.title, p.description, p.video,
            GROUP_CONCAT(c.name_category) AS tags,
            u.username, u.entity_name, u.entity_type
            FROM projects p
            LEFT JOIN users u ON p.id_owner = u.id_user
            LEFT JOIN category_project cp ON p.id_project = cp.id_project
            LEFT JOIN categories c ON cp.id_category = c.id_category
            WHERE p.id_project != :excludeId
            GROUP BY p.id_project
            ORDER BY RAND()
            LIMIT 1;
        ");
        $stmt->execute(['excludeId' => $excludeId]);

        while ($row = $stmt->fetch()) {
            $tagsArray = !empty($row['tags']) ? explode(',', $row['tags']) : [];
            $projects = [
                "id_project" => $row["id_project"],
                "description" => $row["description"],
                "title" => $row["title"],
                "video" => $row["video"],
                "tags" => $tagsArray,
                "username" => $row["username"],
                "entity_name" => $row["entity_name"],
                "entity_type" => $row["entity_type"],
            ];
        }

        // sin resultados
        if (empty($projects)) {
            http_response_code(204);
            exit;
        }

        // todo correcto
        http_response_code(200);
        echo json_encode($projects);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "No s'ha pogut conectar amb el servidor"
        ]);
    }
?>