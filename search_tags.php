<?php
session_start();
include("includes/database.php");

$iduser = $_SESSION['user_id'] ?? 0;
if (!$iduser) { echo json_encode([]); exit; }

$q = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'family';

if(strlen($q)<3){ echo json_encode([]); exit; }

$stmt = $pdo->prepare("
    SELECT id_category, name_category
    FROM categories
    WHERE name_category LIKE :q AND type=:type
    LIMIT 10
");
$stmt->execute([
    'q'=>"%$q%",
    'type'=>$type
]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($results);
exit;
