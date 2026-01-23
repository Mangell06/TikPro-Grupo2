<?php
session_start();
include("includes/database.php");

$iduser = $_SESSION['user_id'] ?? 0;
if (!$iduser) {
    header("Location: login.php");
    exit;
}

// ====== GUARDAR CAMBIOS DEL USUARIO ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Editar usuario
    if (isset($_POST['save_user'])) {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $entity_name = $_POST['entity_name'] ?? '';
        $entity_type = $_POST['entity_type'] ?? '';
        $presentation = $_POST['presentation'] ?? '';
        $tfn = $_POST['tfn'] ?? '';
        $poblation = $_POST['poblation'] ?? '';

        $stmt = $pdo->prepare("
            UPDATE users
            SET name = :name,
                email = :email,
                entity_name = :entity_name,
                entity_type = :entity_type,
                entity_type = :entity_type,
                tfn = :tfn,
                poblation = :poblation
            WHERE id = :id
        ");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'entity_name' => $entity_name,
            'entity_type' => $entity_type,
            'presentation' => $presentation,
            'tfn' => $tfn,
            'poblation' => $poblation,
            'id' => $iduser
        ]);

        $sqlTagDelete = "DELETE from categories_user where id_user = ?";
        $stmtTagDelete = $pdo->prepare($sqlTagDelete);
        $stmtTagDelete -> execute([$iduser]);

        $etiquetes_seleccionades = $_POST['categories'] ?? [];
            if (!empty($etiquetes_seleccionades)) {
                foreach ($etiquetes_seleccionades as $id_categoria) {
                    try {
                        $sql = "INSERT INTO categories_user (id_user, id_category) VALUES (?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$iduser, $id_categoria]);
                    } catch (\Throwable $th) {
                    }
                }
            }
    }

    // // Añadir nueva etiqueta
    // if (isset($_POST['add_tag']) && !empty($_POST['new_tag'])) {
    //     $tagName = trim($_POST['new_tag']);

    //     if ($tagName !== '') {
    //         $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = :name LIMIT 1");
    //         $stmt->execute(['name' => $tagName]);
    //         $category = $stmt->fetch();

    //         if ($category) {
    //             $id = $category['id'];
    //         } else {
    //             $stmt = $pdo->prepare("
    //                 INSERT INTO categories (name, type)
    //                 VALUES (:name, :type)
    //             ");
    //             $stmt->execute([
    //                 'name' => $tagName,
    //                 'type' => 'family'
    //             ]);
    //             $id = $pdo->lastInsertId();
    //         }

    //         $stmt = $pdo->prepare("
    //             SELECT 1 FROM categories_user
    //             WHERE id = :user AND id = :cat
    //         ");
    //         $stmt->execute([
    //             'user' => $iduser,
    //             'cat' => $id
    //         ]);

    //         if (!$stmt->fetch()) {
    //             $stmt = $pdo->prepare("
    //                 INSERT INTO categories_user (id, id)
    //                 VALUES (:user, :cat)
    //             ");
    //             $stmt->execute([
    //                 'user' => $iduser,
    //                 'cat' => $id
    //             ]);
    //         }
    //     }
    // }

    // // Eliminar etiqueta
    // if (isset($_POST['delete_tag']) && !empty($_POST['delete_tag_id'])) {
    //     $id = (int)$_POST['delete_tag_id'];
    //     $stmt = $pdo->prepare("
    //         DELETE FROM categories_user
    //         WHERE id_user = :user AND id_category = :cat
    //     ");
    //     $stmt->execute([
    //         'user' => $iduser,
    //         'cat' => $id
    //     ]);
    // }

    header("Location: profile.php?success=true");
    exit;
}

