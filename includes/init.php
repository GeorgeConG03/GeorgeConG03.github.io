<?php
// Archivo: includes/init.php (versión final corregida)
// Solo iniciar sesión si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la base de datos
$host = 'localhost';
$usuario_db = 'root';
$contraseña_db = 'bri_gitte_03'; // Actualizada para evitar problemas de conexión
$nombre_db = "here'stoyou";

// Crear conexión
$conexion = new mysqli($host, $usuario_db, $contraseña_db, $nombre_db);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar charset
$conexion->set_charset("utf8");

// Verificar y crear columna es_admin si no existe
$check_column = "SHOW COLUMNS FROM usuarios LIKE 'es_admin'";
$result_check = $conexion->query($check_column);

if ($result_check && $result_check->num_rows == 0) {
    $add_column = "ALTER TABLE usuarios ADD COLUMN es_admin TINYINT(1) DEFAULT 0";
    $conexion->query($add_column);
    
    // Asegurar que el usuario ID 1 sea admin
    $update_admin = "UPDATE usuarios SET es_admin = 1 WHERE id_usuario = 1";
    $conexion->query($update_admin);
}

// Definir constantes si no están definidas
if (!defined('DEFAULT_AVATAR')) {
    define('DEFAULT_AVATAR', 'assets/img/avatars/default.png');
}

if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 30 * 24 * 60 * 60); // 30 días en segundos
}

// Incluir funciones solo si no están ya incluidas
if (!function_exists('is_logged_in')) {
    include_once __DIR__ . '/functions.php';
}

// Función para verificar si un usuario es administrador (específica para este sistema)
function esAdminDirecto($id_usuario) {
    global $conexion;
    
    $sql = "SELECT es_admin FROM usuarios WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['es_admin'] == 1;
    }
    
    return false;
}
?>
