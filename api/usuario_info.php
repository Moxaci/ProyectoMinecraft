<?php
// api/usuario_info.php
// Obtiene la información del usuario actual

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    sendErrorResponse('No autenticado', 401);
}

$usuarioId = $_SESSION['usuario_id'];

// Conectar a la base de datos
$conn = getConnection();

$stmt = $conn->prepare("SELECT id_usuario, nombre, correo, fecha_reg FROM usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    closeConnection($conn);
    sendErrorResponse('Usuario no encontrado', 404);
}

$usuario = $result->fetch_assoc();
closeConnection($conn);

sendSuccessResponse([
    'success' => true,
    'usuario' => [
        'id' => $usuario['id_usuario'],
        'nombre' => $usuario['nombre'],
        'correo' => $usuario['correo'],
        'fecha_reg' => $usuario['fecha_reg']
    ]
]);
?>