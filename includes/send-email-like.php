<?php
session_start();
include("database.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');

// Validar sesión y datos
if (!isset($_SESSION['user_id']) || !isset($_POST['project']) || !isset($_POST['idchat'])) {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$projectId = (int)$_POST['project'];
$idchat = $_POST['idchat'];

try {
    // Solo consultamos la info necesaria para el email
    $stmtData = $pdo->prepare("
        SELECT p.title, u_o.email as o_email, u_o.name as o_name,
               u_l.email as l_email, u_l.name as l_name
        FROM projects p
        JOIN users u_o ON p.id_owner = u_o.id
        JOIN users u_l ON u_l.id = ?
        WHERE p.id = ?
    ");
    $stmtData->execute([$userId, $projectId]);
    $matchData = $stmtData->fetch(PDO::FETCH_ASSOC);

    if ($matchData) {
        $mailConfig = ['user' => 'mangell0624@gmail.com', 'pass' => 'wlpw zjuu axlg bsbn'];
        $roles = [
            ['email' => $matchData['o_email'], 'nom' => $matchData['o_name'], 'is_owner' => true],
            ['email' => $matchData['l_email'], 'nom' => $matchData['l_name'], 'is_owner' => false]
        ];

        foreach ($roles as $r) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $mailConfig['user'];
                $mail->Password = $mailConfig['pass'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
                $mail->CharSet = 'UTF-8';
                $mail->setFrom('simbio2@gmail.com', 'SIMBIO');
                $mail->addAddress($r['email'], $r['nom']);
                $mail->isHTML(true);
                $mail->Subject = "Nou Match a SIMBIO: " . $matchData['title'];

                $bodyText = $r['is_owner'] 
                    ? "L'usuari <b>{$matchData['l_name']}</b> vol col·laborar en el teu projecte."
                    : "T'has connectat amb el projecte de <b>{$matchData['o_name']}</b>.";

                $mail->Body = "<div style='background:#f6f9fc; padding:40px; font-family:sans-serif;'>
                    <div style='max-width:500px; margin:0 auto; background:#fff; border-radius:12px; border:1px solid #eee;'>
                        <div style='background:#1a1a1a; padding:20px; text-align:center; color:#fff;'>SIMBIO</div>
                        <div style='padding:30px; text-align:center;'>
                            <h2>Hola {$r['nom']}!</h2>
                            <p>$bodyText</p>
                            <div style='margin:20px 0; padding:15px; background:#f0f7ff; font-weight:bold;'>{$matchData['title']}</div>
                            <a href='https://simbio2.ieti.site/chat.php?talk=$idchat' style='background:#0061ff; color:#fff; padding:12px 25px; text-decoration:none; border-radius:6px; display:inline-block;'>Anar al xat</a>
                        </div>
                    </div>
                </div>";
                $mail->send();
            } catch (Exception $e) { continue; }
        }
        echo json_encode(["success" => true, "message" => "Emails enviados"]);
    } else {
        echo json_encode(["success" => false, "error" => "No se encontraron datos de match"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}