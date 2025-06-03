<?php
// Archivo: includes/auth_admin.php (versión corregida)
// Funciones para verificar permisos de administrador

function esAdmin($id_usuario) {
    global $conexion;
    
    // Debug: mostrar qué usuario se está verificando
    error_log("Verificando admin para usuario ID: " . $id_usuario);
    
    // Verificar si la columna es_admin existe
    $check_column = "SHOW COLUMNS FROM usuarios LIKE 'es_admin'";
    $result_check = $conexion->query($check_column);
    
    if ($result_check->num_rows == 0) {
        // Si no existe la columna, agregarla
        $add_column = "ALTER TABLE usuarios ADD COLUMN es_admin TINYINT(1) DEFAULT 0";
        $conexion->query($add_column);
        
        // Convertir usuario ID 1 en admin
        $update_admin = "UPDATE usuarios SET es_admin = 1 WHERE id_usuario = 1";
        $conexion->query($update_admin);
        
        error_log("Columna es_admin creada y usuario 1 configurado como admin");
    }
    
    $sql = "SELECT es_admin FROM usuarios WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        error_log("Error preparando consulta: " . $conexion->error);
        return false;
    }
    
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $es_admin = $row['es_admin'] == 1;
        error_log("Usuario $id_usuario - es_admin: " . ($es_admin ? 'SÍ' : 'NO'));
        return $es_admin;
    }
    
    error_log("Usuario $id_usuario no encontrado");
    return false;
}

function verificarAdmin() {
    // Verificar ambas posibles variables de sesión
    $user_id = null;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } elseif (isset($_SESSION['usuario_id'])) {
        $user_id = $_SESSION['usuario_id'];
    }
    
    if (!$user_id) {
        error_log("No hay sesión activa");
        header("Location: login.php?error=sesion_requerida");
        exit();
    }
    
    if (!esAdmin($user_id)) {
        error_log("Usuario " . $user_id . " no es administrador");
        header("Location: index.php?error=acceso_denegado");
        exit();
    }
    
    error_log("Acceso de admin autorizado para usuario " . $user_id);
}

function mostrarMenuAdmin() {
    // Verificar ambas posibles variables de sesión
    $user_id = null;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } elseif (isset($_SESSION['usuario_id'])) {
        $user_id = $_SESSION['usuario_id'];
    }
    
    if ($user_id && esAdmin($user_id)) {
        return true;
    }
    return false;
}
?>
