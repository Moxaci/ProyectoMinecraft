<?php
// includes/functions.php
// Funciones de utilidad para el proyecto

/**
 * Hashea una contraseña usando password_hash (bcrypt)
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifica si una contraseña coincide con su hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Valida que el nombre de usuario sea válido
 */
function validateUsername($username) {
    // Solo letras, números y guiones bajos, mínimo 4 caracteres
    return preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username);
}

/**
 * Valida que el correo sea válido
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Verifica si un usuario existe en la base de datos
 */
function userExists($conn, $username) {
    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE nombre = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Verifica si un correo ya está registrado
 */
function emailExists($conn, $email) {
    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Registra un nuevo usuario
 */
function registerUser($conn, $username, $email, $password) {
    $hashedPassword = hashPassword($password);
    
    $stmt = $conn->prepare("INSERT INTO usuario (nombre, correo, contraseña) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Usuario registrado exitosamente'];
    } else {
        return ['success' => false, 'error' => 'Error al registrar usuario: ' . $stmt->error];
    }
}

/**
 * Autentica a un usuario por nombre o correo
 */
function authenticateUser($conn, $usernameOrEmail, $password) {
    // Buscar por nombre o correo
    $stmt = $conn->prepare("SELECT id_usuario, nombre, correo, contraseña FROM usuario WHERE nombre = ? OR correo = ?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'error' => 'Usuario no encontrado'];
    }
    
    $user = $result->fetch_assoc();
    
    // Verificar contraseña
    if (verifyPassword($password, $user['contraseña'])) {
        // Iniciar sesión
        session_start();
        $_SESSION['usuario_id'] = $user['id_usuario'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_correo'] = $user['correo'];
        
        return [
            'success' => true,
            'message' => 'Login exitoso',
            'usuario' => $user['nombre']
        ];
    } else {
        return ['success' => false, 'error' => 'Contraseña incorrecta'];
    }
}

/**
 * Envía respuesta JSON al cliente
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Envía error en formato JSON
 */
function sendErrorResponse($error, $statusCode = 400) {
    sendJsonResponse(['error' => $error], $statusCode);
}

/**
 * Envía éxito en formato JSON
 */
function sendSuccessResponse($data, $statusCode = 200) {
    sendJsonResponse($data, $statusCode);
}

/**
 * Obtiene todos los proyectos de un usuario
 */
function getProyectosByUsuario($conn, $usuarioId) {
    $stmt = $conn->prepare("
        SELECT 
            p.id_proyecto,
            p.nombre,
            p.descripcion,
            p.fecha_creacion,
            COUNT(pd.id_item) as total_items,
            SUM(pd.cantidad) as total_materiales
        FROM proyecto p
        LEFT JOIN proyecto_detalle pd ON p.id_proyecto = pd.id_proyecto
        WHERE p.id_usuario = ?
        GROUP BY p.id_proyecto, p.nombre, p.descripcion, p.fecha_creacion
        ORDER BY p.fecha_creacion DESC
    ");
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $proyectos = [];
    while ($row = $result->fetch_assoc()) {
        $proyectos[] = $row;
    }
    return $proyectos;
}

/**
 * Obtiene los detalles de un proyecto específico
 */
function getProyectoDetalle($conn, $proyectoId, $usuarioId) {
    // Verificar que el proyecto pertenece al usuario
    $stmt = $conn->prepare("
        SELECT id_proyecto, nombre, descripcion, fecha_creacion
        FROM proyecto
        WHERE id_proyecto = ? AND id_usuario = ?
    ");
    $stmt->bind_param("ii", $proyectoId, $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $proyecto = $result->fetch_assoc();
    
    // Obtener los items del proyecto
    $stmt = $conn->prepare("
        SELECT 
            pd.id_item,
            i.nombre,
            i.imagen,
            pd.cantidad,
            i.stack_max
        FROM proyecto_detalle pd
        JOIN item i ON pd.id_item = i.id_item
        WHERE pd.id_proyecto = ?
        ORDER BY i.nombre
    ");
    $stmt->bind_param("i", $proyectoId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $proyecto['items'] = [];
    while ($row = $result->fetch_assoc()) {
        $proyecto['items'][] = $row;
    }
    
    return $proyecto;
}

/**
 * Crea un nuevo proyecto
 */
function crearProyecto($conn, $usuarioId, $nombre, $descripcion = '') {
    $stmt = $conn->prepare("
        INSERT INTO proyecto (id_usuario, nombre, descripcion) 
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iss", $usuarioId, $nombre, $descripcion);
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

/**
 * Agrega un item a un proyecto
 */
function agregarItemProyecto($conn, $proyectoId, $itemId, $cantidad) {
    $stmt = $conn->prepare("
        INSERT INTO proyecto_detalle (id_proyecto, id_item, cantidad) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE cantidad = cantidad + ?
    ");
    $stmt->bind_param("isii", $proyectoId, $itemId, $cantidad, $cantidad);
    return $stmt->execute();
}

/**
 * Elimina un proyecto (solo si pertenece al usuario)
 */
function eliminarProyecto($conn, $proyectoId, $usuarioId) {
    // Primero verificar que el proyecto pertenece al usuario
    $stmt = $conn->prepare("SELECT id_proyecto FROM proyecto WHERE id_proyecto = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $proyectoId, $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    // Eliminar detalles del proyecto
    $stmt = $conn->prepare("DELETE FROM proyecto_detalle WHERE id_proyecto = ?");
    $stmt->bind_param("i", $proyectoId);
    $stmt->execute();
    
    // Eliminar el proyecto
    $stmt = $conn->prepare("DELETE FROM proyecto WHERE id_proyecto = ?");
    $stmt->bind_param("i", $proyectoId);
    return $stmt->execute();
}

/**
 * Obtiene estadísticas para el dashboard
 */
function getEstadisticas($conn, $usuarioId) {
    $estadisticas = [
        'total_proyectos' => 0,
        'total_materiales' => 0,
        'total_hornos' => 0
    ];
    
    // Total de proyectos
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM proyecto WHERE id_usuario = ?");
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    $estadisticas['total_proyectos'] = $result->fetch_assoc()['total'];
    
    // Total de materiales calculados
    $stmt = $conn->prepare("
        SELECT SUM(pd.cantidad) as total 
        FROM proyecto_detalle pd
        JOIN proyecto p ON pd.id_proyecto = p.id_proyecto
        WHERE p.id_usuario = ?
    ");
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    $estadisticas['total_materiales'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Total de horas de horneado (estimado: 10 segundos por item * cantidad / 3600)
    $stmt = $conn->prepare("
        SELECT SUM(pd.cantidad * 10) as total_segundos
        FROM proyecto_detalle pd
        JOIN proyecto p ON pd.id_proyecto = p.id_proyecto
        WHERE p.id_usuario = ?
    ");
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalSegundos = $result->fetch_assoc()['total_segundos'] ?? 0;
    $estadisticas['total_hornos'] = round($totalSegundos / 3600, 1);
    
    return $estadisticas;
}
?>