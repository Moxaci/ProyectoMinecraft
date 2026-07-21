<?php
// api/calcular_proyecto.php
// Calcula los materiales totales de un proyecto restando el inventario

// Activar errores para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    sendErrorResponse('No autenticado', 401);
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$proyectoId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($proyectoId <= 0) {
    sendErrorResponse('ID de proyecto requerido', 400);
    exit;
}

try {
    $conn = getConnection();
    
    // Verificar que el proyecto existe y pertenece al usuario
    $stmt = $conn->prepare("SELECT id_proyecto FROM proyecto WHERE id_proyecto = ? AND id_usuario = ?");
    if (!$stmt) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("ii", $proyectoId, $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        closeConnection($conn);
        sendErrorResponse('Proyecto no encontrado o no autorizado', 404);
        exit;
    }
    
    // 🔥 IMPORTANTE: Usar la función calcularMaterialesConInventario
    // NO cerrar la conexión antes de usarla en funciones internas
    $resultado = calcularMaterialesConInventario($conn, $proyectoId, $usuarioId);
    
    // Ahora sí cerrar la conexión
    closeConnection($conn);
    
    if ($resultado === null) {
        sendErrorResponse('Error al calcular los materiales', 500);
        exit;
    }
    
    // Asegurar que todos los materiales tengan nombre
    foreach ($resultado['materiales'] as &$material) {
        if (!isset($material['nombre']) || empty($material['nombre'])) {
            $material['nombre'] = $material['id_item']; // Fallback
        }
    }
    
    sendSuccessResponse([
        'success' => true,
        'resultado' => $resultado
    ]);
    
} catch (Exception $e) {
    // Log del error
    error_log("Error en calcular_proyecto.php: " . $e->getMessage());
    sendErrorResponse('Error interno del servidor: ' . $e->getMessage(), 500);
}
?>