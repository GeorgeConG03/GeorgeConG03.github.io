<?php
// Archivo alternativo para cierre de sesión
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Guardar mensaje para mostrar después de cerrar sesión
$mensaje = 'Has cerrado sesión correctamente.';

// Eliminar la cookie "Recordarme" si existe
if (isset($_COOKIE['fullmoon_remember'])) {
    setcookie('fullmoon_remember', '', time() - 3600, '/');
    // Intentar con diferentes configuraciones para asegurar que se elimine
    setcookie('fullmoon_remember', '', time() - 3600, '/', '', false, true);
}

// Eliminar todas las cookies de sesión posibles
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        if (strpos($name, 'fullmoon') !== false || $name == session_name()) {
            setcookie($name, '', time() - 3600, '/');
            setcookie($name, '', time() - 3600, '/', '', false, true);
        }
    }
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, borrar también la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Iniciar una nueva sesión para mostrar el mensaje
session_start();

// Mostrar mensaje de éxito
$_SESSION['success_message'] = $mensaje;

// Redireccionar al inicio
header('Location: index.php');
exit;
?>
