<?php
/**
 * SEEDER COMPLETO – FP (6 proyectos)
 * Ejecutar: php seeder/index.php
 */

if (php_sapi_name() !== 'cli') {
    exit("Solo se puede ejecutar desde CLI\n");
}

include('../includes/database.php');

echo "🚀 Iniciando seeder...\n";

/* -------------------------------------------------
   COPIAR VÍDEOS A UPLOADS
-------------------------------------------------- */
echo "🎥 Copiando vídeos a uploads...\n";

$sourceDir = __DIR__ . '/videos/';
$targetDir = __DIR__ . '/../uploads/videos/';

if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

$name_videos = ["amazon", "cocacola", "mercedes", "microsoft","paypal","youtube"];
foreach ($name_videos as $video) {
    $sourceFile = $sourceDir . $video . ".mp4";
    $targetFile = $targetDir . $video . ".mp4";
    if (file_exists($sourceFile)) copy($sourceFile, $targetFile);
    else echo "⚠️ No se encontró: $video.mp4\n";
}

/* -------------------------------------------------
   COPIAR LOGOS A UPLOADS
-------------------------------------------------- */
echo "🎨 Copiando logos a uploads...\n";

$sourceDir = __DIR__ . '/logos/';
$targetDir = __DIR__ . '/../uploads/logos/';
if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

$name_logos = [
    "adobe","airbnb","amazon","apple","cocacola","faceboock","google","ibm","insanoia","insbaixcamp","insbesos",
    "insdelta","insebre","insesteve","insgarrotxa","insjoanXXIII","inslessalines","insmaresme","insmartipol",
    "insmediterrani","insmontsia","inspalafrugell","inspenedes","inspirineus","insriberabaixa","instecbcn","insvalles",
    "insvic","intel","mercedes","microsoft","netflix","oracle","paypal","samsumg","sony","sportify","tesla","uber","youtube"
];

foreach ($name_logos as $logo) {
    $sourceFile = $sourceDir . $logo . ".png";
    $targetFile = $targetDir . $logo . ".png";
    if (file_exists($sourceFile)) copy($sourceFile, $targetFile);
    else echo "⚠️ No se encontró: $logo.png\n";
}

