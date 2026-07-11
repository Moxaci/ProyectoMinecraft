<?php
// api/proyecto_detalle.php
// Obtiene los detalles de un proyecto específico

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    sendErrorResponse('No autenticado', 401);
}

$usuarioId = $_SESSION['usuario_id'];
$proyectoId = $_GET['id'] ?? 0;

if ($proyectoId <= 0) {
    sendErrorResponse('ID de proyecto requerido', 400);
}

// Conectar a la base de datos
$conn = getConnection();

// Obtener detalle del proyecto
$proyecto = getProyectoDetalle($conn, $proyectoId, $usuarioId);
closeConnection($conn);

if ($proyecto === null) {
    sendErrorResponse('Proyecto no encontrado o no autorizado', 404);
}

sendSuccessResponse([
    'success' => true,
    'proyecto' => $proyecto
]);
?>