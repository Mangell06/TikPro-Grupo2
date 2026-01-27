<?php
    session_start();
    include("../includes/database.php");

    $sql = "SELECT projects.*, name, entity_name
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
    <title>Administració de projectes</title>
</head>
<body id="admin-projects">
    <header class="main-header">
        <!-- <h1 class="header-title">SIMBIO</h1> -->
        <?php
            include("../includes/database.php");
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
    <?php foreach ($projects as $project){?>
    <section class="section-project">
        <div class="buttons">
                <button id="upload-hidden-<?=($project['id'])?>" onclick="uploadedVideo('<?=($project['id'])?>','<?='../'.($project['video'])?>')">Cargar Video</button>    
                <button onclick="approvedProject('<?=($project['id'])?>')">Aprovar projecte</button>    
                <button onclick="unapprovedProject('<?($project['id'])?>')">Eliminar projecte</button>  
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
        
        <!-- crear cada section, para cada uno de los proyectos -->
        
    </section>
    <?php
    }
    ?>
    <script>
            function uploadedVideo(projectId, videoScr){
                const button = document.getElementById('upload-hidden-'+projectId);
                if(button.textContent ==='Cargar Video'){
                        button.textContent = 'Esconder Video';
                        document.getElementById('insertVideo-'+projectId).style.display = 'block';
                        
                        if (document.getElementById('insertVideo-'+projectId).innerHTML.trim() === "") {
                            const video = document.createElement('video');
                            video.src = videoScr;
                            video.controls = true;
                            video.style.width = "300px";
                            video.muted = true;
                            document.getElementById('insertVideo-'+projectId).appendChild(video);
                        }
                } else if(button.textContent ==='Esconder Video'){
                     document.getElementById('insertVideo-'+projectId).style.display = 'none';
                     button.textContent = 'Cargar Video';
                     return;
                }
               
                
                
            }

            function approvedProject(projectId){console.log("Aprobando video "+projectId)}
            function unapprovedProject(projectId){console.log("Eliminando video "+projectId)}
    </script>
</body>
</html>
    