// ====== CARGAR DATOS DEL USUARIO ======
$stmt = $pdo->prepare("
    SELECT name, email, entity_name, entity_type, presentation, tfn, poblation
    FROM users
    WHERE id = :id
    LIMIT 1
");
$stmt->execute(['id' => $iduser]);
$user = $stmt->fetch();

// ====== CARGAR ETIQUETAS DEL USUARIO ======
$stmtTags = $pdo->prepare(
    "SELECT c.id, c.name, c.type, cu.id_category 
    FROM categories c
    JOIN categories_user cu ON cu.id_category = c.id
    WHERE cu.id_user = :user_id;
");
$stmtTags->execute(['user_id' => $iduser]);
$tags = $stmtTags->fetchAll(PDO::FETCH_ASSOC);
if (!$tags) {
    $tags = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil</title>
    <link rel="stylesheet" href="/styles.css?q=2">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body id="profile-body">

<header class="main-header">
    <div class="close-session">
            <?php        
        if ($user) {
            echo "<h3 class='user'>" . htmlspecialchars($user['name']) . "</h3>";
        } else {
            echo "<h1>Usuari no encontrat</h1>";
        }
        ?>
        
        <a href="logout.php" id="nav-logout" class="logout-button">Tancar sessió</a>
    
        </div>
</header>

<main class="profile-edit-container">

    <!-- DATOS USUARIO -->
    <section class="profile-card">
        <h2 class="profile-section-title">Dades de l'entitat</h2>

        <form method="POST">
            <div>
                <div class="profile-field">
                <label>Nom i cognoms</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
             <div>
                <div class="profile-field">
                <label>Telèfon</label>
                <input type="text" name="tfn" value="<?= htmlspecialchars($user['tfn']) ?>">
                <label>Ciutat</label>
                <input type="email" name="ciutat" value="<?= htmlspecialchars($user['poblation']) ?>">
            </div>

          <div class="profile-field">
                <label>Nom entitat</label>
                <input type="text" name="entity_name" value="<?= htmlspecialchars($user['entity_name']) ?>">
                <label>Tipus entitat</label>
                <select name="entity_type" class="select">
                    <option value="company" <?= $user['entity_type'] === 'company' ? 'selected' : '' ?>>Empresa</option>
                    <option value="center" <?= $user['entity_type'] === 'center' ? 'selected' : '' ?>>Centre</option>
                </select>
            </div>


            <div class="profile-field">
                <label>Presentació</label>
                <textarea name="presentation"><?= htmlspecialchars($user['presentation']) ?></textarea>
            </div>
            <label>Etiquetes</label>
            <div id="etiquetes-contenidor" class="tags-wrapper"></div>
            <button type="button" id="btnObrirModal" class="buttonEtiquetes">Afegir etiqueta</button>
            <button type="submit" name="add_tag" disabled>+ Afegir</button>
            </div>
            <button type="submit" name="save_user" class="buttonEtiquetes">Guardar</button>
        </form>
    </section>

    

    <!-- PROJECTES -->
    <section class="profile-card">
        <div class="profile-projects-header">
            <h2 class="profile-section-title">Projectes</h2>
            <input type="button" class="profile-new-project" onclick="window.location.href='edit_project.php'" value="+ Nou projecte">
        </div>

        <div class="profile-project-list">
            <?php
            $stmt = $pdo->prepare("
                SELECT id, title, logo_image
                FROM projects
                WHERE id_owner = :id
                ORDER BY id DESC
            ");
            $stmt->execute(['id' => $iduser]);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($projects):
                foreach ($projects as $proj):
            ?>
                <div class="profile-project-item">
                    <a href="edit_project.php?project_id=<?= (int)$proj['id'] ?>">
                        <?php if (!empty($proj['logo_image'])): ?>
                            <img class="imagePreviewProfile" src="<?= htmlspecialchars($proj['logo_image']) ?>" alt="Logo">
                            <!-- <video src="<?= htmlspecialchars($proj['video']) ?>" muted playsinline preload="metadata"></video> -->
                        <?php else: ?>
                            <div class="profile-project-placeholder">Sense imatge</div>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($proj['title']) ?></span>
                    </a>
                </div>
            <?php
                endforeach;
            else:
            ?>
                <p class="profile-no-projects">Encara no tens projectes.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- LINKS -->
    <section class="profile-links">
        <a href="chat.php" class="logout-button">Converses</a>
        <a href="discover.php" class="logout-button">Descobrir</a>
    </section>
<div id="modalCerca" class="custom-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Cerca Familia o Cicle</h3>
            <span class="close-btn-modal" id="btnTancarModal">&times;</span>
        </div>
        <div class="modal-body">
            <input type="text" id="inputCerca" placeholder="Escriu al menys 3 caràcters..." autocomplete="off">
            <div id="llistaResultats" class="results-list"></div>
        </div>
    </div>
</div>
<script type="module">
    import { showNotification } from './notificaciones.js';
    const urlParams = new URLSearchParams(window.location.search);
    window.addEventListener('DOMContentLoaded', () => {
        if (urlParams.get('action') === 'cancelled') {
            showNotification("info", "S'ha cancelat el procéss");
            window.history.replaceState({}, document.title, "profile.php");
        }

        if (urlParams.get('error') === 'no_permission') {
            showNotification("error", "No tens accés a editar aquest projecte");
            window.history.replaceState({}, document.title, "profile.php");
        }

        if (urlParams.get('success') === 'true') {
            showNotification("info", "S'han guardat els canvis correctament!");
            window.history.replaceState({}, document.title, "profile.php");
        }
    });

const tags = <?php echo json_encode($tags) ?>;
tags.forEach((cat) => {
    afegirEtiqueta(cat.id_category, cat.name)
});


const modal = document.getElementById('modalCerca');
const inputCerca = document.getElementById('inputCerca');
const llistaResultats = document.getElementById('llistaResultats');
const contenidorEtiquetes = document.getElementById('etiquetes-contenidor');

    // 1. Obrir/Tancar Modal
document.getElementById('btnObrirModal').onclick = () => modal.style.display = 'block';
document.getElementById('btnTancarModal').onclick = () => {
    modal.style.display = 'none';
    inputCerca.value = '';
    llistaResultats.innerHTML = '';
};

// 2. Cerca AJAX
inputCerca.addEventListener('input', function() {
    const text = this.value.trim();
    
    if (text.length >= 3) {
        // Simulem la petició AJAX (has de canviar la URL pel teu fitxer PHP)
        fetch(`search_tags.php?q=${encodeURIComponent(text)}`)
            .then(res => res.json())
            .then(data => {
                llistaResultats.innerHTML = '';
                data.forEach(cat => {
                    const div = document.createElement('div');
                    div.className = 'result-item';
                    div.textContent = cat.name; // 'name' de la taula categories
                    div.onclick = () => afegirEtiqueta(cat.id, cat.name);
                    llistaResultats.appendChild(div);
                });
            });
    } else {
        llistaResultats.innerHTML = '';
    }
});

// 3. Aplicar Selecció i Tancar
function afegirEtiqueta(id, nom) {
    const contenidor = document.getElementById('etiquetes-contenidor');

    // 1. Creamos el elemento visual (el badge)
    const div = document.createElement('div');
    div.className = 'tag-badge';
    div.style.display = 'inline-flex';
    div.style.alignItems = 'center';
    div.style.margin = '5px';
    div.style.padding = '5px 10px';
    div.style.background = 'rgb(148, 136, 130)';
    div.style.color = 'white';
    div.style.borderRadius = '15px';

    // 2. Creamos el INPUT OCULTO que se mandará por POST
    // Usamos name="categories[]" para recibir un array en el servidor
    const inputHidden = document.createElement('input');
    inputHidden.type = 'hidden';
    inputHidden.name = 'categories[]'; 
    inputHidden.value = id; // El ID de la base de datos (p.ej: 5)

    // 3. Contenido del badge (Texto + Botón eliminar)
    div.innerHTML = `<span>${nom}</span>`;
    
    const btnEliminar = document.createElement('span');
    btnEliminar.innerHTML = ' &times;';
    btnEliminar.style.cursor = 'pointer';
    btnEliminar.style.marginLeft = '10px';
    
    // Al eliminar el div, el inputHidden que está dentro también desaparece
    btnEliminar.onclick = function() {
        div.remove();
    };

    // 4. Ensamblamos: metemos el input dentro del div, y el div en el contenedor
    div.appendChild(inputHidden);
    div.appendChild(btnEliminar);
    contenidor.appendChild(div);

    // Cerrar modal
    document.getElementById('modalCerca').style.display = 'none';
}
</script>
</main>
</body>
</html>
