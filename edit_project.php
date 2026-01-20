<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear projecte</title>
</head>
<body>
<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
?>
    <h1>Crear projecte</h1>

    




<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script type="module">
    import { createElement } from './createElement.js';


function createForm(){
    const form = createElement('<form></form>', 'body', 'project-form', { id: 'project-form' });
    const titleLabel = createElement('<label></label>', 'form', '', { for: 'title'}).text('Títol del projecte: ');
    const title = createElement('<input></input>', 'form', 'project-title', { type: 'text', placeholder: 'Títol del projecte', name: 'title', id: 'title' });
    const description = createElement('<textarea></textarea>', 'form', 'project-description', { placeholder: 'Descripció del projecte' });
    const image = createElement('<input></input>', 'form', 'project-image', { type: 'file', accept: 'image/*' });
    $("body").append(form);
}

createForm();
</script>
</body>
</html>