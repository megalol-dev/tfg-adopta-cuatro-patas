<?php
$db = new SQLite3('baseDatos.db');

$id_perro = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$enviado = false;
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_perro_post = isset($_POST['id_perro']) ? (int)$_POST['id_perro'] : 0;
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $dni = strtoupper(trim($_POST['dni'] ?? ''));
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    // Validación básica en servidor
    $nombre_ok = preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]{2,50}$/u', $nombre);
    $apellidos_ok = preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]{2,80}$/u', $apellidos);
    $dni_ok = preg_match('/^[0-9]{8}[A-Z]$/', $dni);
    $telefono_ok = preg_match('/^[0-9]{9}$/', $telefono);
    $correo_ok = filter_var($correo, FILTER_VALIDATE_EMAIL);

    if (
        $id_perro_post <= 0 ||
        !$nombre_ok ||
        !$apellidos_ok ||
        !$dni_ok ||
        !$telefono_ok ||
        !$correo_ok
    ) {
        $error = 'Se han detectado datos no válidos en el formulario.';
    } else {
        $stmt = $db->prepare("INSERT INTO solicitudes_adopcion 
        (id_perro, nombre, apellidos, dni, telefono, correo, mensaje, estado) 
        VALUES (:id_perro, :nombre, :apellidos, :dni, :telefono, :correo, :mensaje, 'pendiente')");

        $stmt->bindValue(':id_perro', $id_perro_post, SQLITE3_INTEGER);
        $stmt->bindValue(':nombre', $nombre, SQLITE3_TEXT);
        $stmt->bindValue(':apellidos', $apellidos, SQLITE3_TEXT);
        $stmt->bindValue(':dni', $dni, SQLITE3_TEXT);
        $stmt->bindValue(':telefono', $telefono, SQLITE3_TEXT);
        $stmt->bindValue(':correo', $correo, SQLITE3_TEXT);
        $stmt->bindValue(':mensaje', $mensaje, SQLITE3_TEXT);

        $stmt->execute();

        $enviado = true;
    }
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
        <a href="index.php"><button type="button">Inicio</button></a>
    </nav>
</header>

<main class="pagina-formulario">
    <h2 class="form-titulo">Formulario de adopción</h2>

    <?php if ($enviado): ?>
        <p class="mensaje-ok"><strong>Solicitud enviada correctamente.</strong></p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p class="mensaje-error"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <form method="post" class="tarjeta-formulario formulario-validable" id="formulario-adopcion" novalidate>
        <input type="hidden" name="id_perro" value="<?php echo $id_perro; ?>">

        <div class="form-grupo">
            <label for="nombre">Nombre</label>
            <input id="nombre" type="text" name="nombre" required maxlength="50">
            <small class="campo-mensaje" id="error-nombre"></small>
        </div>

        <div class="form-grupo">
            <label for="apellidos">Apellidos</label>
            <input id="apellidos" type="text" name="apellidos" required maxlength="80">
            <small class="campo-mensaje" id="error-apellidos"></small>
        </div>

        <div class="form-grupo">
            <label for="dni">DNI</label>
            <input id="dni" type="text" name="dni" required maxlength="9">
            <small class="campo-mensaje" id="error-dni"></small>
        </div>

        <div class="form-grupo">
            <label for="telefono">Teléfono</label>
            <input id="telefono" type="text" name="telefono" required maxlength="9">
            <small class="campo-mensaje" id="error-telefono"></small>
        </div>

        <div class="form-grupo">
            <label for="correo">Correo</label>
            <input id="correo" type="email" name="correo" required maxlength="100">
            <small class="campo-mensaje" id="error-correo"></small>
        </div>

        <div class="form-grupo">
            <label for="mensaje">Mensaje (opcional)</label>
            <textarea id="mensaje" name="mensaje" rows="4" maxlength="300"></textarea>
            <small class="campo-mensaje" id="error-mensaje"></small>
        </div>

        <button type="submit" class="btn-adoptar">Enviar solicitud</button>
    </form>
</main>

<footer>
    <p>&copy; 2026 Adopta Cuatro Patas - Todos los derechos reservados</p>
</footer>

<script src="validar_formulario_adopcion.js"></script>
</body>
</html>