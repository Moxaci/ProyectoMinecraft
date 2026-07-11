<?php
// api/eliminar_proyecto.php
// Elimina un proyecto (solo si pertenece al usuario)

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    sendErrorResponse('No autenticado', 401);
}

// Verificar método DELETE (o POST con _method)
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

if ($method !== 'DELETE') {
    sendErrorResponse('Método no permitido. Use DELETE', 405);
}

$usuarioId = $_SESSION['usuario_id'];
$proyectoId = $_POST['id'] ?? $_GET['id'] ?? 0;

if ($proyectoId <= 0) {
    sendErrorResponse('ID de proyecto requerido', 400);
}

// Conectar a la base de datos
$conn = getConnection();

// Eliminar proyecto
$result = eliminarProyecto($conn, $proyectoId, $usuarioId);
closeConnection($conn);

if ($result) {
    sendSuccessResponse([
        'success' => true,
        'message' => 'Proyecto eliminado exitosamente'
    ]);
} else {
    sendErrorResponse('No se pudo eliminar el proyecto', 500);
}
?>