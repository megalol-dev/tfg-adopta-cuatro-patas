<?php
//Este archivo se a mantenido puramente por motivos didacticos, en verdad deberia borrase una vez es ejecutado y el usuairo root se crea.

// Conexión a la base de datos
$db = new SQLite3('baseDatos.db');

// Datos del nuevo administrador
$usuario = 'admin_maestro';
$contrasena_plana = '1234567890';

// Generar hash seguro
$contrasena_hash = password_hash($contrasena_plana, PASSWORD_DEFAULT);

// Preparar inserción
$stmt = $db->prepare("INSERT INTO usuarios_internos 
(usuario, contrasena_hash, nombre_completo, correo, rol, activo) 
VALUES (:usuario, :contrasena_hash, :nombre_completo, :correo, :rol, :activo)");

$stmt->bindValue(':usuario', $usuario);
$stmt->bindValue(':contrasena_hash', $contrasena_hash);
$stmt->bindValue(':nombre_completo', 'Administrador Maestro');
$stmt->bindValue(':correo', 'admin@adopta.local');
$stmt->bindValue(':rol', 'gerente');
$stmt->bindValue(':activo', 1);

$resultado = $stmt->execute();

if ($resultado) {
    echo "Usuario administrador creado correctamente.<br>";
    echo "Usuario: $usuario<br>";
    echo "Contraseña (plana solo para referencia): $contrasena_plana<br>";
    echo "Hash almacenado:<br>";
    echo $contrasena_hash;
} else {
    echo "Error al crear el usuario.";
}

?>