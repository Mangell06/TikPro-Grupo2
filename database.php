<?php
session_start();

// === CONFIGURACIÓN DE BASE DE DATOS ===
$host = 'localhost';
$db   = 'simbiodb';
$user = 'simbdmin';
$pass = 'Millon202';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// === REDIRIGIR SI YA ESTÁ LOGUEADO ===
if (isset($_SESSION['user_email'])) {
    header("Location: discover.php");
    exit;
}

// PROCESAR LOGIN 
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? and password = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login correcto
            $_SESSION['user_email'] = $user['email'];
            header("Location: discover.php");
            exit;
        } else {
            $error = "Email o contraseña incorrectos";
        }
    } else {
        $error = "Introduce email y contraseña";
    }
}
?>
