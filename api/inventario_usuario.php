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

// Obtener inventario del usuario
$stmt = $conn->prepare("SELECT id_item, cantidad FROM inventario_usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$result = $stmt->get_result();

$inventario = [];
while ($row = $result->fetch_assoc()) {
    $inventario[] = $row;
}

closeConnection($conn);

sendSuccessResponse([
    'success' => true,
    'inventario' => $inventario
]);
?>