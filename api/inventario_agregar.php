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
$cantidad = intval($_POST['cantidad'] ?? 0);

if (empty($itemId) || $cantidad <= 0) {
    sendErrorResponse('Datos inválidos', 400);
}

$conn = getConnection();

// Insertar o actualizar
$stmt = $conn->prepare("
    INSERT INTO inventario_usuario (id_usuario, id_item, cantidad) 
    VALUES (?, ?, ?) 
    ON DUPLICATE KEY UPDATE cantidad = cantidad + ?
");
$stmt->bind_param("isii", $usuarioId, $itemId, $cantidad, $cantidad);
$success = $stmt->execute();

closeConnection($conn);

if ($success) {
    sendSuccessResponse(['success' => true, 'message' => 'Material agregado al inventario']);
} else {
    sendErrorResponse('Error al agregar al inventario', 500);
}
?>