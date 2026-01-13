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

/* -------------------------------------------------
   COPIAR VÍDEOS A UPLOADS
-------------------------------------------------- */
echo "🎥 Copiando vídeos a uploads...\n";

$sourceDir = __DIR__ . '/videos/';
$targetDir = __DIR__ . '/../uploads/videos/';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$name_videos = ["amazon", "cocacola", "mercedes", "microsoft","paypal","youtube"];

for ($i = 1; $i <= 6; $i++) {
    $sourceFile = $sourceDir . $name_videos[$i-1] . ".mp4";
    $targetFile = $targetDir . $name_videos[$i-1] . ".mp4";

    if (file_exists($sourceFile)) {
        copy($sourceFile, $targetFile);
    } else {
        echo "⚠️ No se encontró: " . $name_videos[$i-1] . ".mp4\n";
    }
}

/* -------------------------------------------------
   COPIAR LOGOS A UPLOADS
-------------------------------------------------- */
echo "🎥 Copiando logos a uploads...\n";

$sourceDir = __DIR__ . '/logos/';
$targetDir = __DIR__ . '/../uploads/logos/';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$name_logos = [
    "adobe", "airbnb", "amazon", "apple","cocacola","faceboock", "google", "ibm", "insanoia", "insbaixcamp", "insbesos", 
    "insdelta", "insebre", "insesteve", "insgarrotxa", "insjoanXXIII", "inslessalines", "insmaresme", "insmartipol", 
    "insmediterrani", "insmontsia", "inspalafrugell", "inspenedes", "inspirineus", "insriberabaixa", "instecbcn", "insvalles", 
    "insvic", "intel", "mercedes", "microsoft", "netflix", "oracle", "paypal", "samsumg", "sony", "sportify", "tesla", "uber", "youtube"
];

for ($i = 1; $i <= 40; $i++) {
    $sourceFile = $sourceDir . $name_logos[$i-1] . ".png";
    $targetFile = $targetDir . $name_logos[$i-1] . ".png";

    if (file_exists($sourceFile)) {
        copy($sourceFile, $targetFile);
    } else {
        echo "⚠️ No se encontró: " . $name_logos[$i-1] . ".png\n";
    }
}


