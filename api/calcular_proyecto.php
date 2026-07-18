<?php
// Calcula los materiales totales de un proyecto restando el inventario

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

// Usar la nueva función con inventario
$resultado = calcularMaterialesConInventario($conn, $proyectoId, $usuarioId);
closeConnection($conn);

if ($resultado === null) {
    sendErrorResponse('Proyecto no encontrado o no autorizado', 404);
}

// Asegurar que todos los materiales tengan nombre
foreach ($resultado['materiales'] as &$material) {
    if (!isset($material['nombre']) || empty($material['nombre'])) {
        $material['nombre'] = getItemNombre($conn, $material['id_item']);
    }
}

sendSuccessResponse([
    'success' => true,
    'resultado' => $resultado
]);
?>