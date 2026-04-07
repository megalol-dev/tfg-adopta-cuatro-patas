<?php
$db = new SQLite3('baseDatos.db');

$id_perro = $_GET['id'];

$enviado = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $stmt = $db->prepare("INSERT INTO solicitudes_adopcion 
    (id_perro, nombre, apellidos, dni, telefono, correo, mensaje, estado) 
    VALUES (:id_perro, :nombre, :apellidos, :dni, :telefono, :correo, :mensaje, 'pendiente')");

    $stmt->bindValue(':id_perro', $_POST['id_perro']);
    $stmt->bindValue(':nombre', $_POST['nombre']);
    $stmt->bindValue(':apellidos', $_POST['apellidos']);
    $stmt->bindValue(':dni', $_POST['dni']);
    $stmt->bindValue(':telefono', $_POST['telefono']);
    $stmt->bindValue(':correo', $_POST['correo']);
    $stmt->bindValue(':mensaje', $_POST['mensaje']);

    $stmt->execute();

    $enviado = true;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de adopción - Adopta Cuatro Patas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

<header>
    <h1>Adopta Cuatro Patas</h1>
    <nav>
        <a href="index.php"><button>Inicio</button></a>
    </nav>
</header>

<main class="pagina-formulario">
    <h2 class="form-titulo">Formulario de adopción</h2>

    <?php if ($enviado): ?>
        <p class="mensaje-ok"><strong>Solicitud enviada correctamente.</strong></p>
    <?php endif; ?>

    <form method="post" class="tarjeta-formulario">
        <input type="hidden" name="id_perro" value="<?php echo $id_perro; ?>">

        <div class="form-grupo">
            <label for="nombre">Nombre</label>
            <input id="nombre" type="text" name="nombre" required>
        </div>

        <div class="form-grupo">
            <label for="apellidos">Apellidos</label>
            <input id="apellidos" type="text" name="apellidos" required>
        </div>

        <div class="form-grupo">
            <label for="dni">DNI</label>
            <input id="dni" type="text" name="dni" required>
        </div>

        <div class="form-grupo">
            <label for="telefono">Teléfono</label>
            <input id="telefono" type="text" name="telefono" required>
        </div>

        <div class="form-grupo">
            <label for="correo">Correo</label>
            <input id="correo" type="email" name="correo" required>
        </div>

        <div class="form-grupo">
            <label for="mensaje">Mensaje (opcional)</label>
            <textarea id="mensaje" name="mensaje" rows="4"></textarea>
        </div>

        <button type="submit" class="btn-adoptar">Enviar solicitud</button>
    </form>
</main>

<footer>
    <p>&copy; 2026 Adopta Cuatro Patas - Todos los derechos reservados</p>
</footer>

</body>
</html>