try {

    /* -------------------------------------------------
       0. LIMPIAR BASE DE DATOS
    -------------------------------------------------- */
    echo "🧹 Limpiando base de datos...\n";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE category_project");
    $pdo->exec("TRUNCATE category_user");
    $pdo->exec("TRUNCATE projects");
    $pdo->exec("TRUNCATE likes");
    $pdo->exec("TRUNCATE favorites");
    $pdo->exec("TRUNCATE messages");
    $pdo->exec("TRUNCATE users");
    $pdo->exec("TRUNCATE categories");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    /* -------------------------------------------------
       1. FAMILIAS Y CICLOS
    -------------------------------------------------- */
    $famCicles = [
        'Informàtica i comunicacions' => [
            'Sistemes microinformàtics i xarxes',
            'Administració de sistemes informàtics en xarxa',
            'Desenvolupament d\'aplicacions multiplataforma',
            'Desenvolupament d\'aplicacions web',
            'Animacions 3D, jocs i entorns interactius'
        ],
        'Administració i gestió' => [
            'Gestió administrativa',
            'Administració i finances',
            'Assistència a la direcció',
            'Màrqueting i publicitat',
            'Comerç internacional',
            'Transport i logística',
            'Agències de viatges i gestió d\'esdeveniments',
            'Gestió d\'allotjaments turístics'
        ],
        'Electricitat i electrònica' => [
            'Instal·lacions elèctriques i automàtiques',
            'Sistemes electrotècnics i automatitzats',
            'Electromecànica de maquinària',
            'Automatització i robòtica industrial',
            'Manteniment electrònic'
        ],
        'Construcció i obra civil' => [
            'Construcció',
            'Obres d\'interior, decoració i rehabilitació',
            'Projectes d\'obra civil',
            'Projectes d\'edificació',
            'Organització i control d\'obres de construcció',
            'Construccions metàl·liques'
        ],
        'Indústria i fabricació' => [
            'Soldadura i caldereria',
            'Mecanització',
            'Fusteria i moble',
            'Disseny i moblament',
            'Programació de la producció en fabricació mecànica',
            'Disseny en fabricació mecànica'
        ],
        'Hostaleria i alimentació' => [
            'Cuina i gastronomia',
            'Serveis en restauració',
            'Elaboració de productes alimentaris',
            'Forneria, pastisseria i confiteria',
            'Vitivinicultura',
            'Processos i qualitat en la indústria alimentària'
        ],
        'Sanitat i serveis socials' => [
            'Emergències sanitàries',
            'Atenció a persones en situació de dependència',
            'Farmàcia i parafarmàcia',
            'Electromedicina clínica',
            'Animació sociocultural i turística',
            'Educació Infantil',
            'Integració social'
        ],
        'Arts i imatge' => [
            'Vídeo, discjòquei i so',
            'Realització de projectes d\'audiovisuals i espectacles',
            'Il·luminació, captació i tractament d\'imatge',
            'So per a audiovisuals i espectacles',
            'Producció d\'audiovisuals i espectacles',
            'Estètica i bellesa',
            'Perruqueria i cosmètica capil·lar',
            'Estètica integral i benestar',
            'Caracterització i maquillatge professional',
            'Assessoria d\'imatge personal i corporativa',
            'Estilisme i direcció de perruqueria'
        ],
        'Agricultura i medi natural' => [
            'Producció agropecuària',
            'Producció agroecològica',
            'Aprofitament i conservació del medi natural',
            'Jardineria i floristeria',
            'Activitats eqüestres',
            'Gestió forestal i del medi natural',
            'Paisatgisme i medi rural'
        ],
        'Marítim i pesca' => [
            'Navegació i pesca de litoral',
            'Cultius Aqüícoles',
            'Operacions subaquàtiques i hiperbàriques',
            'Transport marítim i pesca d\'altura',
            'Manteniment i control de la maquinària de vaixells i embarcacions'
        ],
        'Química i laboratori' => [
            'Planta química',
            'Planta química (productes farmacèutics i cosmètics)',
            'Operacions de laboratori',
            'Laboratori d\'anàlisi i control de qualitat',
            'Laboratori clínic i biomèdic',
            'Radioteràpia i dosimetria',
            'Audiologia protètica',
            'Higiene bucodental',
            'Imatge per al diagnòstic i medicina nuclear'
        ],
        'Textil i moda' => [
            'Fabricació i ennobliment de productes tèxtils',
            'Confecció i moda',
            'Disseny tècnic en tèxtil i pell',
            'Vestuari a mida i d\'espectacles',
            'Patronatge i moda'
        ]
    ];


    echo "🏷️ Insertando categorías...\n";
    $tagCicles = [];

    foreach ($famCicles as $family => $cycles) {
        $stmt = $pdo->prepare(
            "INSERT INTO categories (name_category, Type, id_category_parent)
             VALUES (?, 'family', NULL)"
        );
        $stmt->execute([$family]);
        $familyId = $pdo->lastInsertId();

        foreach ($cycles as $cycle) {
            $stmt = $pdo->prepare(
                "INSERT INTO categories (name_category, Type, id_category_parent)
                 VALUES (?, 'cicle', ?)"
            );
            $stmt->execute([$cycle, $familyId]);
            $tagCicles[$family][] = $pdo->lastInsertId();
        }
    }

    /* -------------------------------------------------
       2. USUARIOS (20 centros + 20 empresas)
    -------------------------------------------------- */
    echo "👤 Creando usuarios...\n";

    $centres = [
        'Institut Tecnològic de Barcelona', 'Institut La Ribera',
        'Institut Montsià', 'Institut Vallès',
        'Institut Joan XXIII', 'Institut Delta',
        'Institut Mediterrani', 'Institut Pirineu',
        'Institut Besòs', 'Institut Garrotxa',
        'Institut Ebre', 'Institut Maresme',
        'Institut Penedès', 'Institut Miquel Martí i Pol',
        'Institut Empordà', 'Institut Les Salines',
        'Institut Anoia', 'Institut Baix Camp',
        'Institut Vic', 'Institut Esteve Terradas i Illa'
    ];


    $empreses = [
        'Google', 'Microsoft', 'Amazon', 'Apple',
        'Facebook', 'IBM', 'Intel', 'Oracle',
        'Samsung', 'Sony', 'CocaCola', 'Mercedes-Benz',
        'Netflix', 'Tesla', 'Adobe', 'Uber',
        'Airbnb', 'Spotify', 'PayPal', 'YouTube'
    ];

    $centro_logo = [
        'instecbcn', 'insriberabaixa', 'insmontsia', 'insvalles', 'insjoanXXIII', 'insdelta', 'insmediterrani', 'inspirineus', 'insbesos', 'insgarrotxa',
        'insebre', 'insmaresme', 'inspenedes', 'insmartipol', 'inspalafrugell', 'inslessalines', 'insanoia', 'insbaixcamp', 'insvic', 'insesteve'
    ];

    // Centros
    foreach ($centres as $i => $nom) {
        $email = strtolower(preg_replace('/[^a-zA-Z]/', '', $nom)) . '@edu.cat';
        $username = 'center' . ($i + 1);
        $pdo->prepare(
            "INSERT INTO users (email, password, username, entity_name, entity_type, presentation, logo_image)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        )->execute([
            $email,
            hash('sha256','constraseña' . $i),
            $username,
            $nom,
            'Center',
            "Usuario del centro $nom",
            "uploads/logos/" . $centro_logo[$i] . ".png"
        ]);
    }

    $empresa_logo = [
        'google', 'microsoft', 'amazon', 'apple', 'faceboock', 'ibm', 'intel', 'oracle', 'samsumg', 'sony', 'cocacola', 'mercedes',
        'netflix', 'tesla', 'adobe', 'uber', 'airbnb', 'spotify', 'paypal', 'youtube'
    ];

    // Empresas
    foreach ($empreses as $i => $nom) {
        $email = strtolower(preg_replace('/[^a-zA-Z]/', '', $nom)) . '@empresa.com';
        $username = 'company' . ($i + 1);
        $pdo->prepare(
            "INSERT INTO users (email, password, username, entity_name, entity_type, presentation, logo_image)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        )->execute([
            $email,
            hash('sha256','password' . $i),
            $username,
            $nom,
            'Company',
            "Usuario de la empresa $nom",
            "uploads/logos/" . $empresa_logo[$i] . ".png",
        ]);
    }

    /* -------------------------------------------------
       3. PROJECTS (solo 6)
    -------------------------------------------------- */
    echo "📁 Insertando proyectos...\n";

    $stmt = $pdo->query("SELECT id_user, entity_name FROM users WHERE entity_type = 'center' LIMIT 6");
    $centersUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $projects_titles = [
        'Amazon', 'CocaCola', 'Mercedes-Benz', 'Microsoft', 'PayPal', 'YouTube'
    ];
    
    $projects_videos = [
        'amazon', 'cocacola', 'mercedes', 'microsoft', 'paypal', 'youtube'
    ];

    $descriptions = [
        'Campaña de vídeo promocional para destacar ofertas y envíos rápidos de Amazon.',
        'Vídeo de marketing mostrando la experiencia refrescante de CocaCola en eventos.',
        'Proyecto audiovisual resaltando el diseño y la innovación de los nuevos modelos Mercedes.',
        'Serie de vídeos educativos sobre el uso de herramientas Microsoft en la oficina.',
        'Vídeo explicativo sobre cómo usar PayPal para pagos seguros y rápidos.',
        'Campaña de vídeos virales para promocionar contenido y funciones de YouTube.'
    ];

    $id_empresas = [
        '23', '31', '32', '22', '39', '40'
    ];

    foreach ($centersUsers as $i => $center) {
        $pdo->prepare(
            "INSERT INTO projects (title, description, video, date_creation, state, id_owner)
             VALUES (?, ?, ?, ?, ?, ?)"
        )->execute([
            $projects_titles[$i],
            $descriptions[$i],
            "uploads/videos/" . $projects_videos[$i] . ".mp4",
            date('Y-m-d'),
            'Active',
            $id_empresas[$i]
        ]);
    }

    /* -------------------------------------------------
       4. category_project
    -------------------------------------------------- */
    echo "🏷️ Asignando categorías a proyectos...\n";

    $stmt = $pdo->query("SELECT id_project FROM projects ORDER BY id_project ASC");
    $projects = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($projects as $i => $projectId) {
        $family = array_keys($tagCicles)[$i % count($tagCicles)];
        foreach ($tagCicles[$family] as $catId) {
            $pdo->prepare(
                "INSERT INTO category_project (id_project, id_category)
                 VALUES (?, ?)"
            )->execute([$projectId, $catId]);
        }
    }

    /* -------------------------------------------------
       5. category_user
    -------------------------------------------------- */
    echo "🏷️ Asignando categorías a usuarios...\n";

    $stmt = $pdo->query("SELECT id_user FROM users ORDER BY id_user ASC");
    $category_users = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($category_users as $i => $userId) {
        $family = array_keys($tagCicles)[$i % count($tagCicles)];
        foreach ($tagCicles[$family] as $catId) {
            $pdo->prepare(
                "INSERT INTO category_user (id_user, id_category)
                 VALUES (?, ?)"
            )->execute([$userId, $catId]);
        }
    }

    /* -------------------------------------------------
       6. FAVORITES
    -------------------------------------------------- */
    echo "⭐ Insertando favoritos...\n";

    $stmt = $pdo->query("SELECT id_user, entity_type FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        if ($user['entity_type'] === 'Center') {
            for ($i = 0; $i < 2 && $i < count($projects); $i++) {
                $pdo->prepare(
                    "INSERT INTO favorites (id_user, id_project)
                     VALUES (?, ?)"
                )->execute([$user['id_user'], $projects[$i]]);
            }
        } else {
            for ($i = count($projects) - 2; $i < count($projects); $i++) {
                $pdo->prepare(
                    "INSERT INTO favorites (id_user, id_project)
                     VALUES (?, ?)"
                )->execute([$user['id_user'], $projects[$i]]);
            }
        }
    }

    /* -------------------------------------------------
       7. LIKES
    -------------------------------------------------- */
    echo "❤️ Insertando likes...\n";

    foreach ($users as $user) {
        if ($user['entity_type'] === 'Center') {
            for ($i = count($projects) - 2; $i < count($projects); $i++) {
                $pdo->prepare(
                    "INSERT INTO likes (id_user, id_project)
                     VALUES (?, ?)"
                )->execute([$user['id_user'], $projects[$i]]);
            }
        } else {
            for ($i = 0; $i < 2 && $i < count($projects); $i++) {
                $pdo->prepare(
                    "INSERT INTO likes (id_user, id_project)
                     VALUES (?, ?)"
                )->execute([$user['id_user'], $projects[$i]]);
            }
        }
    }

    /* -------------------------------------------------
       8. MESSAGES
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
                "INSERT INTO messages (sender, destination, text_message, date_message, read_status)
                 VALUES (?, ?, ?, ?, 0)"
            )->execute([
                $center['id_user'],
                $company['id_user'],
                "Hola, soy el usuario del centro {$center['id_user']}",
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
                "INSERT INTO messages (sender, destination, text_message, date_message, read_status)
                 VALUES (?, ?, ?, ?, 0)"
            )->execute([
                $company['id_user'],
                $center['id_user'],
                "Hola, soy el usuario de la empresa {$company['id_user']}",
                $date
            ]);
            $j++;
        }
    }

    echo "✅ Seeder ejecutado correctamente\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
