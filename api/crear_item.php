<?php
// api/crear_item.php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

// Validar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Solo aceptar POST con JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Obtener y decodificar JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Validar campos obligatorios
$id_item = trim($input['id_item'] ?? '');
$nombre = trim($input['nombre'] ?? '');
$imagen = trim($input['imagen'] ?? '');
$stack_max = (int)($input['stack_max'] ?? 64);
$es_base = (int)($input['es_base'] ?? 1);
$descripcion = trim($input['descripcion'] ?? '');
$tipo_herramienta = !empty($input['tipo_herramienta']) ? $input['tipo_herramienta'] : null;
$nivel_herramienta = !empty($input['nivel_herramienta']) ? $input['nivel_herramienta'] : null;

if (empty($id_item) || empty($nombre) || empty($imagen)) {
    echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios']);
    exit;
}

// Validar URL de imagen (seguridad)
if (strpos($imagen, 'minecraft.wiki') === false) {
    echo json_encode(['success' => false, 'error' => 'La URL de la imagen debe provenir de minecraft.wiki']);
    exit;
}

$conn = getConnection();

// Verificar si el item ya existe
$checkStmt = $conn->prepare("SELECT id_item FROM item WHERE id_item = ?");
$checkStmt->bind_param("s", $id_item);
$checkStmt->execute();
if ($checkStmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Este item ya existe en la base de datos']);
    closeConnection($conn);
    exit;
}
$checkStmt->close();

// Insertar el item
$stmt = $conn->prepare("INSERT INTO item (id_item, nombre, descripcion, imagen, es_base, stack_max, tipo_herramienta, nivel_herramienta) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssiiss", $id_item, $nombre, $descripcion, $imagen, $es_base, $stack_max, $tipo_herramienta, $nivel_herramienta);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Error al insertar item: ' . $stmt->error]);
    $stmt->close();
    closeConnection($conn);
    exit;
}
$stmt->close();

// Si NO es base, procesar la receta
if ($es_base === 0) {
    $receta = $input['receta'] ?? null;
    if (!$receta) {
        echo json_encode(['success' => false, 'error' => 'Faltan datos de la receta']);
        closeConnection($conn);
        exit;
    }

    $cantidad_resultado = (int)($receta['cantidad_resultado'] ?? 1);
    $forma = $receta['forma'] ?? 'shapeless';
    $ingredientes = $receta['ingredientes'] ?? [];

    if (empty($ingredientes)) {
        echo json_encode(['success' => false, 'error' => 'La receta debe tener al menos un ingrediente']);
        closeConnection($conn);
        exit;
    }

    // Insertar receta
    $stmt = $conn->prepare("INSERT INTO receta (id_item_resultado, cantidad_resultado, forma) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $id_item, $cantidad_resultado, $forma);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Error al insertar receta: ' . $stmt->error]);
        $stmt->close();
        closeConnection($conn);
        exit;
    }
    $id_receta = $conn->insert_id;
    $stmt->close();

    // Insertar ingredientes
    foreach ($ingredientes as $ingrediente) {
        $id_item_ingred = trim($ingrediente['id_item_ingred'] ?? '');
        $cantidad = (int)($ingrediente['cantidad'] ?? 1);
        
        if (empty($id_item_ingred)) continue;

        $stmt = $conn->prepare("INSERT INTO ingrediente_receta (id_receta, id_item_ingred, cantidad) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $id_receta, $id_item_ingred, $cantidad);
        
        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'error' => 'Error al insertar ingrediente: ' . $stmt->error]);
            $stmt->close();
            closeConnection($conn);
            exit;
        }
        $stmt->close();
    }
}

closeConnection($conn);

echo json_encode(['success' => true, 'mensaje' => 'Item y receta creados correctamente']);
?>