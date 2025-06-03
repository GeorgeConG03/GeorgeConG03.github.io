<?php
// Archivo de inicialización de sesión simple
// Incluir este archivo al principio de cada página

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función simple para verificar si el usuario está logueado
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}
?>
