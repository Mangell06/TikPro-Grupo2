<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Discover</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body id="discover-body">

<div id="discover-container">
    <h1 id="discover-title">Bienvenido</h1>
    <p id="discover-user">
        Usuario: <?php echo $_SESSION['user_email']; ?>
    </p>

    <a id="logout-link" href="logout.php">Cerrar sesión</a>
</div>

</body>
</html>
