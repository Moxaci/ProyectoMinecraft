<?php
// Obtiene el inventario del usuario

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    sendErrorResponse('No autenticado', 401);
}

$usuarioId = $_SESSION['usuario_id'];
$conn = getConnection();
$inventario = getInventarioUsuario($conn, $usuarioId);
closeConnection($conn);

sendSuccessResponse([
    'success' => true,
    'inventario' => $inventario
]);
?>