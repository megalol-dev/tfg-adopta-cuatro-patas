<?php
declare(strict_types=1);

require_once __DIR__ . '/seguridad.php';
exigir_login();

$db = abrir_db();

$mensaje = '';
$error = '';

// Vista actual
$vista = $_GET['vista'] ?? 'pendientes';
if ($vista !== 'pendientes' && $vista !== 'gestionadas') {
    $vista = 'pendientes';
}

/* =========================
   ACCIONES: VALIDAR / BORRAR
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id_solicitud = (int)($_POST['id_solicitud'] ?? 0);

    if ($id_solicitud <= 0) {
        $error = 'Solicitud inválida.';
    } else {

        // Obtener solicitud + datos del perro
        $stmt = $db->prepare("
            SELECT s.*, p.nombre AS perro_nombre, p.estado AS perro_estado
            FROM solicitudes_adopcion s
            JOIN perros p ON p.id_perro = s.id_perro
            WHERE s.id_solicitud = :id
            LIMIT 1
        ");
        $stmt->bindValue(':id', $id_solicitud, SQLITE3_INTEGER);
        $res = $stmt->execute();
        $sol = $res ? $res->fetchArray(SQLITE3_ASSOC) : null;

        if (!$sol) {
            $error = 'No se encontró la solicitud.';
        } else {

            if ($accion === 'borrar') {
                $stmt = $db->prepare("DELETE FROM solicitudes_adopcion WHERE id_solicitud = :id");
                $stmt->bindValue(':id', $id_solicitud, SQLITE3_INTEGER);
                $stmt->execute();
                $mensaje = 'Solicitud eliminada correctamente.';
                $vista = 'pendientes';
            }

            if ($accion === 'validar') {

                $id_perro = (int)$sol['id_perro'];

                if ((string)$sol['perro_estado'] !== 'disponible') {
                    $error = 'No se puede validar: el perro no está disponible.';
                } else {

                    $db->exec('BEGIN');

                    try {
                        // 1) Crear/obtener adoptante por DNI
                        $dni = trim((string)$sol['dni']);

                        $stmt = $db->prepare("SELECT id_adoptante FROM adoptantes WHERE dni = :dni LIMIT 1");
                        $stmt->bindValue(':dni', $dni, SQLITE3_TEXT);
                        $res = $stmt->execute();
                        $adopt = $res ? $res->fetchArray(SQLITE3_ASSOC) : null;

                        if ($adopt) {
                            $id_adoptante = (int)$adopt['id_adoptante'];
                        } else {
                            $stmt = $db->prepare("
                                INSERT INTO adoptantes (nombre, apellidos, dni, telefono, correo)
                                VALUES (:nombre, :apellidos, :dni, :telefono, :correo)
                            ");
                            $stmt->bindValue(':nombre', (string)$sol['nombre'], SQLITE3_TEXT);
                            $stmt->bindValue(':apellidos', (string)$sol['apellidos'], SQLITE3_TEXT);
                            $stmt->bindValue(':dni', $dni, SQLITE3_TEXT);
                            $stmt->bindValue(':telefono', (string)$sol['telefono'], SQLITE3_TEXT);
                            $stmt->bindValue(':correo', (string)$sol['correo'], SQLITE3_TEXT);
                            $stmt->execute();

                            $id_adoptante = (int)$db->lastInsertRowID();
                        }

                        // 2) Crear adopción (id_perro UNIQUE)
                        $stmt = $db->prepare("
                            INSERT INTO adopciones (id_perro, id_adoptante, id_solicitud, notas)
                            VALUES (:id_perro, :id_adoptante, :id_solicitud, :notas)
                        ");
                        $stmt->bindValue(':id_perro', $id_perro, SQLITE3_INTEGER);
                        $stmt->bindValue(':id_adoptante', $id_adoptante, SQLITE3_INTEGER);
                        $stmt->bindValue(':id_solicitud', $id_solicitud, SQLITE3_INTEGER);
                        $stmt->bindValue(':notas', (string)($sol['mensaje'] ?? ''), SQLITE3_TEXT);
                        $stmt->execute();

                        // 3) Marcar perro como adoptado
                        $stmt = $db->prepare("
                            UPDATE perros
                            SET estado = 'adoptado',
                                actualizado_en = datetime('now')
                            WHERE id_perro = :id_perro
                        ");
                        $stmt->bindValue(':id_perro', $id_perro, SQLITE3_INTEGER);
                        $stmt->execute();

                        // 4) Marcar solicitud como cerrada
                        $stmt = $db->prepare("
                            UPDATE solicitudes_adopcion
                            SET estado = 'cerrada',
                                gestionada_por = :gestor
                            WHERE id_solicitud = :id_solicitud
                        ");
                        $stmt->bindValue(':gestor', (int)($_SESSION['usuario_id'] ?? 0), SQLITE3_INTEGER);
                        $stmt->bindValue(':id_solicitud', $id_solicitud, SQLITE3_INTEGER);
                        $stmt->execute();

                        $db->exec('COMMIT');
                        $mensaje = 'Solicitud validada: adopción registrada correctamente.';
                        $vista = 'gestionadas';

                    } catch (Throwable $e) {
                        $db->exec('ROLLBACK');
                        $error = 'Error al validar la solicitud (posible adopción ya existente).';
                    }
                }
            }
        }
    }
}

function foto_publica(int $id_perro): string {
    return "../img/perros/dog{$id_perro}.png";
}
function foto_sistema(int $id_perro): string {
    return __DIR__ . "/../img/perros/dog{$id_perro}.png";
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver peticiones - Adopta Cuatro Patas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../estilos.css">
</head>

<body>

<header>
    <h1>Adopta Cuatro Patas</h1>
    <nav>
        <a href="panel_control.php"><button>Panel de control</button></a>
        <a href="cerrar_sesion.php"><button>Cerrar sesión</button></a>
    </nav>
</header>

<main class="pagina-formulario">
    <h2 class="form-titulo">Gestión de peticiones</h2>

    <?php if ($mensaje !== ''): ?>
        <p class="mensaje-ok"><strong><?php echo h($mensaje); ?></strong></p>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p class="mensaje-error"><strong><?php echo h($error); ?></strong></p>
    <?php endif; ?>

    <!-- Navegación -->
    <section class="tarjeta-formulario peticiones-nav">
        <a href="ver_peticiones.php?vista=pendientes">
            <button type="button" class="<?php echo $vista === 'pendientes' ? 'btn-primario' : 'btn-secundario'; ?>">
                Pendientes
            </button>
        </a>
        <a href="ver_peticiones.php?vista=gestionadas">
            <button type="button" class="<?php echo $vista === 'gestionadas' ? 'btn-primario' : 'btn-secundario'; ?>">
                Gestionadas
            </button>
        </a>
    </section>

    <?php if ($vista === 'pendientes'): ?>

        <section class="tarjeta-formulario tabla-admin">
            <h3 class="subtitulo-admin">Peticiones pendientes</h3>

            <?php
            $pendientes = $db->query("
                SELECT s.*, p.nombre AS perro_nombre, p.raza AS perro_raza, p.edad_anios AS perro_edad, p.estado AS perro_estado
                FROM solicitudes_adopcion s
                JOIN perros p ON p.id_perro = s.id_perro
                WHERE s.estado IN ('pendiente','en_contacto','aprobada')
                ORDER BY s.creada_en DESC
            ");
            ?>

            <div class="tabla-wrapper">
                <table class="tabla-peticiones">
                    <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Perro</th>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th>Mensaje</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php
                    $hay = false;
                    while ($s = $pendientes->fetchArray(SQLITE3_ASSOC)):
                        $hay = true;
                        $idp = (int)$s['id_perro'];
                        $estadoPerro = (string)$s['perro_estado'];
                    ?>
                        <tr>
                            <td>
                                <?php if (file_exists(foto_sistema($idp))): ?>
                                    <img class="tabla-foto" src="<?php echo h(foto_publica($idp)); ?>" alt="Foto">
                                <?php else: ?>
                                    <em>Sin foto</em>
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong><?php echo h((string)$s['perro_nombre']); ?></strong><br>
                                <span class="muted"><?php echo h((string)$s['perro_raza']); ?> · <?php echo (int)$s['perro_edad']; ?> años</span><br>
                                <span class="badge <?php echo badge_estado($estadoPerro); ?>">
                                    <?php echo h($estadoPerro); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo h((string)$s['nombre']); ?> <?php echo h((string)$s['apellidos']); ?><br>
                                <span class="muted">DNI: <?php echo h((string)$s['dni']); ?></span>
                            </td>

                            <td>
                                <span class="muted">Tel:</span> <?php echo h((string)$s['telefono']); ?><br>
                                <span class="muted">Email:</span> <?php echo h((string)$s['correo']); ?>
                            </td>

                            <td><?php echo h((string)($s['mensaje'] ?? '')); ?></td>
                            <td><?php echo h((string)$s['creada_en']); ?></td>

                            <td>
                                <div class="acciones-inline">

                                    <form method="post">
                                        <input type="hidden" name="id_solicitud" value="<?php echo (int)$s['id_solicitud']; ?>">
                                        <input type="hidden" name="accion" value="validar">
                                        <button type="submit" class="btn-primario">Validar</button>
                                    </form>

                                    <form method="post" onsubmit="return confirm('¿Seguro que desea borrar esta solicitud?');">
                                        <input type="hidden" name="id_solicitud" value="<?php echo (int)$s['id_solicitud']; ?>">
                                        <input type="hidden" name="accion" value="borrar">
                                        <button type="submit" class="btn-peligro">Borrar</button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                    <?php if (!$hay): ?>
                        <tr><td colspan="7"><em>No existen peticiones pendientes.</em></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    <?php else: ?>

        <section class="tarjeta-formulario tabla-admin">
            <h3 class="subtitulo-admin">Peticiones gestionadas</h3>

            <?php
            $gestionadas = $db->query("
                SELECT
                    a.id_adopcion,
                    a.adoptado_en,
                    p.id_perro,
                    p.nombre AS perro_nombre,
                    p.raza AS perro_raza,
                    p.edad_anios AS perro_edad,
                    o.nombre AS adoptante_nombre,
                    o.apellidos AS adoptante_apellidos,
                    o.dni AS adoptante_dni,
                    o.telefono AS adoptante_telefono,
                    o.correo AS adoptante_correo
                FROM adopciones a
                JOIN perros p ON p.id_perro = a.id_perro
                JOIN adoptantes o ON o.id_adoptante = a.id_adoptante
                ORDER BY a.adoptado_en DESC
            ");
            ?>

            <div class="tabla-wrapper">
                <table class="tabla-peticiones">
                    <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Perro</th>
                        <th>Adoptante</th>
                        <th>Contacto</th>
                        <th>Fecha adopción</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php
                    $hay = false;
                    while ($g = $gestionadas->fetchArray(SQLITE3_ASSOC)):
                        $hay = true;
                        $idp = (int)$g['id_perro'];
                    ?>
                        <tr>
                            <td>
                                <?php if (file_exists(foto_sistema($idp))): ?>
                                    <img class="tabla-foto" src="<?php echo h(foto_publica($idp)); ?>" alt="Foto">
                                <?php else: ?>
                                    <em>Sin foto</em>
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong><?php echo h((string)$g['perro_nombre']); ?></strong><br>
                                <span class="muted"><?php echo h((string)$g['perro_raza']); ?> · <?php echo (int)$g['perro_edad']; ?> años</span><br>
                                <span class="badge badge-morado">adoptado</span>
                            </td>

                            <td>
                                <?php echo h((string)$g['adoptante_nombre']); ?> <?php echo h((string)$g['adoptante_apellidos']); ?><br>
                                <span class="muted">DNI: <?php echo h((string)$g['adoptante_dni']); ?></span>
                            </td>

                            <td>
                                <span class="muted">Tel:</span> <?php echo h((string)$g['adoptante_telefono']); ?><br>
                                <span class="muted">Email:</span> <?php echo h((string)$g['adoptante_correo']); ?>
                            </td>

                            <td><?php echo h((string)$g['adoptado_en']); ?></td>
                        </tr>
                    <?php endwhile; ?>

                    <?php if (!$hay): ?>
                        <tr><td colspan="5"><em>No existen adopciones registradas.</em></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    <?php endif; ?>

</main>

<footer>
    <p>&copy; 2026 Adopta Cuatro Patas - Todos los derechos reservados</p>
</footer>

</body>
</html>