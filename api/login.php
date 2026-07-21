<?php
// api/login.php
// Endpoint para autenticar usuarios

// Iniciar sesión para manejar la sesión del usuario
session_start();

// Incluir archivos de configuración
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Configurar respuesta como JSON
header('Content-Type: application/json');

// Verificar que sea método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Método no permitido', 405);
}

// Obtener datos del formulario
$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

// Validar datos
if (empty($usuario) || empty($password)) {
    sendErrorResponse('Usuario y contraseña son requeridos', 400);
}

// Conectar a la base de datos
$conn = getConnection();

// Autenticar usuario
$result = authenticateUser($conn, $usuario, $password);
closeConnection($conn);

if ($result['success']) {
    sendSuccessResponse([
        'success' => true,
        'message' => 'Login exitoso',
        'usuario' => $result['usuario']
    ], 200);
} else {
    sendErrorResponse($result['error'], 401);
}
?>