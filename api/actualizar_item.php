<?php
// api/actualizar_item.php
// Actualiza la cantidad de un item en el proyecto

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
$itemId = $_POST['item_id'] ?? '';
$cantidad = intval($_POST['cantidad'] ?? 0);

if ($proyectoId <= 0 || empty($itemId)) {
    sendErrorResponse('Datos inválidos', 400);
}

$conn = getConnection();

// Verificar que el proyecto pertenece al usuario
$stmt = $conn->prepare("SELECT id_proyecto FROM proyecto WHERE id_proyecto = ? AND id_usuario = ?");
$stmt->bind_param("ii", $proyectoId, $usuarioId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    closeConnection($conn);
    sendErrorResponse('Proyecto no encontrado o no autorizado', 404);
}

// Actualizar item
$success = updateProyectoItem($conn, $proyectoId, $itemId, $cantidad);
closeConnection($conn);

if ($success) {
    sendSuccessResponse([
        'success' => true,
        'message' => 'Item actualizado exitosamente'
    ]);
} else {
    sendErrorResponse('Error al actualizar el item', 500);
}
?>