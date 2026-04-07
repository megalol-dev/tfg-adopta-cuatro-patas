<?php
declare(strict_types=1);

require_once __DIR__ . '/seguridad.php';
exigir_login();

$db = abrir_db();

$mensaje = '';
$modo_edicion = false;
$perro_editar = null;

$carpetaImagenes = __DIR__ . '/../img/perros/';
if (!is_dir($carpetaImagenes)) {
    mkdir($carpetaImagenes, 0775, true);
}

/* ============================
   CREAR PERRO
============================ */
if (isset($_POST['crear_perro'])) {

    $nombre = trim($_POST['nombre'] ?? '');
    $raza = trim($_POST['raza'] ?? '');
    $edad_anios = (int)($_POST['edad_anios'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estado = $_POST['estado'] ?? 'disponible';

    if ($nombre === '' || $raza === '') {
        $mensaje = 'Error: Nombre y raza son obligatorios.';
    } else {
        $stmt = $db->prepare("
            INSERT INTO perros (nombre, raza, edad_anios, descripcion, estado, creado_por)
            VALUES (:nombre, :raza, :edad, :descripcion, :estado, :creado_por)
        ");
        $stmt->bindValue(':nombre', $nombre, SQLITE3_TEXT);
        $stmt->bindValue(':raza', $raza, SQLITE3_TEXT);
        $stmt->bindValue(':edad', $edad_anios, SQLITE3_INTEGER);
        $stmt->bindValue(':descripcion', $descripcion, SQLITE3_TEXT);
        $stmt->bindValue(':estado', $estado, SQLITE3_TEXT);
        $stmt->bindValue(':creado_por', (int)($_SESSION['usuario_id'] ?? 0), SQLITE3_INTEGER);
        $stmt->execute();

        $id_perro = (int)$db->lastInsertRowID();

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $ok = guardar_foto_png($id_perro, $carpetaImagenes, $mensaje);
            if ($ok) {
                $mensaje = "Perro creado correctamente (ID $id_perro) y foto guardada.";
            } else {
                $mensaje = $mensaje !== '' ? $mensaje : "Perro creado (ID $id_perro), pero no se pudo guardar la foto.";
            }
        } else {
            $mensaje = "Perro creado correctamente (ID $id_perro).";
        }
    }
}

/* ============================
   CARGAR PERRO PARA EDITAR
============================ */
if (isset($_GET['editar'])) {
    $modo_edicion = true;
    $id = (int)$_GET['editar'];

    $stmt = $db->prepare("SELECT * FROM perros WHERE id_perro = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $res = $stmt->execute();
    $perro_editar = $res ? $res->fetchArray(SQLITE3_ASSOC) : null;

    if (!$perro_editar) {
        $modo_edicion = false;
        $mensaje = 'No se encontró el perro indicado para editar.';
    }
}

/* ============================
   ACTUALIZAR PERRO
============================ */
if (isset($_POST['actualizar_perro'])) {

    $id = (int)($_POST['id_perro'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $raza = trim($_POST['raza'] ?? '');
    $edad_anios = (int)($_POST['edad_anios'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estado = $_POST['estado'] ?? 'disponible';

    if ($id <= 0 || $nombre === '' || $raza === '') {
        $mensaje = 'Error: datos inválidos para actualizar.';
    } else {
        $stmt = $db->prepare("
            UPDATE perros
            SET nombre = :nombre,
                raza = :raza,
                edad_anios = :edad,
                descripcion = :descripcion,
                estado = :estado,
                actualizado_en = datetime('now')
            WHERE id_perro = :id
        ");
        $stmt->bindValue(':nombre', $nombre, SQLITE3_TEXT);
        $stmt->bindValue(':raza', $raza, SQLITE3_TEXT);
        $stmt->bindValue(':edad', $edad_anios, SQLITE3_INTEGER);
        $stmt->bindValue(':descripcion', $descripcion, SQLITE3_TEXT);
        $stmt->bindValue(':estado', $estado, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $ok = guardar_foto_png($id, $carpetaImagenes, $mensaje);
            if ($ok) {
                $mensaje = "Perro actualizado correctamente y foto guardada.";
            } else {
                $mensaje = $mensaje !== '' ? $mensaje : "Perro actualizado, pero no se pudo guardar la foto.";
            }
        } else {
            $mensaje = "Perro actualizado correctamente.";
        }

        $modo_edicion = false;
        $perro_editar = null;
    }
}

/* ============================
   LISTAR PERROS
============================ */
$perros = $db->query("SELECT * FROM perros ORDER BY id_perro DESC");

/* ============================
   FUNCIÓN: guardar foto PNG
============================ */
function guardar_foto_png(int $id_perro, string $carpetaImagenes, string &$mensaje): bool
{
    if (!isset($_FILES['foto'])) {
        $mensaje = 'No se recibió ningún archivo.';
        return false;
    }

    $f = $_FILES['foto'];

    if ($f['error'] !== UPLOAD_ERR_OK) {
        $mensaje = 'Error al subir la imagen.';
        return false;
    }

    $info = getimagesize($f['tmp_name']);
    if ($info === false) {
        $mensaje = 'El archivo no es una imagen válida.';
        return false;
    }

    if ($info[2] !== IMAGETYPE_PNG) {
        $mensaje = 'La imagen debe ser PNG.';
        return false;
    }

    $destino = $carpetaImagenes . "dog{$id_perro}.png";

    if (!move_uploaded_file($f['tmp_name'], $destino)) {
        $mensaje = 'No se pudo guardar la imagen en el servidor.';
        return false;
    }

    return true;
}

function badge_estado(string $estado): string {
    return match ($estado) {
        'disponible' => 'badge-ok',
        'reservado'  => 'badge-warning',
        'adoptado'   => 'badge-morado',
        'baja'       => 'badge-off',
        default      => 'badge'
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
    <title>Crear / Editar Animal - Adopta Cuatro Patas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../estilos.css">
</head>

<body>

<header>
    <h1>Adopta Cuatro Patas</h1>
    <nav>
        <a href="../index.php"><button>Inicio</button></a>
        <a href="panel_control.php"><button>Panel de control</button></a>
        <a href="cerrar_sesion.php"><button>Cerrar sesión</button></a>
    </nav>
</header>

<main class="pagina-formulario">

    <h2 class="form-titulo"><?php echo $modo_edicion ? 'Editar animal' : 'Crear animal'; ?></h2>

    <?php if ($mensaje !== ''): ?>
        <p class="mensaje-ok"><strong><?php echo h($mensaje); ?></strong></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="tarjeta-formulario formulario-admin">

        <?php if ($modo_edicion && $perro_editar): ?>
            <input type="hidden" name="id_perro" value="<?php echo (int)$perro_editar['id_perro']; ?>">
        <?php endif; ?>

        <fieldset>
            <legend>Datos del perro</legend>

            <div class="form-grupo">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required
                       value="<?php echo $perro_editar ? h((string)$perro_editar['nombre']) : ''; ?>">
            </div>

            <div class="form-grupo">
                <label for="raza">Raza</label>
                <input type="text" id="raza" name="raza" required
                       value="<?php echo $perro_editar ? h((string)$perro_editar['raza']) : ''; ?>">
            </div>

            <div class="form-grupo">
                <label for="edad_anios">Edad (años)</label>
                <input type="number" id="edad_anios" name="edad_anios" min="0" max="40" required
                       value="<?php echo $perro_editar ? (int)$perro_editar['edad_anios'] : 0; ?>">
            </div>

            <div class="form-grupo">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4"><?php echo $perro_editar ? h((string)$perro_editar['descripcion']) : ''; ?></textarea>
            </div>

            <div class="form-grupo">
                <label for="estado">Estado</label>
                <?php $estadoActual = $perro_editar ? (string)$perro_editar['estado'] : 'disponible'; ?>
                <select id="estado" name="estado" required>
                    <option value="disponible" <?php echo $estadoActual === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                    <option value="reservado" <?php echo $estadoActual === 'reservado' ? 'selected' : ''; ?>>Reservado</option>
                    <option value="adoptado" <?php echo $estadoActual === 'adoptado' ? 'selected' : ''; ?>>Adoptado</option>
                    <option value="baja" <?php echo $estadoActual === 'baja' ? 'selected' : ''; ?>>Oculto</option>
                </select>
            </div>
        </fieldset>

        <fieldset>
            <legend>Imagen del perro (PNG)</legend>

            <p class="nota-admin">
                La imagen se guarda en <strong>img/perros/</strong> con el nombre
                <strong>dog&lt;id_perro&gt;.png</strong> (por ejemplo: dog1.png).
            </p>

            <div class="form-grupo">
                <label for="foto">Seleccionar imagen (PNG)</label>
                <input type="file" id="foto" name="foto" accept="image/png">
            </div>

            <?php if ($modo_edicion && $perro_editar): ?>
                <?php
                    $idFoto = (int)$perro_editar['id_perro'];
                    $rutaPublica = "../img/perros/dog{$idFoto}.png";
                    $rutaSistema = __DIR__ . "/../img/perros/dog{$idFoto}.png";
                ?>
                <div class="vista-previa">
                    <strong>Vista previa actual:</strong><br>
                    <?php if (file_exists($rutaSistema)): ?>
                        <img class="img-previa" src="<?php echo h($rutaPublica); ?>" alt="Foto del perro">
                    <?php else: ?>
                        <em>No hay imagen guardada.</em>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </fieldset>

        <?php if ($modo_edicion): ?>
            <button type="submit" name="actualizar_perro" class="btn-primario">Actualizar animal</button>
        <?php else: ?>
            <button type="submit" name="crear_perro" class="btn-primario">Crear animal</button>
        <?php endif; ?>

    </form>

    <section class="tarjeta-formulario tabla-admin">
        <h3 class="subtitulo-admin">Lista de animales</h3>

        <div class="tabla-wrapper">
            <table class="tabla-animales">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>Raza</th>
                    <th>Edad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>

                <tbody>
                <?php while ($p = $perros->fetchArray(SQLITE3_ASSOC)): ?>
                    <?php
                        $idp = (int)$p['id_perro'];
                        $fotoPublica = "../img/perros/dog{$idp}.png";
                        $fotoSistema = __DIR__ . "/../img/perros/dog{$idp}.png";
                        $estadoP = (string)$p['estado'];
                    ?>
                    <tr>
                        <td><?php echo $idp; ?></td>
                        <td>
                            <?php if (file_exists($fotoSistema)): ?>
                                <img class="tabla-foto" src="<?php echo h($fotoPublica); ?>" alt="Foto">
                            <?php else: ?>
                                <em>Sin foto</em>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo h((string)$p['nombre']); ?></strong></td>
                        <td><?php echo h((string)$p['raza']); ?></td>
                        <td><?php echo (int)$p['edad_anios']; ?></td>
                        <td>
                            <span class="badge <?php echo badge_estado($estadoP); ?>">
                                <?php echo h(texto_estado($estadoP)); ?>
                            </span>
                        </td>
                        <td>
                            <a class="link-accion" href="?editar=<?php echo $idp; ?>">Editar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>

            </table>
        </div>
    </section>

</main>

<footer>
    <p>&copy; 2026 Adopta Cuatro Patas - Todos los derechos reservados</p>
</footer>

</body>
</html>