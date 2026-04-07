<?php
// admin/seguridad.php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function abrir_db(): SQLite3
{
    // Ruta relativa desde /admin hacia la BD en la raíz
    $dbPath = __DIR__ . '/../baseDatos.db';

    if (!file_exists($dbPath)) {
        http_response_code(500);
        exit('Error: No se encontró el archivo de base de datos.');
    }

    $db = new SQLite3($dbPath);
    $db->exec('PRAGMA foreign_keys = ON;');
    return $db;
}

function esta_logueado(): bool
{
    return isset($_SESSION['usuario_id'], $_SESSION['rol'], $_SESSION['usuario']);
}

function exigir_login(): void
{
    if (!esta_logueado()) {
        header('Location: iniciar_sesion.php');
        exit;
    }
}

function exigir_rol(string $rolRequerido): void
{
    exigir_login();

    // Jerarquía simple: gerente > ayudante
    $rolActual = $_SESSION['rol'] ?? '';

    if ($rolRequerido === 'gerente' && $rolActual !== 'gerente') {
        http_response_code(403);
        exit('Acceso denegado: se requiere rol gerente.');
    }
}

function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}