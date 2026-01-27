<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
include("database.php");
header('Content-Type: application/json');

try {
    // Solo permitir POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["error" => "Mètode no permès"]);
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
    $categories = $registerData["categories"];
    $population = $registerData["population"];
    $nameentity = $registerData["nameentity"];
    $typeentity = $registerData["typeentity"];
    
    // Generación de código de activación
    $codeactivate = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);
    $codeexpire = date('Y-m-d H:i:s', strtotime('+48 hours'));
    
    $presentationValue = isset($registerData["presentation"]) ? $registerData["presentation"] : null;

    $sqlcreation = "INSERT INTO users (email, tfn, password, name, poblation, entity_name, 
    entity_type, is_active, code_activate, code_expire, presentation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $values = [$email, $tfn, $password, $username, $population, $nameentity, $typeentity, 0, $codeactivate, $codeexpire, $presentationValue];

    try {
        $stmt = $pdo->prepare($sqlcreation);
        $stmt->execute($values);

        $userId = $pdo->lastInsertId();

        if (!empty($categories) && is_array($categories)) {
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $sqlCatIds = "SELECT id FROM categories WHERE name IN ($placeholders)";
            $stmtCats = $pdo->prepare($sqlCatIds);
            $stmtCats->execute($categories);
            $categoryData = $stmtCats->fetchAll(PDO::FETCH_ASSOC);

            if ($categoryData) {
                $sqlRelation = "INSERT INTO categories_user (id_user, id_category) VALUES (?, ?)";
                $stmtRel = $pdo->prepare($sqlRelation);
                foreach ($categoryData as $cat) {
                    $stmtRel->execute([$userId, $cat['id']]);
                }
            }
        }
    } catch (PDOException $err) {
        http_response_code(500);
        echo json_encode([
            "error" => "Error al insertar usuari: " . $err->getMessage()
        ]);
        exit;
    }

    // --- CONFIGURACIÓN DE EMAIL CON ESTILOS GMAIL ---
    $verificationLink = "https://simbio2.ieti.site/register.php?validate=" . $codeactivate;
    
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mangell0624@gmail.com';
        $mail->Password   = 'wlpw zjuu axlg bsbn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('simbio2@gmail.com', 'SIMBIO');
        $mail->addAddress($email, $username);

        $mail->isHTML(true);
        $mail->Subject = "Verifica el teu compte a SIMBIO";

        // HTML Body con CSS Inline
        $mail->Body = "
        <div style='background-color: #f6f9fc; padding: 50px 20px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <div style='max-width: 550px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #e6ebf1;'>
                
                <div style='background-color: #1a1a1a; padding: 30px; text-align: center;'>
                    <h1 style='color: #ffffff; margin: 0; font-size: 26px; letter-spacing: 5px; font-weight: 800; text-transform: uppercase;'>SIMBIO</h1>
                </div>

                <div style='padding: 40px; text-align: center;'>
                    <h2 style='color: #1a1f36; margin: 0 0 15px 0; font-size: 24px; font-weight: 700;'>Benvingut/da, $username!</h2>
                    <p style='color: #4f566b; font-size: 16px; line-height: 1.6; margin-bottom: 30px;'>
                        Gràcies per registrar-te a SIMBIO. Per començar a utilitzar la plataforma i connectar amb altres entitats, confirma la teva adreça de correu clicant al botó següent:
                    </p>
                    
                    <div style='margin: 35px 0;'>
                        <a href='$verificationLink' style='background-color: #0061ff; color: #ffffff; padding: 16px 32px; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 8px; display: inline-block; box-shadow: 0 4px 6px rgba(0,97,255,0.2);'>
                            Confirmar Compte
                        </a>
                    </div>
                    
                    <p style='color: #a3acb9; font-size: 13px; margin-top: 25px; line-height: 1.5;'>
                        Si el botó no funciona, pots copiar i enganxar aquest enllaç al teu navegador:<br>
                        <a href='$verificationLink' style='color: #0061ff;'>$verificationLink</a>
                    </p>
                </div>

                <div style='background-color: #fafbff; padding: 25px; text-align: center; border-top: 1px solid #e6ebf1;'>
                    <p style='color: #8792a2; font-size: 12px; margin: 0;'>
                        Aquest enllaç de verificació caduca en 48 hores.<br>
                        &copy; 2026 SIMBIO Project. Tots els drets reservats.
                    </p>
                </div>
            </div>
        </div>";

        $mail->AltBody = "Hola $username, verifica el teu compte a SIMBIO fent clic en aquest enllaç: $verificationLink";

        $mail->send();
    } catch (Exception $e) {
        // Log interno del error si fuera necesario
    }

    echo json_encode(["success" => true, "message" => "Usuari registrat correctament"]);
    exit;

} catch (Exception $err) {
    http_response_code(503);
    echo json_encode([
        "error" => "No s'ha pogut connectar amb el servidor"
    ]);
    exit;
}
?>