<?php
// Archivo: moderar-foro.php
require_once 'includes/init.php';
require_once 'includes/foros_functions.php';
require_once 'includes/auth_admin.php';

// Verificar que el usuario esté logueado
if (!is_logged_in()) {
    show_message("Debes iniciar sesión para moderar.", "error");
    redirect("ini_sec.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    $user_id = $_SESSION['user_id'];
    $es_admin_sitio = function_exists('esAdminDirecto') && esAdminDirecto($user_id);
    
    switch ($accion) {
        case 'eliminar':
        case 'ocultar':
        case 'restaurar':
            $id_post = (int)$_POST['id_post'];
            
            // Obtener información del post
            $sql_post = "SELECT fp.*, f.id_creador FROM foro_posts fp 
                         JOIN foros f ON fp.id_foro = f.id_foro 
                         WHERE fp.id_post = ?";
            $stmt_post = $conexion->prepare($sql_post);
            $stmt_post->bind_param("i", $id_post);
            $stmt_post->execute();
            $result_post = $stmt_post->get_result();
            
            if ($post_data = $result_post->fetch_assoc()) {
                $es_admin_foro = esAdminForo($post_data['id_foro'], $user_id);
                $es_propietario = ($post_data['id_usuario'] == $user_id);
                
                // Verificar permisos
                if ($es_admin_sitio || $es_admin_foro || $es_propietario) {
                    $nuevo_estado = ($accion == 'eliminar') ? 'eliminado' : 
                                   (($accion == 'ocultar') ? 'oculto' : 'activo');
                    
                    $sql_update = "UPDATE foro_posts SET estado = ? WHERE id_post = ?";
                    $stmt_update = $conexion->prepare($sql_update);
                    $stmt_update->bind_param("si", $nuevo_estado, $id_post);
                    
                    if ($stmt_update->execute()) {
                        // Actualizar contador de posts del foro
                        $sql_count = "UPDATE foros SET total_posts = (SELECT COUNT(*) FROM foro_posts WHERE id_foro = ? AND estado = 'activo') WHERE id_foro = ?";
                        $stmt_count = $conexion->prepare($sql_count);
                        $stmt_count->bind_param("ii", $post_data['id_foro'], $post_data['id_foro']);
                        $stmt_count->execute();
                        
                        show_message("Post " . $accion . " correctamente.", "success");
                    } else {
                        show_message("Error al " . $accion . " el post.", "error");
                    }
                } else {
                    show_message("No tienes permisos para realizar esta acción.", "error");
                }
            }
            break;
            
        case 'banear_usuario':
            if (!$es_admin_sitio) {
                show_message("Solo los administradores pueden banear usuarios.", "error");
                break;
            }
            
            $id_usuario_ban = (int)$_POST['id_usuario'];
            $razon = $_POST['razon'] ?? 'Violación de las normas del foro';
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
            $stmt_ban->bind_param("iiss", $id_usuario_ban, $user_id, $razon, $fecha_expiracion);
            
            if ($stmt_ban->execute()) {
                // Ocultar todos los posts del usuario baneado
                $sql_hide_posts = "UPDATE foro_posts SET estado = 'oculto' WHERE id_usuario = ?";
                $stmt_hide_posts = $conexion->prepare($sql_hide_posts);
                $stmt_hide_posts->bind_param("i", $id_usuario_ban);
                $stmt_hide_posts->execute();
                
                $mensaje = $duracion_dias > 0 ? 
                    "Usuario baneado por {$duracion_dias} días." : 
                    "Usuario baneado permanentemente.";
                show_message($mensaje, "success");
            } else {
                show_message("Error al banear usuario.", "error");
            }
            break;
    }
}

// Redirigir de vuelta
redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
?>
