<?php
// api/crear_proyecto.php
// Crea un nuevo proyecto

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    sendErrorResponse('No autenticado', 401);
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Método no permitido', 405);
}

$usuarioId = $_SESSION['usuario_id'];
$nombre = $_POST['nombre'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';

// Validar nombre
if (empty($nombre) || strlen($nombre) < 3) {
    sendErrorResponse('El nombre debe tener al menos 3 caracteres', 400);
}

// Conectar a la base de datos
$conn = getConnection();

// Crear proyecto
$proyectoId = crearProyecto($conn, $usuarioId, $nombre, $descripcion);
closeConnection($conn);

if ($proyectoId) {
    sendSuccessResponse([
        'success' => true,
        'message' => 'Proyecto creado exitosamente',
        'id_proyecto' => $proyectoId
    ], 201);
} else {
    sendErrorResponse('Error al crear el proyecto', 500);
}
?>