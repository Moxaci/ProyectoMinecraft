<?php
// api/calcular_proyecto.php
// Calcula los materiales totales de un proyecto

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    sendErrorResponse('No autenticado', 401);
}

$usuarioId = $_SESSION['usuario_id'];
$proyectoId = $_GET['id'] ?? 0;

if ($proyectoId <= 0) {
    sendErrorResponse('ID de proyecto requerido', 400);
}

$conn = getConnection();

// Calcular materiales
$resultado = calcularMateriales($conn, $proyectoId, $usuarioId);
closeConnection($conn);

if ($resultado === null) {
    sendErrorResponse('Proyecto no encontrado o no autorizado', 404);
}

// Obtener nombres de los items
foreach ($resultado['materiales'] as &$material) {
    $material['nombre'] = getItemNombre($conn, $material['id_item']);
}

sendSuccessResponse([
    'success' => true,
    'resultado' => $resultado
]);
?>