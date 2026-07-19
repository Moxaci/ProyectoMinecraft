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

/**
 * Obtiene todos los items disponibles (catálogo)
 */
function getAllItems($conn) {
    $result = $conn->query("
        SELECT id_item, nombre, descripcion, imagen, es_base, stack_max 
        FROM item 
        ORDER BY nombre
    ");
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}

/**
 * Obtiene los items de un proyecto
 */
function getProyectoItems($conn, $proyectoId, $usuarioId) {
    // Verificar que el proyecto pertenece al usuario
    $stmt = $conn->prepare("SELECT id_proyecto FROM proyecto WHERE id_proyecto = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $proyectoId, $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    // Obtener items del proyecto
    $stmt = $conn->prepare("
        SELECT 
            pd.id_item,
            i.nombre,
            i.imagen,
            i.stack_max,
            i.es_base,
            pd.cantidad
        FROM proyecto_detalle pd
        JOIN item i ON pd.id_item = i.id_item
        WHERE pd.id_proyecto = ?
        ORDER BY i.nombre
    ");
    if (!$stmt) return null;
    
    $stmt->bind_param("i", $proyectoId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id_item' => $row['id_item'],
            'nombre' => $row['nombre'],
            'imagen' => $row['imagen'],
            'stack_max' => intval($row['stack_max']),
            'es_base' => intval($row['es_base']),
            'cantidad' => intval($row['cantidad'])
        ];
    }
    return $items;
}

/**
 * Agrega o actualiza un item en el proyecto
 */
function upsertProyectoItem($conn, $proyectoId, $itemId, $cantidad) {
    $stmt = $conn->prepare("
        INSERT INTO proyecto_detalle (id_proyecto, id_item, cantidad) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE cantidad = cantidad + ?
    ");
    $stmt->bind_param("isii", $proyectoId, $itemId, $cantidad, $cantidad);
    return $stmt->execute();
}

/**
 * Actualiza la cantidad de un item en el proyecto
 */
function updateProyectoItem($conn, $proyectoId, $itemId, $cantidad) {
    if ($cantidad <= 0) {
        // Si la cantidad es 0 o negativa, eliminar el item
        $stmt = $conn->prepare("DELETE FROM proyecto_detalle WHERE id_proyecto = ? AND id_item = ?");
        $stmt->bind_param("is", $proyectoId, $itemId);
        return $stmt->execute();
    }
    
    $stmt = $conn->prepare("
        UPDATE proyecto_detalle 
        SET cantidad = ? 
        WHERE id_proyecto = ? AND id_item = ?
    ");
    $stmt->bind_param("iis", $cantidad, $proyectoId, $itemId);
    return $stmt->execute();
}

/**
 * Elimina un item del proyecto
 */
function removeProyectoItem($conn, $proyectoId, $itemId) {
    $stmt = $conn->prepare("DELETE FROM proyecto_detalle WHERE id_proyecto = ? AND id_item = ?");
    $stmt->bind_param("is", $proyectoId, $itemId);
    return $stmt->execute();
}

/**
 * Calcula los materiales totales de un proyecto (recursivo)
 */
function calcularMateriales($conn, $proyectoId, $usuarioId) {
    // Verificar que el proyecto pertenece al usuario
    $stmt = $conn->prepare("SELECT id_proyecto FROM proyecto WHERE id_proyecto = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $proyectoId, $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    // Obtener los items del proyecto
    $items = getProyectoItems($conn, $proyectoId, $usuarioId);
    if (!$items) return null;
    
    $resultado = [];
    $tiempoTotal = 0;
    
    foreach ($items as $item) {
        $materiales = descomponerItem($conn, $item['id_item'], $item['cantidad']);
        
        // Sumar materiales
        foreach ($materiales as $id => $data) {
            if (isset($resultado[$id])) {
                $resultado[$id]['cantidad'] += $data['cantidad'];
                $resultado[$id]['tiempo'] += $data['tiempo'] ?? 0;
            } else {
                $resultado[$id] = $data;
            }
        }
    }
    
    // Calcular tiempo total
    $tiempoTotal = array_sum(array_column($resultado, 'tiempo'));
    
    return [
        'materiales' => array_values($resultado),
        'tiempo_total' => $tiempoTotal,
        'stacks' => calcularStacks($resultado)
    ];
}

/**
 * Descompone recursivamente un item en sus materiales base
 */
function descomponerItem($conn, $itemId, $cantidad, $profundidad = 0) {
    // Límite de recursión para evitar bucles infinitos
    if ($profundidad > 10) return [];
    
    $resultado = [];
    $cantidad = intval($cantidad);
    
    // Verificar si es un item base
    $stmt = $conn->prepare("SELECT es_base FROM item WHERE id_item = ?");
    if (!$stmt) return [];
    
    $stmt->bind_param("s", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    
    if ($item && intval($item['es_base']) === 1) {
        // Es un item base, agregarlo directamente con su nombre
        $nombre = getItemNombre($conn, $itemId);
        $resultado[$itemId] = [
            'id_item' => $itemId,
            'nombre' => $nombre,
            'cantidad' => $cantidad,
            'tiempo' => 0
        ];
        return $resultado;
    }
    
    // Buscar recetas para este item
    $stmt = $conn->prepare("
        SELECT r.id_receta, r.cantidad_resultado, ir.id_item_ingred, ir.cantidad
        FROM receta r
        JOIN ingrediente_receta ir ON r.id_receta = ir.id_receta
        WHERE r.id_item_resultado = ?
    ");
    if (!$stmt) return [];
    
    $stmt->bind_param("s", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // No hay receta, tratarlo como item base
        $nombre = getItemNombre($conn, $itemId);
        $resultado[$itemId] = [
            'id_item' => $itemId,
            'nombre' => $nombre,
            'cantidad' => $cantidad,
            'tiempo' => 0
        ];
        return $resultado;
    }
    
    // Procesar receta
    $recetas = [];
    while ($row = $result->fetch_assoc()) {
        $recetas[$row['id_receta']]['resultado'] = intval($row['cantidad_resultado']);
        $recetas[$row['id_receta']]['ingredientes'][] = [
            'id_item' => $row['id_item_ingred'],
            'cantidad' => intval($row['cantidad'])
        ];
    }
    
    // Tomar la primera receta (simplificado)
    $receta = reset($recetas);
    $factor = $cantidad / intval($receta['resultado']);
    
    foreach ($receta['ingredientes'] as $ingrediente) {
        $cantidadIngrediente = intval($ingrediente['cantidad']) * $factor;
        $subItems = descomponerItem($conn, $ingrediente['id_item'], $cantidadIngrediente, $profundidad + 1);
        
        foreach ($subItems as $id => $data) {
            if (isset($resultado[$id])) {
                $resultado[$id]['cantidad'] += $data['cantidad'];
            } else {
                $resultado[$id] = $data;
            }
        }
    }
    
    return $resultado;
}

/**
 * Calcula stacks a partir de los materiales
 */
function calcularStacks($materiales) {
    $stacks = [];
    foreach ($materiales as $id => $data) {
        $cantidad = $data['cantidad'];
        $stackMax = $data['stack_max'] ?? 64;
        $stacks[$id] = [
            'id_item' => $id,
            'cantidad' => $cantidad,
            'stacks' => floor($cantidad / $stackMax),
            'resto' => $cantidad % $stackMax,
            'stack_max' => $stackMax
        ];
    }
    return $stacks;
}

/**
 * Obtiene el nombre de un item por su ID
 */
function getItemNombre($conn, $itemId) {
    $stmt = $conn->prepare("SELECT nombre FROM item WHERE id_item = ?");
    $stmt->bind_param("s", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['nombre'];
    }
    return $itemId;
}

/**
 * Actualiza el nombre de un proyecto
 */
function updateProyectoNombre($conn, $proyectoId, $usuarioId, $nombre) {
    $stmt = $conn->prepare("UPDATE proyecto SET nombre = ? WHERE id_proyecto = ? AND id_usuario = ?");
    $stmt->bind_param("sii", $nombre, $proyectoId, $usuarioId);
    return $stmt->execute();
}

/**
 * Obtiene el inventario de un usuario
 */
function getInventarioUsuario($conn, $usuarioId) {
    $stmt = $conn->prepare("
        SELECT 
            iu.id_item,
            i.nombre,
            i.imagen,
            iu.cantidad,
            i.stack_max
        FROM inventario_usuario iu
        JOIN item i ON iu.id_item = i.id_item
        WHERE iu.id_usuario = ?
        ORDER BY i.nombre
    ");
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $inventario = [];
    while ($row = $result->fetch_assoc()) {
        $inventario[] = $row;
    }
    return $inventario;
}

/**
 * Actualiza el inventario de un usuario (agrega o actualiza cantidad)
 */
function updateInventarioUsuario($conn, $usuarioId, $itemId, $cantidad) {
    if ($cantidad <= 0) {
        // Si cantidad es 0 o negativa, eliminar del inventario
        $stmt = $conn->prepare("DELETE FROM inventario_usuario WHERE id_usuario = ? AND id_item = ?");
        $stmt->bind_param("is", $usuarioId, $itemId);
        return $stmt->execute();
    }
    
    // Verificar si ya existe
    $stmt = $conn->prepare("SELECT cantidad FROM inventario_usuario WHERE id_usuario = ? AND id_item = ?");
    $stmt->bind_param("is", $usuarioId, $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Actualizar
        $stmt = $conn->prepare("UPDATE inventario_usuario SET cantidad = ? WHERE id_usuario = ? AND id_item = ?");
        $stmt->bind_param("iis", $cantidad, $usuarioId, $itemId);
    } else {
        // Insertar
        $stmt = $conn->prepare("INSERT INTO inventario_usuario (id_usuario, id_item, cantidad) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $usuarioId, $itemId, $cantidad);
    }
    
    return $stmt->execute();
}

/**
 * Calcula los materiales necesarios restando el inventario
 */
function calcularMaterialesConInventario($conn, $proyectoId, $usuarioId) {
    try {
        // Primero calcular los materiales totales del proyecto
        $resultado = calcularMateriales($conn, $proyectoId, $usuarioId);
        if (!$resultado) {
            return null;
        }
        
        // Obtener inventario del usuario
        $inventario = getInventarioUsuario($conn, $usuarioId);
        $inventarioMap = [];
        foreach ($inventario as $item) {
            $inventarioMap[$item['id_item']] = intval($item['cantidad']);
        }
        
        // Restar inventario a los materiales calculados
        $materialesFinales = [];
        foreach ($resultado['materiales'] as $material) {
            $id = $material['id_item'];
            $cantidad = intval($material['cantidad']);
            $disponible = isset($inventarioMap[$id]) ? intval($inventarioMap[$id]) : 0;
            
            // OBTENER EL NOMBRE DE LA BASE DE DATOS
            $nombre = getItemNombre($conn, $id);
            
            $necesario = max(0, $cantidad - $disponible);
            $materialesFinales[] = [
                'id_item' => $id,
                'nombre' => $nombre, // Usar el nombre de la BD
                'cantidad' => $necesario,
                'cantidad_original' => $cantidad,
                'disponible' => $disponible,
                'stack_max' => isset($material['stack_max']) ? intval($material['stack_max']) : 64,
                'tiempo' => isset($material['tiempo']) ? intval($material['tiempo']) : 0
            ];
        }
        
        // Recalcular stacks
        $stacksFinales = [];
        foreach ($materialesFinales as $m) {
            $stackMax = intval($m['stack_max']);
            $cantidad = intval($m['cantidad']);
            $stacksFinales[$m['id_item']] = [
                'id_item' => $m['id_item'],
                'nombre' => $m['nombre'], // Incluir nombre también aquí
                'cantidad' => $cantidad,
                'stacks' => intval(floor($cantidad / $stackMax)),
                'resto' => $cantidad % $stackMax,
                'stack_max' => $stackMax
            ];
        }
        
        return [
            'materiales' => $materialesFinales,
            'stacks' => $stacksFinales,
            'tiempo_total' => isset($resultado['tiempo_total']) ? intval($resultado['tiempo_total']) : 0,
            'inventario_usado' => count(array_filter($inventarioMap, function($v) { return $v > 0; }))
        ];
        
    } catch (Exception $e) {
        error_log("Error en calcularMaterialesConInventario: " . $e->getMessage());
        return null;
    }
}
?>