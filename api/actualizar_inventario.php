<?php
// Actualiza el inventario del usuario

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
$itemId = $_POST['item_id'] ?? '';
$cantidad = intval($_POST['cantidad'] ?? 0);

if (empty($itemId)) {
    sendErrorResponse('Item requerido', 400);
}

$conn = getConnection();
$success = updateInventarioUsuario($conn, $usuarioId, $itemId, $cantidad);
closeConnection($conn);

if ($success) {
    sendSuccessResponse([
        'success' => true,
        'message' => 'Inventario actualizado'
    ]);
} else {
    sendErrorResponse('Error al actualizar inventario', 500);
}
?>