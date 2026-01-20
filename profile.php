<?php
session_start();
include("includes/database.php");

$iduser = $_SESSION['user_id'] ?? 0;

if (!$iduser) {
    header("Location: login.php");
    exit;
}

// Cargar datos del usuario activo
$stmt = $pdo->prepare("
    SELECT username,
           entity_name,
           entity_type,
           logo_image,
           presentation
    FROM users
    WHERE id_user = :id
    LIMIT 1
");
$stmt->execute(['id' => $iduser]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="icono-simbio.png" type="image/png">
</head>

<body id="profile-body">

<header class="profile-header">
    <h1 class="profile-title">Perfil</h1>
</header>

<main class="profile-container">

    <!-- DATOS USUARIO -->
    <section class="profile-card">

        <?php if (!empty($user['logo_image'])): ?>
            <img src="<?= htmlspecialchars($user['logo_image']) ?>" class="profile-logo">
        <?php endif; ?>

        <h2 class="profile-section-title">Dades de l'entitat</h2>

        <div class="profile-field">
            <label>Nom i cognoms</label>
            <input type="text" value="<?= htmlspecialchars($user['username']) ?>">
        </div>

        <div class="profile-field">
            <label>Nom entitat</label>
            <input type="text" value="<?= htmlspecialchars($user['entity_name']) ?>">
        </div>

        <div class="profile-field">
            <label>Tipus entitat</label>
            <input type="text" value="<?= htmlspecialchars($user['entity_type']) ?>">
        </div>

        <div class="profile-field">
            <label>Presentació</label>
            <textarea class="profile-textarea"><?= htmlspecialchars($user['presentation']) ?></textarea>
        </div>

    </section>

    <!-- ETIQUETAS -->
    <section class="profile-card">
        <h2 class="profile-section-title">Etiquetes</h2>

        <div class="profile-tags">
            <span class="profile-tag">Informàtica <button type="button" class="profile-tag-remove">✕</button></span>
            <span class="profile-tag">DAM <button type="button" class="profile-tag-remove">✕</button></span>
        </div>

        <button type="button" class="profile-add-tag">+ Afegir</button>
    </section>

    <!-- PROJECTES -->
    <section class="profile-card">
        <div class="profile-projects-header">
            <h2 class="profile-section-title">Projectes</h2>
            <button class="profile-new-project">+ Nou projecte</button>
        </div>

        <div class="profile-project-list">
            <?php
            $stmt = $pdo->prepare("SELECT id_project, title, featured_image FROM projects WHERE id_user = :id ORDER BY id_project DESC");
            $stmt->execute(['id' => $iduser]);
            $projects = $stmt->fetchAll();

            if ($projects):
                foreach ($projects as $proj):
            ?>
                    <div class="profile-project-item">
                        <a href="project.php?id=<?= $proj['id_project'] ?>">
                            <img src="<?= htmlspecialchars($proj['featured_image']) ?>" alt="<?= htmlspecialchars($proj['title']) ?>">
                            <span><?= htmlspecialchars($proj['title']) ?></span>
                        </a>
                    </div>
            <?php
                endforeach;
            else:
                echo '<p class="profile-no-projects">Encara no tens projectes.</p>';
            endif;
            ?>
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
