<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['notifications']) || !is_array($_SESSION['notifications'])) {
    $_SESSION['notifications'] = [];
}

echo json_encode(['notifications' => $_SESSION['notifications']]);
?>