<?php
// includes/config.php
// Configuración de la base de datos

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Akme2211AK47');  // Deja vacío si no tiene contraseña
define('DB_NAME', 'minecraft_proyect');

// Crear conexión
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Establecer charset a UTF-8
    $conn->set_charset("utf8");
    
    return $conn;
}

// Función para cerrar la conexión
function closeConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>
