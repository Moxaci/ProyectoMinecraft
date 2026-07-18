<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    sendErrorResponse('No autenticado', 401);
}

$usuarioId = $_SESSION['usuario_id'];
$conn = getConnection();

$stmt = $conn->prepare("DELETE FROM inventario_usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $usuarioId);
$success = $stmt->execute();

closeConnection($conn);

if ($success) {
    sendSuccessResponse(['success' => true, 'message' => 'Inventario limpiado']);
} else {
    sendErrorResponse('Error al limpiar el inventario', 500);
}
?>