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
    $stmt = $conn->prepare("SELECT id_usuario, nombre, correo, contraseña FROM usuario WHERE nombre = ? OR correo = ?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['success' => false, 'error' => 'Usuario no encontrado'];
    }

    $user = $result->fetch_assoc();

    if (verifyPassword($password, $user['contraseña'])) {
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
    $stmt = $conn->prepare("SELECT id_proyecto FROM proyecto WHERE id_proyecto = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $proyectoId, $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return false;
    }

    $stmt = $conn->prepare("DELETE FROM proyecto_detalle WHERE id_proyecto = ?");
    $stmt->bind_param("i", $proyectoId);
    $stmt->execute();

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

    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM proyecto WHERE id_usuario = ?");
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    $estadisticas['total_proyectos'] = $result->fetch_assoc()['total'];

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
    $stmt = $conn->prepare("SELECT id_proyecto FROM proyecto WHERE id_proyecto = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $proyectoId, $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return null;
    }

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
 * Obtiene el nombre de un item por su ID
 */
function getItemNombre($conn, $itemId) {
    if (!$conn) {
        return $itemId;
    }

    $stmt = $conn->prepare("SELECT nombre FROM item WHERE id_item = ?");
    if (!$stmt) {
        return $itemId;
    }

    $stmt->bind_param("s", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['nombre'];
    }
    return $itemId;
}

/**
 * Obtiene el tiempo de fundición para un item (SOLO si el item de entrada es base)
 */
function getTiempoFundicion($conn, $itemId) {
    $stmt = $conn->prepare("
    SELECT f.id_item_entrada, f.tiempo_segundos, i.es_base
    FROM fundicion f
    JOIN item i ON f.id_item_entrada = i.id_item
    WHERE f.id_item_salida = ? AND i.es_base = 1
    LIMIT 1
    ");
    $stmt->bind_param("s", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return intval($row['tiempo_segundos']);
    }
    return 0;
}

/**
 * Descompone recursivamente un item en sus materiales base
 */
function descomponerItem($conn, $itemId, $cantidad, $profundidad = 0) {
    if (!$conn) return [];
    if ($profundidad > 10) return [];

    $cantidad = floatval($cantidad);
    if ($cantidad <= 0) return [];

    // Verificar si es base
    $stmt = $conn->prepare("SELECT es_base FROM item WHERE id_item = ?");
    if (!$stmt) return [];
    $stmt->bind_param("s", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if ($item && intval($item['es_base']) === 1) {
        $nombre = getItemNombre($conn, $itemId);
        return [
            $itemId => [
                'id_item' => $itemId,
                'nombre' => $nombre,
                'cantidad' => $cantidad,
                'tiempo' => 0
            ]
        ];
    }

    // Verificar fundición
    $stmt = $conn->prepare("
    SELECT id_item_entrada, tiempo_segundos
    FROM fundicion
    WHERE id_item_salida = ?
    LIMIT 1
    ");
    if (!$stmt) return [];
    $stmt->bind_param("s", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $itemEntrada = $row['id_item_entrada'];
        $tiempoPorUnidad = intval($row['tiempo_segundos']);
        $subItems = descomponerItem($conn, $itemEntrada, $cantidad, $profundidad + 1);

        foreach ($subItems as $id => &$data) {
            $data['tiempo'] += $tiempoPorUnidad * $cantidad;
        }
        return $subItems;
    }

    // Verificar receta
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
        $nombre = getItemNombre($conn, $itemId);
        return [
            $itemId => [
                'id_item' => $itemId,
                'nombre' => $nombre,
                'cantidad' => $cantidad,
                'tiempo' => 0
            ]
        ];
    }

    // Procesar receta
    $recetas = [];
    while ($row = $result->fetch_assoc()) {
        $recetaId = $row['id_receta'];
        if (!isset($recetas[$recetaId])) {
            $recetas[$recetaId] = [
                'resultado' => intval($row['cantidad_resultado']),
                'ingredientes' => []
            ];
        }
        $recetas[$recetaId]['ingredientes'][] = [
            'id_item' => $row['id_item_ingred'],
            'cantidad' => intval($row['cantidad'])
        ];
    }

    $receta = reset($recetas);
    $ejecuciones = ceil($cantidad / $receta['resultado']);

    $resultado = [];

    foreach ($receta['ingredientes'] as $ingrediente) {
        $cantidadIngrediente = $ingrediente['cantidad'] * $ejecuciones;
        $subItems = descomponerItem($conn, $ingrediente['id_item'], $cantidadIngrediente, $profundidad + 1);

        foreach ($subItems as $id => $data) {
            if (isset($resultado[$id])) {
                $resultado[$id]['cantidad'] += $data['cantidad'];
                $resultado[$id]['tiempo'] += $data['tiempo'] ?? 0;
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
        $cantidad = floatval($data['cantidad']);
        $stackMax = isset($data['stack_max']) ? intval($data['stack_max']) : 64;
        $nombre = isset($data['nombre']) ? $data['nombre'] : $id;

        $stacks[$id] = [
            'id_item' => $id,
            'nombre' => $nombre,
            'cantidad' => $cantidad,
            'stacks' => floor($cantidad / $stackMax),
            'resto' => $cantidad % $stackMax,
            'stack_max' => $stackMax
        ];
    }
    return $stacks;
}

/**
 * Calcula los materiales totales de un proyecto (recursivo)
 */
function calcularMateriales($conn, $proyectoId, $usuarioId) {
    try {
        $stmt = $conn->prepare("SELECT id_proyecto FROM proyecto WHERE id_proyecto = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $proyectoId, $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null;
        }

        $items = getProyectoItems($conn, $proyectoId, $usuarioId);
        if (!$items) return null;

        $acumulador = [];

        foreach ($items as $item) {
            $cantidad = intval($item['cantidad']);
            $materiales = descomponerItem($conn, $item['id_item'], $cantidad);

            foreach ($materiales as $id => $data) {
                if (!isset($acumulador[$id])) {
                    $acumulador[$id] = [
                        'id_item' => $id,
                        'nombre' => $data['nombre'] ?? getItemNombre($conn, $id),
                        'cantidad' => 0,
                        'tiempo' => 0,
                        'stack_max' => $data['stack_max'] ?? 64
                    ];
                }
                $acumulador[$id]['cantidad'] += $data['cantidad'];
                $acumulador[$id]['tiempo'] += $data['tiempo'] ?? 0;
            }
        }

        foreach ($acumulador as $id => &$material) {
            if (empty($material['nombre'])) {
                $material['nombre'] = getItemNombre($conn, $id);
            }
            if (!isset($material['stack_max']) || $material['stack_max'] <= 0) {
                $material['stack_max'] = 64;
            }
        }
        unset($material);

        $tiempoTotal = 0;
        foreach ($acumulador as $material) {
            $tiempoTotal += $material['tiempo'];
        }

        $materialesLista = array_values($acumulador);

        return [
            'materiales' => $materialesLista,
            'tiempo_total' => $tiempoTotal,
            'stacks' => calcularStacks($acumulador)
        ];

    } catch (Exception $e) {
        error_log("Error en calcularMateriales: " . $e->getMessage());
        return null;
    }
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
        $stmt = $conn->prepare("DELETE FROM inventario_usuario WHERE id_usuario = ? AND id_item = ?");
        $stmt->bind_param("is", $usuarioId, $itemId);
        return $stmt->execute();
    }

    $stmt = $conn->prepare("SELECT cantidad FROM inventario_usuario WHERE id_usuario = ? AND id_item = ?");
    $stmt->bind_param("is", $usuarioId, $itemId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE inventario_usuario SET cantidad = ? WHERE id_usuario = ? AND id_item = ?");
        $stmt->bind_param("iis", $cantidad, $usuarioId, $itemId);
    } else {
        $stmt = $conn->prepare("INSERT INTO inventario_usuario (id_usuario, id_item, cantidad) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $usuarioId, $itemId, $cantidad);
    }

    return $stmt->execute();
}

/**
 * Calcula los materiales necesarios restando el inventario
 * CON GESTIÓN DE RESIDUOS (SOBRANTES DE RECETAS)
 */
function calcularMaterialesConInventario($conn, $proyectoId, $usuarioId) {
    try {
        // 1. Obtener los items principales del proyecto
        $stmt = $conn->prepare("
        SELECT pd.id_item, pd.cantidad
        FROM proyecto_detalle pd
        WHERE pd.id_proyecto = ?
        ");
        $stmt->bind_param("i", $proyectoId);
        $stmt->execute();
        $result = $stmt->get_result();

        $itemsRequeridos = [];
        while ($row = $result->fetch_assoc()) {
            $itemsRequeridos[$row['id_item']] = floatval($row['cantidad']);
        }

        if (empty($itemsRequeridos)) {
            return null;
        }

        // 2. Obtener y mapear el inventario del usuario
        $inventario = getInventarioUsuario($conn, $usuarioId);
        $inventarioMap = [];
        $inventarioOriginal = [];
        foreach ($inventario as $item) {
            $inventarioMap[$item['id_item']] = floatval($item['cantidad']);
            $inventarioOriginal[$item['id_item']] = floatval($item['cantidad']);
        }

        $acumulador = [];
        $tiempoTotal = 0;
        $residuos = []; // <--- REGISTRO DE SOBRANTES

        // 3. Procesar la cola de requerimientos nivel por nivel (Iterativo)
        $procesando = true;
        $profundidad = 0;

        while ($procesando && $profundidad < 15) {
            $profundidad++;
            $nuevosRequeridos = [];
            $procesando = false;

            foreach ($itemsRequeridos as $id => $cantidadReq) {
                if ($cantidadReq <= 0) continue;

                // A. Intentar descontar del inventario ANTES de descomponer
                if (isset($inventarioMap[$id]) && $inventarioMap[$id] > 0) {
                    $descontar = min($cantidadReq, $inventarioMap[$id]);
                    $cantidadReq -= $descontar;
                    $inventarioMap[$id] -= $descontar;
                }

                if ($cantidadReq <= 0) continue;

                // B. Si aún falta cantidad, evaluar cómo craftearlo/fundirlo
                $stmt = $conn->prepare("SELECT es_base FROM item WHERE id_item = ?");
                if ($stmt) {
                    $stmt->bind_param("s", $id);
                    $stmt->execute();
                    $resBase = $stmt->get_result()->fetch_assoc();
                    $esBase = $resBase ? intval($resBase['es_base']) : 1;
                } else {
                    $esBase = 1;
                }

                if ($esBase === 1) {
                    // Es un material base
                    if (!isset($acumulador[$id])) {
                        $acumulador[$id] = ['id_item' => $id, 'cantidad' => 0, 'tiempo' => 0];
                    }
                    $acumulador[$id]['cantidad'] += $cantidadReq;
                } else {
                    // Intentar descomponer por fundición
                    $stmt = $conn->prepare("SELECT id_item_entrada, tiempo_segundos FROM fundicion WHERE id_item_salida = ? LIMIT 1");
                    $stmt->bind_param("s", $id);
                    $stmt->execute();
                    $resFundicion = $stmt->get_result()->fetch_assoc();

                    if ($resFundicion) {
                        $itemEntrada = $resFundicion['id_item_entrada'];
                        $tiempo = intval($resFundicion['tiempo_segundos']) * $cantidadReq;

                        if (!isset($nuevosRequeridos[$itemEntrada])) $nuevosRequeridos[$itemEntrada] = 0;
                        $nuevosRequeridos[$itemEntrada] += $cantidadReq;
                        $tiempoTotal += $tiempo;
                        $procesando = true;
                    } else {
                        // Intentar descomponer por mesa de crafteo (receta)
                        $stmt = $conn->prepare("
                        SELECT r.id_receta, r.cantidad_resultado, ir.id_item_ingred, ir.cantidad
                        FROM receta r
                        JOIN ingrediente_receta ir ON r.id_receta = ir.id_receta
                        WHERE r.id_item_resultado = ?
                        ");
                        $stmt->bind_param("s", $id);
                        $stmt->execute();
                        $resReceta = $stmt->get_result();

                        if ($resReceta->num_rows > 0) {
                            $recetas = [];
                            while ($row = $resReceta->fetch_assoc()) {
                                $recetaId = $row['id_receta'];
                                if (!isset($recetas[$recetaId])) {
                                    $recetas[$recetaId] = ['resultado' => floatval($row['cantidad_resultado']), 'ingredientes' => []];
                                }
                                $recetas[$recetaId]['ingredientes'][] = ['id_item' => $row['id_item_ingred'], 'cantidad' => floatval($row['cantidad'])];
                            }
                            $receta = reset($recetas);

                            // Calcular cuántas veces hay que hacer la receta
                            $ejecuciones = ceil($cantidadReq / $receta['resultado']);

                            // --- REGISTRAR SOBRANTE ---
                            $sobrante = ($ejecuciones * $receta['resultado']) - $cantidadReq;
                            if ($sobrante > 0) {
                                if (!isset($residuos[$id])) $residuos[$id] = 0;
                                $residuos[$id] += $sobrante;
                            }
                            // -------------------------

                            foreach ($receta['ingredientes'] as $ingrediente) {
                                $idIng = $ingrediente['id_item'];
                                $cantIng = $ingrediente['cantidad'] * $ejecuciones;

                                if (!isset($nuevosRequeridos[$idIng])) $nuevosRequeridos[$idIng] = 0;
                                $nuevosRequeridos[$idIng] += $cantIng;
                            }
                            $procesando = true;
                        } else {
                            // Fallback: tratar como base
                            if (!isset($acumulador[$id])) {
                                $acumulador[$id] = ['id_item' => $id, 'cantidad' => 0, 'tiempo' => 0];
                            }
                            $acumulador[$id]['cantidad'] += $cantidadReq;
                        }
                    }
                }
            }
            $itemsRequeridos = $nuevosRequeridos;
        }

        // 4. Completar datos faltantes
        foreach ($acumulador as $id => &$material) {
            $material['nombre'] = getItemNombre($conn, $id);
            $stmt = $conn->prepare("SELECT stack_max FROM item WHERE id_item = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $resStack = $stmt->get_result()->fetch_assoc();
            $material['stack_max'] = $resStack ? intval($resStack['stack_max']) : 64;
            if ($material['stack_max'] <= 0) $material['stack_max'] = 64;
        }
        unset($material);

        // 5. Formatear salida de Stacks
        $stacksFinales = [];
        foreach ($acumulador as $id => $m) {
            $stackMax = intval($m['stack_max']);
            $cantidad = floatval($m['cantidad']);
            $stacksFinales[$id] = [
                'id_item' => $id,
                'nombre' => $m['nombre'],
                'cantidad' => $cantidad,
                'stacks' => intval(floor($cantidad / $stackMax)),
                'resto' => $cantidad % $stackMax,
                'stack_max' => $stackMax
            ];
        }

        $inventarioUsado = 0;
        foreach ($inventarioOriginal as $id => $cantOrig) {
            if ($inventarioMap[$id] < $cantOrig) $inventarioUsado++;
        }

        // 6. Formatear residuos
        $residuosFormateados = [];
        foreach ($residuos as $idRes => $cantRes) {
            $residuosFormateados[] = [
                'id_item' => $idRes,
                'nombre' => getItemNombre($conn, $idRes),
                'cantidad' => $cantRes
            ];
        }

        return [
            'materiales' => array_values($acumulador),
            'stacks' => $stacksFinales,
            'tiempo_total' => $tiempoTotal,
            'inventario_usado' => $inventarioUsado,
            'residuos' => $residuosFormateados // <--- RESIDUOS EN EL JSON
        ];

    } catch (Exception $e) {
        error_log("Error en calcularMaterialesConInventario: " . $e->getMessage());
        return null;
    }
}
?>
