<?php
    session_start();
    include("../includes/database.php");

   $sql = "SELECT projects.*, 
               projects.state AS project_state,
               users.name, 
               users.entity_name
        FROM projects
        LEFT JOIN users ON projects.id_owner = users.id;";
    $stmt = $pdo->prepare($sql);
    $stmt -> execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <link rel="icon" href="/oak_4986983.png" type="image/png">
    <title>Administració de projectes</title>
</head>
<body id="admin-projects">
    <header class="main-header">
        <?php
            $userAdmin =  $_SESSION['admin_id'];
            $stmt = $pdo->prepare("SELECT name FROM users WHERE id = :idadmin");
            $stmt->execute(['idadmin' => $userAdmin]);

            $adminInfo = $stmt->fetch();
            
            if ($adminInfo) {
                echo "<h3 class='user'>" . htmlspecialchars($adminInfo['name']) . "</h3>";
            } else {
                echo "<h1>Usuari no trobat</h1>";
            }
            ?>
            <a href="/admin/logout.php" id="nav-logout" class="logout-button">Tancar sessió</a>
    </header>
    <?php foreach ($projects as $project): 
    $isArchived = ($project['project_state'] === 'archived');
    ?>
    <section id="section-project-<?= $project['id'] ?>" 
             class="section-project <?= $isArchived ? 'is-archived' : '' ?>">
        <div class="buttons">
                <button id="upload-hidden-<?=($project['id'])?>" onclick="uploadedVideo('<?=($project['id'])?>','<?='../'.($project['video'])?>')">Cargar Video</button>    
               <button id="buttonReactive-<?= $project['id'] ?>" 
                onclick="approvedProject('<?= $project['id'] ?>')" 
                <?= !$isArchived ? 'disabled' : '' ?>>
            Reactivar projecte
        </button>

        <button id="buttonDelete-<?= $project['id'] ?>" 
                onclick="unapprovedProject('<?= $project['id'] ?>')"
                <?= $isArchived ? 'disabled' : '' ?>>
            Eliminar projecte
        </button>
        </div>
        <div class="set">
            <div class="insert-video" id="insertVideo-<?=$project['id']?>"></div>
            <div class="explication">
                <h1><?=htmlspecialchars($project['title'])?></h1>
                <div class="userdiv">
                    <img src=<?=htmlspecialchars('../'.$project['logo_image'])?> class="img" alt="">
                    <pre><?=htmlspecialchars($project['name'])." - ".htmlspecialchars($project['entity_name'])?></pre>
                </div>
                <p><?=htmlspecialchars($project['description'])?></p>
            </div>
        </div>
        
    </section>
    <?php endforeach; ?>
    <script type="module">
            import { sendLog } from '/create-logs.js';
            import { showNotification } from '/notificaciones.js';
            function uploadedVideo(projectId, videoScr){
                const button = document.getElementById('upload-hidden-'+projectId);
                if(button.textContent ==='Cargar Video'){
                        button.textContent = 'Amagar Video';
                        document.getElementById('insertVideo-'+projectId).classList.remove("amagarVideo");
                        
                        if (document.getElementById('insertVideo-'+projectId).innerHTML.trim() === "") {
                            const video = document.createElement('video');
                            video.src = videoScr;
                            video.controls = true;
                            video.style.width = "300px";
                            video.muted = true;
                            document.getElementById('insertVideo-'+projectId).appendChild(video);
                        }
                } else if(button.textContent ==='Amagar Video'){
                     document.getElementById('insertVideo-'+projectId).classList.add("amagarVideo");
                     button.textContent = 'Cargar Video';
                     return;
                }
               
            }

            function approvedProject(projectId){
                fetch('../includes/approved.php', { 
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
                        sendLog(`El admin con id <?php echo json_encode($_SESSION["admin_id"]);?> ha reactivado el proyecto con id `+projectId);
                        showNotification("info", "S'ha reactivat el projecte correctament!");
                        const container = document.getElementById('section-project-' + projectId);
                        const btnReactive = document.getElementById('buttonReactive-' + projectId);
                        const btnDelete = document.getElementById('buttonDelete-' + projectId);

                        if (container) {
                            container.classList.remove('is-archived');
                            if(btnDelete) btnDelete.disabled = false;
                            if(btnReactive) btnReactive.disabled = true;
                            
                        }
                    } else {
                        alert("Error: " + (data.error || data.message));
                    }
                })
                .catch(error => {
                    console.error('Error detallado:', error);
                });
            }
            function unapprovedProject(projectId) {
                fetch('../includes/unnapprove.php', { 
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
                        sendLog(`El admin con id <?php echo json_encode($_SESSION["admin_id"]);?> ha borrado el proyecto con id `+projectId);
                        showNotification("success", "S'ha eliminat el projecte correctament!");
                        const container = document.getElementById('section-project-' + projectId);
                        const btnReactive = document.getElementById('buttonReactive-' + projectId);
                        const btnDelete = document.getElementById('buttonDelete-' + projectId);

                        if (container) {
                            container.classList.add('is-archived');
                            if(btnDelete) btnDelete.disabled = true;
                            if(btnReactive) btnReactive.disabled = false;
                            
                        }
                    } else {
                        alert("Error: " + (data.error || data.message));
                    }
                })
                .catch(error => {
                    console.error('Error detallado:', error);
                });
            }

            window.uploadedVideo = uploadedVideo;
            window.approvedProject = approvedProject;
            window.unapprovedProject = unapprovedProject;
    </script>
</body>
</html>
    