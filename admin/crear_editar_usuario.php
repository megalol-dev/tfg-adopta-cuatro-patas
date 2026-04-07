<?php
declare(strict_types=1);

require_once __DIR__ . '/seguridad.php';
exigir_rol('gerente');

$db = abrir_db();

$mensaje = '';
$modo_edicion = false;
$usuario_editar = null;

/* ============================
   CREAR USUARIO
============================ */
if (isset($_POST['crear_usuario'])) {

    $usuario = trim($_POST['usuario']);
    $contrasena = $_POST['contrasena'];
    $nombre_completo = trim($_POST['nombre_completo']);
    $correo = trim($_POST['correo']);
    $rol = $_POST['rol'];
    $activo = (int)$_POST['activo'];

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO usuarios_internos
        (usuario, contrasena_hash, nombre_completo, correo, rol, activo)
        VALUES (:usuario, :hash, :nombre_completo, :correo, :rol, :activo)");

    $stmt->bindValue(':usuario', $usuario);
    $stmt->bindValue(':hash', $hash);
    $stmt->bindValue(':nombre_completo', $nombre_completo);
    $stmt->bindValue(':correo', $correo);
    $stmt->bindValue(':rol', $rol);
    $stmt->bindValue(':activo', $activo);

    $stmt->execute();

    $mensaje = "Usuario creado correctamente.";
}

/* ============================
   CARGAR DATOS PARA EDITAR
============================ */
if (isset($_GET['editar'])) {
    $modo_edicion = true;
    $id = (int)$_GET['editar'];

    $stmt = $db->prepare("SELECT * FROM usuarios_internos WHERE id_usuario = :id");
    $stmt->bindValue(':id', $id);
    $res = $stmt->execute();
    $usuario_editar = $res->fetchArray(SQLITE3_ASSOC);
}

/* ============================
   ACTUALIZAR USUARIO
============================ */
if (isset($_POST['actualizar_usuario'])) {

    $id = (int)$_POST['id_usuario'];
    $nombre_completo = trim($_POST['nombre_completo']);
    $correo = trim($_POST['correo']);
    $rol = $_POST['rol'];
    $activo = (int)$_POST['activo'];

    $stmt = $db->prepare("UPDATE usuarios_internos
        SET nombre_completo = :nombre_completo,
            correo = :correo,
            rol = :rol,
            activo = :activo
        WHERE id_usuario = :id");

    $stmt->bindValue(':nombre_completo', $nombre_completo);
    $stmt->bindValue(':correo', $correo);
    $stmt->bindValue(':rol', $rol);
    $stmt->bindValue(':activo', $activo);
    $stmt->bindValue(':id', $id);

    $stmt->execute();

    $mensaje = "Usuario actualizado correctamente.";
    $modo_edicion = false;
}

/* ============================
   LISTAR USUARIOS
============================ */
$usuarios = $db->query("SELECT * FROM usuarios_internos ORDER BY id_usuario DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear / Editar Usuario</title>
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

    <h2 class="form-titulo"><?php echo $modo_edicion ? "Editar Usuario" : "Crear Usuario"; ?></h2>

    <?php if ($mensaje): ?>
        <p class="mensaje-ok"><strong><?php echo htmlspecialchars($mensaje); ?></strong></p>
    <?php endif; ?>

    <!-- TARJETA única para el formulario -->
    <form method="post" class="tarjeta-formulario formulario-admin">

        <?php if ($modo_edicion && $usuario_editar): ?>

            <input type="hidden" name="id_usuario" value="<?php echo (int)$usuario_editar['id_usuario']; ?>">

            <div class="form-grupo">
                <label for="nombre_completo">Nombre completo</label>
                <input id="nombre_completo" type="text" name="nombre_completo"
                       value="<?php echo htmlspecialchars((string)$usuario_editar['nombre_completo']); ?>">
            </div>

            <div class="form-grupo">
                <label for="correo">Correo</label>
                <input id="correo" type="email" name="correo"
                       value="<?php echo htmlspecialchars((string)$usuario_editar['correo']); ?>">
            </div>

            <div class="form-grupo">
                <label for="rol">Rol</label>
                <select id="rol" name="rol">
                    <option value="gerente" <?php if($usuario_editar['rol']=='gerente') echo 'selected'; ?>>Gerente</option>
                    <option value="ayudante" <?php if($usuario_editar['rol']=='ayudante') echo 'selected'; ?>>Ayudante</option>
                </select>
            </div>

            <div class="form-grupo">
                <label for="activo">Activo</label>
                <select id="activo" name="activo">
                    <option value="1" <?php if((int)$usuario_editar['activo']===1) echo 'selected'; ?>>Activo</option>
                    <option value="0" <?php if((int)$usuario_editar['activo']===0) echo 'selected'; ?>>Inactivo</option>
                </select>
            </div>

            <button type="submit" name="actualizar_usuario" class="btn-primario">Actualizar Usuario</button>

        <?php else: ?>

            <div class="form-grupo">
                <label for="usuario">Usuario</label>
                <input id="usuario" type="text" name="usuario" required>
            </div>

            <div class="form-grupo">
                <label for="contrasena">Contraseña</label>
                <input id="contrasena" type="password" name="contrasena" required>
            </div>

            <div class="form-grupo">
                <label for="nombre_completo">Nombre completo</label>
                <input id="nombre_completo" type="text" name="nombre_completo">
            </div>

            <div class="form-grupo">
                <label for="correo">Correo</label>
                <input id="correo" type="email" name="correo">
            </div>

            <div class="form-grupo">
                <label for="rol">Rol</label>
                <select id="rol" name="rol">
                    <option value="ayudante">Ayudante</option>
                    <option value="gerente">Gerente</option>
                </select>
            </div>

            <div class="form-grupo">
                <label for="activo">Activo</label>
                <select id="activo" name="activo">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>

            <button type="submit" name="crear_usuario" class="btn-primario">Crear Usuario</button>

        <?php endif; ?>

    </form>

    <!-- TABLA en tarjeta para que se vea uniforme -->
    <section class="tarjeta-formulario tabla-admin">
        <h3 class="subtitulo-admin">Lista de usuarios</h3>

        <div class="tabla-wrapper">
            <table class="tabla-usuarios">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
                </thead>

                <tbody>
                <?php while ($u = $usuarios->fetchArray(SQLITE3_ASSOC)): ?>
                    <tr>
                        <td><?php echo (int)$u['id_usuario']; ?></td>
                        <td><strong><?php echo htmlspecialchars((string)$u['usuario']); ?></strong></td>
                        <td><?php echo htmlspecialchars((string)$u['nombre_completo']); ?></td>
                        <td><?php echo htmlspecialchars((string)$u['correo']); ?></td>
                        <td>
                            <span class="badge <?php echo $u['rol']==='gerente' ? 'badge-morado' : 'badge-menta'; ?>">
                                <?php echo htmlspecialchars((string)$u['rol']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo ((int)$u['activo']===1) ? 'badge-ok' : 'badge-off'; ?>">
                                <?php echo ((int)$u['activo']===1) ? 'Sí' : 'No'; ?>
                            </span>
                        </td>
                        <td>
                            <a class="link-accion" href="?editar=<?php echo (int)$u['id_usuario']; ?>">Editar</a>
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