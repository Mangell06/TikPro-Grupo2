<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
session_start();
include("database.php");

header('Content-Type: application/json');

// 1. Verificacions de seguretat
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Mètode no permès"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Sessió no iniciada"]);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$projectId = isset($_POST['project']) ? (int) $_POST['project'] : 0;
$liked = isset($_POST['liked']) ? (int) $_POST['liked'] : 0;

if ($projectId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Projecte invàlid"]);
    exit;
}

try {
    // 2. Comprovar si el projecte existeix
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Projecte no trobat"]);
        exit;
    }

    $pdo->beginTransaction();

    if ($liked === 1) {
        // Si ja tenia like, l'eliminem (unlike)
        $stmt = $pdo->prepare("DELETE FROM likes WHERE id_user = ? AND id_project = ?");
        $stmt->execute([$userId, $projectId]);
        $action = "removed";
    } else {
        // Fem l'INSERT del nou like
        $stmt = $pdo->prepare("INSERT INTO likes (id_user, id_project) VALUES (?, ?)");
        $stmt->execute([$userId, $projectId]);
        $action = "added";

        // --- LÒGICA D'ENVIAMENT D'EMAIL (Només si és un nou like) ---
        
        // Obtenim les dades del propietari, del projecte i de qui fa el like
        $stmtData = $pdo->prepare("
            SELECT 
                p.titol AS p_titol, 
                u_owner.email AS owner_email, u_owner.name AS owner_nom,
                u_liker.email AS liker_email, u_liker.name AS liker_nom
            FROM projects p
            JOIN users u_owner ON p.id_user = u_owner.id
            JOIN users u_liker ON u_liker.id = ?
            WHERE p.id = ?
        ");
        $stmtData->execute([$userId, $projectId]);
        $match = $stmtData->fetch(PDO::FETCH_ASSOC);

        if ($match) {
            enviarEmailsMatch($match);
        }
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "action" => $action,
        "message" => $action === "added" ? "Match registrat i emails enviats" : "Like eliminat"
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["error" => "Error intern: " . $e->getMessage()]);
}

/**
 * Funció per enviar els dos correus (propietari i interessat)
 */
function enviarEmailsMatch($data) {
    // Configura aquí les teves credencials de Gmail
    $smtpHost = 'smtp.gmail.com';
    $smtpUser = 'mangell0624@gmail.com';
    $smtpPass = 'wlpw zjuu axlg bsbn';

    $destinataris = [
        ['email' => $data['owner_email'], 'nom' => $data['owner_nom'], 'tipus' => 'owner'],
        ['email' => $data['liker_email'], 'nom' => $data['liker_nom'], 'tipus' => 'liker']
    ];

    foreach ($destinataris as $desti) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('simbio2@gmail.com', 'SIMBIO MATCH');
            $mail->addAddress($desti['email'], $desti['nom']);

            $mail->isHTML(true);
            $mail->Subject = "Nou Match! - " . $data['p_titol'];

            // Text personalitzat
            if ($desti['tipus'] === 'owner') {
                $contingut = "L'usuari <b>{$data['liker_nom']}</b> ha mostrat interès en el teu projecte!";
            } else {
                $contingut = "T'has connectat amb el projecte de <b>{$data['owner_nom']}</b>. Estigues atent per a futures comunicacions!";
            }

            $mail->Body = "
            <div style='background-color: #f6f9fc; padding: 50px 20px; font-family: sans-serif;'>
                <div style='max-width: 550px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e6ebf1;'>
                    <div style='background-color: #1a1a1a; padding: 30px; text-align: center;'>
                        <h1 style='color: #ffffff; margin: 0; font-size: 24px; letter-spacing: 4px; text-transform: uppercase;'>SIMBIO MATCH</h1>
                    </div>
                    <div style='padding: 40px; text-align: center;'>
                        <div style='background-color: #e6f0ff; color: #0061ff; display: inline-block; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: bold; margin-bottom: 20px;'>
                            🎉 COINCIDÈNCIA DETECTADA
                        </div>
                        <h2 style='color: #1a1f36; margin-bottom: 15px;'>Hola {$desti['nom']}!</h2>
                        <p style='color: #4f566b; line-height: 1.6;'>$contingut</p>
                        
                        <div style='background-color: #fafbff; border: 1px dashed #d1d9e6; padding: 20px; border-radius: 12px; margin: 25px 0;'>
                            <p style='margin: 0; color: #1a1f36; font-weight: 600; font-size: 18px;'>{$data['p_titol']}</p>
                        </div>
                        
                        <a href='https://simbio2.ieti.site/messages.php' style='background-color: #0061ff; color: #ffffff; padding: 15px 30px; text-decoration: none; font-weight: bold; border-radius: 8px; display: inline-block;'>
                            Anar als meus missatges
                        </a>
                    </div>
                    <div style='background-color: #fafbff; padding: 20px; text-align: center; border-top: 1px solid #e6ebf1; color: #8792a2; font-size: 12px;'>
                        © 2026 SIMBIO Project. Recorda revisar el teu xat per concretar la col·laboració.
                    </div>
                </div>
            </div>";

            $mail->send();
        } catch (Exception $e) {
            // Si falla un email, continuem amb el següent
            continue;
        }
    }
}