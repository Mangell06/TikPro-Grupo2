<?php
session_start();
include("includes/database.php");

$iduser = $_SESSION['user_id'] ?? 0;
$id_project = (int)($_POST['id_project'] ?? 0);
$id_category = (int)($_POST['id_category'] ?? 0);

if(!$iduser || !$id_project || !$id_category){
    echo json_encode(['success'=>false]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT IGNORE INTO category_project (id_project, id_category)
    VALUES (:proj, :cat)
");
$stmt->execute([
    'proj'=>$id_project,
    'cat'=>$id_category
]);

echo json_encode(['success'=>true]);
exit;
