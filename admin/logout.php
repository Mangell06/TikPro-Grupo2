<?php
// Registrar logout antes de destruir sesión
if (isset($_SESSION['admin_id'])) {
    $adminId = $_SESSION['admin_id'];
    
    // Incluir función de logs
    include_once("create-logs.php"); // o el path correcto donde esté sendLog PHP
    if (function_exists('sendLog')) {
        sendLog("Admin con ID $adminId ha cerrado sesión (logout).");
    }
}

session_start();
unset($_SESSION['admin_id']);
header("Location: /admin/login.php");
exit;