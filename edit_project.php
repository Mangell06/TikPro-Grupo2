<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
?>
 <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="styles.css">
        <title>Crear projecte</title>
    </head>
    <body id="discover-body">
    <header class="header-discovered">
        <h1 class="header-title">Crear projecte</h1>
    </header>
    </body>
    <?php
    // si la persona ya tiene un proyecto debería de salir el edit_project.php en formato editar.
    //si la persona tiene un proyecto: necesito el tag del proyecto, tengo que hacer un join a categories_projecto > categories
    include("includes/database.php");
    $sql = 
        "SELECT projects.id, projects.title, projects.description, projects.video, users.id, users.logo_image
            FROM projects
            LEFT JOIN users ON projects.id_owner = users.id
            WHERE users.id = :user_id;
        ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $projects = $stmt->fetchAll();
    //si hay un proyecto
    if(!empty($projects)){
        echo "<h1>El usuario tiene proyectos (Entró en el IF)</h1>";
        //poner las categorias que ya tiene tiene el usuario.
        $sql = 
        "SELECT projects.id, categories_project.id_category, categories.name, categories.type, categories.id_category_parent, users.id as user
            FROM projects
            LEFT JOIN users ON users.id  = projects.id_owner
            LEFT JOIN categories_project ON projects.id = categories_project.id_project
            LEFT JOIN categories ON categories_project.id_category = categories.id
            WHERE users.id = :user_id;
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $tags = $stmt->fetchAll();
        //hacer un selector de todas las categorias que no tiene.
         
    } else{
        echo "<h1>El usuario NO tiene proyectos (Entró en el ELSE)</h1>";
        //parametro get de project cuando se edita
        //cargar todas las categorias del usuario
        $sql = 
        "SELECT id_user, id_category, categories.name
            FROM categories_user
            LEFT JOIN users ON users.id = id_user
            LEFT JOIN categories ON categories.id = id_category
            WHERE users.id = :user_id;
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $usertags = $stmt->fetchAll();
        foreach($usertags as $tag){
            echo("<pre>".$tag['name']."</pre>");
        }
        //cargar las categorias del proyecto
        $sql = 
        "SELECT id, name FROM categories;";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $categories = $stmt->fetchAll();
        // foreach($categories as $category){
        //     echo("<h1>".$category['name']."</h1>");
        // }
        
    }   
        //
    ?>
   
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="module">
        import { createElement } from './createElement.js';


    function createForm(){
        const main = createElement('<main></main>', 'body','main-edit');
        const section = createElement('<section></section>', main);
        const form = createElement('<form></form>', 'main', '', { id: 'project-form' });
        const titleLabel = createElement('<label></label>', 'form', '', { for: 'title'}).text('Títol del projecte: ');
        const title = createElement('<input></input>', 'form', 'project-title', { type: 'text', placeholder: 'Títol del projecte', name: 'title', id: 'title' });
        const descriptionLabel = createElement('<label></label>', 'form', '', {for: 'titleDescription'}).text('Descripció del projecte:');
        const description = createElement('<textarea></textarea>', 'form', 'project-description', { placeholder: 'Descripció del projecte',name: 'titleDescription', id: 'titleDescription' });
        const image = createElement('<input></input>', 'form', 'project-image', { type: 'file', accept: 'image/*', name: 'image'});
        const userTags = <?php echo json_encode($usertags); ?>;
        for (let i = 0; i < userTags.length; i++) {
            const tag = createElement('<span></span>', 'form', 'user-tag-'+userTags[i].id_category).text(userTags[i].name);
        }
        const search = createElement('<input></input>', 'form', '', {type: 'search', placeholder: "Cerca les etiquetes", name: "query"})
        const buttonSearch = createElement('<button></button>', 'form').text("Cercar");
        const video = createElement('<input></input>', 'form', 'project-video', { type: 'file', accept: 'video/*', name: 'video' }); //hay que limitarlo
        const button = createElement('<button></button>', 'form', 'submit-button', { type: 'submit'}).text('Crear projecte');
        
        console.log(userTags);
        $(main).append(form);
    }


    createForm();
    document.getElementById('project-form').addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(event.target);
        formData.get('title'); 
        formData.get('titleDescription');
        formData.get('image');
        formData.get('video');
        console.log(formData.get('video').name);
        console.log(formData.get('video').size);
        console.log(formData.get('image').name);
        console.log(formData.get('image').size);
        // console.log(formData);

    });
    

</script>
    
</body>
</html>
