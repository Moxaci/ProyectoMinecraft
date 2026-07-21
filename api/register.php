<?php
// api/register.php
// Endpoint para registrar nuevos usuarios

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
$nombre = $_POST['nombre'] ?? '';
$correo = $_POST['correo'] ?? '';
$password = $_POST['password'] ?? '';

// Validar datos
if (empty($nombre) || empty($correo) || empty($password)) {
    sendErrorResponse('Todos los campos son requeridos', 400);
}

// Validar nombre de usuario
if (!validateUsername($nombre)) {
    sendErrorResponse('El nombre debe tener entre 4 y 20 caracteres (solo letras, números y guiones bajos)', 400);
}

// Validar correo
if (!validateEmail($correo)) {
    sendErrorResponse('Correo electrónico inválido', 400);
}

// Validar contraseña (mínimo 6 caracteres)
if (strlen($password) < 6) {
    sendErrorResponse('La contraseña debe tener al menos 6 caracteres', 400);
}

// Conectar a la base de datos
$conn = getConnection();

// Verificar si el nombre de usuario ya existe
if (userExists($conn, $nombre)) {
    closeConnection($conn);
    sendErrorResponse('El nombre de usuario ya está en uso', 409);
}

// Verificar si el correo ya existe
if (emailExists($conn, $correo)) {
    closeConnection($conn);
    sendErrorResponse('El correo electrónico ya está registrado', 409);
}

// Registrar usuario
$result = registerUser($conn, $nombre, $correo, $password);
closeConnection($conn);

if ($result['success']) {
    sendSuccessResponse(['success' => true, 'message' => 'Usuario registrado exitosamente'], 201);
} else {
    sendErrorResponse($result['error'], 500);
}
?>