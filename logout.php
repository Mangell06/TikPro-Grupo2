<?php
// Registrar logout antes de destruir sesión
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    // Incluir función de logs
    include_once("create-logs.php"); // o el path correcto donde esté sendLog PHP
    if (function_exists('sendLog')) {
        sendLog("Usuario con ID $userId ha cerrado sesión (logout).");
    }
}

session_start();
session_destroy();

header("Location: login.php");
exit;