try {
    /* -------------------------------------------------
       0. LIMPIAR BASE DE DATOS
    -------------------------------------------------- */
    echo "🧹 Limpiando base de datos...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tables = ['categories_project','categories_user','projects','likes','favorites','messages','users','categories'];
    foreach ($tables as $table) $pdo->exec("TRUNCATE $table");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    /* -------------------------------------------------
       1. FAMILIAS Y CICLOS
    -------------------------------------------------- */
    $famCicles = [
        'Informàtica i comunicacions' => ['Sistemes microinformàtics i xarxes','Administració de sistemes informàtics en xarxa','Desenvolupament d\'aplicacions multiplataforma','Desenvolupament d\'aplicacions web','Animacions 3D, jocs i entorns interactius'],
        'Administració i gestió' => ['Gestió administrativa','Administració i finances','Assistència a la direcció','Màrqueting i publicitat','Comerç internacional','Transport i logística','Agències de viatges i gestió d\'esdeveniments','Gestió d\'allotjaments turístics'],
        'Electricitat i electrònica' => ['Instal·lacions elèctriques i automàtiques','Sistemes electrotècnics i automatitzats','Electromecànica de maquinària','Automatització i robòtica industrial','Manteniment electrònic']
        // ... puedes agregar el resto de familias
    ];

    echo "🏷️ Insertando categorías...\n";
    $tagCicles = [];
    foreach ($famCicles as $family => $cycles) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, type, id_category_parent) VALUES (?, 'family', NULL)");
        $stmt->execute([$family]);
        $familyId = $pdo->lastInsertId();
        foreach ($cycles as $cycle) {
            $stmt = $pdo->prepare("INSERT INTO categories (name, type, id_category_parent) VALUES (?, 'cicle', ?)");
            $stmt->execute([$cycle, $familyId]);
            $tagCicles[$family][] = $pdo->lastInsertId();
        }
    }

    /* -------------------------------------------------
    2. USUARIOS (20 centros + 20 empresas)
    -------------------------------------------------- */
    echo "👤 Creando usuarios...\n";

    // 20 centros
    $centres = [
        'Institut Tecnològic de Barcelona','Institut La Ribera','Institut Montsià','Institut Vallès',
        'Institut Joan XXIII','Institut Delta','Institut Besòs','Institut Garrotxa',
        'Institut Ebre','Institut Maresme','Institut Penedès','Institut Miquel Martí i Pol',
        'Institut Empordà','Institut Les Salines','Institut Anoia','Institut Baix Camp',
        'Institut Vic','Institut Esteve Terradas i Illa','Institut Pirineu','Institut Mediterrani'
    ];

    // 20 empresas
    $empreses = [
        'Google','Microsoft','Amazon','Apple','Facebook','IBM','Intel','Oracle',
        'Samsung','Sony','CocaCola','Mercedes-Benz','Netflix','Tesla','Adobe','Uber',
        'Airbnb','Spotify','PayPal','YouTube'
    ];

    // logos de centros (debe coincidir con $centres)
    $centro_logo = [
        'instecbcn','insriberabaixa','insmontsia','insvalles','insjoanXXIII','insdelta','insbesos','insgarrotxa',
        'insebre','insmaresme','inspenedes','insmartipol','inspalafrugell','inslessalines','insanoia','insbaixcamp',
        'insvic','insesteve','inspirineus','insmediterrani'
    ];

    // logos de empresas (debe coincidir con $empreses)
    $empresa_logo = [
        'google','microsoft','amazon','apple','faceboock','ibm','intel','oracle',
        'samsumg','sony','cocacola','mercedes','netflix','tesla','adobe','uber',
        'airbnb','spotify','paypal','youtube'
    ];

    // Insertar centros
    foreach ($centres as $i => $nom) {
        $email = strtolower(preg_replace('/[^a-zA-Z]/','',$nom)).'@edu.cat';
        $pdo->prepare("INSERT INTO users (email,password,name,entity_name,entity_type,presentation,logo_image) VALUES (?,?,?,?,?,?,?)")
            ->execute([$email, hash('sha256','constraseña'.$i), $nom, $nom, 'center', "Usuario del centro $nom", "uploads/logos/".$centro_logo[$i].".png"]);
    }

    // Insertar empresas
    foreach ($empreses as $i => $nom) {
        $email = strtolower(preg_replace('/[^a-zA-Z]/','',$nom)).'@empresa.com';
        $pdo->prepare("INSERT INTO users (email,password,name,entity_name,entity_type,presentation,logo_image) VALUES (?,?,?,?,?,?,?)")
            ->execute([$email, hash('sha256','password'.$i), $nom, $nom, 'company', "Usuario de la empresa $nom", "uploads/logos/".$empresa_logo[$i].".png"]);
    }

    /* -------------------------------------------------
    3. PROJECTS (6 proyectos)
    -------------------------------------------------- */
    echo "📁 Insertando proyectos...\n";
    $stmt = $pdo->query("SELECT id FROM users WHERE entity_type='center' LIMIT 6");
    $centersUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $projects_titles = [
        'Enviaments ràpids amb Amazon', '¿Podries esbrinar el nou sabor de CocaCola?', 'Nou Mercedes-Benz elèctric', 'Prova el nou Office amb IA', 'Necessitem col·laboració amb el nostre nou model de Paypal', 'Volem crear un programa que ajudi a renderitzar un video más ràpid'
    ];
    
    $projects_videos = [
        'amazon', 'cocacola', 'mercedes', 'microsoft', 'paypal', 'youtube'
    ];

    $descriptions = [
        "Gaudeix de la màxima comoditat amb els nostres enviaments ràpids a través d'Amazon, dissenyats perquè rebis els teus productes en temps rècord. Gràcies a la seva logística avançada, garantim una entrega eficient i totalment segura directament a la teva porta. Ja no cal esperar: demana avui mateix i tingues el que necessites a les teves mans abans del que t'imagines.",
        "Prepara els teus sentits per a una experiència totalment inesperada i refrescant. T’atreveixes a acceptar el repte i esbrinar el nou sabor de Coca-Cola abans que ningú? No et quedis amb el dubte i deixa’t sorprendre per aquesta edició única que canviarà tot el que coneixies.",
        "Descobreix el futur de la conducció amb el nou Mercedes-Benz elèctric, on el luxe i la sostenibilitat s'uneixen en un disseny impecable. Experimenta una potència silenciosa i una tecnologia d'avantguarda que redefineixen cada quilòmetre del teu trajecte. Passa a l'emissió zero sense renunciar a l'elegància i al rendiment excepcional que només una estrella pot oferir.",
        "Porta la teva productivitat al següent nivell i prova el nou Office amb IA, l'eina definitiva per treballar de manera més intel·ligent. Deixa que la intel·ligència artificial redacti esborranys, resumeixi documents complexos i organitzi les teves dades en un obrir i tancar d'ulls. Transforma la teva rutina creativa i estalvia temps amb una experiència totalment integrada que pensa amb tu.",
        "Estem impulsant un canvi en el món dels pagaments digitals i necessitem col·laboració amb el nostre nou model de PayPal. El teu coneixement i la teva experiència són claus per optimitzar aquesta eina i fer-la més accessible per a tothom. Suma’t a aquest projecte innovador i ajuda’ns a definir el futur de les transaccions financeres amb total seguretat.",
        "Volem crear un programa que ajudi a renderitzar un vídeo més ràpid, eliminant les llargues esperes i optimitzant al màxim els recursos del teu ordinador. El nostre objectiu és oferir una eina fluida i potent que permeti als creadors de contingut centrar-se en la creativitat en lloc de la càrrega del sistema. Uneix-te a la nostra iniciativa i ajuda'ns a transformar el flux de treball en l'edició de vídeo professional.",
    ];
    $id_empresas = [1,2,3,4,5,6]; // ajustar según usuarios insertados

    foreach ($centersUsers as $i => $centerId) {
        // Insertar proyecto
        $pdo->prepare("INSERT INTO projects (title, description, video, date_creation, state, id_owner) VALUES (?,?,?,?,?,?)")
            ->execute([
                $projects_titles[$i],
                $descriptions[$i],
                "uploads/videos/".$projects_videos[$i].".mp4",
                date('Y-m-d'),
                'active',
                $id_empresas[$i]
            ]);
        
        $projectId = $pdo->lastInsertId(); // obtener id del proyecto insertado

        // Asignar entre 1 y 3 categorías aleatorias por proyecto
        $allCategoryIds = [];
        foreach ($tagCicles as $family => $cycles) {
            $allCategoryIds = array_merge($allCategoryIds, $cycles);
        }

        shuffle($allCategoryIds); // mezclar para aleatoriedad
        $categoriesToAssign = array_slice($allCategoryIds, 0, rand(1, 3));

        foreach ($categoriesToAssign as $categoryId) {
            $pdo->prepare("INSERT INTO categories_project (id_project, id_category) VALUES (?, ?)")
                ->execute([$projectId, $categoryId]);
        }
    }

   /* -------------------------------------------------
   4. ASIGNAR CATEGORÍAS ALEATORIAS A TODOS LOS 40 USUARIOS
-------------------------------------------------- */
echo "🏷️ Asignando categorías aleatorias a todos los usuarios...\n";

