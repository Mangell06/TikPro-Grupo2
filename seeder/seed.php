<?php
/**
 * SEEDER COMPLETO – FP (6 proyectos)
 * Ejecutar: php seeder/index.php
 */

if (php_sapi_name() !== 'cli') {
    exit("Solo se puede ejecutar desde CLI\n");
}

include('../config/database.php');

echo "🚀 Iniciando seeder...\n";

try {

    /* -------------------------------------------------
       0. LIMPIAR BASE DE DATOS
    -------------------------------------------------- */
    echo "🧹 Limpiando base de datos...\n";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE Projects_Categories");
    $pdo->exec("TRUNCATE Projects");
    $pdo->exec("TRUNCATE Likes");
    $pdo->exec("TRUNCATE Favorites");
    $pdo->exec("TRUNCATE Messages");
    $pdo->exec("TRUNCATE Users");
    $pdo->exec("TRUNCATE Categories");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    /* -------------------------------------------------
       1. FAMILIAS Y CICLOS
    -------------------------------------------------- */
    $famCicles = [
        'Informàtica i comunicacions' => ['SMX', 'ASIX'],
        'Administració i gestió'       => ['Gestió Administrativa', 'Administració i Finances'],
        'Electricitat i electrònica'   => ['Instal·lacions Elèctriques', 'Automatització i Robòtica']
    ];

    echo "🏷️ Insertando categorías...\n";
    $tagCicles = [];

    foreach ($famCicles as $family => $cycles) {
        $stmt = $pdo->prepare(
            "INSERT INTO Categories (name_category, Type, ID_Category_Parent)
             VALUES (?, 'Family', NULL)"
        );
        $stmt->execute([$family]);
        $familyId = $pdo->lastInsertId();

        foreach ($cycles as $cycle) {
            $stmt = $pdo->prepare(
                "INSERT INTO Categories (name_category, Type, ID_Category_Parent)
                 VALUES (?, 'Cicle', ?)"
            );
            $stmt->execute([$cycle, $familyId]);
            $tagCicles[$family][] = $pdo->lastInsertId();
        }
    }

    /* -------------------------------------------------
       2. USUARIOS (6 centros + 4 empresas)
    -------------------------------------------------- */
    echo "👤 Creando usuarios...\n";

    $centres = [
        'Institut Tecnològic de Barcelona', 'Institut La Ribera',
        'Institut Montsià', 'Institut Vallès',
        'Institut Joan XXIII', 'Institut Delta'
    ];

    $empreses = ['Google', 'Microsoft', 'Amazon', 'Apple'];

    // Centros
    foreach ($centres as $i => $nom) {
        $email = strtolower(preg_replace('/[^a-zA-Z]/', '', $nom)) . '@edu.cat';
        $username = 'center' . ($i + 1);
        $pdo->prepare(
            "INSERT INTO Users (Email, Password, Username, entity_name, entity_type, Level_Studies, Presentation)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        )->execute([
            $email,
            hash('sha256','password123'),
            $username,
            $nom,
            'Center',
            'FP',
            "Usuario del centro $nom"
        ]);
    }

    // Empresas
    foreach ($empreses as $i => $nom) {
        $email = strtolower(preg_replace('/[^a-zA-Z]/', '', $nom)) . '@empresa.com';
        $username = 'company' . ($i + 1);
        $pdo->prepare(
            "INSERT INTO Users (Email, Password, Username, entity_name, entity_type, Level_Studies, Presentation)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        )->execute([
            $email,
            hash('sha256','password123'),
            $username,
            $nom,
            'Company',
            'N/A',
            "Usuario de la empresa $nom"
        ]);
    }

    /* -------------------------------------------------
       3. PROJECTS (solo 6)
    -------------------------------------------------- */
    echo "📁 Insertando proyectos...\n";

    $stmt = $pdo->query("SELECT ID_User, entity_name FROM Users WHERE entity_type = 'Center' LIMIT 6");
    $centersUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($centersUsers as $i => $center) {
        $pdo->prepare(
            "INSERT INTO Projects (Title, Description, Video, Date_Creation, State, ID_Owner)
             VALUES (?, ?, ?, ?, ?, ?)"
        )->execute([
            "Projecte FP " . ($i + 1),
            "Projecte real del centre " . $center['entity_name'],
            "videos/proyecto" . ($i + 1) . ".mp4",
            date('Y-m-d'),
            'Active',
            $center['ID_User']
        ]);
    }

    /* -------------------------------------------------
       4. PROJECTS_CATEGORIES
    -------------------------------------------------- */
    echo "🏷️ Asignando categorías a proyectos...\n";

    $stmt = $pdo->query("SELECT ID_Project FROM Projects ORDER BY ID_Project ASC");
    $projects = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($projects as $i => $projectId) {
        $family = array_keys($tagCicles)[$i % count($tagCicles)];
        foreach ($tagCicles[$family] as $catId) {
            $pdo->prepare(
                "INSERT INTO Projects_Categories (ID_Project, ID_Category)
                 VALUES (?, ?)"
            )->execute([$projectId, $catId]);
        }
    }

    /* -------------------------------------------------
       5. FAVORITES
    -------------------------------------------------- */
    echo "⭐ Insertando favoritos...\n";

    $stmt = $pdo->query("SELECT ID_User, entity_type FROM Users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        if ($user['entity_type'] === 'Center') {
            for ($i = 0; $i < 2 && $i < count($projects); $i++) {
                $pdo->prepare(
                    "INSERT INTO Favorites (ID_User, ID_Project)
                     VALUES (?, ?)"
                )->execute([$user['ID_User'], $projects[$i]]);
            }
        } else {
            for ($i = count($projects) - 2; $i < count($projects); $i++) {
                $pdo->prepare(
                    "INSERT INTO Favorites (ID_User, ID_Project)
                     VALUES (?, ?)"
                )->execute([$user['ID_User'], $projects[$i]]);
            }
        }
    }

    /* -------------------------------------------------
       6. LIKES
    -------------------------------------------------- */
    echo "❤️ Insertando likes...\n";

    foreach ($users as $user) {
        if ($user['entity_type'] === 'Center') {
            for ($i = count($projects) - 2; $i < count($projects); $i++) {
                $pdo->prepare(
                    "INSERT INTO Likes (ID_User, ID_Project)
                     VALUES (?, ?)"
                )->execute([$user['ID_User'], $projects[$i]]);
            }
        } else {
            for ($i = 0; $i < 2 && $i < count($projects); $i++) {
                $pdo->prepare(
                    "INSERT INTO Likes (ID_User, ID_Project)
                     VALUES (?, ?)"
                )->execute([$user['ID_User'], $projects[$i]]);
            }
        }
    }

    /* -------------------------------------------------
       7. MESSAGES
    -------------------------------------------------- */
    echo "✉️ Insertando mensajes...\n";

    $centers   = array_filter($users, fn($u) => $u['entity_type'] === 'Center');
    $companies = array_filter($users, fn($u) => $u['entity_type'] === 'Company');
    $date = date('Y-m-d');

    foreach ($centers as $center) {
        $j = 0;
        foreach ($companies as $company) {
            if ($j >= 2) break;
            $pdo->prepare(
                "INSERT INTO Messages (Sender, Destination, Text_Message, Date_Message, Read_Status)
                 VALUES (?, ?, ?, ?, 0)"
            )->execute([
                $center['ID_User'],
                $company['ID_User'],
                "Hola, soy el usuario del centro {$center['ID_User']}",
                $date
            ]);
            $j++;
        }
    }

    foreach ($companies as $company) {
        $j = 0;
        foreach ($centers as $center) {
            if ($j >= 2) break;
            $pdo->prepare(
                "INSERT INTO Messages (Sender, Destination, Text_Message, Date_Message, Read_Status)
                 VALUES (?, ?, ?, ?, 0)"
            )->execute([
                $company['ID_User'],
                $center['ID_User'],
                "Hola, soy el usuario de la empresa {$company['ID_User']}",
                $date
            ]);
            $j++;
        }
    }

    echo "✅ Seeder ejecutado correctamente\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
