<?php
// api/renombrar_proyecto.php
// Renombra un proyecto

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    sendErrorResponse('No autenticado', 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Método no permitido', 405);
}

$usuarioId = $_SESSION['usuario_id'];
$proyectoId = $_POST['proyecto_id'] ?? 0;
$nombre = trim($_POST['nombre'] ?? '');

if ($proyectoId <= 0 || empty($nombre) || strlen($nombre) < 3) {
    sendErrorResponse('Nombre inválido (mínimo 3 caracteres)', 400);
}

$conn = getConnection();
$success = updateProyectoNombre($conn, $proyectoId, $usuarioId, $nombre);
closeConnection($conn);

if ($success) {
    sendSuccessResponse([
        'success' => true,
        'message' => 'Proyecto renombrado exitosamente'
    ]);
} else {
    sendErrorResponse('Error al renombrar el proyecto', 500);
}
?>