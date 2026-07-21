<?php
// api/quitar_item.php
// Elimina un item del proyecto

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

// Eliminar item
$success = removeProyectoItem($conn, $proyectoId, $itemId);
closeConnection($conn);

if ($success) {
    sendSuccessResponse([
        'success' => true,
        'message' => 'Item eliminado exitosamente'
    ]);
} else {
    sendErrorResponse('Error al eliminar el item', 500);
}
?>