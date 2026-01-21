<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
include("database.php");
try {

    // Solo permitir POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        exit;
    }

    $registerData = json_decode($_POST['registerData'], true);
    if (!isset($registerData) || empty($registerData)) {
        http_response_code(400);
        echo json_encode([
            "error" => "No s'han rebut les dades de registre"
        ]);
        exit;
    }
    $username = $registerData["username"];
    $email = $registerData["email"];
    $tfn = $registerData["tfn"];
    $password = hash('sha256', $registerData["password"]);
    $population = $registerData["population"];
    $nameentity = $registerData["nameentity"];
    $typeentity = $registerData["typeentity"];
    $codeactivate = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);
    $codeexpire = date('Y-m-d H:i:s', strtotime('+48 hours'));
    if (isset($registerData["presentation"])) {
        $presentation = $registerData["presentation"];
    }
    $sqlcreation = "INSERT INTO users (email, tfn, password, name, poblation, entity_name, 
    entity_type, is_active, code_activate, code_expire, presentation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $presentationValue = isset($registerData["presentation"]) ? $registerData["presentation"] : null;

    $values = [$email, $tfn, $password, $username, $population, $nameentity, $typeentity, 0, $codeactivate, $codeexpire, $presentationValue];

    try {
        $stmt= $pdo->prepare($sqlcreation);
        $stmt->execute($values);
    } catch (PDOException $err) {
        http_response_code(500);
        echo json_encode([
            "error" => "Error al insertar usuario: " . $err->getMessage(),
            "code" => $err->getCode()
        ]);
        exit;
    }

       // Preparar datos para PHPMailer
    $verificationLink = "https://simbio2.ieti.site/register.php?validate=" . $codeactivate;
    $subject = "Verifica tu cuenta en SIMBIO";
    $htmlBody = "Hola $username,<br><br>Por favor verifica tu cuenta haciendo click en el siguiente enlace:<br><a href='$verificationLink'>$verificationLink</a><br><br>Gracias!";
    $plainBody = "Hola $username,\n\nPor favor verifica tu cuenta haciendo click en el siguiente enlace:\n$verificationLink\n\nGracias!";

    // Enviar correo con PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mangell0624@gmail.com';
        $mail->Password   = 'wlpw zjuu axlg bsbn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('simbio2@gmail.com', 'SIMBIO');
        $mail->addAddress($email, $username);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $plainBody;

        $mail->send();
    } catch (Exception $e) {
        http_response_code(503);
        echo json_encode([
            "error" => "No s'ha pogut enviar el correu"
        ]);
    }

    echo json_encode(["success" => true, "message" => "Usuario registrado correctamente"]);
    exit;
} catch (Exception $err) {
    http_response_code(503);
    echo json_encode([
        "error" => "No s'ha pogut conectar amb el servidor"
    ]);
    exit;
}
?>