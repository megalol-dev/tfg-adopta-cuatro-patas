<?php
declare(strict_types=1);

require_once __DIR__ . '/seguridad.php';
exigir_login();

$rol = $_SESSION['rol'] ?? 'ayudante';
$usuario = $_SESSION['usuario'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de control - Adopta Cuatro Patas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../estilos.css">
</head>

<body>

<header>
    <h1>Adopta Cuatro Patas</h1>
    <nav>
        <a href="../index.php"><button>Inicio</button></a>
        <a href="cerrar_sesion.php"><button>Cerrar sesión</button></a>
    </nav>
</header>

<main class="pagina-formulario">
    

    <!-- UNA sola tarjeta para todo -->
    <section class="tarjeta-formulario panel-contenedor">

        <h2 class="form-titulo">Panel de control</h2>

        <p class="panel-info">
            Sesión iniciada como <strong><?php echo h($usuario); ?></strong>
            (rol: <strong><?php echo h($rol); ?></strong>).
        </p>

        <div class="panel-botones">
            <?php if ($rol === 'gerente'): ?>
                <a href="crear_editar_usuario.php">
                    <button class="btn-primario">Crear / Editar Usuario</button>
                </a>
            <?php endif; ?>

            <a href="crear_editar_animal.php">
                <button class="btn-primario">Crear / Editar Animal</button>
            </a>

            <a href="ver_peticiones.php?vista=pendientes">
                <button class="btn-primario">Ver peticiones</button>
            </a>
        </div>

    </section>

</main>

<footer>
    <p>&copy; 2026 Adopta Cuatro Patas - Todos los derechos reservados</p>
</footer>

</body>
</html>
