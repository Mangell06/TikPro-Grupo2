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
        $name = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $tfn = $_POST['tfn'] ?? '';
        $poblation = $_POST['poblation'] ?? '';
        $entity_name = $_POST['entity_name'] ?? '';
        $entity_type = $_POST['entity_type'] ?? '';
        $presentation = $_POST['presentation'] ?? '';

        $stmt = $pdo->prepare("
            UPDATE users
            SET name = :name,
                email = :email,
                tfn = :tfn,
                poblation = :poblation,
                entity_name = :entity_name,
                entity_type = :entity_type,
                presentation = :presentation
            WHERE id = :id
        ");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'tfn' => $tfn,
            'poblation' => $poblation,
            'entity_name' => $entity_name,
            'entity_type' => $entity_type,
            'presentation' => $presentation,
            'id' => $iduser
        ]);

        $_SESSION['message_type'] = 'success';
        $_SESSION['message_text'] = 'Perfil guardado correctamente';
    }

    // Añadir nueva etiqueta
    if (isset($_POST['add_tag']) && !empty($_POST['new_tag'])) {
        $tagName = trim($_POST['new_tag']);

        if ($tagName !== '') {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = :name LIMIT 1");
            $stmt->execute(['name' => $tagName]);
            $category = $stmt->fetch();

            if ($category) {
                $id_category = $category['id'];
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO categories (name, type)
                    VALUES (:name, :type)
                ");
                $stmt->execute([
                    'name' => $tagName,
                    'type' => 'family'
                ]);
                $id_category = $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare("
                SELECT 1 FROM categories_user
                WHERE id_user = :user AND id_category = :cat
            ");
            $stmt->execute([
                'user' => $iduser,
                'cat' => $id_category
            ]);

            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("
                    INSERT INTO categories_user (id_user, id_category)
                    VALUES (:user, :cat)
                ");
                $stmt->execute([
                    'user' => $iduser,
                    'cat' => $id_category
                ]);
                $_SESSION['message_type'] = 'success';
                $_SESSION['message_text'] = 'Etiqueta añadida correctamente';
            } else {
                $_SESSION['message_type'] = 'info';
                $_SESSION['message_text'] = 'La etiqueta ya existe';
            }
        }
    }

    // Eliminar etiqueta
    if (isset($_POST['delete_tag']) && !empty($_POST['delete_tag_id'])) {
        $id_category = (int)$_POST['delete_tag_id'];
        $stmt = $pdo->prepare("
            DELETE FROM categories_user
            WHERE id_user = :user AND id_category = :cat
        ");
        $stmt->execute([
            'user' => $iduser,
            'cat' => $id_category
        ]);

        $_SESSION['message_type'] = 'success';
        $_SESSION['message_text'] = 'Etiqueta eliminada correctamente';
    }

    header("Location: profile.php");
    exit;
}

// ====== CARGAR DATOS DEL USUARIO ======
$stmt = $pdo->prepare("
    SELECT name, email, tfn, poblation, entity_name, entity_type, logo_image, presentation
    FROM users
    WHERE id = :id
    LIMIT 1
");
$stmt->execute(['id' => $iduser]);
$user = $stmt->fetch();

// ====== CARGAR ETIQUETAS DEL USUARIO ======
$stmtTags = $pdo->prepare("
    SELECT c.id, c.name, c.type
    FROM categories c
    JOIN categories_user cu ON cu.id_category = c.id
    WHERE cu.id_user = :user_id
");
$stmtTags->execute(['user_id' => $iduser]);
$tags = $stmtTags->fetchAll(PDO::FETCH_ASSOC);

// ====== CARGAR PROYECTOS DEL USUARIO ======
$stmtProjects = $pdo->prepare("
    SELECT id, title, video
    FROM projects
    WHERE id_owner = :id
    ORDER BY id DESC
");
$stmtProjects->execute(['id' => $iduser]);
$projects = $stmtProjects->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil</title>
    <link rel="stylesheet" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body id="profile-body">

<header class="profile-header">
    <h1 class="profile-title">Perfil</h1>
</header>

<main class="profile-container">

    <!-- DATOS USUARIO -->
    <section class="profile-card">
        <h2 class="profile-section-title">Datos de la entidad</h2>

        <form method="POST">
            <div class="profile-field">
                <label>Nombre y apellidos</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['name']) ?>">
            </div>

            <div class="profile-field">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
            </div>

            <div class="profile-field">
                <label>Teléfono</label>
                <input type="text" name="tfn" value="<?= htmlspecialchars($user['tfn']) ?>">
            </div>

            <div class="profile-field">
                <label>Localidad</label>
                <input type="text" name="poblation" value="<?= htmlspecialchars($user['poblation']) ?>">
            </div>

            <div class="profile-field">
                <label>Nombre entidad</label>
                <input type="text" name="entity_name" value="<?= htmlspecialchars($user['entity_name']) ?>">
            </div>

            <div class="profile-field">
                <label>Tipo entidad</label>
                <input type="text" name="entity_type" value="<?= htmlspecialchars($user['entity_type']) ?>">
            </div>

            <div class="profile-field">
                <label>Presentación</label>
                <textarea name="presentation"><?= htmlspecialchars($user['presentation']) ?></textarea>
            </div>

            <button type="submit" name="save_user">Guardar</button>
        </form>
    </section>

    <!-- ETIQUETAS -->
    <section class="profile-card">
        <h2 class="profile-section-title">Etiquetas</h2>

        <div class="profile-tags">
            <?php foreach ($tags as $t): ?>
                <form method="POST" style="display:inline-block;">
                    <span class="profile-tag">
                        <?= htmlspecialchars($t['name']) ?>
                        <input type="hidden" name="delete_tag_id" value="<?= $t['id'] ?>">
                        <button type="submit" name="delete_tag">✕</button>
                    </span>
                </form>
            <?php endforeach; ?>
        </div>

        <form method="POST" style="margin-top:10px;">
            <input type="text" name="new_tag" placeholder="Nueva etiqueta" required>
            <button type="submit" name="add_tag">+ Añadir</button>
        </form>
    </section>

    <!-- PROYECTOS -->
    <section class="profile-card">
        <div class="profile-projects-header">
            <h2 class="profile-section-title">Proyectos</h2>
            <button type="button" class="profile-new-project">+ Nuevo proyecto</button>
        </div>

        <div class="profile-project-list">
            <?php if ($projects): ?>
                <?php foreach ($projects as $proj): ?>
                    <div class="profile-project-item">
                        <a href="project.php?id=<?= (int)$proj['id'] ?>">
                            <?php if (!empty($proj['video'])): ?>
                                <video src="<?= htmlspecialchars($proj['video']) ?>" muted playsinline preload="metadata"></video>
                            <?php else: ?>
                                <div class="profile-project-placeholder">Sin vídeo</div>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($proj['title']) ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="profile-no-projects">Aún no tienes proyectos.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- LINKS -->
    <section class="profile-links">
        <a href="chat.php" class="profile-link-btn">💬 Conversaciones</a>
        <a href="discover.php" class="profile-link-btn">🔥 Descubrir</a>
    </section>

</main>

<!-- ALERTAS DE MENSAJE -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="module">
import { showNotification } from './notificaciones.js';

<?php if (!empty($_SESSION['message_text'])): ?>
showNotification(
    <?= json_encode($_SESSION['message_type']) ?>,
    <?= json_encode($_SESSION['message_text']) ?>,
    <?= json_encode($user['name']) ?>
);
<?php 
unset($_SESSION['message_text'], $_SESSION['message_type']);
endif; ?>
</script>

</body>
</html>
