<?php
declare(strict_types=1);

require_once __DIR__ . '/seguridad.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($usuario === '' || $contrasena === '') {
        $error = 'Debe introducir usuario y contraseña.';
    } else {
        $db = abrir_db();
        $stmt = $db->prepare('
            SELECT id_usuario, usuario, contrasena_hash, rol, activo
            FROM usuarios_internos
            WHERE usuario = :usuario
            LIMIT 1
        ');
        $stmt->bindValue(':usuario', $usuario, SQLITE3_TEXT);
        $res = $stmt->execute();
        $fila = $res ? $res->fetchArray(SQLITE3_ASSOC) : false;

        if (!$fila) {
            $error = 'Credenciales incorrectas.';
        } elseif ((int)$fila['activo'] !== 1) {
            $error = 'El usuario está inactivo.';
        } else {
            $hash = (string)$fila['contrasena_hash'];

            if (!password_verify($contrasena, $hash)) {
                $error = 'Credenciales incorrectas.';
            } else {
                // Login OK: crear sesión
                session_regenerate_id(true);

                $_SESSION['usuario_id'] = (int)$fila['id_usuario'];
                $_SESSION['usuario'] = (string)$fila['usuario'];
                $_SESSION['rol'] = (string)$fila['rol'];

                header('Location: panel_control.php');
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - Adopta Cuatro Patas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../estilos.css">
</head>

<body>

<header>
    <h1>Adopta Cuatro Patas</h1>
    <nav>
        <a href="../index.php"><button>Inicio</button></a>
    </nav>
</header>

<main class="pagina-formulario">
    <h2 class="form-titulo">Acceso de administración</h2>

    <?php if ($error !== ''): ?>
        <p style="color: red;"><strong><?php echo h($error); ?></strong></p>
    <?php endif; ?>

    <form method="post" action="" class="tarjeta-formulario">
        <div class="form-grupo">
            <label for="usuario">Usuario</label><br>
            <input type="text" id="usuario" name="usuario" required>
        </div>
        <br>

        <div class="form-grupo">
            <label for="contrasena">Contraseña</label><br>
            <input type="password" id="contrasena" name="contrasena" required>
        </div>
        <br>

        <button type="submit">Iniciar sesión</button>
    </form>
</main>

<footer>
    <p>&copy; 2026 Adopta Cuatro Patas - Todos los derechos reservados</p>
</footer>

</body>
</html>
