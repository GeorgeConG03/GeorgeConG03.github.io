<?php
// Archivo: includes/foros_functions.php
// Funciones para el sistema de foros

// Obtener información completa de un foro
if (!function_exists('obtenerInfoForo')) {
    function obtenerInfoForo($id_foro) {
        global $conexion;
        
        $sql = "SELECT f.*, u.nombre as nombre_creador 
                FROM foros f 
                LEFT JOIN usuarios u ON f.id_creador = u.id_usuario 
                WHERE f.id_foro = ? AND f.activo = 1";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            error_log("Error en obtenerInfoForo: " . $conexion->error);
            return false;
        }
        
        $stmt->bind_param("i", $id_foro);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
}

// Verificar si un usuario es miembro de un foro
if (!function_exists('esMiembroForo')) {
    function esMiembroForo($id_foro, $user_id) {
        global $conexion;
        
        $sql = "SELECT id_miembro FROM foro_miembros WHERE id_foro = ? AND id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("ii", $id_foro, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
}

// Verificar si un usuario es admin de un foro
if (!function_exists('esAdminForo')) {
    function esAdminForo($id_foro, $user_id) {
        global $conexion;
        
        $sql = "SELECT rol FROM foro_miembros WHERE id_foro = ? AND id_usuario = ? AND rol IN ('admin', 'moderador')";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("ii", $id_foro, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
}

// Unirse a un foro
if (!function_exists('unirseAForo')) {
    function unirseAForo($id_foro, $user_id) {
        global $conexion;
        
        // Verificar si ya es miembro
        if (esMiembroForo($id_foro, $user_id)) {
            return false;
        }
        
        $sql = "INSERT INTO foro_miembros (id_foro, id_usuario) VALUES (?, ?)";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("ii", $id_foro, $user_id);
        
        if ($stmt->execute()) {
            // Actualizar contador de miembros
            $sql_update = "UPDATE foros SET total_miembros = (SELECT COUNT(*) FROM foro_miembros WHERE id_foro = ?) WHERE id_foro = ?";
            $stmt_update = $conexion->prepare($sql_update);
            $stmt_update->bind_param("ii", $id_foro, $id_foro);
            $stmt_update->execute();
            
            return true;
        }
        
        return false;
    }
}

// Salir de un foro
if (!function_exists('salirDeForo')) {
    function salirDeForo($id_foro, $user_id) {
        global $conexion;
        
        // No permitir que el creador salga del foro
        $sql_check = "SELECT id_creador FROM foros WHERE id_foro = ?";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bind_param("i", $id_foro);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $foro = $result_check->fetch_assoc();
        
        if ($foro['id_creador'] == $user_id) {
            return false; // El creador no puede salir
        }
        
        $sql = "DELETE FROM foro_miembros WHERE id_foro = ? AND id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("ii", $id_foro, $user_id);
        
        if ($stmt->execute()) {
            // Actualizar contador de miembros
            $sql_update = "UPDATE foros SET total_miembros = (SELECT COUNT(*) FROM foro_miembros WHERE id_foro = ?) WHERE id_foro = ?";
            $stmt_update = $conexion->prepare($sql_update);
            $stmt_update->bind_param("ii", $id_foro, $id_foro);
            $stmt_update->execute();
            
            return true;
        }
        
        return false;
    }
}

// Obtener posts de un foro
if (!function_exists('obtenerPostsForo')) {
    function obtenerPostsForo($id_foro, $id_post_padre = null) {
        global $conexion;
        
        $sql = "SELECT p.*, u.nombre as nombre_usuario, 
                       (SELECT avatar FROM perfiles WHERE id_usuario = p.id_usuario LIMIT 1) as avatar,
                       (SELECT COUNT(*) FROM foro_post_likes WHERE id_post = p.id_post AND tipo = 'like') as likes,
                       (SELECT COUNT(*) FROM foro_post_likes WHERE id_post = p.id_post AND tipo = 'dislike') as dislikes,
                       fm.rol as rol_foro
                FROM foro_posts p 
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                LEFT JOIN foro_miembros fm ON p.id_foro = fm.id_foro AND p.id_usuario = fm.id_usuario
                WHERE p.id_foro = ? AND p.estado = 'activo'";
        
        if ($id_post_padre === null) {
            $sql .= " AND p.id_post_padre IS NULL";
        } else {
            $sql .= " AND p.id_post_padre = ?";
        }
        
        $sql .= " ORDER BY p.es_hilo_principal DESC, p.fecha_creacion DESC";
        
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            error_log("Error en obtenerPostsForo: " . $conexion->error);
            return [];
        }
        
        if ($id_post_padre === null) {
            $stmt->bind_param("i", $id_foro);
        } else {
            $stmt->bind_param("ii", $id_foro, $id_post_padre);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
        
        return $posts;
    }
}

// Obtener reacción del usuario a un post
if (!function_exists('obtenerReaccionPost')) {
    function obtenerReaccionPost($id_post, $user_id) {
        global $conexion;
        
        if (!$user_id) return null;
        
        $sql = "SELECT tipo FROM foro_post_likes WHERE id_post = ? AND id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param("ii", $id_post, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['tipo'];
        }
        
        return null;
    }
}

// Renderizar un post del foro
if (!function_exists('renderizarPost')) {
    function renderizarPost($post, $es_miembro, $es_admin_foro, $es_admin_sitio, $nivel = 0) {
        $user_id = $_SESSION['user_id'] ?? null;
        $puede_moderar = $es_admin_sitio || $es_admin_foro || ($user_id == $post['id_usuario']);
        $reaccion_usuario = obtenerReaccionPost($post['id_post'], $user_id);
        
        $avatar = $post['avatar'] ?: '../assets/img/avatars/default.png';
        $margen_izquierdo = $nivel * 30;
        
        // Determinar badges del usuario
        $badges = [];
        if ($es_admin_sitio) {
            $badges[] = '<span class="badge badge-admin">👑 ADMIN</span>';
        }
        if ($post['rol_foro'] == 'admin') {
            $badges[] = '<span class="badge badge-foro-admin">🛡️ ADMIN FORO</span>';
        } elseif ($post['rol_foro'] == 'moderador') {
            $badges[] = '<span class="badge badge-moderador">🔨 MOD</span>';
        }
        
        echo "<div class='post-item' style='margin-left: {$margen_izquierdo}px;' data-post-id='{$post['id_post']}'>";
        
        // Header del post
        echo "<div class='post-header'>";
        echo "<img src='{$avatar}' alt='Avatar' class='post-avatar'>";
        echo "<div class='post-info'>";
        echo "<div class='post-author-line'>";
        echo "<a href='../perfil.php?id={$post['id_usuario']}' class='post-author'>";
        echo htmlspecialchars($post['nombre_usuario'] ?: 'Usuario Anónimo');
        echo "</a>";
        
        // Mostrar badges
        foreach ($badges as $badge) {
            echo " " . $badge;
        }
        
        echo "</div>";
        
        if ($post['es_hilo_principal']) {
            echo "<span class='hilo-principal-badge'>📌 HILO PRINCIPAL</span>";
        }
        
        echo "<span class='post-date'>" . time_elapsed_string($post['fecha_creacion']) . "</span>";
        echo "</div></div>";
        
        // Título del post (si existe)
        if ($post['titulo']) {
            echo "<h3 class='post-titulo'>" . htmlspecialchars($post['titulo']) . "</h3>";
        }
        
        // Contenido del post
        echo "<div class='post-content'>";
        echo nl2br(htmlspecialchars($post['contenido']));
        echo "</div>";
        
        // Imagen del post (si existe)
        if ($post['imagen']) {
            echo "<div class='post-imagen'>";
            echo "<img src='../{$post['imagen']}' alt='Imagen del post' class='post-img'>";
            echo "</div>";
        }
        
        // Acciones del post
        echo "<div class='post-actions'>";
        
        if ($es_miembro) {
            // Botones de like/dislike
            $like_class = $reaccion_usuario === 'like' ? 'active' : '';
            $dislike_class = $reaccion_usuario === 'dislike' ? 'active' : '';
            
            echo "<button class='reaction-btn like-btn {$like_class}' onclick='reaccionarPost({$post['id_post']}, \"like\")'>";
            echo "👍 <span class='like-count'>{$post['likes']}</span>";
            echo "</button>";
            
            echo "<button class='reaction-btn dislike-btn {$dislike_class}' onclick='reaccionarPost({$post['id_post']}, \"dislike\")'>";
            echo "👎 <span class='dislike-count'>{$post['dislikes']}</span>";
            echo "</button>";
            
            // Botón de responder
            echo "<button class='reply-btn' onclick='mostrarFormularioRespuestaPost({$post['id_post']})'>💬 Responder</button>";
        }
        
        // Acciones de moderación
        if ($puede_moderar) {
            echo "<button class='moderate-btn' onclick='mostrarOpcionesModeracionPost({$post['id_post']}, {$post['id_usuario']})'>⚙️ Moderar</button>";
        }
        
        echo "</div>";
        
        // Formulario de respuesta (oculto por defecto)
        if ($es_miembro) {
            echo "<div class='reply-form-post' id='reply-form-post-{$post['id_post']}' style='display: none;'>";
            echo "<form action='../procesar-foro-post.php' method='post' enctype='multipart/form-data'>";
            echo "<input type='hidden' name='id_foro' value='{$post['id_foro']}'>";
            echo "<input type='hidden' name='id_post_padre' value='{$post['id_post']}'>";
            echo "<input type='hidden' name='csrf_token' value='" . generate_csrf_token() . "'>";
            echo "<textarea name='contenido' placeholder='Escribe tu respuesta...' rows='3' required></textarea>";
            echo "<input type='file' name='imagen' accept='image/*' style='margin: 0.5rem 0;'>";
            echo "<div class='reply-form-actions'>";
            echo "<button type='submit' class='btn btn-primary'>Responder</button>";
            echo "<button type='button' class='btn btn-secondary' onclick='ocultarFormularioRespuestaPost({$post['id_post']})'>Cancelar</button>";
            echo "</div>";
            echo "</form>";
            echo "</div>";
        }
        
        echo "</div>";
        
        // Mostrar respuestas
        $respuestas = obtenerPostsForo($post['id_foro'], $post['id_post']);
        foreach ($respuestas as $respuesta) {
            renderizarPost($respuesta, $es_miembro, $es_admin_foro, $es_admin_sitio, $nivel + 1);
        }
    }
}

// Obtener foros unidos por un usuario
if (!function_exists('obtenerForosUsuario')) {
    function obtenerForosUsuario($user_id) {
        global $conexion;
        
        $sql = "SELECT f.*, u.nombre as nombre_creador, fm.fecha_union, fm.rol
                FROM foros f 
                INNER JOIN foro_miembros fm ON f.id_foro = fm.id_foro
                LEFT JOIN usuarios u ON f.id_creador = u.id_usuario
                WHERE fm.id_usuario = ? AND f.activo = 1
                ORDER BY fm.fecha_union DESC";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $foros = [];
        while ($row = $result->fetch_assoc()) {
            $foros[] = $row;
        }
        
        return $foros;
    }
}

// Obtener todos los foros para mostrar en index
if (!function_exists('obtenerTodosLosForos')) {
    function obtenerTodosLosForos($limite = 10) {
        global $conexion;
        
        $sql = "SELECT f.*, u.nombre as nombre_creador
                FROM foros f 
                LEFT JOIN usuarios u ON f.id_creador = u.id_usuario
                WHERE f.activo = 1
                ORDER BY f.fecha_creacion DESC
                LIMIT ?";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            return [];
        }
        
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $foros = [];
        while ($row = $result->fetch_assoc()) {
            $foros[] = $row;
        }
        
        return $foros;
    }
}
?>
