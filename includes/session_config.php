<?php
/**
 * Configuración de sesiones para el sitio
 * Este archivo debe ser incluido en init.php
 */

// Configurar parámetros de sesión antes de iniciar la sesión
// Duración de la sesión: 30 días (en segundos)
$session_lifetime = 30 * 24 * 60 * 60; // 30 días

// Configurar parámetros de cookie
ini_set('session.cookie_lifetime', $session_lifetime);
ini_set('session.gc_maxlifetime', $session_lifetime);

// Configurar el uso de cookies para las sesiones
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);

// Configurar cookies seguras si estamos en HTTPS
$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
$httponly = true; // Previene acceso a la cookie desde JavaScript
$samesite = 'Lax'; // Previene CSRF

// Configurar parámetros de cookie
ini_set('session.cookie_secure', $secure);
ini_set('session.cookie_httponly', $httponly);

// Para PHP 7.3.0 o superior
if (PHP_VERSION_ID >= 70300) {
    ini_set('session.cookie_samesite', $samesite);
}

// Configurar el nombre de la sesión
session_name('fullmoon_session');

// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Regenerar el ID de sesión periódicamente para prevenir ataques de fijación de sesión
if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 3600) {
    // Regenerar el ID de sesión cada hora
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Función para implementar "Recordarme" con cookies
function remember_me($user_id, $remember_token) {
    $cookie_name = 'fullmoon_remember';
    $cookie_time = time() + (30 * 24 * 60 * 60); // 30 días
    
    // Crear un token seguro
    $token_value = $user_id . ':' . $remember_token;
    
    // Establecer la cookie
    setcookie(
        $cookie_name,
        $token_value,
        $cookie_time,
        '/',
        '',
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        true
    );
}

// Función para verificar la cookie "Recordarme"
function check_remember_cookie() {
    global $conexion;
    
    if (isset($_COOKIE['fullmoon_remember']) && !is_logged_in()) {
        $cookie_value = $_COOKIE['fullmoon_remember'];
        
        // Verificar que el valor de la cookie tiene el formato correcto
        if (strpos($cookie_value, ':') === false) {
            setcookie('fullmoon_remember', '', time() - 3600, '/');
            return false;
        }
        
        list($user_id, $token) = explode(':', $cookie_value);
        
        // Validar el token en la base de datos
        $query = "SELECT * FROM usuarios WHERE id_usuario = ? AND remember_token = ?";
        $stmt = $conexion->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("is", $user_id, $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Iniciar sesión para el usuario
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['username'] = $user['nombre'];
                
                // Actualizar el token para mayor seguridad
                $new_token = bin2hex(random_bytes(32));
                $update_query = "UPDATE usuarios SET remember_token = ? WHERE id_usuario = ?";
                $update_stmt = $conexion->prepare($update_query);
                
                if ($update_stmt) {
                    $update_stmt->bind_param("si", $new_token, $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    // Actualizar la cookie con el nuevo token
                    remember_me($user_id, $new_token);
                }
                
                return true;
            }
            
            $stmt->close();
        }
        
        // Si llegamos aquí, la cookie no es válida, eliminarla
        setcookie('fullmoon_remember', '', time() - 3600, '/');
    }
    
    return false;
}

// Verificar si el usuario está logueado
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Verificar la cookie "Recordarme" al cargar la página
// Solo si la función is_logged_in está definida
if (function_exists('is_logged_in')) {
    check_remember_cookie();
}
?>
