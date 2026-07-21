<?php
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

if (empty($itemId)) {
    sendErrorResponse('ID de item requerido', 400);
}

$conn = getConnection();

$stmt = $conn->prepare("DELETE FROM inventario_usuario WHERE id_usuario = ? AND id_item = ?");
$stmt->bind_param("is", $usuarioId, $itemId);
$success = $stmt->execute();

closeConnection($conn);

if ($success) {
    sendSuccessResponse(['success' => true, 'message' => 'Material eliminado del inventario']);
} else {
    sendErrorResponse('Error al eliminar del inventario', 500);
}
?>