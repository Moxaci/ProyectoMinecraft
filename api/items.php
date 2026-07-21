<?php
// api/items.php
// Obtiene todos los items del catálogo

session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    sendErrorResponse('No autenticado', 401);
}

$conn = getConnection();
$items = getAllItems($conn);
closeConnection($conn);

sendSuccessResponse([
    'success' => true,
    'items' => $items
]);
?>