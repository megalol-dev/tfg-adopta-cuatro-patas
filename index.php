<?php
declare(strict_types=1);

$db = new SQLite3(__DIR__ . '/baseDatos.db');
$db->exec('PRAGMA foreign_keys = ON;');

/* Solo se muestran los perros NO ocultos */
$resultado = $db->query("SELECT * FROM perros WHERE estado != 'baja' ORDER BY id_perro DESC");

function estado_css(string $estado): string {
    return match ($estado) {
        'disponible' => 'estado-disponible',
        'reservado'  => 'estado-reservado',
        'adoptado'   => 'estado-adoptado',
        'baja'       => 'estado-baja',
        default      => ''
    };
}

function texto_estado(string $estado): string {
    return match ($estado) {
        'baja' => 'Oculto',
        default => ucfirst($estado)
    };
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Adopta Cuatro Patas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

<header>
    <h1>Adopta Cuatro Patas</h1>
    <nav>
        <a href="quienes_somos.html"><button>Quiénes somos</button></a>
        <a href="como_ayudar.html"><button>Cómo ayudar</button></a>
        <a href="contacto.html"><button>Contacta con nosotros</button></a>
    </nav>
</header>

<main>
    <h2>Perros</h2>

    <section class="contenedor-tarjetas">
        <?php while ($perro = $resultado->fetchArray(SQLITE3_ASSOC)): ?>
            <?php
                $id = (int)$perro['id_perro'];
                $estado = (string)$perro['estado'];

                $rutaImgPublica = "img/perros/dog{$id}.png";
                $rutaImgSistema = __DIR__ . "/img/perros/dog{$id}.png";

                $claseEstado = estado_css($estado);
            ?>

            <article class="tarjeta-perro">
                <?php if (file_exists($rutaImgSistema)): ?>
                    <img
                        src="<?php echo htmlspecialchars($rutaImgPublica); ?>"
                        alt="Foto de <?php echo htmlspecialchars((string)$perro['nombre']); ?>">
                <?php else: ?>
                    <p class="muted"><em>Sin imagen</em></p>
                <?php endif; ?>

                <h3><?php echo htmlspecialchars((string)$perro['nombre']); ?></h3>

                <p><strong>Raza:</strong> <?php echo htmlspecialchars((string)$perro['raza']); ?></p>
                <p><strong>Edad:</strong> <?php echo (int)$perro['edad_anios']; ?> años</p>
                <p class="muted"><?php echo htmlspecialchars((string)$perro['descripcion']); ?></p>

                <span class="etiqueta-estado <?php echo htmlspecialchars($claseEstado); ?>">
                    <?php echo htmlspecialchars(texto_estado($estado)); ?>
                </span>

                <?php if ($estado === 'disponible'): ?>
                    <a href="formulario_adopcion.php?id=<?php echo $id; ?>">
                        <button class="btn-adoptar">Adoptar</button>
                    </a>
                <?php else: ?>
                    <button type="button" disabled class="btn-adoptar">No disponible</button>
                <?php endif; ?>
            </article>

        <?php endwhile; ?>
    </section>

</main>

<footer>
    <p>&copy; 2026 Adopta Cuatro Patas - Todos los derechos reservados</p>
</footer>

</body>
</html>