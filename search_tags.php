<?php
session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    include("includes/database.php");
$q = $_GET['q'] ?? '';
$sql = "SELECT id, name FROM categories WHERE name LIKE ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$q%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);
?>