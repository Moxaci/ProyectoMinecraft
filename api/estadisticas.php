<?php
// api/estadisticas.php
// Obtiene estadísticas para el dashboard

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    sendErrorResponse('No autenticado', 401);
}

$usuarioId = $_SESSION['usuario_id'];

// Conectar a la base de datos
$conn = getConnection();

// Obtener estadísticas
$estadisticas = getEstadisticas($conn, $usuarioId);
closeConnection($conn);

sendSuccessResponse([
    'success' => true,
    'estadisticas' => $estadisticas
]);
?>