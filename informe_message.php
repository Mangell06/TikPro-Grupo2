<?php
include("includes/database.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . "/vendor/autoload.php";

$stmtAllUsers = $pdo->query("SELECT id, email, name FROM users");
$allUsers = $stmtAllUsers->fetchAll(PDO::FETCH_ASSOC);

foreach ($allUsers as $user) {

    $USER_ID = $user['id'];

    // calcular fecha desde ayer a las 22:00
    $startDate = new DateTime('now');
    $startDate->modify('-1 day');
    $startDate->setTime(22, 0, 0);
    $start = $startDate->format('Y-m-d H:i:s');

    // obtener mensajes recibidos para este usuario
    $stmt = $pdo->prepare("
        SELECT 
            u.name AS sender_name,
            m.text_message,
            m.date_message
        FROM messages m
        JOIN chats ch ON ch.id = m.id_chat
        JOIN users u ON u.id = m.sender
        WHERE 
            (ch.user_owner = :uid1 OR ch.other_user = :uid2)
            AND m.sender <> :uid3
            AND m.date_message >= :start
        ORDER BY u.name, m.date_message ASC
    ");
    $stmt->execute([
        ':uid1' => $USER_ID,
        ':uid2' => $USER_ID,
        ':uid3' => $USER_ID,
        ':start' => $start
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // saltar si no hay mensajes
    if (!$rows) continue;

    // agrupar por remitente
    $grouped = [];
    foreach ($rows as $r) {
        $grouped[$r['sender_name']][] = [
            'text' => $r['text_message'],
            'date' => $r['date_message']
        ];
    }

    $MAIL_USER = 'mangell0624@gmail.com';
    $MAIL_PASS = 'wlpw zjuu axlg bsbn';
    $FROM_EMAIL = 'simbio2@gmail.com';
    $FROM_NAME = 'SIMBIO';

    // construir HTML del email
    $messagesHtml = "";
    foreach ($grouped as $sender => $messages) {
        $messagesHtml .= "<h3 style='margin-top:20px;'>$sender</h3><ul>";
        foreach ($messages as $m) {
            $messagesHtml .= "<li style='margin-bottom:6px;'>"
                . htmlspecialchars($m['text']) .
                " <span style='color:#888;font-size:12px;'>(" . $m['date'] . ")</span></li>";
        }
        $messagesHtml .= "</ul>";
    }

    $body = "
    <div style='background:#f6f9fc; padding:40px; font-family:sans-serif;'>
      <div style='max-width:600px; margin:0 auto; background:#fff; border-radius:12px; border:1px solid #eee;'>
        <div style='background:#1a1a1a; padding:20px; text-align:center; color:#fff;'>SIMBIO</div>
        <div style='padding:30px;'>
          <h2>Hola {$user['name']}</h2>
          <p>Aquests són els missatges que has rebut des d'ahir a les <b>22:00</b>:</p>
          $messagesHtml
        </div>
      </div>
    </div>
    ";

    // enviar email con PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $MAIL_USER;
        $mail->Password = $MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($FROM_EMAIL, $FROM_NAME);
        $mail->addAddress($user['email'], $user['name']);

        $mail->isHTML(true);
        $mail->Subject = "Informe de missatges rebuts";
        $mail->Body = $body;

        $mail->send();
    } catch (Exception $e) {
        echo "Error enviando email a {$user['email']}: {$mail->ErrorInfo}\n";
    }
}
?>