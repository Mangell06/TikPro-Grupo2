<?php
session_start();

// === CONFIGURACIÓN BD ===
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

$pdo = new PDO($dsn, $user, $pass, $options);

// === REDIRIGIR SI YA ESTÁ LOGUEADO ===
if (isset($_SESSION['user_email'])) {
    header("Location: discover.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {

        // MISMO HASH QUE EL SEEDER
        $password_hashed = hash('sha256', $password);

        $stmt = $pdo->prepare(
            "SELECT ID_User, Email 
             FROM Users 
             WHERE Email = ? AND Password = ?
             LIMIT 1"
        );

        $stmt->execute([$email, $password_hashed]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_email'] = $user['Email'];
            $_SESSION['user_id']    = $user['ID_User'];

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