// Obtener todos los usuarios
$stmt = $pdo->query("SELECT id FROM users ORDER BY id ASC");
$allUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Todas las categorías cicle
$allCategoryIds = [];
foreach ($tagCicles as $family => $cycles) {
    $allCategoryIds = array_merge($allCategoryIds, $cycles);
}
$totalCategories = count($allCategoryIds);

// Asignar entre 3 y 5 categorías aleatorias por usuario
foreach ($allUsers as $userId) {
    shuffle($allCategoryIds); // mezclar categorías
    $categoriesToAssign = array_slice($allCategoryIds, 0, rand(3, 5));
    foreach ($categoriesToAssign as $categoryId) {
        $pdo->prepare("INSERT INTO categories_user (id_user, id_category) VALUES (?, ?)")
            ->execute([$userId, $categoryId]);
    }
}

    /* -------------------------------------------------
    5. FAVORITES
    -------------------------------------------------- */
    echo "⭐ Insertando favoritos...\n";

    $stmt = $pdo->query("SELECT id, entity_type FROM users");
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT id FROM projects ORDER BY id ASC");
    $allProjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($allUsers as $user) {
        if ($user['entity_type'] === 'center') {
            // Los primeros 2 proyectos
            for ($i = 0; $i < 2 && $i < count($allProjects); $i++) {
                $pdo->prepare("INSERT INTO favorites (id_user, id_project) VALUES (?, ?)")
                    ->execute([$user['id'], $allProjects[$i]]);
            }
        } else {
            // Los últimos 2 proyectos
            for ($i = count($allProjects) - 2; $i < count($allProjects); $i++) {
                $pdo->prepare("INSERT INTO favorites (id_user, id_project) VALUES (?, ?)")
                    ->execute([$user['id'], $allProjects[$i]]);
            }
        }
    }

    /* -------------------------------------------------
    6. LIKES
    -------------------------------------------------- */
    echo "❤️ Insertando likes...\n";

    foreach ($allUsers as $user) {
        if ($user['entity_type'] === 'center') {
            // Últimos 2 proyectos
            for ($i = count($allProjects) - 2; $i < count($allProjects); $i++) {
                $pdo->prepare("INSERT INTO likes (id_user, id_project) VALUES (?, ?)")
                    ->execute([$user['id'], $allProjects[$i]]);
            }
        } else {
            // Primeros 2 proyectos
            for ($i = 0; $i < 2 && $i < count($allProjects); $i++) {
                $pdo->prepare("INSERT INTO likes (id_user, id_project) VALUES (?, ?)")
                    ->execute([$user['id'], $allProjects[$i]]);
            }
        }
    }

    echo "✅ Seeder completo ejecutado correctamente\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
