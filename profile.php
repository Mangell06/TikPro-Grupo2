<?php
session_start();
include("includes/database.php");

$iduser = $_SESSION['user_id'] ?? 0;
if (!$iduser) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                presentation = :presentation,
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

    header("Location: profile.php?success=true");
    exit;
}

$stmt = $pdo->prepare("
    SELECT name, email, entity_name, entity_type, presentation, tfn, poblation
    FROM users
    WHERE id = :id
    LIMIT 1
");
$stmt->execute(['id' => $iduser]);
$user = $stmt->fetch();

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
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Perfil</title>
    <link rel="stylesheet" href="/styles.css?q=2">
    <link rel="icon" href="oak_4986983.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="profile-body">

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
            </div>
            <div class="profile-field">
                    <label>Telèfon</label>
                    <input type="text" name="tfn" value="<?= htmlspecialchars($user['tfn']) ?>">
                    <label>Ciutat</label>
                    <input type="text" name="poblation" value="<?= htmlspecialchars($user['poblation']) ?>">
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
                   
                <label>Etiquetes</label>
                <div id="etiquetes-contenidor" class="tags-wrapper"></div>
                <div class="buttons-profile">
                    <button type="button" id="btnObrirModal" class="buttonEtiquetes">Afegir etiqueta</button>
                    <button type="submit" name="save_user" class="buttonEtiquetes">Guardar</button>      
                </div>
                </div> 
        </form>
    </section>

    

    <!-- PROJECTES -->
    <section class="profile-card">
        <div class="profile-projects-header">
            <h2 class="profile-section-title">Projectes</h2>
            <input type="button" class="buttonEtiquetes" onclick="window.location.href='edit_project.php'" value="+ Nou projecte">
        </div>

        <div class="profile-project-list">
            <?php
            $stmt = $pdo->prepare("
                SELECT id, title, logo_image, state
                FROM projects
                WHERE id_owner = :id AND projects.state = 'active'
                ORDER BY id DESC
            ");
            $stmt->execute(['id' => $iduser]);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($projects):
                foreach ($projects as $proj):
            ?>
                <div class="profile-project-item" id="section-project-<?= (int)$proj['id'] ?>">
                    <?php if (!empty($proj['logo_image'])): ?>
                            <img class="imagePreviewProfile" src="<?= htmlspecialchars($proj['logo_image']) ?>" alt="Logo">
                        <?php else: ?>
                            <div class="profile-project-placeholder">Sense imatge</div>
                        <?php endif; ?>
                    <div class="set-buttons-with-text">
                            <span><?= htmlspecialchars($proj['title']) ?></span>
                    <div class="buttons-edit-delete">
                            <a class="tag-badge" href="edit_project.php?project_id=<?= (int)$proj['id'] ?>">Editar</a>
                            <button class="tag-badge" onclick="unapprovedProject(<?= (int)$proj['id'] ?>)">Esborrar</button>
                    </div>
                    </div>
                    
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
    import { loadNotifications } from './load-notifications.js';

    loadNotifications();
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

document.getElementById('btnObrirModal').onclick = () => modal.style.display = 'block';
document.getElementById('btnTancarModal').onclick = () => {
    modal.style.display = 'none';
    inputCerca.value = '';
    llistaResultats.innerHTML = '';
};

inputCerca.addEventListener('input', function() {
    const text = this.value.trim();
    
    if (text.length >= 3) {
        fetch(`search_tags.php?q=${encodeURIComponent(text)}`)
            .then(res => res.json())
            .then(data => {
                llistaResultats.innerHTML = '';
                data.forEach(cat => {
                    const div = document.createElement('div');
                    div.className = 'result-item';
                    div.textContent = cat.name;
                    div.onclick = () => afegirEtiqueta(cat.id, cat.name);
                    llistaResultats.appendChild(div);
                });
            });
    } else {
        llistaResultats.innerHTML = '';
    }
});

function afegirEtiqueta(id, nom) {
    const contenidor = document.getElementById('etiquetes-contenidor');
    const div = document.createElement('div');
    div.className = 'tag-badge';
    div.style.display = 'inline-flex';
    div.style.alignItems = 'center';
    div.style.margin = '5px';
    div.style.padding = '5px 10px';
    div.style.background = '#69604e';
    div.style.color = 'white';
    div.style.borderRadius = '15px';

    const inputHidden = document.createElement('input');
    inputHidden.type = 'hidden';
    inputHidden.name = 'categories[]'; 
    inputHidden.value = id;

    div.innerHTML = `<span>${nom}</span>`;
    
    const btnEliminar = document.createElement('span');
    btnEliminar.innerHTML = ' &times;';
    btnEliminar.style.cursor = 'pointer';
    btnEliminar.style.marginLeft = '10px';
    
    btnEliminar.onclick = function() {
        div.remove();
    };

    div.appendChild(inputHidden);
    div.appendChild(btnEliminar);
    contenidor.appendChild(div);

    document.getElementById('modalCerca').style.display = 'none';
}

    window.unapprovedProject = function(projectId) {
    fetch('includes/unnapprove.php', { 
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ projectId: projectId })
    })
    .then(response => {
        if (!response.ok) throw new Error('Error en el servidor');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const projectItem = document.getElementById('section-project-' + projectId);
            
            if (projectItem) {
                const parent = projectItem.parentElement;
                projectItem.remove();
                const remainingProjects = parent.querySelectorAll('.profile-project-item');
                
                if (remainingProjects.length === 0) {
                    const p = document.createElement('p');
                    p.classList.add('profile-no-projects');
                    p.textContent = "Encara no tens projectes.";
                    parent.appendChild(p);
                    showNotification("info", "S'ha eliminat correctament el projecte");
                }
            }
        } else {
            alert("Error: " + (data.error || data.message));
        }
    })
    .catch(error => {
        console.error('Error detallado:', error);
    });
}
</script>
</main>
</body>
</html>
