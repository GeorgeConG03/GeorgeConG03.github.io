<?php
// Archivo: includes/comentarios_functions.php
// Funciones para el sistema de comentarios

// Obtener comentarios de una reseña con sus reacciones
if (!function_exists('obtenerComentarios')) {
    function obtenerComentarios($id_resena, $id_comentario_padre = null) {
        global $conexion;
        
        // Consulta base para obtener comentarios
        $sql = "SELECT c.*, u.nombre as nombre_usuario, 
                       (SELECT avatar FROM perfiles WHERE id_usuario = c.id_usuario LIMIT 1) as avatar,
                       (SELECT COUNT(*) FROM comentario_reacciones WHERE id_comentario = c.id_comentario AND tipo = 'like') as likes,
                       (SELECT COUNT(*) FROM comentario_reacciones WHERE id_comentario = c.id_comentario AND tipo = 'dislike') as dislikes
                FROM comentarios c 
                LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario 
                WHERE c.id_resena = ? AND c.estado = 'activo'";
        
        if ($id_comentario_padre === null) {
            $sql .= " AND c.id_comentario_padre IS NULL";
        } else {
            $sql .= " AND c.id_comentario_padre = ?";
        }
        
        $sql .= " ORDER BY c.fecha_creacion ASC";
        
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            // Si hay un error en la preparación, devolver un array vacío
            error_log("Error en la consulta SQL: " . $conexion->error);
            return [];
        }
        
        if ($id_comentario_padre === null) {
            $stmt->bind_param("i", $id_resena);
        } else {
            $stmt->bind_param("ii", $id_resena, $id_comentario_padre);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $comentarios = [];
        while ($row = $result->fetch_assoc()) {
            $comentarios[] = $row;
        }
        
        return $comentarios;
    }
}

// Obtener la reacción del usuario actual a un comentario
if (!function_exists('obtenerReaccionUsuario')) {
    function obtenerReaccionUsuario($id_comentario, $user_id) {
        global $conexion;
        
        if (!$user_id) return null;
        
        $sql = "SELECT tipo FROM comentario_reacciones WHERE id_comentario = ? AND id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            error_log("Error en la consulta SQL: " . $conexion->error);
            return null;
        }
        
        $stmt->bind_param("ii", $id_comentario, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['tipo'];
        }
        
        return null;
    }
}

// Verificar si un usuario puede moderar comentarios
if (!function_exists('puedeModerar')) {
    function puedeModerar($user_id, $comment_user_id) {
        // El usuario puede moderar si es admin o es el propietario del comentario
        return (function_exists('esAdminDirecto') && esAdminDirecto($user_id)) || 
               ($user_id == $comment_user_id);
    }
}

