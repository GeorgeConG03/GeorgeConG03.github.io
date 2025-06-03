<?php
// Archivo: moderar-comentario.php
require_once 'includes/init.php';
require_once 'includes/auth_admin.php';

// Solo administradores pueden acceder
verificarAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    $id_comentario = (int)$_POST['id_comentario'];
    $admin_id = $_SESSION['user_id'];
    
    switch ($accion) {
        case 'eliminar':
            $sql = "UPDATE comentarios SET estado = 'eliminado' WHERE id_comentario = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id_comentario);
            
            if ($stmt->execute()) {
                show_message("Comentario eliminado correctamente.", "success");
            } else {
                show_message("Error al eliminar comentario.", "error");
            }
            break;
            
        case 'banear_usuario':
            // Obtener el ID del usuario del comentario
            $sql_user = "SELECT id_usuario FROM comentarios WHERE id_comentario = ?";
            $stmt_user = $conexion->prepare($sql_user);
            $stmt_user->bind_param("i", $id_comentario);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();
            
            if ($user_data = $result_user->fetch_assoc()) {
                $user_to_ban = $user_data['id_usuario'];
                $razon = $_POST['razon'] ?? 'Violación de las normas de la comunidad';
                $duracion_dias = (int)($_POST['duracion_dias'] ?? 7);
                
                // Calcular fecha de expiración
                $fecha_expiracion = null;
                if ($duracion_dias > 0) {
                    $fecha_expiracion = date('Y-m-d H:i:s', strtotime("+{$duracion_dias} days"));
                }
                
                // Insertar ban
                $sql_ban = "INSERT INTO usuarios_baneados (id_usuario, id_admin, razon, fecha_expiracion) 
                           VALUES (?, ?, ?, ?)";
                $stmt_ban = $conexion->prepare($sql_ban);
                $stmt_ban->bind_param("iiss", $user_to_ban, $admin_id, $razon, $fecha_expiracion);
                
                if ($stmt_ban->execute()) {
                    // Marcar todos los comentarios del usuario como baneados
                    $sql_ban_comments = "UPDATE comentarios SET estado = 'baneado' WHERE id_usuario = ?";
                    $stmt_ban_comments = $conexion->prepare($sql_ban_comments);
                    $stmt_ban_comments->bind_param("i", $user_to_ban);
                    $stmt_ban_comments->execute();
                    
                    $mensaje = $duracion_dias > 0 ? 
                        "Usuario baneado por {$duracion_dias} días." : 
                        "Usuario baneado permanentemente.";
                    show_message($mensaje, "success");
                } else {
                    show_message("Error al banear usuario.", "error");
                }
            }
            break;
            
        case 'restaurar':
            $sql = "UPDATE comentarios SET estado = 'activo' WHERE id_comentario = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $id_comentario);
            
            if ($stmt->execute()) {
                show_message("Comentario restaurado correctamente.", "success");
            } else {
                show_message("Error al restaurar comentario.", "error");
            }
            break;
    }
}

// Redirigir de vuelta
redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
?>
