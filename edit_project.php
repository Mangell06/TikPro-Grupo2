<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Crear projecte</title>
</head>
<body id="discover-body">
<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
?>
    <header class="header-discovered">
        <h1 class="header-title">Crear projecte</h1>
    </header>
   
    <!-- posicionar todo bien -->




<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="module">
    import { createElement } from './createElement.js';


function createForm(){
    const main = createElement('<main></main>', 'body','main-edit');
    const section = createElement('<section></section>', main);
    const form = createElement('<form></form>', 'main', 'project-form', { id: 'project-form' });
    const titleLabel = createElement('<label></label>', 'form', '', { for: 'title'}).text('Títol del projecte: ');
    const title = createElement('<input></input>', 'form', 'project-title', { type: 'text', placeholder: 'Títol del projecte', name: 'title', id: 'title' });
    const descriptionLabel = createElement('<label></label>', 'form', '', {for: 'titleDescription'}).text('Descripció del projecte:');
    const description = createElement('<textarea></textarea>', 'form', 'project-description', { placeholder: 'Descripció del projecte',name: 'titleDescription', id: 'titleDescription' });
    const image = createElement('<input></input>', 'form', 'project-image', { type: 'file', accept: 'image/*' });
    const search = createElement('<input></input>', 'form', '', {type: 'search', placeholder: "Cerca les etiquetes", name: "query"})
    const buttonSearch = createElement('<button></button>', 'form').text("Cercar");
    const video = createElement('<input></input>', 'form', 'project-image', { type: 'file', accept: 'image/*' }); //hay que limitarlo
    $(main).append(form);

    // si la persona ya tiene un proyecto debería de salir el edit_project.php en formato editar.
    //si la persona tiene un proyecto: necesito el tag del proyecto, tengo que hacer un join a categories_projecto > categories
    $sql = 
        "SELECT projects.id, projects.title, projects.description, projects.id_owner, projects.video, users.id, users.logo_image,  
            FROM simbiodb.projects,
            LEFT JOIN users ON projects.id_owner = users.id,
            WHERE users.id = :user_id;
        ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $projects = $stmt->fetchAll();
    //si hay un proyecto
    if(!empty($projects)){
        //coger los tags
         $sql = 
        "SELECT projects.id as project, categories_project.id_project, categories_project.id_category, categories.name, categories.type, categories.id_category_parent, users.id as user
            FROM simbiodb.projects
            LEFT JOIN users ON users.id  = projects.id_owner
            LEFT JOIN categories_project ON projects.id = categories_project.id_project
            LEFT JOIN categories ON categories_project.id_category = categories.id
            WHERE users.id = :user_id;
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $tags = $stmt->fetchAll();

        //
    }
    

}

createForm();
</script>
</body>
</html>