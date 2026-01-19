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
        $username = $_POST['username'] ?? '';
        $entity_name = $_POST['entity_name'] ?? '';
        $entity_type = $_POST['entity_type'] ?? '';
        $presentation = $_POST['presentation'] ?? '';

        $stmt = $pdo->prepare("
            UPDATE users
            SET username = :username,
                entity_name = :entity_name,
                entity_type = :entity_type,
                presentation = :presentation
            WHERE id_user = :id
        ");
        $stmt->execute([
            'username' => $username,
            'entity_name' => $entity_name,
            'entity_type' => $entity_type,
            'presentation' => $presentation,
            'id' => $iduser
        ]);
        $success_msg = "Dades guardades correctament!";
    }

    // Añadir nueva etiqueta
    if (isset($_POST['add_tag']) && !empty($_POST['new_tag'])) {
        $tagName = trim($_POST['new_tag']);

        if($tagName !== '') {
            // Buscar categoría existente
            $stmt = $pdo->prepare("SELECT id_category FROM categories WHERE name_category = :name LIMIT 1");
            $stmt->execute(['name' => $tagName]);
            $category = $stmt->fetch();

            if ($category) {
                $id_category = $category['id_category'];
            } else {
                // Crear nueva categoría, tipo 'family' por defecto
                $stmt = $pdo->prepare("INSERT INTO categories (name_category, type) VALUES (:name, :type)");
                $stmt->execute([
                    'name' => $tagName,
                    'type' => 'family' // Cambiado a valor válido del ENUM
                ]);
                $id_category = $pdo->lastInsertId();
            }

            // Insertar relación en category_user si no existe
            $stmt = $pdo->prepare("SELECT 1 FROM category_user WHERE id_user = :user AND id_category = :cat");
            $stmt->execute(['user'=>$iduser,'cat'=>$id_category]);
            if(!$stmt->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO category_user (id_user, id_category) VALUES (:user, :cat)");
                $stmt->execute(['user'=>$iduser,'cat'=>$id_category]);
            }
        }
    }

    // Eliminar etiqueta
    if (isset($_POST['delete_tag']) && !empty($_POST['delete_tag_id'])) {
        $id_category = (int)$_POST['delete_tag_id'];
        $stmt = $pdo->prepare("DELETE FROM category_user WHERE id_user = :user AND id_category = :cat");
        $stmt->execute(['user'=>$iduser,'cat'=>$id_category]);
    }

    // Recargar la página para reflejar cambios
    header("Location: profile.php");
    exit;
}

// ====== CARGAR DATOS DEL USUARIO ======
$stmt = $pdo->prepare("
    SELECT username, entity_name, entity_type, logo_image, presentation
    FROM users
    WHERE id_user = :id
    LIMIT 1
");
$stmt->execute(['id' => $iduser]);
$user = $stmt->fetch();

// ====== CARGAR ETIQUETAS DEL USUARIO ======
$stmtTags = $pdo->prepare("
    SELECT c.id_category, c.name_category, c.type
    FROM categories c
    JOIN category_user cu ON cu.id_category = c.id_category
    WHERE cu.id_user = :user_id
");
$stmtTags->execute(['user_id' => $iduser]);
$tags = $stmtTags->fetchAll(PDO::FETCH_ASSOC);
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
        <h2 class="profile-section-title">Dades de l'entitat</h2>
        <?php if (!empty($success_msg)): ?>
            <p class="success-msg"><?= htmlspecialchars($success_msg) ?></p>
        <?php endif; ?>

        <form method="POST">
            <div class="profile-field">
                <label>Nom i cognoms</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>">
            </div>

            <div class="profile-field">
                <label>Nom entitat</label>
                <input type="text" name="entity_name" value="<?= htmlspecialchars($user['entity_name']) ?>">
            </div>

            <div class="profile-field">
                <label>Tipus entitat</label>
                <input type="text" name="entity_type" value="<?= htmlspecialchars($user['entity_type']) ?>">
            </div>

            <div class="profile-field">
                <label>Presentació</label>
                <textarea name="presentation"><?= htmlspecialchars($user['presentation']) ?></textarea>
            </div>

            <button type="submit" name="save_user">Guardar</button>
        </form>
    </section>

    <!-- ETIQUETAS -->
    <section class="profile-card">
        <h2 class="profile-section-title">Etiquetes</h2>

        <div class="profile-tags">
        <?php foreach($tags as $t): ?>
            <form method="POST" style="display:inline-block;">
                <span class="profile-tag">
                    <?= htmlspecialchars($t['name_category']) ?>
                    <input type="hidden" name="delete_tag_id" value="<?= $t['id_category'] ?>">
                    <button type="submit" name="delete_tag">✕</button>
                </span>
            </form>
        <?php endforeach; ?>
        </div>

        <form method="POST" style="margin-top:10px;">
            <input type="text" name="new_tag" placeholder="Nova etiqueta" required></br>
            <button type="submit" name="add_tag">+ Afegir</button>
        </form>
    </section>

    <!-- PROJECTES -->
    <section class="profile-card">
        <div class="profile-projects-header">
            <h2 class="profile-section-title">Projectes</h2>
            <button type="button" class="profile-new-project">+ Nou projecte</button>
        </div>

        <div class="profile-project-list">
            <?php
            $stmt = $pdo->prepare("
                SELECT id_project, title, video
                FROM projects
                WHERE id_owner = :id
                ORDER BY id_project DESC
            ");
            $stmt->execute(['id' => $iduser]);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($projects && count($projects) > 0):
                foreach ($projects as $proj):
            ?>
                    <div class="profile-project-item">
                        <a href="project.php?id=<?= (int)$proj['id_project'] ?>">
                            <?php if (!empty($proj['video'])): ?>
                                <video src="<?= htmlspecialchars($proj['video']) ?>" muted playsinline preload="metadata"></video>
                            <?php else: ?>
                                <div class="profile-project-placeholder">Sense vídeo</div>
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
        <a href="chat.php" class="profile-link-btn">💬 Converses</a>
        <a href="discover.php" class="profile-link-btn">🔥 Descobrir</a>
    </section>

</main>
</body>
</html>
