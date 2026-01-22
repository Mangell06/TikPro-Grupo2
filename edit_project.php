<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    include("includes/database.php");
    $projectId = null;
    $project_editar = false;
    $tags_editar = [];
    $error_permisos = false;
    if(isset($_GET["project_id"])){
        $projectId = (int)$_GET["project_id"];
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' and $projectId === null) {
            $sql = 
               "INSERT INTO projects (title, description, date_creation, state, id_owner, video, logo_image)
               VALUES (:title, :description, :date_creation, 'active', :id_owner, :video, :image)";
            $stmt = $pdo->prepare($sql);
            $infoVideo = pathinfo($_FILES['video']['name']);
            $newnameVideo = uniqid("video_", true) . "." . $infoVideo['extension']; 
            $targetVideo = 'preuploads/' . $newnameVideo;
            move_uploaded_file($_FILES['video']['tmp_name'], $targetVideo);

            $infoImage = pathinfo($_FILES['image']['name']);
            $newnameImage = uniqid("img_", true) . "." . $infoImage['extension']; 
            $targetImage = 'uploads/logos/' . $newnameImage;
            move_uploaded_file($_FILES['image']['tmp_name'], $targetImage);

            $stmt->execute(['title' => $_POST['title'], 'description' => $_POST['description'],'date_creation' => date("Y-m-d"), 'id_owner' => $_SESSION['user_id'], 'video' => "uploads/videos/".$newnameVideo, 'image' => "uploads/logos/".$newnameImage]);      
            $newProjectId = $pdo->lastInsertId();
            // Capturamos el array de IDs de las etiquetas
            $etiquetes_seleccionades = $_POST['categories'] ?? [];

            if (!empty($etiquetes_seleccionades)) {
                foreach ($etiquetes_seleccionades as $id_categoria) {
                    try {
                        $sql = "INSERT INTO categories_project (id_project, id_category) VALUES (?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$newProjectId, $id_categoria]);
                    } catch (\Throwable $th) {
                    }
                }
            }

            header("Location: profile.php?success=true");
            exit;
    }           

 
    if(isset($_GET["project_id"])){
 
        $sql = 
            "SELECT projects.id as project_id, projects.title, projects.description, projects.video, projects.logo_image, users.id as users_id
                FROM projects
                LEFT JOIN users ON projects.id_owner = users.id
                WHERE projects.id = :project_id;
            ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['project_id' => $projectId]);
        $project_editar = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($project_editar === false){
            header("Location: profile.php");
            exit;

        }

        $sql = 
        "SELECT categories.name, categories_project.id_project, categories_project.id_category
            FROM categories_project
            LEFT JOIN categories ON categories_project.id_category = categories.id
            WHERE categories_project.id_project = :project_id;
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['project_id' => $projectId]);
        $tags_editar = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($project_editar['users_id'] != $_SESSION['user_id'])
        {
            $error_permisos = true;
        }

        if(isset($projectId) and $_SERVER['REQUEST_METHOD'] === 'POST'){

            if($project_editar['users_id'] != $_SESSION['user_id'])
            {
                echo "No tens accés a editar aquest projecte";
                exit;
            }

            $sql=
            "UPDATE projects
            SET title = :title, description = :description, video = :video, logo_image = :logo_image
            WHERE projects.id = :projects_id;
            ";
            $stmt = $pdo->prepare($sql);

            $newPathVideo = $project_editar['video'];
            if (isset($_FILES['video']) && $_FILES['video']['name']){
                $infoVideo = pathinfo($_FILES['video']['name']);
                $newnameVideo = uniqid("video_", true) . "." . $infoVideo['extension']; 
                $targetVideo = 'preuploads/' . $newnameVideo;
                move_uploaded_file($_FILES['video']['tmp_name'], $targetVideo);
                $newPathVideo = "uploads/videos/".$newnameVideo;
            }

            $newPathImage = $project_editar['logo_image'];
            if (isset($_FILES['image']) && $_FILES['image']['name']) {
                echo "isset image";
                $infoImage = pathinfo($_FILES['image']['name']);
                $newnameImage = uniqid("img_", true) . "." . $infoImage['extension']; 
                $targetImage = 'uploads/logos/' . $newnameImage;
                move_uploaded_file($_FILES['image']['tmp_name'], $targetImage);
                $newPathImage = "uploads/logos/".$newnameImage;
            }

            $sqlTagDelete = "DELETE from categories_project where id_project = ?";
            $stmtTagDelete = $pdo->prepare($sqlTagDelete);
            $stmtTagDelete -> execute([$projectId]);

            $etiquetes_seleccionades = $_POST['categories'] ?? [];
            if (!empty($etiquetes_seleccionades)) {
                foreach ($etiquetes_seleccionades as $id_categoria) {
                    try {
                        $sqlTagInsert = "INSERT INTO categories_project (id_project, id_category) VALUES (?, ?)";
                        $stmtTagInsert = $pdo->prepare($sqlTagInsert);
                        $stmtTagInsert -> execute([$projectId, $id_categoria]);
                    } catch (\Throwable $th) {
                            //
                    }
                    
                }
            }
            $stmt->execute(['title' => $_POST['title'], 'description' => $_POST['description'], 'video' => $newPathVideo, 'logo_image' => $newPathImage, 'projects_id' => $projectId]);   
        
            header("Location: profile.php?success=true");
            exit;
        } 
    } else {
        $sql = 
        "SELECT categories.name, categories_user.id_user, categories_user.id_category
            from categories_user
            LEFT JOIN categories ON categories_user.id_category = categories.id
            where categories_user.id_user = :id_user;
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_user' => $_SESSION['user_id']]);
        $tags_editar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
?>
 <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="styles.css?q=1">
        <title>Crear projecte</title>
    </head>
    <body id="discover-body">
    <header class="main-header">
        <h1 class="header-title">Crear projecte</h1>
    <div class="close-session">
            <?php

        $iduser = $_SESSION["user_id"];

        $stmtUser = $pdo->prepare("SELECT name FROM users WHERE id = :iduser");
        $stmtUser->execute(['iduser' => $iduser]);

        $user = $stmtUser->fetch();
        
        if ($user) {
            echo "<h3 class='user'>" . htmlspecialchars($user['name']) . "</h3>";
        } else {
            echo "<h1>Usuari no encontrat</h1>";
        }
        ?>
        
        <a href="logout.php" id="nav-logout" class="logout-button">Tancar sessió</a>
    
        </div>
    </header>
   
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="module">
    import { createElement } from './createElement.js';
    import { showNotification } from './notificaciones.js';
    import { sendLog } from './create-logs.js';
    const projectId = <?php echo $projectId ?? "null" ?>;
    const permissionError = <?php echo $error_permisos ? "true" : "false" ?>;
    if (permissionError){
        showNotification("error", "No tens accés a editar aquest projecte")
    }

    if (projectId == null){
        //log sendLog(`Usuario ${<?php echo json_encode(value: $_SESSION['id_user']); ?>} está creando un proyecto`);
    } else{
        //log sendLog(`Usuario ${<?php echo json_encode($_SESSION['id_user']); ?>} está editando el proyecto ${<?php echo json_encode($projectId); ?>}`);
    }
    


    function createForm(){
        const main = createElement('<main></main>', 'body','main-edit');
        const section = createElement('<section></section>', main);
        const form = createElement('<form method="post" enctype="multipart/form-data"></form>', 'main', '', { id: 'project-form' });
        const titleLabel = createElement('<label></label>', 'form', '', { for: 'title'}).text('Títol del projecte: ');
        const title = createElement('<input></input>', 'form', 'project-title', { type: 'text', placeholder: 'Títol del projecte', name: 'title', id: 'title' });
        const descriptionLabel = createElement('<label></label>', 'form', '', {for: 'description'}).text('Descripció del projecte:');
        const description = createElement('<textarea></textarea>', 'form', 'project-description', { placeholder: 'Descripció del projecte',name: 'description', id: 'description' });
        const etiquetes = createElement('<label></label>', 'form').text("Etiquetes:");
        const search = createElement('<div id="etiquetes-contenidor" class="tags-wrapper"></div>', 'form', '', {type: 'search', placeholder: "Cerca les etiquetes", name: "query"})
        const buttonSearch = createElement('<button type="button" id="btnObrirModal">Afegir etiqueta</button>', 'form', 'buttonEtiquetes');
        const pImage =createElement('<p></p>', 'form', '', {id: 'image'});
        const imatgedestacada = createElement('<label></label>', 'form').text("Imatge destacada:");
        const image = createElement('<input></input>', 'form', 'project-image', { type: 'file', accept: 'image/*', name: 'image'});
        const videodestacat = createElement('<label></label>', 'form').text("Video:");
        const pVideo =createElement('<p></p>', 'form', '', {id: 'video'});
        const video = createElement('<input></input>', 'form', 'project-video', { type: 'file', accept: 'video/*', name: 'video' }); //hay que limitarlo
        const button = createElement('<button></button>', 'form', 'buttonEtiquetes', { type: 'submit'}).text("<?php echo isset($_GET["project_id"]) ? 'Editar projecte' : 'Crear projecte' ?>");
        
        
        $(main).append(form);
        const projectData = <?php echo json_encode($project_editar); ?>;
        if(projectData){
            uploadInformation();
        }
        uploadInformationTags();
    }

    createForm();
    function verifyContentInputs(title, description, imageName, videoName){
        if (title.length === 0){
             showNotification("error","El títol no pot estar buït");
             return false;
        }
        if(description.length === 0){
            showNotification("error","La descripció no pot estar buida");
            return false;
        }
        
        if(projectId == null && (!imageName || !videoName)){
            showNotification("error","S'ha d'introduïr tant una foto com un video");
            return false;
        }

        return true;
    }
    document.getElementById('project-form').addEventListener('submit', (event) => {
        event.preventDefault();
        
        let valid = true;

        const formData = new FormData(event.target);
        const title = formData.get('title'); 
        const description = formData.get('description');
        const image = formData.get('image');
        const video = formData.get('video');
        const videoName = formData.get('video').name;
        const videoSize = formData.get('video').size;
        const imageName = formData.get('image').name;
        const imageSize = formData.get('image').size;
        //pasar a una funcion fuera y llamarla.
        const mb = (formData.get('video').size/1000)/1000;
        if (mb > 200){
            showNotification("error","No es pot pujar un video de més de 200MB");
            //log sendLog(`Usuario ${<?php echo json_encode(value: $_SESSION['id_user']); ?>} está intentando subir un video de más de 200MB`);
            video.value = '';
            valid = false;
        }

        if (verifyContentInputs(title,description,imageName,videoName) == false)
            valid = false;
        
        if(valid)
        {
            document.getElementById("project-form").submit();
            //log sendLog(`Usuario ${<?php echo json_encode(value: $_SESSION['id_user']); ?>} ha creado/editado exitosamente un proyecto`);
        }
    });

    function uploadInformation(){
        const project = <?php echo json_encode($project_editar); ?>;
    
        if (project) {
            document.getElementById('title').value = project.title || '';
            document.getElementById('description').value = project.description || '';
            document.getElementById('image').textContent = "El imatge que es va insertar l'última vegada: "+project.logo_image.slice(14);
            document.getElementById('video').textContent = "El video que es va insertar l'última vegada: "+project.video.slice(15);
        }

        // hay que poner la imagen y el video abajo del sitio para que lo vea el usuario
    }

    function uploadInformationTags(){
        const tags = <?php echo json_encode($tags_editar); ?>;
        tags.forEach((cat) => {
            afegirEtiqueta(cat.id_category, cat.name)
        });
    }

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
</body>
</html>