// Renderizar un comentario con todas sus funcionalidades
if (!function_exists('renderizarComentario')) {
    function renderizarComentario($comentario, $id_resena, $nivel = 0) {
        $user_id = $_SESSION['user_id'] ?? null;
        $es_admin = $user_id && function_exists('esAdminDirecto') && esAdminDirecto($user_id);
        $puede_moderar = puedeModerar($user_id, $comentario['id_usuario']);
        $reaccion_usuario = obtenerReaccionUsuario($comentario['id_comentario'], $user_id);
        
        $avatar = $comentario['avatar'] ?: 'assets/img/avatars/default.png';
        $margen_izquierdo = $nivel * 40; // Indentación para respuestas
        
        echo "<div class='comment-item' style='margin-left: {$margen_izquierdo}px;' data-comment-id='{$comentario['id_comentario']}'>";
        
        // Header del comentario
        echo "<div class='comment-header'>";
        echo "<img src='{$avatar}' alt='Avatar' class='comment-avatar'>";
        echo "<div class='comment-info'>";
        echo "<span class='comment-author'>";
        echo "<a href='perfil.php?id={$comentario['id_usuario']}' class='author-link'>";
        echo htmlspecialchars($comentario['nombre_usuario'] ?: 'Usuario Anónimo');
        echo "</a></span>";
        
        if ($comentario['calificacion']) {
            echo "<div class='comment-rating'>";
            for ($i = 1; $i <= 5; $i++) {
                echo $i <= $comentario['calificacion'] ? "<span class='star filled'>★</span>" : "<span class='star'>☆</span>";
            }
            echo "</div>";
        }
        
        echo "<span class='comment-date'>" . time_elapsed_string($comentario['fecha_creacion']) . "</span>";
        echo "</div></div>";
        
        // Contenido del comentario
        echo "<div class='comment-content'>";
        echo nl2br(htmlspecialchars($comentario['texto']));
        echo "</div>";
        
        // Botones de reacción y acciones
        echo "<div class='comment-actions'>";
        
        if ($user_id) {
            // Botones de like/dislike
            $like_class = $reaccion_usuario === 'like' ? 'active' : '';
            $dislike_class = $reaccion_usuario === 'dislike' ? 'active' : '';
            
            echo "<button class='reaction-btn like-btn {$like_class}' onclick='reaccionar({$comentario['id_comentario']}, \"like\")'>";
            echo "👍 <span class='like-count'>{$comentario['likes']}</span>";
            echo "</button>";
            
            echo "<button class='reaction-btn dislike-btn {$dislike_class}' onclick='reaccionar({$comentario['id_comentario']}, \"dislike\")'>";
            echo "👎 <span class='dislike-count'>{$comentario['dislikes']}</span>";
            echo "</button>";
            
            // Botón de responder (solo para comentarios principales)
            if ($nivel === 0) {
                echo "<button class='reply-btn' onclick='mostrarFormularioRespuesta({$comentario['id_comentario']})'>Responder</button>";
            }
        }
        
        // Acciones de moderación
        if ($puede_moderar) {
            echo "<a href='editar-comentario.php?id={$comentario['id_comentario']}' class='edit-btn'>Editar</a>";
        }
        
        if ($es_admin) {
            echo "<button class='moderate-btn' onclick='mostrarOpcionesModeracion({$comentario['id_comentario']}, {$comentario['id_usuario']})'>Moderar</button>";
        }
        
        echo "</div>";
        
        // Formulario de respuesta (oculto por defecto)
        if ($user_id && $nivel === 0) {
            echo "<div class='reply-form' id='reply-form-{$comentario['id_comentario']}' style='display: none;'>";
            echo "<form action='procesar-comentario.php' method='post'>";
            echo "<input type='hidden' name='id_resena' value='{$id_resena}'>";
            echo "<input type='hidden' name='id_comentario_padre' value='{$comentario['id_comentario']}'>";
            echo "<input type='hidden' name='csrf_token' value='" . generate_csrf_token() . "'>";
            echo "<textarea name='comentario' placeholder='Escribe tu respuesta...' rows='3' required></textarea>";
            echo "<div class='reply-form-actions'>";
            echo "<button type='submit' class='btn btn-primary'>Responder</button>";
            echo "<button type='button' class='btn btn-secondary' onclick='ocultarFormularioRespuesta({$comentario['id_comentario']})'>Cancelar</button>";
            echo "</div>";
            echo "</form>";
            echo "</div>";
        }
        
        echo "</div>";
        
        // Mostrar respuestas
        $respuestas = obtenerComentarios($id_resena, $comentario['id_comentario']);
        foreach ($respuestas as $respuesta) {
            renderizarComentario($respuesta, $id_resena, $nivel + 1);
        }
    }
}

// Función para calcular tiempo transcurrido si no existe
if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'año',
            'm' => 'mes',
            'w' => 'semana',
            'd' => 'día',
            'h' => 'hora',
            'i' => 'minuto',
            's' => 'segundo',
        );
        
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? ($k == 'm' ? 'es' : 's') : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? 'hace ' . implode(', ', $string) : 'justo ahora';
    }
}
?>
